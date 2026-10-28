<?php
if (!defined('ABSPATH')) exit;

/** Attendance, contractor billing and two-stage inter-site stock movements. */
trait BSM_Workforce_Transfers {
    public function operations_upgrade() {
        if (get_option('bsm_operations_version') === '2') return;
        global $wpdb; $p=$this->prefix; $c=$wpdb->get_charset_collate();
        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        $schemas=[
            "CREATE TABLE {$p}holidays (
                id bigint unsigned NOT NULL AUTO_INCREMENT,
                holiday_date date NOT NULL,
                holiday_name varchar(190) NOT NULL,
                employee_ids longtext NULL,
                PRIMARY KEY  (id),
                KEY holiday_date (holiday_date)
            ) ENGINE=InnoDB $c;",
            "CREATE TABLE {$p}contractor_employees (
                id bigint unsigned NOT NULL AUTO_INCREMENT,
                contractor_id bigint unsigned NOT NULL,
                employee_name varchar(190) NOT NULL,
                mobile varchar(30) NOT NULL DEFAULT '',
                address text NULL,
                bank_name varchar(190) NOT NULL DEFAULT '',
                ifsc varchar(20) NOT NULL DEFAULT '',
                account_number varchar(60) NOT NULL DEFAULT '', 
                daily_rate decimal(18,2) NOT NULL DEFAULT 0,
                hourly_rate decimal(18,2) NOT NULL DEFAULT 0,
                status varchar(20) NOT NULL DEFAULT 'active',
                PRIMARY KEY  (id),
                KEY contractor_id (contractor_id)
            ) ENGINE=InnoDB $c;",
            "CREATE TABLE {$p}time_entries (
                id bigint unsigned NOT NULL AUTO_INCREMENT,
                work_date date NOT NULL,
                employee_id bigint unsigned NOT NULL DEFAULT 0,
                worker_id bigint unsigned NOT NULL DEFAULT 0,
                contractor_id bigint unsigned NOT NULL DEFAULT 0,
                site_id bigint unsigned NOT NULL DEFAULT 0,
                from_time time NOT NULL,
                to_time time NOT NULL,
                minutes int unsigned NOT NULL,
                activity text NOT NULL,
                daily_rate decimal(18,2) NOT NULL DEFAULT 0,
                hourly_rate decimal(18,2) NOT NULL DEFAULT 0,
                status varchar(20) NOT NULL DEFAULT 'submitted',
                bill_id bigint unsigned NOT NULL DEFAULT 0,
                created_by bigint unsigned NOT NULL,
                approved_by bigint unsigned NOT NULL DEFAULT 0,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY office_day (employee_id,work_date),
                KEY worker_day (worker_id,work_date),
                KEY bill_id (bill_id)
            ) ENGINE=InnoDB $c;",
            "CREATE TABLE {$p}site_transfers (
                id bigint unsigned NOT NULL AUTO_INCREMENT,
                transfer_no varchar(80) NULL,
                transfer_date date NOT NULL,
                from_site_id bigint unsigned NOT NULL,
                to_site_id bigint unsigned NOT NULL,
                vehicle_no varchar(80) NOT NULL DEFAULT '',
                remarks text NULL,
                status varchar(30) NOT NULL DEFAULT 'draft',
                dispatched_by bigint unsigned NOT NULL DEFAULT 0,
                dispatched_at datetime NULL,
                created_by bigint unsigned NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY transfer_no (transfer_no),
                KEY from_site (from_site_id),
                KEY to_site (to_site_id)
            ) ENGINE=InnoDB $c;",
            "CREATE TABLE {$p}site_transfer_items (
                id bigint unsigned NOT NULL AUTO_INCREMENT,
                transfer_id bigint unsigned NOT NULL,
                item_id bigint unsigned NOT NULL,
                unit_id bigint unsigned NOT NULL DEFAULT 0,
                qty decimal(18,2) NOT NULL,
                received_qty decimal(18,2) NOT NULL DEFAULT 0,
                damaged_qty decimal(18,2) NOT NULL DEFAULT 0,
                rate decimal(18,2) NOT NULL DEFAULT 0,
                remarks text NULL,
                PRIMARY KEY  (id),
                KEY transfer_id (transfer_id)
            ) ENGINE=InnoDB $c;",
            "CREATE TABLE {$p}site_transfer_receipts (
                id bigint unsigned NOT NULL AUTO_INCREMENT,
                transfer_id bigint unsigned NOT NULL,
                transfer_item_id bigint unsigned NOT NULL,
                receipt_date date NOT NULL,
                received_qty decimal(18,2) NOT NULL DEFAULT 0,
                damaged_qty decimal(18,2) NOT NULL DEFAULT 0,
                created_by bigint unsigned NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY transfer_id (transfer_id)
            ) ENGINE=InnoDB $c;"
        ];
        foreach($schemas as $sql) dbDelta($sql);
        foreach(['labour_contractors','contractor_bills'] as $table) {
            if(!$wpdb->get_var("SHOW COLUMNS FROM {$p}{$table} LIKE 'payment_basis'"))
                $wpdb->query("ALTER TABLE {$p}{$table} ADD payment_basis varchar(30) NOT NULL DEFAULT 'direct'");
        }
        foreach(['mobile'=>"varchar(30) NOT NULL DEFAULT ''",'address'=>'text NULL','bank_name'=>"varchar(190) NOT NULL DEFAULT ''",'ifsc'=>"varchar(20) NOT NULL DEFAULT ''",'account_number'=>"varchar(60) NOT NULL DEFAULT ''"] as $column=>$definition){
            if(!$wpdb->get_var("SHOW COLUMNS FROM {$p}contractor_employees LIKE '$column'"))$wpdb->query("ALTER TABLE {$p}contractor_employees ADD $column $definition");
        }
        $ready=true; foreach(['holidays','contractor_employees','time_entries','site_transfers','site_transfer_items','site_transfer_receipts'] as $table) {
            if($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($p.$table)))!==$p.$table) $ready=false;
        }
        foreach(['labour_contractors','contractor_bills'] as $table)if(!$wpdb->get_var("SHOW COLUMNS FROM {$p}{$table} LIKE 'payment_basis'"))$ready=false;
        foreach(['mobile','address','bank_name','ifsc','account_number'] as $column)if(!$wpdb->get_var("SHOW COLUMNS FROM {$p}contractor_employees LIKE '$column'"))$ready=false;
        if($ready) { $this->create_app_pages(); update_option('bsm_operations_version','2'); }
    }
    private function ops_user() {
        global $wpdb;
        $u=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->prefix}users WHERE wp_user_id=%d AND status='active' LIMIT 1",get_current_user_id()),ARRAY_A);
        if(!$u && !current_user_can('manage_options')) throw new Exception('Your account is not linked to an active application user.');
        return $u?:['id'=>0,'role'=>'administrator'];
    }
    private function ops_admin() { return current_user_can('manage_options') || $this->allowance_admin_allowed(); }
    private function ops_require_admin() { if(!$this->ops_admin()) throw new Exception('Only HO/Admin can perform this action.'); }
    private function ops_site($id) {
        global $wpdb; if(!$id || !$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}sites WHERE id=%d",$id))) throw new Exception('Select a valid Site.');
        $scope=$this->employee_site_scope(); if(is_array($scope) && $scope && !in_array((int)$id,$scope,true)) throw new Exception('You do not have access to this Site.');
    }
    private function ops_date($value) {
        $v=sanitize_text_field($value); $d=DateTime::createFromFormat('!Y-m-d',$v);
        if(!$d || $d->format('Y-m-d')!==$v) throw new Exception('Select a valid date.'); return $v;
    }
    private function ops_worker_details($data) {
        $name=trim(sanitize_text_field($data['employee_name']??''));if($name==='')throw new Exception('Employee Name is required.');
        $mobile=trim(sanitize_text_field($data['mobile']??''));if($mobile!==''&&!preg_match('/^[0-9]{10}$/',$mobile))throw new Exception('Employee Mobile must contain 10 digits.');
        $ifsc=strtoupper(trim(sanitize_text_field($data['ifsc']??'')));if($ifsc!==''&&!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/',$ifsc))throw new Exception('Enter a valid Employee IFSC.');
        $account=trim(sanitize_text_field($data['account_number']??''));if($account!==''&&!preg_match('/^[0-9]{1,60}$/',$account))throw new Exception('Employee AC No. must contain digits only.');
        return ['employee_name'=>$name,'mobile'=>$mobile,'address'=>sanitize_textarea_field($data['address']??''),'bank_name'=>sanitize_text_field($data['bank_name']??''),'ifsc'=>$ifsc,'account_number'=>$account];
    }
    private function ops_save_contractor_employees($id,$parent,$employees) {
        $this->ops_require_admin();global $wpdb;$p=$this->prefix;$lock=$this->ops_lock('contractor_'.($id?:'new'));
        try {
            $wpdb->query('START TRANSACTION');
            if($id){if(!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}labour_contractors WHERE id=%d FOR UPDATE",$id)))throw new Exception('Contractor not found.');$this->ops_write($wpdb->update($p.'labour_contractors',$parent,['id'=>$id]));}
            else{$this->ops_write($wpdb->insert($p.'labour_contractors',$parent));$id=(int)$wpdb->insert_id;}
            $existing=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}contractor_employees WHERE contractor_id=%d FOR UPDATE",$id),ARRAY_A);$saved=[];
            foreach($employees as $employee){
                if(!is_array($employee))throw new Exception('Invalid Employee row.');$eid=(int)($employee['id']??0);
                if(!$eid && !array_filter(array_intersect_key($employee,array_flip(['employee_name','mobile','address','bank_name','ifsc','account_number'])),function($v){return trim((string)$v)!=='';}))continue;
                if($eid && in_array($eid,$saved,true))throw new Exception('An Employee row cannot be repeated.');
                $old=null;foreach($existing as $e)if((int)$e['id']===$eid)$old=$e;
                if($eid&&!$old)throw new Exception('Employee does not belong to this Contractor.');
                $row=$this->ops_worker_details($employee);$row['contractor_id']=$id;$row['status']='active';
                if($eid)$this->ops_write($wpdb->update($p.'contractor_employees',$row,['id'=>$eid]));
                else{$this->ops_write($wpdb->insert($p.'contractor_employees',$row));$eid=(int)$wpdb->insert_id;}
                $saved[]=$eid;
            }
            foreach($existing as $e)if(!in_array((int)$e['id'],$saved,true)&&$e['status']==='active'){
                // Preserve employee IDs referenced by historical time sheets/bills.
                if($wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}time_entries WHERE worker_id=%d LIMIT 1",$e['id'])))$this->ops_write($wpdb->update($p.'contractor_employees',['status'=>'inactive'],['id'=>$e['id']]));
                else $this->ops_write($wpdb->delete($p.'contractor_employees',['id'=>$e['id']]));
            }
            $wpdb->query('COMMIT');return $id;
        }catch(Throwable $e){$wpdb->query('ROLLBACK');throw $e;}
        finally{$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));}
    }
    private function ops_time_interval($date,$from,$to) {
        $start=strtotime($date.' '.substr($from,0,5).' UTC');
        $end=strtotime($date.' '.substr($to,0,5).' UTC');
        if($end<$start)$end+=86400;
        return [$start,$end];
    }
    private function ops_write($ok) { global $wpdb; if($ok===false) throw new Exception('Unable to save the record. Please retry.'); }
    private function ops_lock($key) {
        global $wpdb; $name=substr($this->prefix.'ops_'.$key,0,64);
        if((int)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 5)',$name))!==1) throw new Exception('Another update is in progress. Please retry.'); return $name;
    }
    private function ops_holiday_dates($employee,$start,$end) {
        global $wpdb; $dates=[];
        foreach($wpdb->get_results($wpdb->prepare("SELECT holiday_date,employee_ids FROM {$this->prefix}holidays WHERE holiday_date BETWEEN %s AND %s",$start,$end),ARRAY_A) as $h) {
            $ids=json_decode($h['employee_ids']?:'[]',true)?:[];
            if(!$ids || in_array((int)$employee,array_map('intval',$ids),true)) $dates[$h['holiday_date']]=true;
        } return $dates;
    }
    private function ops_transfer_movements($site=0,$item=0,$start='',$end='') {
        global $wpdb; $p=$this->prefix;
        $w="t.status NOT IN ('draft','cancelled')"; $args=[];
        if($site){$w.=' AND (t.from_site_id=%d OR t.to_site_id=%d)';$args[]=$site;$args[]=$site;}
        if($item){$w.=' AND d.item_id=%d';$args[]=$item;}
        $sql="SELECT t.*,d.id detail_id,d.item_id,d.unit_id,d.qty,d.rate,i.item_name,u.unit_name FROM {$p}site_transfers t JOIN {$p}site_transfer_items d ON d.transfer_id=t.id LEFT JOIN {$p}items i ON i.id=d.item_id LEFT JOIN {$p}units u ON u.id=d.unit_id WHERE $w";
        $rows=$wpdb->get_results($args?$wpdb->prepare($sql,$args):$sql,ARRAY_A);$out=[];
        foreach($rows as $r){
            $date=substr($r['dispatched_at']?:$r['transfer_date'],0,10);
            if((!$start||$date>=$start)&&(!$end||$date<=$end)&&(!$site||(int)$r['from_site_id']===$site)) $out[]=['site_id'=>(int)$r['from_site_id'],'item_id'=>(int)$r['item_id'],'unit_id'=>(int)$r['unit_id'],'item_name'=>$r['item_name'],'unit'=>$r['unit_name'],'in'=>0,'out'=>(float)$r['qty'],'rate'=>(float)$r['rate'],'date'=>$date];
            if(!$site||(int)$r['to_site_id']===$site){
                $rw='transfer_item_id=%d';$ra=[(int)$r['detail_id']];
                if($start){$rw.=' AND receipt_date>=%s';$ra[]=$start;} if($end){$rw.=' AND receipt_date<=%s';$ra[]=$end;}
                $qty=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(received_qty),0) FROM {$p}site_transfer_receipts WHERE $rw",$ra));
                if($qty) $out[]=['site_id'=>(int)$r['to_site_id'],'item_id'=>(int)$r['item_id'],'unit_id'=>(int)$r['unit_id'],'item_name'=>$r['item_name'],'unit'=>$r['unit_name'],'in'=>$qty,'out'=>0,'rate'=>(float)$r['rate'],'date'=>$date];
            }
        } return $out;
    }
    private function ops_stock_delta($site,$item) { $n=0;foreach($this->ops_transfer_movements((int)$site,(int)$item) as $r)$n+=$r['in']-$r['out'];return $n; }
    private function ops_time_rows($d,$user) {
        global $wpdb;$p=$this->prefix;$w='1=1';$a=[];
        foreach(['from'=>'work_date>=','to'=>'work_date<='] as $k=>$expr)if(!empty($d[$k])){$w.=" AND e.$expr%s";$a[]=$this->ops_date($d[$k]);}
        foreach(['employee_id','worker_id','contractor_id','site_id'] as $k)if(!empty($d[$k])){$w.=" AND e.$k=%d";$a[]=(int)$d[$k];}
        if(!empty($d['status'])){$w.=' AND e.status=%s';$a[]=sanitize_key($d['status']);}
        if(!$this->ops_admin()){
            $views=json_decode($user['allowed_views']??'[]',true)?:[];
            // Contractor entry can be delegated; office entries always remain employee-owned.
            if(in_array('contractor_employees',$views,true)) {
                $scope=$this->employee_site_scope();
                $w.=' AND (e.employee_id=%d OR (e.worker_id>0 AND e.created_by=%d))';$a[]=(int)$user['id'];$a[]=get_current_user_id();
                if($scope){$w.=' AND (e.site_id=0 OR e.site_id IN ('.implode(',',array_map('intval',$scope)).'))';}
            } else {$w.=' AND e.employee_id=%d AND e.worker_id=0';$a[]=(int)$user['id'];}
        }
        $sql="SELECT e.*,COALESCE(w.employee_name,u.name) employee_name,s.site_name,c.contractor_name,b.bill_no FROM {$p}time_entries e LEFT JOIN {$p}users u ON u.id=e.employee_id LEFT JOIN {$p}contractor_employees w ON w.id=e.worker_id LEFT JOIN {$p}sites s ON s.id=e.site_id LEFT JOIN {$p}labour_contractors c ON c.id=e.contractor_id LEFT JOIN {$p}contractor_bills b ON b.id=e.bill_id WHERE $w ORDER BY e.work_date DESC,e.from_time,e.id";
        return $wpdb->get_results($a?$wpdb->prepare($sql,$a):$sql,ARRAY_A);
    }
    public function ajax_operations() {
        $this->verify_ajax();global $wpdb;$p=$this->prefix;$lock=null;
        try {
            $user=$this->ops_user();$op=sanitize_key($_POST['op']??'');
            $d=json_decode(wp_unslash($_POST['data']??'{}'),true);if(!is_array($d))throw new Exception('Invalid request.');
            $admin=$this->ops_admin();$now=current_time('mysql');$uid=get_current_user_id();$result=[];
            if(!$admin){
                $views=json_decode($user['allowed_views']??'[]',true)?:[];
                $needed=strpos($op,'transfer')===0?['site_transfer_form','site_transfer_master']:(strpos($op,'holiday')===0?['holiday_list']:(strpos($op,'worker')===0||strpos($op,'bill')===0?['contractor_employees','contractor_calculated_bill']:['time_sheet','time_sheet_master']));
                if($op!=='meta' && !array_intersect($needed,$views))throw new Exception('You do not have permission to access this view.');
            }
            switch($op) {
            case 'meta':
                $employees=$wpdb->get_results("SELECT id,name label FROM {$p}users WHERE status='active' ORDER BY name",ARRAY_A);
                if(!$admin)$employees=array_values(array_filter($employees,function($e)use($user){return (int)$e['id']===(int)$user['id'];}));
                $workers=$wpdb->get_results("SELECT w.*,w.employee_name label,c.contractor_name,c.payment_basis FROM {$p}contractor_employees w JOIN {$p}labour_contractors c ON c.id=w.contractor_id ORDER BY w.employee_name",ARRAY_A);
                if(!$admin)foreach($workers as &$worker){unset($worker['mobile'],$worker['address'],$worker['bank_name'],$worker['ifsc'],$worker['account_number']);}unset($worker);
                $destinations=$wpdb->get_results("SELECT id,site_name label FROM {$p}sites WHERE status='active' ORDER BY site_name",ARRAY_A);
                $result=['destinations'=>$destinations,'admin'=>$admin,'employee_id'=>(int)$user['id'],'employees'=>$employees,'workers'=>$workers];break;
            case 'holiday_list':
                $holidays=$wpdb->get_results("SELECT * FROM {$p}holidays ORDER BY holiday_date DESC",ARRAY_A);
                if(!$admin)$holidays=array_values(array_filter($holidays,function($h)use($user){$ids=json_decode($h['employee_ids']?:'[]',true)?:[];return !$ids||in_array((int)$user['id'],array_map('intval',$ids),true);}));
                $result=['rows'=>$holidays];break;
            case 'holiday_save':
                $this->ops_require_admin();$id=(int)($d['id']??0);$name=sanitize_text_field($d['holiday_name']??'');if(!$name)throw new Exception('Holiday Name is required.');
                $ids=array_values(array_unique(array_filter(array_map('intval',(array)($d['employee_ids']??[])))));
                foreach($ids as $eid)if(!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}users WHERE id=%d",$eid)))throw new Exception('Invalid Employee.');
                $row=['holiday_date'=>$this->ops_date($d['holiday_date']??''),'holiday_name'=>$name,'employee_ids'=>wp_json_encode($ids)];
                $duplicate=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}holidays WHERE holiday_date=%s AND holiday_name=%s AND id<>%d",$row['holiday_date'],$name,$id));if($duplicate)throw new Exception('This Holiday already exists.');
                $this->ops_write($id?$wpdb->update($p.'holidays',$row,['id'=>$id]):$wpdb->insert($p.'holidays',$row));break;
            case 'holiday_delete':$this->ops_require_admin();$this->ops_write($wpdb->delete($p.'holidays',['id'=>(int)$d['id']]));break;
            case 'worker_save':
                $this->ops_require_admin();$id=(int)($d['id']??0);$cid=(int)($d['contractor_id']??0);$name=sanitize_text_field($d['employee_name']??'');
                if(!$name||!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}labour_contractors WHERE id=%d",$cid)))throw new Exception('Contractor and Employee Name are required.');
                if((float)($d['daily_rate']??0)<0||(float)($d['hourly_rate']??0)<0)throw new Exception('Rates cannot be negative.');
                if($id && $wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}time_entries WHERE worker_id=%d LIMIT 1",$id)) && $cid!==(int)$wpdb->get_var($wpdb->prepare("SELECT contractor_id FROM {$p}contractor_employees WHERE id=%d",$id)))throw new Exception('A used Employee cannot be moved to another Contractor.');
                $details=$this->ops_worker_details(array_merge((array)($id?$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}contractor_employees WHERE id=%d",$id),ARRAY_A):[]),$d));
                $row=array_merge($details,['contractor_id'=>$cid,'employee_name'=>$name,'daily_rate'=>round((float)($d['daily_rate']??0),2),'hourly_rate'=>round((float)($d['hourly_rate']??0),2),'status'=>($d['status']??'active')==='inactive'?'inactive':'active']);
                $this->ops_write($id?$wpdb->update($p.'contractor_employees',$row,['id'=>$id]):$wpdb->insert($p.'contractor_employees',$row));break;
            case 'time_list':$result=['rows'=>$this->ops_time_rows($d,$user)];break;
            case 'time_save':
                $date=$this->ops_date($d['work_date']??'');$eid=(int)($d['employee_id']??0);$wid=(int)($d['worker_id']??0);$site=(int)($d['site_id']??0);$worker=null;
                if($wid){
                    if(!$admin && !in_array('contractor_employees',json_decode($user['allowed_views']??'[]',true)?:[],true))throw new Exception('Contractor time entry requires permission.');
                    $worker=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}contractor_employees WHERE id=%d AND status='active'",$wid),ARRAY_A);if(!$worker)throw new Exception('Select an active Contractor Employee.');$eid=0;if(!$site)throw new Exception('Site is required for Contractor Employee.');$this->ops_site($site);
                }else{
                    if(!$eid||!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}users WHERE id=%d AND status='active'",$eid)))throw new Exception('Select an active Employee.');
                    if(!$admin && $eid!==(int)$user['id'])throw new Exception('You can enter only your own Time Sheet.'); if($site)$this->ops_site($site);
                }
                $lock=$this->ops_lock('time_'.($wid?'w'.$wid:'e'.$eid));$wpdb->query('START TRANSACTION');
                $existing=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}time_entries WHERE employee_id=%d AND worker_id=%d AND work_date=%s FOR UPDATE",$eid,$wid,$date),ARRAY_A);
                foreach($existing as $e){if($wid&&!$admin){$this->ops_site((int)$e['site_id']);if((int)$e['created_by']!==$uid)throw new Exception('This Employee day is managed by another user.');}}
                $all=(array)($d['rows']??[]);$saved=[];$intervals=[];$submitted_ids=[];
                foreach($all as $r){
                    $id=(int)($r['id']??0);if($id&&isset($submitted_ids[$id]))throw new Exception('Duplicate Time Sheet row.');if($id)$submitted_ids[$id]=true;$old=null;foreach($existing as $e)if((int)$e['id']===$id)$old=$e;
                    if($id&&!$old)throw new Exception('Time entry does not belong to this Employee and Date.');
                    if($old && ($old['status']==='approved'||(int)$old['bill_id'])){$saved[]=$id;$intervals[]=$this->ops_time_interval($date,$old['from_time'],$old['to_time']);continue;}
                    $from=sanitize_text_field($r['from_time']??'');$to=sanitize_text_field($r['to_time']??'');$activity=sanitize_textarea_field($r['activity']??'');
                    if(!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/',$from)||!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/',$to)||$to===$from||!$activity)throw new Exception('Enter Activity and different From and To times for each row.');
                    $interval=$this->ops_time_interval($date,$from,$to);
                    $minutes=(int)(($interval[1]-$interval[0])/60);
                    $intervals[]=$interval;
                    $row=['work_date'=>$date,'employee_id'=>$eid,'worker_id'=>$wid,'contractor_id'=>(int)($worker['contractor_id']??0),'site_id'=>$site,'from_time'=>$from,'to_time'=>$to,'minutes'=>$minutes,'activity'=>$activity,'daily_rate'=>$worker['daily_rate']??0,'hourly_rate'=>$worker['hourly_rate']??0,'status'=>'submitted','created_by'=>$uid,'updated_at'=>$now];
                    $this->ops_write($id?$wpdb->update($p.'time_entries',$row,['id'=>$id]):$wpdb->insert($p.'time_entries',$row));$saved[]=$id?:$wpdb->insert_id;
                }
                // Locked entries omitted by a client still participate in overlap validation.
                foreach($existing as $e)if(!in_array((int)$e['id'],array_map('intval',$saved),true)&&($e['status']==='approved'||(int)$e['bill_id'])){$saved[]=(int)$e['id'];$intervals[]=$this->ops_time_interval($date,$e['from_time'],$e['to_time']);}
                // Check adjacent dates as overnight work extends into the next calendar day.
                $previous=gmdate('Y-m-d',strtotime($date.' UTC')-86400);$next=gmdate('Y-m-d',strtotime($date.' UTC')+86400);
                $adjacent=$wpdb->get_results($wpdb->prepare("SELECT work_date,from_time,to_time FROM {$p}time_entries WHERE employee_id=%d AND worker_id=%d AND work_date BETWEEN %s AND %s AND work_date<>%s",$eid,$wid,$previous,$next,$date),ARRAY_A);
                foreach($adjacent as $entry)$intervals[]=$this->ops_time_interval($entry['work_date'],$entry['from_time'],$entry['to_time']);
                usort($intervals,function($a,$b){return $a[0]<=>$b[0];});for($i=1;$i<count($intervals);$i++)if($intervals[$i][0]<$intervals[$i-1][1])throw new Exception('Time entries cannot overlap for the same Employee.');
                foreach($existing as $e)if(!in_array((int)$e['id'],array_map('intval',$saved),true))$this->ops_write($wpdb->delete($p.'time_entries',['id'=>$e['id']]));
                $wpdb->query('COMMIT');break;
            case 'time_update':case 'time_delete':
                $id=(int)($d['id']??0);$entry=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}time_entries WHERE id=%d",$id),ARRAY_A);if(!$entry)throw new Exception('Time entry not found.');
                $lock=$this->ops_lock('time_'.((int)$entry['worker_id']?'w'.$entry['worker_id']:'e'.$entry['employee_id']));$wpdb->query('START TRANSACTION');
                $entry=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}time_entries WHERE id=%d FOR UPDATE",$id),ARRAY_A);if(!$entry)throw new Exception('Time entry not found.');
                if((int)$entry['bill_id'])throw new Exception('Billed time entries cannot be edited or deleted.');
                if(!$admin){
                    $own=(int)$entry['worker_id']===0&&(int)$entry['employee_id']===(int)$user['id'];
                    $delegated=(int)$entry['worker_id']>0&&(int)$entry['created_by']===$uid&&in_array('contractor_employees',json_decode($user['allowed_views']??'[]',true)?:[],true);
                    if((!$own&&!$delegated)||$entry['status']!=='submitted')throw new Exception('Only your own unapproved entries can be edited or deleted.');
                    if((int)$entry['site_id'])$this->ops_site((int)$entry['site_id']);
                }
                if($op==='time_delete'){$this->ops_write($wpdb->delete($p.'time_entries',['id'=>$id]));$wpdb->query('COMMIT');break;}
                $site=(int)($d['site_id']??$entry['site_id']);
                if((int)$entry['worker_id']>0){if(!$site)throw new Exception('Site is required for Contractor Employee.');$this->ops_site($site);}elseif($site)$this->ops_site($site);
                $from=sanitize_text_field($d['from_time']??'');$to=sanitize_text_field($d['to_time']??'');$activity=sanitize_textarea_field($d['activity']??'');
                if(!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/',$from)||!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/',$to)||$from===$to||!$activity)throw new Exception('Enter Activity and different From and To times.');
                $interval=$this->ops_time_interval($entry['work_date'],$from,$to);$previous=gmdate('Y-m-d',strtotime($entry['work_date'].' UTC')-86400);$next=gmdate('Y-m-d',strtotime($entry['work_date'].' UTC')+86400);
                $others=$wpdb->get_results($wpdb->prepare("SELECT work_date,from_time,to_time FROM {$p}time_entries WHERE employee_id=%d AND worker_id=%d AND work_date BETWEEN %s AND %s AND id<>%d",$entry['employee_id'],$entry['worker_id'],$previous,$next,$id),ARRAY_A);
                foreach($others as $other){$peer=$this->ops_time_interval($other['work_date'],$other['from_time'],$other['to_time']);if($interval[0]<$peer[1]&&$peer[0]<$interval[1])throw new Exception('Time entries cannot overlap for the same Employee.');}
                $status=$admin&&(int)$entry['worker_id']>0&&!empty($d['approved'])?'approved':'submitted';
                $this->ops_write($wpdb->update($p.'time_entries',['site_id'=>$site,'from_time'=>$from,'to_time'=>$to,'minutes'=>(int)(($interval[1]-$interval[0])/60),'activity'=>$activity,'status'=>$status,'approved_by'=>$status==='approved'?$uid:0,'updated_at'=>$now],['id'=>$id]));$wpdb->query('COMMIT');break;
            case 'time_status':
                $this->ops_require_admin();$id=(int)($d['id']??0);$lock=$this->ops_lock('time_status');$wpdb->query('START TRANSACTION');
                $e=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}time_entries WHERE id=%d FOR UPDATE",$id),ARRAY_A);if(!$e||(int)$e['bill_id'])throw new Exception('A billed entry is locked.');
                $st=$d['status']??'';if(!in_array($st,['approved','submitted'],true))throw new Exception('Invalid status.');
                $this->ops_write($wpdb->update($p.'time_entries',['status'=>$st,'approved_by'=>$st==='approved'?$uid:0,'updated_at'=>$now],['id'=>$id]));$wpdb->query('COMMIT');break;
            case 'bill_preview':case 'bill_create':
                $this->ops_require_admin();$cid=(int)($d['contractor_id']??0);$site=(int)($d['site_id']??0);$this->ops_site($site);$from=$this->ops_date($d['from']??'');$to=$this->ops_date($d['to']??'');if($to<$from)throw new Exception('Invalid date range.');
                $basis=$wpdb->get_var($wpdb->prepare("SELECT payment_basis FROM {$p}labour_contractors WHERE id=%d",$cid));if(!in_array($basis,['employee','time'],true))throw new Exception('Use the existing Contractor Bill Form for Direct Payment, or select an employee/time basis in Contractor Master.');
                if($op==='bill_create'){$lock=$this->ops_lock('bill_'.$cid);$wpdb->query('START TRANSACTION');}
                $entries=$wpdb->get_results($wpdb->prepare("SELECT e.*,w.employee_name FROM {$p}time_entries e JOIN {$p}contractor_employees w ON w.id=e.worker_id WHERE e.contractor_id=%d AND e.site_id=%d AND e.work_date BETWEEN %s AND %s AND e.status='approved' AND e.bill_id=0 ORDER BY e.worker_id,e.work_date,e.id".($op==='bill_create'?' FOR UPDATE':''),$cid,$site,$from,$to),ARRAY_A);
                $lines=[];$ids=[];$total=0;
                foreach($entries as $e){
                    $key=$basis==='employee'?$e['worker_id'].'|'.$e['work_date']:(string)$e['id'];
                    $ids[]=(int)$e['id'];if(isset($lines[$key]))continue;
                    // A daily-rate employee is payable once per calendar date across all Sites/bills.
                    if($basis==='employee' && $wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}time_entries WHERE worker_id=%d AND work_date=%s AND bill_id>0 LIMIT 1",$e['worker_id'],$e['work_date'])))throw new Exception('This Employee day is already billed. Split daily allocations before billing another Site.');
                    if($basis==='employee' && $wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}time_entries WHERE worker_id=%d AND work_date=%s AND site_id<>%d AND status='approved' AND bill_id=0 LIMIT 1",$e['worker_id'],$e['work_date'],$site)))throw new Exception('Daily-rate work spans multiple Sites. Use time-based billing for that Employee day.');
                    $units=$basis==='employee'?1:((int)$e['minutes']/60);$rate=(float)($basis==='employee'?$e['daily_rate']:$e['hourly_rate']);if($rate<=0)throw new Exception('An approved entry has no applicable rate. Correct the Employee rate and resubmit the entry before approval.');
                    $amount=round($units*$rate,2);$lines[$key]=['employee_name'=>$e['employee_name'],'work_date'=>$e['work_date'],'units'=>round($units,4),'rate'=>$rate,'amount'=>$amount];$total+=$amount;
                }
                $result=['rows'=>array_values($lines),'basis'=>$basis,'total'=>round($total,2)];
                if($op==='bill_create'){
                    if(!$ids)throw new Exception('No approved, unbilled Time Sheet entries found.');$no=sanitize_text_field($d['bill_no']??'');if(!$no)throw new Exception('Bill No. is required.');if($wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}contractor_bills WHERE bill_no=%s",$no)))throw new Exception('This Bill No. already exists.');$gst=round((float)($d['gst_amount']??0),2);if($gst<0)throw new Exception('GST cannot be negative.');
                    $this->ops_write($wpdb->insert($p.'contractor_bills',['bill_no'=>$no,'bill_date'=>$this->ops_date($d['bill_date']??''),'contractor_id'=>$cid,'site_id'=>$site,'from_date'=>$from,'to_date'=>$to,'taxable_amount'=>$total,'gst_amount'=>$gst,'total_amount'=>round($total+$gst,2),'payment_basis'=>$basis,'bill_reference'=>'Time Sheet','remarks'=>wp_json_encode(array_values($lines)),'status'=>'approved','created_by'=>$uid,'approved_by'=>$uid,'created_at'=>$now,'updated_at'=>$now]));
                    $bill=$wpdb->insert_id;$this->ops_write($wpdb->query("UPDATE {$p}time_entries SET bill_id=".(int)$bill.' WHERE id IN ('.implode(',',$ids).')'));$wpdb->query('COMMIT');$result['bill_id']=$bill;
                }break;
            case 'transfer_stock':
                $site=(int)($d['site_id']??0);$this->ops_site($site);$rows=[];
                foreach($wpdb->get_results("SELECT id,item_name label,unit_id FROM {$p}items WHERE status='active' ORDER BY item_name",ARRAY_A) as $i){$st=$this->site_available_stock($site,(int)$i['id']);if($st['available_qty']>0){$i['available_qty']=$st['available_qty'];$rows[]=$i;}}$result=['rows'=>$rows];break;
            case 'transfer_list':
                $w='1=1';$a=[];$scope=$this->employee_site_scope();if($scope){$list=implode(',',array_map('intval',$scope));$w.=" AND (t.from_site_id IN ($list) OR t.to_site_id IN ($list))";}
                if(!empty($d['site_id'])){$w.=' AND (t.from_site_id=%d OR t.to_site_id=%d)';$a[]=(int)$d['site_id'];$a[]=(int)$d['site_id'];}
                if(!empty($d['from'])){$w.=' AND t.transfer_date>=%s';$a[]=$this->ops_date($d['from']);}if(!empty($d['to'])){$w.=' AND t.transfer_date<=%s';$a[]=$this->ops_date($d['to']);}if(!empty($d['status'])){$w.=' AND t.status=%s';$a[]=sanitize_key($d['status']);}
                $sql="SELECT t.*,s.site_name from_site,d.site_name to_site FROM {$p}site_transfers t JOIN {$p}sites s ON s.id=t.from_site_id JOIN {$p}sites d ON d.id=t.to_site_id WHERE $w ORDER BY t.id DESC";$result=['rows'=>$wpdb->get_results($a?$wpdb->prepare($sql,$a):$sql,ARRAY_A)];break;
            case 'transfer_get':
                $t=$wpdb->get_row($wpdb->prepare("SELECT t.*,s.site_name from_site,d.site_name to_site FROM {$p}site_transfers t JOIN {$p}sites s ON s.id=t.from_site_id JOIN {$p}sites d ON d.id=t.to_site_id WHERE t.id=%d",(int)($d['id']??0)),ARRAY_A);if(!$t)throw new Exception('Transfer not found.');
                $scope=$this->employee_site_scope();if($scope&&!in_array((int)$t['from_site_id'],$scope,true)&&!in_array((int)$t['to_site_id'],$scope,true))throw new Exception('Transfer access denied.');
                $t['items']=$wpdb->get_results($wpdb->prepare("SELECT d.*,i.item_name,u.unit_name FROM {$p}site_transfer_items d JOIN {$p}items i ON i.id=d.item_id LEFT JOIN {$p}units u ON u.id=d.unit_id WHERE d.transfer_id=%d",$t['id']),ARRAY_A);
                $t['documents']=$wpdb->get_results($wpdb->prepare("SELECT file_url,original_filename FROM {$p}documents WHERE module='site_transfers' AND reference_id=%d ORDER BY id",$t['id']),ARRAY_A);
                $t['receipts']=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}site_transfer_receipts WHERE transfer_id=%d ORDER BY id",$t['id']),ARRAY_A);$result=$t;break;
            case 'transfer_save':
                $id=(int)($d['id']??0);$from=(int)($d['from_site_id']??0);$to=(int)($d['to_site_id']??0);$this->ops_site($from);
                if(!$to||$to===$from||!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}sites WHERE id=%d",$to)))throw new Exception('Select a different destination Site.');
                $lock=$this->ops_lock('transfer_save');$wpdb->query('START TRANSACTION');
                if($id){$old=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}site_transfers WHERE id=%d FOR UPDATE",$id),ARRAY_A);if(!$old||$old['status']!=='draft')throw new Exception('Only Draft transfers can be edited.');$this->ops_site((int)$old['from_site_id']);}
                $row=['transfer_date'=>$this->ops_date($d['transfer_date']??''),'from_site_id'=>$from,'to_site_id'=>$to,'vehicle_no'=>sanitize_text_field($d['vehicle_no']??''),'remarks'=>sanitize_textarea_field($d['remarks']??''),'updated_at'=>$now];
                if($id){$this->ops_write($wpdb->update($p.'site_transfers',$row,['id'=>$id]));$this->ops_write($wpdb->delete($p.'site_transfer_items',['transfer_id'=>$id]));}else{$row['created_by']=$uid;$this->ops_write($wpdb->insert($p.'site_transfers',$row));$id=$wpdb->insert_id;$this->ops_write($wpdb->update($p.'site_transfers',['transfer_no'=>'ST-'.date('ym',strtotime($row['transfer_date'])).'-'.str_pad($id,5,'0',STR_PAD_LEFT)],['id'=>$id]));}
                $seen=[];foreach((array)($d['items']??[]) as $it){$item=(int)($it['item_id']??0);$qty=round((float)($it['qty']??0),2);if(!$item||$qty<=0||isset($seen[$item]))throw new Exception('Select unique Items and positive quantities.');$seen[$item]=true;
                    $master=$wpdb->get_row($wpdb->prepare("SELECT unit_id FROM {$p}items WHERE id=%d AND status='active'",$item),ARRAY_A);if(!$master)throw new Exception('Select an active Item.');
                    $st=$this->site_available_stock($from,$item);if($qty>$st['available_qty']+0.0001)throw new Exception('Transfer Quantity exceeds available stock.');
                    $this->ops_write($wpdb->insert($p.'site_transfer_items',['transfer_id'=>$id,'item_id'=>$item,'unit_id'=>(int)$master['unit_id'],'qty'=>$qty,'rate'=>$st['rate'],'remarks'=>sanitize_text_field($it['remarks']??'')]));}
                if(!$seen)throw new Exception('Add at least one Item.');$wpdb->query('COMMIT');$result=['id'=>$id];break;
            case 'transfer_document':
                $id=(int)($d['id']??0);$t=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}site_transfers WHERE id=%d",$id),ARRAY_A);if(!$t)throw new Exception('Transfer not found.');
                $scope=$this->employee_site_scope();if($scope&&!in_array((int)$t['from_site_id'],$scope,true)&&!in_array((int)$t['to_site_id'],$scope,true))throw new Exception('Transfer access denied.');
                if(empty($_FILES['file']['name']))throw new Exception('Select a supporting document.');
                if((int)($_FILES['file']['size']??0)>10*1024*1024)throw new Exception('Supporting document must be 10 MB or smaller.');
                require_once ABSPATH.'wp-admin/includes/file.php';
                $up=wp_handle_upload($_FILES['file'],['test_form'=>false,'mimes'=>['pdf'=>'application/pdf','jpg|jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp']]);
                if(isset($up['error']))throw new Exception($up['error']);
                $this->ops_write($wpdb->insert($p.'documents',['module'=>'site_transfers','reference_id'=>$id,'document_type'=>'Transfer Supporting Document','file_url'=>esc_url_raw($up['url']),'original_filename'=>sanitize_file_name($_FILES['file']['name']),'uploaded_by'=>$uid,'uploaded_at'=>$now]));break;
            case 'transfer_action':
                $id=(int)($d['id']??0);$action=sanitize_key($d['action']??'');$lock=$this->ops_lock('stock_dispatch');$wpdb->query('START TRANSACTION');
                $t=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}site_transfers WHERE id=%d FOR UPDATE",$id),ARRAY_A);if(!$t)throw new Exception('Transfer not found.');
                $items=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}site_transfer_items WHERE transfer_id=%d FOR UPDATE",$id),ARRAY_A);
                if($action==='dispatch'){
                    $this->ops_site((int)$t['from_site_id']);if($t['status']!=='draft'||!$items)throw new Exception('Only a Draft transfer can be dispatched.');
                    foreach($items as $it){$st=$this->site_available_stock((int)$t['from_site_id'],(int)$it['item_id']);if((float)$it['qty']>$st['available_qty']+0.0001)throw new Exception('Insufficient source stock. Refresh and adjust the Draft.');}
                    $this->ops_write($wpdb->update($p.'site_transfers',['status'=>'dispatched','dispatched_by'=>$uid,'dispatched_at'=>$now,'updated_at'=>$now],['id'=>$id]));
                    foreach($items as $it)$this->ops_write($wpdb->insert($p.'stock_ledger',['transaction_date'=>substr($now,0,10),'location_type'=>'SITE','site_id'=>$t['from_site_id'],'item_id'=>$it['item_id'],'unit_id'=>$it['unit_id'],'transaction_type'=>'SITE_TRANSFER_OUT','reference_type'=>'site_transfers','reference_id'=>$id,'qty_in'=>0,'qty_out'=>$it['qty'],'rate'=>$it['rate'],'created_at'=>$now]));
                }elseif($action==='receive'){
                    $this->ops_site((int)$t['to_site_id']);if(!in_array($t['status'],['dispatched','partially_received'],true))throw new Exception('This transfer cannot receive more material.');$date=$this->ops_date($d['receipt_date']??'');if($date<substr($t['dispatched_at'],0,10)||$date>current_time('Y-m-d'))throw new Exception('Receipt Date must be between Dispatch Date and today.');
                    $input=[];foreach((array)($d['items']??[]) as $x){$key=(int)$x['id'];if(isset($input[$key]))throw new Exception('Duplicate receipt Item.');$input[$key]=$x;}
                    $changed=false;$remaining=0;$valid=[];foreach($items as $it){$key=(int)$it['id'];$valid[]=$key;$r=$input[$key]??[];$good=round((float)($r['received_qty']??0),2);$bad=round((float)($r['damaged_qty']??0),2);$left=round((float)$it['qty']-(float)$it['received_qty']-(float)$it['damaged_qty'],2);
                        if($good<0||$bad<0||$good+$bad>$left+0.0001)throw new Exception('Receipt and Damaged quantities cannot exceed the remaining quantity.');$remaining+=round($left-$good-$bad,2);if($good+$bad<=0)continue;$changed=true;
                        $this->ops_write($wpdb->insert($p.'site_transfer_receipts',['transfer_id'=>$id,'transfer_item_id'=>$key,'receipt_date'=>$date,'received_qty'=>$good,'damaged_qty'=>$bad,'created_by'=>$uid,'created_at'=>$now]));
                        $this->ops_write($wpdb->update($p.'site_transfer_items',['received_qty'=>round((float)$it['received_qty']+$good,2),'damaged_qty'=>round((float)$it['damaged_qty']+$bad,2)],['id'=>$key]));
                        if($good)$this->ops_write($wpdb->insert($p.'stock_ledger',['transaction_date'=>$date,'location_type'=>'SITE','site_id'=>$t['to_site_id'],'item_id'=>$it['item_id'],'unit_id'=>$it['unit_id'],'transaction_type'=>'SITE_TRANSFER_IN','reference_type'=>'site_transfers','reference_id'=>$id,'qty_in'=>$good,'qty_out'=>0,'rate'=>$it['rate'],'created_at'=>$now]));
                    }
                    if(array_diff(array_keys($input),$valid))throw new Exception('Invalid receipt Item.');if(!$changed)throw new Exception('Enter a quantity to receive or record as damaged.');
                    $this->ops_write($wpdb->update($p.'site_transfers',['status'=>$remaining>0.0001?'partially_received':'received','updated_at'=>$now],['id'=>$id]));
                }elseif($action==='cancel'){
                    $this->ops_require_admin();$this->ops_site((int)$t['from_site_id']);if($t['status']!=='draft')throw new Exception('Only Draft transfers can be cancelled. Dispatched stock requires reconciliation.');$this->ops_write($wpdb->update($p.'site_transfers',['status'=>'cancelled','updated_at'=>$now],['id'=>$id]));
                }else throw new Exception('Invalid transfer action.');
                $wpdb->query('COMMIT');break;
            default:throw new Exception('Unknown operation.');
            }
            if($lock)$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));
            wp_send_json_success($result);
        }catch(Throwable $e){
            $wpdb->query('ROLLBACK');if($lock)$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));
            wp_send_json_error(['message'=>$e->getMessage()]);
        }
    }
}
