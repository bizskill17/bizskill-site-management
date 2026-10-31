<?php
if (!defined('ABSPATH')) exit;

/** MessageAutoSender outbox. All stored notification dates are UTC; schedules use IST. */
trait BSM_Notifications {
    private function nt_catalog() {
        return [
            'requisition_submitted'=>['Requisition Submitted','site','instant'],
            'requisition_approved'=>['Requisition Approved','site','instant'],
            'pending_requisition'=>['Pending Requisition Approval','site','scheduled'],
            'pending_dispatch'=>['Pending Material Dispatch','site','scheduled'],
            'material_dispatched'=>['Material Dispatched','site','instant'],
            'pending_receipt'=>['Pending Site Material Receipt','site','scheduled'],
            'site_received'=>['Site Receipt Added','site','instant'],
            'labour_attendance'=>['Site Labour Attendance','site','daily'],
            'site_timesheet'=>['Site Daily Timesheet','site','daily'],
            'office_timesheet'=>['Daily Employee Timesheet','office','daily'],
            'pending_po'=>['PO Made but Not Received','office','scheduled'],
            'po_received'=>['PO Material Received','office','instant'],
            'pending_po_approval'=>['Pending PO Approval','office','scheduled'],
            'po_approved'=>['PO Approved','office','instant']
        ];
    }
    public function notifications_boot() {
        add_filter('cron_schedules',function($s){$s['bsm_five_minutes']=['interval'=>300,'display'=>'Every 5 minutes'];return $s;});
        add_action('init',[$this,'notifications_upgrade'],20);
        add_action('bsm_notifications_tick',[$this,'notifications_tick']);
        add_action('wp_ajax_bsm_notifications',[$this,'ajax_notifications']);
        // The observer reads committed records after AJAX mutations; it never sends HTTP here.
        add_action('shutdown',function(){if(defined('DOING_AJAX')&&DOING_AJAX&&strpos((string)($_POST['action']??''),'bsm_')===0&&get_option('bsm_notifications_schema')==='1'){
            try{$this->nt_scan();}catch(Throwable $e){error_log('BSM notification observer failed.');}
        }});
    }
    public function notifications_upgrade() {
        global $wpdb;$p=$this->prefix;
        if(get_option('bsm_notifications_schema')!=='1'){
            $this->migrate_purchase_orders();
            require_once ABSPATH.'wp-admin/includes/upgrade.php';$c=$wpdb->get_charset_collate();
            dbDelta("CREATE TABLE {$p}notification_queue (
                id bigint unsigned NOT NULL AUTO_INCREMENT,
                event_key varchar(64) NOT NULL,
                notification_type varchar(50) NOT NULL,
                site_id bigint unsigned NOT NULL DEFAULT 0,
                group_id varchar(190) NOT NULL DEFAULT '',
                reference_no varchar(190) NOT NULL DEFAULT '',
                message longtext NOT NULL,
                attachment_url text NULL,
                status varchar(30) NOT NULL DEFAULT 'queued',
                attempts int unsigned NOT NULL DEFAULT 0,
                cycle_attempts int unsigned NOT NULL DEFAULT 0,
                history longtext NULL,
                next_attempt datetime NOT NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                response text NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY event_key (event_key),
                KEY due (status,next_attempt)
            ) ENGINE=InnoDB $c;");
            dbDelta("CREATE TABLE {$p}notification_state (
                record_key varchar(100) NOT NULL,
                state varchar(80) NOT NULL,
                PRIMARY KEY  (record_key)
            ) ENGINE=InnoDB $c;");
            if(!$wpdb->get_var("SHOW COLUMNS FROM {$p}sites LIKE 'whatsapp_group_id'"))$wpdb->query("ALTER TABLE {$p}sites ADD whatsapp_group_id varchar(190) NOT NULL DEFAULT ''");
            foreach(['approved_by'=>'bigint unsigned NULL','approved_at'=>'datetime NULL','rejection_reason'=>'text NULL'] as $column=>$definition){if(!$wpdb->get_var("SHOW COLUMNS FROM {$p}purchase_orders LIKE '$column'"))$wpdb->query("ALTER TABLE {$p}purchase_orders ADD $column $definition");}
            if($wpdb->get_var("SHOW TABLES LIKE '{$p}notification_queue'")&&$wpdb->get_var("SHOW TABLES LIKE '{$p}notification_state'")&&$wpdb->get_var("SHOW COLUMNS FROM {$p}sites LIKE 'whatsapp_group_id'")&&$wpdb->get_var("SHOW COLUMNS FROM {$p}purchase_orders LIKE 'approved_at'")){
                $this->nt_scan(true);update_option('bsm_notifications_schema','1',false);
            }
        }
        if(get_option('bsm_notifications_schema')==='1'&&get_option('bsm_notification_attachments_schema')!=='1'){
            if(!$wpdb->get_var("SHOW COLUMNS FROM {$p}notification_queue LIKE 'attachment_url'"))$wpdb->query("ALTER TABLE {$p}notification_queue ADD attachment_url text NULL");
            if($wpdb->get_var("SHOW COLUMNS FROM {$p}notification_queue LIKE 'attachment_url'"))update_option('bsm_notification_attachments_schema','1',false);
        }
        if(get_option('bsm_notifications_schema')==='1'){
            // Replace the old one-minute event without accumulating duplicate cron entries.
            $scheduled=wp_get_scheduled_event('bsm_notifications_tick');
            if($scheduled&&($scheduled->schedule!=='bsm_five_minutes'||(int)$scheduled->interval!==300)){
                wp_clear_scheduled_hook('bsm_notifications_tick');$scheduled=false;
            }
            if(!$scheduled)wp_schedule_event(time()+300,'bsm_five_minutes','bsm_notifications_tick');
        }
    }
    private function nt_settings(){
        $s=get_option('bsm_notification_settings',[]);return array_merge(['enabled'=>false,'username'=>'','password'=>'','office_group'=>'','types'=>[],'times'=>[],'days'=>[1,2,3,4,5,6],'include_holidays'=>true,'retention'=>90],is_array($s)?$s:[]);
    }
    private function nt_admin(){return current_user_can('manage_options');}
    private function nt_encrypt($password){
        if(!function_exists('openssl_encrypt'))throw new Exception('OpenSSL is required to save notification credentials.');
        $iv=random_bytes(12);$tag='';$cipher=openssl_encrypt($password,'aes-256-gcm',hash('sha256',wp_salt('auth'),true),OPENSSL_RAW_DATA,$iv,$tag);
        if($cipher===false)throw new Exception('Unable to encrypt password.');return base64_encode($iv.$tag.$cipher);
    }
    private function nt_password($encrypted){
        if(!$encrypted)return '';if(!function_exists('openssl_decrypt'))return '';$data=base64_decode($encrypted,true);if($data===false||strlen($data)<29)return '';
        return openssl_decrypt(substr($data,28),'aes-256-gcm',hash('sha256',wp_salt('auth'),true),OPENSSL_RAW_DATA,substr($data,0,12),substr($data,12,16))?:'';
    }
    private function nt_now(){return gmdate('Y-m-d H:i:s');}
    private function nt_date($value){return $value?date('d/m/Y',strtotime(substr($value,0,10).' UTC')):'';}
    private function nt_num($value){return rtrim(rtrim(number_format((float)$value,2,'.',''),'0'),'.');}
    private function nt_name($id,$wp=false){
        global $wpdb;$id=(int)$id;if(!$id)return '';
        $name=$wpdb->get_var($wpdb->prepare('SELECT name FROM '.$this->prefix.'users WHERE '.($wp?'wp_user_id':'id').'=%d LIMIT 1',$id));
        if(!$name&&$wp){$u=get_userdata($id);$name=$u?$u->display_name:'';}return (string)$name;
    }
    private function nt_label($table,$column,$id){
        global $wpdb;return (string)$wpdb->get_var($wpdb->prepare("SELECT $column FROM {$this->prefix}$table WHERE id=%d",(int)$id));
    }
    private function nt_site($id){return $this->nt_label('sites','site_name',$id);}
    private function nt_group($site){$s=$this->nt_settings();return $site?$this->nt_label('sites','whatsapp_group_id',$site):$s['office_group'];}
    private function nt_optional($label,$value){return trim((string)$value)!==''?"\n$label: ".trim((string)$value):'';}
    private function nt_parts($message,$limit=2800){
        // Split on complete lines, with a bounded fallback for an unusually long activity.
        $parts=[];$current='';foreach(explode("\n",$message) as $line){
            while(strlen($line)>$limit){if($current!==''){$parts[]=$current;$current='';}$chunk=function_exists('mb_strcut')?mb_strcut($line,0,$limit,'UTF-8'):substr($line,0,$limit);while($chunk!==''&&!preg_match('//u',$chunk))$chunk=substr($chunk,0,-1);if($chunk==='')$chunk=substr($line,0,$limit);$parts[]=$chunk;$line=substr($line,strlen($chunk));}
            if(strlen($current)+strlen($line)+1>$limit){$parts[]=$current;$current='';}$current.=($current!==''?"\n":'').$line;
        }if($current!=='')$parts[]=$current;return $parts;
    }
    private function nt_queue($type,$sites,$reference,$message,$event,$test=false,$attachment_url=''){
        global $wpdb;$s=$this->nt_settings();if(!$test&&(!$s['enabled']||empty($s['types'][$type])))return;
        $destinations=[];foreach(array_unique(array_map('intval',(array)$sites)) as $site){$group=trim($this->nt_group($site));$key=$group!==''?$group:'missing:'.$site;if(isset($destinations[$key]))continue;$destinations[$key]=true;
            $parts=$this->nt_parts($message);foreach($parts as $i=>$part){$now=$this->nt_now();$key=hash('sha256',"$event|$type|$group|".($group===''?$site:'')."|$i");
                $inserted=$wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$this->prefix}notification_queue (event_key,notification_type,site_id,group_id,reference_no,message,attachment_url,status,attempts,next_attempt,created_at,updated_at,response) VALUES (%s,%s,%d,%s,%s,%s,%s,%s,0,%s,%s,%s,%s)",$key,$type,$site,$group,$reference,(count($parts)>1?'Part '.($i+1).'/'.count($parts)."\n":'') .$part,$i===0?$attachment_url:'',$group===''?'skipped':'queued',$now,$now,$now,$group===''?'Group ID missing':''));if($inserted===false)throw new Exception('Unable to queue notification.');
            }
        }
    }
    private function nt_sources(){return ['material_requisitions','material_dispatches','site_receipts','material_inwards','purchase_orders','site_transfers','site_transfer_receipts'];}
    private function nt_scan($baseline=false){
        global $wpdb;$p=$this->prefix;
        if(!$wpdb->get_var("SELECT GET_LOCK('".esc_sql($p."nt_scan")."',0)"))return;
        try{foreach($this->nt_sources() as $source){
            $rows=$wpdb->get_results("SELECT * FROM {$p}$source",ARRAY_A)?:[];
            if($source==='site_transfer_receipts'&&!$baseline){
                $batches=[];foreach($rows as $row){if($wpdb->get_var($wpdb->prepare("SELECT state FROM {$p}notification_state WHERE record_key=%s",$source.':'.$row['id']))!==null)continue;$batch=$row['transfer_id'].':'.$row['created_at'].':'.$row['created_by'];$batches[$batch][]=$row;}
                foreach($batches as $batch){$r=$batch[0];$r['batch_ids']=array_column($batch,'id');foreach($this->nt_record_events($source,$r,null) as $event)$this->nt_queue($event['type'],$event['sites'],$event['reference'],$event['message'],'transfer-receipt:'.implode('-',$r['batch_ids']));foreach($batch as $row)$wpdb->replace($p.'notification_state',['record_key'=>$source.':'.$row['id'],'state'=>'saved']);}
                continue;
            }
            foreach($rows as $r){$key=$source.':'.$r['id'];$state=(string)($r['status']??'saved');$old=$wpdb->get_var($wpdb->prepare("SELECT state FROM {$p}notification_state WHERE record_key=%s",$key));
                if($old===$state)continue;
                if(!$baseline){foreach($this->nt_record_events($source,$r,$old) as $event)$this->nt_queue($event['type'],$event['sites'],$event['reference'],$event['message'],($event['event_id']??$key).':'.$event['type'],false,$event['attachment_url']??'');}
                $wpdb->replace($p.'notification_state',['record_key'=>$key,'state'=>$state]);
            }
        }}finally{$wpdb->get_var("SELECT RELEASE_LOCK('".esc_sql($p."nt_scan")."')");}
    }
    private function nt_record_events($source,$r,$old){
        $status=strtolower($r['status']??'saved');$events=[];$types=[];
        if($source==='material_requisitions'){
            if($old===null&&in_array($status,['submitted','pending_approval'],true))$types[]='requisition_submitted';
            if(in_array($status,['approved','partially_dispatched','dispatched'],true)&&!in_array($old,['approved','partially_dispatched','dispatched'],true))$types[]='requisition_approved';
        }elseif($source==='material_dispatches'&&!in_array($old,['dispatched','partly_received','partially_received','received'],true)&&in_array($status,['dispatched','partly_received','partially_received','received'],true))$types[]='material_dispatched';
        elseif($source==='site_receipts'&&($old===null||$old==='draft')&&!in_array($status,['draft','cancelled'],true))$types[]='site_received';
        elseif($source==='material_inwards'&&$old===null&&!in_array($status,['cancelled','rejected'],true)&&!empty($r['purchase_order_id']))$types[]='po_received';
        elseif($source==='purchase_orders'&&$status==='approved'&&$old!=='approved')$types[]='po_approved';
        elseif($source==='site_transfers'&&in_array($status,['dispatched','partially_received','received'],true)&&!in_array($old,['dispatched','partially_received','received'],true))$types[]='material_dispatched';
        elseif($source==='site_transfer_receipts'&&$old===null)$types[]='site_received';
        foreach($types as $type){$event=$this->nt_event_message($type,$source,$r);if($event){if($source==='site_transfer_receipts')$event['event_id']='transfer-receipt:'.$r['transfer_id'].':'.$r['created_at'];$events[]=$event;}}
        return $events;
    }
    private function nt_items($table,$fk,$id){
        global $wpdb;$p=$this->prefix;return $wpdb->get_results($wpdb->prepare("SELECT d.*,i.item_name,u.unit_name FROM {$p}$table d LEFT JOIN {$p}items i ON i.id=d.item_id LEFT JOIN {$p}units u ON u.id=d.unit_id WHERE d.$fk=%d ORDER BY d.id",$id),ARRAY_A)?:[];
    }
    private function nt_line($r,$quantity){return '• '.$r['item_name'].' — '.$this->nt_num($quantity).' '.($r['unit_name']??'');}
    private function nt_event_message($type,$source,$r){
        global $wpdb;$p=$this->prefix;$id=(int)$r['id'];$sites=[(int)($r['site_id']??0)];$reference='';$message='';$attachment_url='';
        if($source==='material_requisitions'){
            $approved=$type==='requisition_approved';$reference=$r['requisition_no'];$message='*'.($approved?'REQUISITION APPROVED':'REQUISITION SUBMITTED')."*\nSite: ".$this->nt_site($r['site_id'])."\nRequisition: $reference\nDate: ".$this->nt_date($r['requisition_date']).$this->nt_optional('Required Date',$this->nt_date($r['required_date']??''))."\n\n";
            foreach($this->nt_items('material_requisition_items','requisition_id',$id) as $line)$message.=$this->nt_line($line,$line['requested_qty']).($approved?' | Approved: '.$this->nt_num($line['approved_qty']).' '.$line['unit_name']:'')."\n";
            $message.=$approved?'Approved By: '.$this->nt_name($r['approved_by']??0,true):'Submitted By: '.$this->nt_name($r['requested_by']??0);
            $message.=$approved?$this->nt_optional('Approval Date',$this->nt_date($r['approved_at']??'')):'';
            $message.=$this->nt_optional('Remarks',$approved?($r['approval_remarks']??''):($r['remarks']??''));
        }elseif($source==='material_dispatches'||$source==='site_transfers'){
            $transfer=$source==='site_transfers';if(!$transfer)$attachment_url=esc_url_raw($r['dispatch_photo_url']??'',['http','https']);$to=(int)($transfer?$r['to_site_id']:$r['site_id']);$from=(int)($r['from_site_id']??0);$sites=[$to];if($from)$sites[]=$from;$reference=$transfer?$r['transfer_no']:$r['dispatch_no'];
            $message="*MATERIAL DISPATCHED*\nDispatch: $reference\nDate: ".$this->nt_date($transfer?$r['transfer_date']:$r['dispatch_date'])."\nFrom: ".($from?$this->nt_site($from):$this->nt_label('warehouses','warehouse_name',$r['warehouse_id']??0))."\nTo: ".$this->nt_site($to)."\n";
            $message.=$this->nt_optional('Requisition',!empty($r['requisition_id'])?$this->nt_label('material_requisitions','requisition_no',$r['requisition_id']):'')."\n";
            foreach($this->nt_items($transfer?'site_transfer_items':'material_dispatch_items',$transfer?'transfer_id':'dispatch_id',$id) as $line)$message.=$this->nt_line($line,$line['qty'])."\n";
            foreach(['vehicle_no'=>'Vehicle','driver_name'=>'Driver','driver_mobile'=>'Driver Mobile'] as $field=>$label)$message.=$this->nt_optional($label,$r[$field]??'');
            $message.=$this->nt_optional('Dispatched By',$this->nt_name($r['dispatched_by']??$r['created_by']??0,true))."\nPlease verify the material and record the receipt.";
        }elseif($source==='site_receipts'){
            $dispatch=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}material_dispatches WHERE id=%d",$r['dispatch_id']),ARRAY_A);$sites=[(int)$r['site_id']];if(!empty($dispatch['from_site_id']))$sites[]=(int)$dispatch['from_site_id'];$reference=$r['receipt_no'];
            $message="*SITE MATERIAL RECEIVED*\nSite: ".$this->nt_site($r['site_id'])."\nReceipt: $reference\nDate: ".$this->nt_date($r['receipt_date'])."\nDispatch: ".($dispatch['dispatch_no']??'')."\n\n1.Item | Received Now | Pending Receipt\n";
            foreach($this->nt_items('site_receipt_items','receipt_id',$id) as $line){$pending=$this->nt_dispatch_pending((int)$line['dispatch_item_id']);$message.='* '.$line['item_name'].' | '.$this->nt_num($line['received_qty']).' '.$line['unit_name'].' | '.$this->nt_num($pending).' '.$line['unit_name']."\n";}
            $message.='Received By: '.$this->nt_name($r['received_by']??0).$this->nt_optional('Remarks',$r['remarks']??'');
        }elseif($source==='site_transfer_receipts'){
            $t=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}site_transfers WHERE id=%d",$r['transfer_id']),ARRAY_A);if(!$t)return null;$sites=[(int)$t['from_site_id'],(int)$t['to_site_id']];$reference=$t['transfer_no'];
            $message="*SITE MATERIAL RECEIVED*\nTransfer: $reference\nFrom: ".$this->nt_site($t['from_site_id'])."\nTo: ".$this->nt_site($t['to_site_id'])."\nDate: ".$this->nt_date($r['receipt_date'])."\n\nItem | Received Now | Damaged | Pending\n";
            $batch_filter=!empty($r['batch_ids'])?' AND rr.id IN ('.implode(',',array_map('intval',$r['batch_ids'])).')':'';
            $received=$wpdb->get_results($wpdb->prepare("SELECT rr.*,d.qty,d.received_qty received_total,d.damaged_qty damaged_total,i.item_name,u.unit_name FROM {$p}site_transfer_receipts rr JOIN {$p}site_transfer_items d ON d.id=rr.transfer_item_id LEFT JOIN {$p}items i ON i.id=d.item_id LEFT JOIN {$p}units u ON u.id=d.unit_id WHERE rr.transfer_id=%d AND rr.created_at=%s $batch_filter ORDER BY rr.id",$r['transfer_id'],$r['created_at']),ARRAY_A)?:[];
            foreach($received as $line)$message.='• '.$line['item_name'].' | '.$this->nt_num($line['received_qty']).' '.$line['unit_name'].' | '.$this->nt_num($line['damaged_qty']).' '.$line['unit_name'].' | '.$this->nt_num(max(0,$line['qty']-$line['received_total']-$line['damaged_total'])).' '.$line['unit_name']."\n";
            $message.='Received By: '.$this->nt_name($r['created_by'],true);
        }elseif($source==='purchase_orders'){
            $sites=[0];$reference=$r['po_no'];$message="*PURCHASE ORDER APPROVED*\nPO: $reference\nSupplier: ".$this->nt_label('vendors','vendor_name',$r['vendor_id'])."\nDelivery To: ".$this->nt_label('warehouses','warehouse_name',$r['warehouse_id'])."\nApproval Date: ".$this->nt_date($r['approved_at']??'')."\n\n";$total=0;
            foreach($this->nt_items('purchase_order_items','purchase_order_id',$id) as $line){$message.=$this->nt_line($line,$line['po_qty'])."\n";$total+=$line['po_qty']*$line['rate'];}$message.='PO Amount: ₹'.number_format($total,2)."\nApproved By: ".$this->nt_name($r['approved_by']??0,true);
        }elseif($source==='material_inwards'){
            $sites=[0];$po=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}purchase_orders WHERE id=%d",$r['purchase_order_id']),ARRAY_A);if(!$po)return null;$reference=$po['po_no'];$message="*PO MATERIAL RECEIVED*\nPO: $reference\nReceipt: ".$r['inward_no']."\nSupplier: ".$this->nt_label('vendors','vendor_name',$po['vendor_id'])."\nDate: ".$this->nt_date($r['inward_date'])."\nReceived At: ".$this->nt_label('warehouses','warehouse_name',$r['warehouse_id'])."\n\nItem | Received Now | Pending\n";$remaining=0;
            foreach($this->nt_items('material_inward_items','inward_id',$id) as $line){if(empty($line['purchase_order_item_id']))continue;$pi=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}purchase_order_items WHERE id=%d",$line['purchase_order_item_id']),ARRAY_A);$pending=max(0,$pi['po_qty']-$this->po_item_received_qty($pi['id']));$message.='• '.$line['item_name'].' | '.$this->nt_num($line['qty']).' '.$line['unit_name'].' | '.$this->nt_num($pending).' '.$line['unit_name']."\n";}
            foreach($this->nt_items('purchase_order_items','purchase_order_id',(int)$po['id']) as $pi)$remaining+=max(0,$pi['po_qty']-$this->po_item_received_qty($pi['id']));$message.='Received By: '.$this->nt_name($r['created_by']??0,true)."\nPO Status: ".($remaining>0.009?'Partially Received':'Fully Received');
        }
        return $message!==''?compact('type','sites','reference','message','attachment_url'):null;
    }
    private function nt_dispatch_pending($id){
        global $wpdb;$p=$this->prefix;return max(0,(float)$wpdb->get_var($wpdb->prepare("SELECT d.qty-COALESCE((SELECT SUM(r.received_qty+r.damaged_qty) FROM {$p}site_receipt_items r JOIN {$p}site_receipts h ON h.id=r.receipt_id WHERE r.dispatch_item_id=d.id AND h.status<>'cancelled'),0) FROM {$p}material_dispatch_items d WHERE d.id=%d",$id)));
    }
    private function nt_summary($type,$site,$date){
        global $wpdb;$p=$this->prefix;$site=(int)$site;$heading=strtoupper($this->nt_catalog()[$type][0]);$blocks=[];
        if($type==='pending_requisition'||$type==='pending_dispatch'){
            $states=$type==='pending_requisition'?"'pending_approval','submitted','draft'":"'approved','partially_dispatched','dispatched'";
            $heads=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}material_requisitions WHERE site_id=%d AND status IN ($states) ORDER BY required_date,id",$site),ARRAY_A)?:[];
            foreach($heads as $h){$lines=[];foreach($this->nt_items('material_requisition_items','requisition_id',$h['id']) as $r){$qty=(float)$r['requested_qty'];if($type==='pending_dispatch'){$dispatched=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(d.qty),0) FROM {$p}material_dispatch_items d JOIN {$p}material_dispatches h ON h.id=d.dispatch_id WHERE d.requisition_item_id=%d AND h.status<>'cancelled'",$r['id']));$qty=max(0,$r['approved_qty']-$dispatched);}if($qty>0.009)$lines[]=$this->nt_line($r,$qty);}
                if($lines)$blocks[]=($type==='pending_requisition'?(count($blocks)+1).'.':'').$h['requisition_no'].' | '.$this->nt_date($h['requisition_date']).$this->nt_optional('Required Date',$this->nt_date($h['required_date']??''))."\n".implode("\n",$lines).$this->nt_optional('Submitted By',$this->nt_name($h['requested_by']??0));
            }
        }elseif($type==='pending_receipt'){
            $heads=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}material_dispatches WHERE site_id=%d AND status NOT IN ('cancelled','draft') ORDER BY dispatch_date,id",$site),ARRAY_A)?:[];
            foreach($heads as $h){$lines=[];foreach($this->nt_items('material_dispatch_items','dispatch_id',$h['id']) as $r){$qty=$this->nt_dispatch_pending($r['id']);if($qty>0.009)$lines[]='* '.substr($this->nt_line($r,$qty),strlen('• ')).' pending receipt';}if($lines)$blocks[]=(count($blocks)+1).'.'.$h['dispatch_no'].' | '.$this->nt_date($h['dispatch_date'])."\n".implode("\n",$lines);}
            $transfers=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}site_transfers WHERE to_site_id=%d AND status IN ('dispatched','partially_received') ORDER BY transfer_date,id",$site),ARRAY_A)?:[];
            foreach($transfers as $h){$lines=[];foreach($this->nt_items('site_transfer_items','transfer_id',$h['id']) as $r){$qty=max(0,$r['qty']-$r['received_qty']-$r['damaged_qty']);if($qty>0.009)$lines[]='* '.substr($this->nt_line($r,$qty),strlen('• ')).' pending receipt';}if($lines)$blocks[]=(count($blocks)+1).'.'.$h['transfer_no'].' | '.$this->nt_date($h['transfer_date'])."\nFrom: ".$this->nt_site($h['from_site_id'])."\n".implode("\n",$lines);}
        }elseif($type==='labour_attendance'){
            $rows=$wpdb->get_results($wpdb->prepare("SELECT a.*,c.contractor_name FROM {$p}labour_attendance a LEFT JOIN {$p}labour_contractors c ON c.id=a.contractor_id WHERE a.site_id=%d AND a.attendance_date=%s ORDER BY c.contractor_name,a.id",$site,$date),ARRAY_A)?:[];$total=0;
            foreach($rows as $r){$details=json_decode($r['remarks']??'',true);$hours=$details['employee_hours']??[];$text='*'.$r['contractor_name'].'*';if(!$hours)$text.="\nLegacy attendance: ".$r['no_of_labour'].' employees (hours not recorded)';foreach($hours as $h){if((float)$h['working_hours']<=0)continue;$text.="\n• ".$h['employee_name'].' — '.$this->nt_num($h['working_hours']).' hours';$total+=(float)$h['working_hours'];}$text.=$this->nt_optional('Updated By',$this->nt_name($r['updated_by']?:$r['created_by'],true));$blocks[]=$text;}if($blocks)$blocks[]='Total Working Hours: '.$this->nt_num($total);
        }elseif($type==='site_timesheet'||$type==='office_timesheet'){
            $where=$type==='office_timesheet'?'t.worker_id=0':$wpdb->prepare('t.site_id=%d',$site);
            $rows=$wpdb->get_results($wpdb->prepare("SELECT t.*,COALESCE(w.employee_name,u.name) employee_name,c.contractor_name,s.site_name FROM {$p}time_entries t LEFT JOIN {$p}users u ON u.id=t.employee_id LEFT JOIN {$p}contractor_employees w ON w.id=t.worker_id LEFT JOIN {$p}labour_contractors c ON c.id=t.contractor_id LEFT JOIN {$p}sites s ON s.id=t.site_id WHERE t.work_date=%s AND $where ORDER BY t.worker_id,t.employee_id,t.from_time",$date),ARRAY_A)?:[];
            $groups=[];$total=0;foreach($rows as $r){$key=$r['worker_id']?'w'.$r['worker_id']:'e'.$r['employee_id'];if(!isset($groups[$key]))$groups[$key]=['label'=>$r['employee_name'].(!empty($r['contractor_name'])?' | '.$r['contractor_name']:''),'lines'=>[],'minutes'=>0];$groups[$key]['lines'][]='• '.date('h:i A',strtotime($r['from_time'])).'–'.date('h:i A',strtotime($r['to_time'])).($type==='office_timesheet'?' | '.($r['site_name']?:'Office'):'').' | '.$r['activity'].' | '.$this->nt_num($r['minutes']/60).' hours';$groups[$key]['minutes']+=(int)$r['minutes'];$total+=(int)$r['minutes'];}
            foreach($groups as $g)$blocks[]='*'.$g['label']."*\n".implode("\n",$g['lines'])."\nTotal: ".$this->nt_num($g['minutes']/60).' hours';if($blocks){if($type==='office_timesheet')$blocks[]='Total Employees Reported: '.count($groups);$blocks[]='Total Recorded Hours: '.$this->nt_num($total/60);}
        }elseif($type==='pending_po'||$type==='pending_po_approval'){
            $states=$type==='pending_po_approval'?"'pending_approval','created'":"'approved','sent','partly_received','fully_received'";
            $heads=$wpdb->get_results("SELECT * FROM {$p}purchase_orders WHERE status IN ($states) ORDER BY po_date,id",ARRAY_A)?:[];
            foreach($heads as $h){$lines=[];$amount=0;foreach($this->nt_items('purchase_order_items','purchase_order_id',$h['id']) as $r){$amount+=$r['po_qty']*$r['rate'];$pending=max(0,$r['po_qty']-$this->po_item_received_qty($r['id']));if($pending>0.009)$lines[]=$this->nt_line($r,$pending).' pending';}
                $text=$h['po_no'].' | '.$this->nt_date($h['po_date'])."\nSupplier: ".$this->nt_label('vendors','vendor_name',$h['vendor_id'])."\nDelivery To: ".$this->nt_label('warehouses','warehouse_name',$h['warehouse_id']);
                if($type==='pending_po_approval')$blocks[]=$text."\nAmount: ₹".number_format($amount,2).$this->nt_optional('Created By',$this->nt_name($h['created_by']??0,true));elseif($lines)$blocks[]=$text."\n".implode("\n",$lines);
            }
        }
        if(!$blocks)return '';return '*'.$heading.'*'.($site?"\nSite: ".$this->nt_site($site):'')."\n".(in_array($type,['labour_attendance','site_timesheet','office_timesheet'],true)?'Date: ':'As On: ').$this->nt_date($date)."\n\n".implode("\n\n",$blocks);
    }
    private function nt_scheduled(){
        $s=$this->nt_settings();if(!$s['enabled'])return;$now=new DateTimeImmutable('now',new DateTimeZone('Asia/Kolkata'));if(!in_array((int)$now->format('N'),array_map('intval',$s['days']),true))return;
        global $wpdb;
        if(empty($s['include_holidays'])){
            $holidays=$wpdb->get_col($wpdb->prepare("SELECT employee_ids FROM {$this->prefix}holidays WHERE holiday_date=%s",$now->format('Y-m-d')))?:[];
            foreach($holidays as $ids){$raw=trim((string)$ids);if($raw===''||$raw==='null'||json_decode($raw,true)===[])return;}
        }
        $sites=$wpdb->get_col("SELECT id FROM {$this->prefix}sites WHERE status='active'")?:[];
        foreach($this->nt_catalog() as $type=>$definition){if($definition[2]==='instant'||empty($s['types'][$type]))continue;
            $due=array_values(array_filter($s['times'][$type]??[],function($t)use($now){return $t<=$now->format('H:i');}));sort($due);if(!$due)continue;
            foreach([end($due)] as $time){if(!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/',$time)||$now->format('H:i')<$time)continue;
                // Catch up today's missed slots, never yesterday's summaries.
                $slot=$now->format('Y-m-d').':'.$time;
                foreach($definition[1]==='office'?[0]:$sites as $site){$key='schedule:'.$type.':'.$slot.':'.$site;$done=$wpdb->get_var($wpdb->prepare("SELECT state FROM {$this->prefix}notification_state WHERE record_key=%s",$key));if($done!==null){
                        // Recover daily slots previously marked done while their report was empty.
                        if($definition[2]!=='daily'||(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->prefix}notification_queue WHERE notification_type=%s AND site_id=%d AND reference_no=%s",$type,$site,$slot))>0)continue;
                    }
                    $message=$this->nt_summary($type,$site,$now->format('Y-m-d'));
                    if($message==='')continue;
                    $this->nt_queue($type,[$site],$slot,$message,$key);
                    $wpdb->replace($this->prefix.'notification_state',['record_key'=>$key,'state'=>'done']);
                }
            }
        }
    }
    public function notifications_tick(){
        if(get_option('bsm_notifications_schema')!=='1')return;
        try{$this->nt_scan();$this->nt_scheduled();$this->nt_worker();$this->nt_cleanup();update_option('bsm_notifications_last_tick',$this->nt_now(),false);}catch(Throwable $e){error_log('BSM notification background processing failed.');}
    }
    private function nt_worker(){
        global $wpdb;$p=$this->prefix;if(!$wpdb->get_var("SELECT GET_LOCK('".esc_sql($p.'nt_send')."',0)"))return;
        try{
            $now=$this->nt_now();$stale=gmdate('Y-m-d H:i:s',time()-300);
            $wpdb->query($wpdb->prepare("UPDATE {$p}notification_queue SET status='review_required',response='Interrupted send; verify provider history before retrying.',updated_at=%s WHERE status='sending' AND updated_at<%s",$now,$stale));
            $s=$this->nt_settings();$password=$this->nt_password($s['password']);if(get_option('bsm_notifications_blocked')||!$s['username']||!$password)return;
            $rows=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}notification_queue WHERE status='queued' AND next_attempt<=%s ORDER BY id LIMIT 5",$now),ARRAY_A)?:[];
            foreach($rows as $r){
                if($r['notification_type']!=='test'&&(!$s['enabled']||empty($s['types'][$r['notification_type']]))){$wpdb->update($p.'notification_queue',['status'=>'skipped','response'=>'Notification disabled','updated_at'=>$now],['id'=>$r['id']]);continue;}
                if(!$this->nt_still_relevant($r)){$wpdb->update($p.'notification_queue',['status'=>'skipped','response'=>'Source cancelled or no outstanding data.','updated_at'=>$now],['id'=>$r['id']]);continue;}
                $claim=$wpdb->query($wpdb->prepare("UPDATE {$p}notification_queue SET status='sending',attempts=attempts+1,cycle_attempts=cycle_attempts+1,updated_at=%s WHERE id=%d AND status='queued'",$now,$r['id']));if($claim!==1)continue;
                $payload=['recipientIds'=>[$r['group_id']],'message'=>[$r['message']]];if(!empty($r['attachment_url']))$payload['filePathUrl']=[$r['attachment_url']];
                $response=wp_remote_post('https://app.messageautosender.com/api/v1/message/create',['timeout'=>20,'redirection'=>0,'headers'=>['accept'=>'application/json','Content-Type'=>'application/json','Authorization'=>'Basic '.base64_encode($s['username'].':'.$password)],'body'=>wp_json_encode($payload)]);
                $status='accepted';$detail='Request accepted by provider.';$delay=0;
                if(is_wp_error($response)){$status='review_required';$detail='Transport error; outcome unknown. Verify provider history before retrying.';}
                else{
                    $code=wp_remote_retrieve_response_code($response);$body=wp_remote_retrieve_body($response);$json=json_decode($body,true);
                    $failed=is_array($json)&&((isset($json['success'])&&!$json['success'])||!empty($json['error'])||in_array(strtolower((string)($json['status']??'')),['failed','error'],true));
                    $detail='HTTP '.$code.' '.substr(preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f]/','',$body),0,1500);
                    $detail=str_replace([$s['username'],$password,base64_encode($s['username'].':'.$password)],'[redacted]',$detail);
                    if($code===401||$code===403){$status='failed';update_option('bsm_notifications_blocked','Authentication rejected. Update credentials before sending again.',false);}
                    elseif($code===429||$code>=500){$status=((int)$r['cycle_attempts']+1<3)?'queued':'failed';$delay=60*(2**(int)$r['cycle_attempts']);}
                    elseif($code<200||$code>=300||$failed)$status='failed';
                    elseif(!is_array($json)||!(!empty($json['success'])||in_array(strtolower((string)($json['status']??'')),['success','accepted','queued'],true)||!empty($json['id'])||!empty($json['messageId'])||!empty($json['data']))){$status='review_required';$detail='Unrecognized success response; verify provider history. '.$detail;}
                }
                $history=json_decode($r['history']??'[]',true)?:[];$history[]=['at'=>$this->nt_now(),'attempt'=>(int)$r['attempts']+1,'status'=>$status,'response'=>$detail];
                $wpdb->update($p.'notification_queue',['history'=>wp_json_encode($history),'status'=>$status,'response'=>$detail,'next_attempt'=>gmdate('Y-m-d H:i:s',time()+$delay),'updated_at'=>$this->nt_now()],['id'=>$r['id']]);
                if(get_option('bsm_notifications_blocked'))break;
            }
        }finally{$wpdb->get_var("SELECT RELEASE_LOCK('".esc_sql($p.'nt_send')."')");}
    }
    private function nt_still_relevant($row){
        global $wpdb;$p=$this->prefix;$type=$row['notification_type'];$defs=$this->nt_catalog();
        if(isset($defs[$type])&&$defs[$type][2]!=='instant'){
            $date=substr($row['reference_no'],0,10);if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date))return false;
            return $this->nt_summary($type,(int)$row['site_id'],$date)!=='';
        }
        $sources=['requisition_submitted'=>['material_requisitions','requisition_no'],'requisition_approved'=>['material_requisitions','requisition_no'],'material_dispatched'=>['material_dispatches','dispatch_no'],'site_received'=>['site_receipts','receipt_no'],'po_approved'=>['purchase_orders','po_no'],'po_received'=>['purchase_orders','po_no']];
        if(!isset($sources[$type]))return true;[$table,$column]=$sources[$type];$status=$wpdb->get_var($wpdb->prepare("SELECT status FROM {$p}$table WHERE $column=%s",$row['reference_no']));
        if($status===null&&in_array($type,['site_received','material_dispatched'],true))$status=$wpdb->get_var($wpdb->prepare("SELECT status FROM {$p}site_transfers WHERE transfer_no=%s",$row['reference_no']));
        return $status!==null&&!in_array($status,['cancelled','rejected'],true);
    }
    private function nt_cleanup(){
        if(get_transient('bsm_notification_cleanup'))return;global $wpdb;$s=$this->nt_settings();$cutoff=gmdate('Y-m-d H:i:s',time()-max(7,(int)$s['retention'])*DAY_IN_SECONDS);
        $wpdb->query($wpdb->prepare("DELETE FROM {$this->prefix}notification_queue WHERE created_at<%s AND status IN ('accepted','skipped','failed')",$cutoff));
        $wpdb->query($wpdb->prepare("DELETE FROM {$this->prefix}notification_state WHERE record_key LIKE 'schedule:%%' AND SUBSTRING_INDEX(SUBSTRING_INDEX(record_key,':',3),':',-1)<%s",substr($cutoff,0,10)));set_transient('bsm_notification_cleanup',1,DAY_IN_SECONDS);
    }
    public function ajax_notifications(){
        $this->verify_ajax();if(!$this->nt_admin())wp_send_json_error(['message'=>'Only Admin can manage notifications.'],403);
        global $wpdb;$p=$this->prefix;$op=sanitize_key($_POST['op']??'get');$d=json_decode(wp_unslash($_POST['data']??'{}'),true);$d=is_array($d)?$d:[];
        try{
            if($op==='save'){
                $old=$this->nt_settings();$s=$old;$s['enabled']=!empty($d['enabled']);$s['include_holidays']=!empty($d['include_holidays']);$s['username']=sanitize_text_field($d['username']??'');$s['office_group']=sanitize_text_field($d['office_group']??'');$s['retention']=max(7,min(365,(int)($d['retention']??90)));$s['days']=array_values(array_intersect([1,2,3,4,5,6,7],array_map('intval',(array)($d['days']??[]))));$s['types']=[];$s['times']=[];
                foreach($this->nt_catalog() as $key=>$def){$s['types'][$key]=!empty($d['types'][$key]);if($def[2]!=='instant'){$times=array_unique(array_filter(array_map('trim',explode(',',(string)($d['times'][$key]??'')))));if(count($times)>6)throw new Exception('Use at most six times per notification.');foreach($times as $t)if(!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/',$t))throw new Exception('Schedule times must use HH:MM, separated by commas.');if($s['types'][$key]&&!$times)throw new Exception('Enter a schedule for '.$def[0].'.');sort($times);$s['times'][$key]=array_values($times);}}
                if(!empty($d['password']))$s['password']=$this->nt_encrypt((string)$d['password']);
                if($s['enabled']&&(!$s['username']||!$this->nt_password($s['password'])))throw new Exception('Enter Username and Password before enabling notifications.');
                // Baseline current event states before enabling, avoiding historical instant messages.
                if($s['enabled']&&!$old['enabled']){$this->nt_scan(true);$now=new DateTimeImmutable('now',new DateTimeZone('Asia/Kolkata'));$site_ids=$wpdb->get_col("SELECT id FROM {$p}sites WHERE status='active'")?:[];foreach($s['times'] as $type=>$times)foreach($times as $time)if($time<=$now->format('H:i'))foreach($this->nt_catalog()[$type][1]==='office'?[0]:$site_ids as $site)$wpdb->replace($p.'notification_state',['record_key'=>'schedule:'.$type.':'.$now->format('Y-m-d').':'.$time.':'.$site,'state'=>'done']);}
                update_option('bsm_notification_settings',$s,false);if($old['username']!==$s['username']||!empty($d['password']))delete_option('bsm_notifications_blocked');wp_send_json_success(['message'=>'Notification settings saved.']);
            }
            if($op==='preview'||$op==='test'){
                $type=sanitize_key($d['type']??'');$site=(int)($d['site_id']??0);if(!isset($this->nt_catalog()[$type]))throw new Exception('Select a notification type.');if($this->nt_catalog()[$type][1]==='office')$site=0;elseif(!$site||!$this->nt_site($site))throw new Exception('Select a Site.');
                if($op==='test'){$group=$this->nt_group($site);if(!$group)throw new Exception('Save the destination Group ID first.');$s=$this->nt_settings();if(!$s['username']||!$this->nt_password($s['password']))throw new Exception('Save connection credentials first.');$this->nt_queue('test',[$site],'Connection test',"*SITE MANAGEMENT TEST*\nDestination: ".($site?$this->nt_site($site):'Office')."\nConnection test requested by ".wp_get_current_user()->display_name,'test:'.wp_generate_uuid4(),true);wp_send_json_success(['message'=>'Test queued. Check Notification Log after the next cron run.']);}
                $def=$this->nt_catalog()[$type];$date=(new DateTimeImmutable('now',new DateTimeZone('Asia/Kolkata')))->format('Y-m-d');$message='';
                if($def[2]!=='instant')$message=$this->nt_summary($type,$site,$date);
                else{$sources=['requisition_submitted'=>'material_requisitions','requisition_approved'=>'material_requisitions','material_dispatched'=>'material_dispatches','site_received'=>'site_receipts','po_received'=>'material_inwards','po_approved'=>'purchase_orders'];$source=$sources[$type];$where=$site?$wpdb->prepare('site_id=%d',$site):'1=1';if($source==='material_inwards')$where.=' AND purchase_order_id>0';if(in_array($type,['po_approved','requisition_approved'],true))$where.=" AND status='approved'";$r=$wpdb->get_row("SELECT * FROM {$p}$source WHERE $where ORDER BY id DESC LIMIT 1",ARRAY_A);if($r){$event=$this->nt_event_message($type,$source,$r);$message=$event['message']??'';}}
                wp_send_json_success(['message'=>$message?:'No matching records available for preview.','group'=>$this->nt_group($site)]);
            }
            if($op==='retry'){
                $id=(int)($d['id']??0);$r=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}notification_queue WHERE id=%d",$id),ARRAY_A);if(!$r||!in_array($r['status'],['failed','review_required'],true))throw new Exception('Only failed or review-required messages can be retried.');if($r['status']==='review_required'&&empty($d['confirmed']))throw new Exception('Confirm you checked provider history before retrying.');$wpdb->update($p.'notification_queue',['status'=>'queued','cycle_attempts'=>0,'next_attempt'=>$this->nt_now(),'updated_at'=>$this->nt_now(),'response'=>'Manual retry requested by Admin.'],['id'=>$id]);wp_send_json_success(['message'=>'Message queued for retry.']);
            }
            if($op==='reject_po'){
                $id=(int)($d['id']??0);$reason=sanitize_textarea_field($d['reason']??'');if(!$reason)throw new Exception('Enter a rejection reason.');$updated=$wpdb->query($wpdb->prepare("UPDATE {$p}purchase_orders SET status='rejected',rejection_reason=%s,updated_at=%s WHERE id=%d AND status IN ('created','pending_approval')",$reason,current_time('mysql'),$id));if(!$updated)throw new Exception('PO is not pending approval.');wp_send_json_success(['message'=>'PO rejected.']);
            }
            if($op==='approve_po'){
                $id=(int)($d['id']??0);$updated=$wpdb->query($wpdb->prepare("UPDATE {$p}purchase_orders SET status='approved',approved_by=%d,approved_at=%s,updated_at=%s WHERE id=%d AND status IN ('created','pending_approval')",get_current_user_id(),current_time('mysql'),current_time('mysql'),$id));if(!$updated)throw new Exception('PO is not pending approval.');$this->nt_scan();wp_send_json_success(['message'=>'PO approved.']);
            }
            if($op==='get'){
                $s=$this->nt_settings();$s['password_saved']=!empty($s['password']);unset($s['password']);$logs=$wpdb->get_results("SELECT q.*,s.site_name FROM {$p}notification_queue q LEFT JOIN {$p}sites s ON s.id=q.site_id ORDER BY q.id DESC LIMIT 100",ARRAY_A)?:[];
                foreach($logs as &$log){foreach(['created_at','updated_at','next_attempt'] as $field)$log[$field]=wp_date('d/m/Y h:i:s A',strtotime($log[$field].' UTC'),new DateTimeZone('Asia/Kolkata'));$history=json_decode($log['history']??'[]',true)?:[];foreach($history as &$attempt){if(!empty($attempt['at']))$attempt['at']=wp_date('d/m/Y h:i:s A',strtotime($attempt['at'].' UTC'),new DateTimeZone('Asia/Kolkata'));}unset($attempt);$log['history']=$history?wp_json_encode($history):'';}unset($log);
                wp_send_json_success(['settings'=>$s,'catalog'=>$this->nt_catalog(),'sites'=>$wpdb->get_results("SELECT id,site_name,whatsapp_group_id FROM {$p}sites ORDER BY site_name",ARRAY_A),'logs'=>$logs,'blocked'=>get_option('bsm_notifications_blocked',''),'last_tick'=>get_option('bsm_notifications_last_tick','')?wp_date('d/m/Y h:i:s A',strtotime(get_option('bsm_notifications_last_tick').' UTC'),new DateTimeZone('Asia/Kolkata')):'']);
            }
            throw new Exception('Unknown notification action.');
        }catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()]);}
    }
}
