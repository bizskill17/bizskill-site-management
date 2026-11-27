<?php
/** Site procurement: approved requisition -> PO -> direct site receipt. */
trait BSM_Site_Procurement {
    public function site_procurement_upgrade() {
        if(get_option('bsm_site_procurement_schema')==='1')return;
        global $wpdb;$p=$this->prefix;
        $this->migrate_purchase_orders();
        $changes=[
            'purchase_orders'=>['site_id'=>'BIGINT UNSIGNED NULL','requisition_id'=>'BIGINT UNSIGNED NULL','site_flow'=>'TINYINT NOT NULL DEFAULT 0'],
            'material_inwards'=>['site_flow'=>'TINYINT NOT NULL DEFAULT 0'],
            'material_returns'=>['purchase_receipt_id'=>'BIGINT UNSIGNED NULL','vendor_id'=>'BIGINT UNSIGNED NULL','site_flow'=>'TINYINT NOT NULL DEFAULT 0']
        ];
        foreach($changes as $table=>$columns)foreach($columns as $column=>$definition){
            if(!$wpdb->get_var("SHOW COLUMNS FROM {$p}{$table} LIKE '$column'")){
                if($wpdb->query("ALTER TABLE {$p}{$table} ADD $column $definition")===false)return;
            }
        }
        if($wpdb->query("CREATE TABLE IF NOT EXISTS {$p}site_stock_allocations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,warehouse_id BIGINT UNSIGNED NOT NULL,site_id BIGINT UNSIGNED NOT NULL,item_id BIGINT UNSIGNED NOT NULL,unit_id BIGINT UNSIGNED NULL,
            allocation_date DATE NOT NULL,qty DECIMAL(18,2) NOT NULL,rate DECIMAL(18,2) NOT NULL DEFAULT 0,reviewed_by BIGINT UNSIGNED NOT NULL,created_at DATETIME NOT NULL,
            PRIMARY KEY(id),KEY site_item(site_id,item_id),KEY source_item(warehouse_id,item_id)
        ) ".$wpdb->get_charset_collate())===false)return;
        // Only backfill unambiguous PO references. Historical receipts/stock remain untouched.
        $ok=$wpdb->query("UPDATE {$p}purchase_orders po JOIN (
            SELECT z.purchase_order_id,MIN(ri.requisition_id) requisition_id,MIN(r.site_id) site_id
            FROM (SELECT id purchase_order_item_id,purchase_order_id,requisition_item_id FROM {$p}purchase_order_items WHERE requisition_item_id>0
              UNION SELECT poi.id purchase_order_item_id,poi.purchase_order_id,a.requisition_item_id FROM {$p}purchase_order_item_allocations a JOIN {$p}purchase_order_items poi ON poi.id=a.purchase_order_item_id) z
            JOIN {$p}material_requisition_items ri ON ri.id=z.requisition_item_id JOIN {$p}material_requisitions r ON r.id=ri.requisition_id
            GROUP BY z.purchase_order_id HAVING COUNT(DISTINCT ri.requisition_id)=1 AND COUNT(DISTINCT r.site_id)=1 AND COUNT(DISTINCT z.purchase_order_item_id)=(SELECT COUNT(*) FROM {$p}purchase_order_items all_items WHERE all_items.purchase_order_id=z.purchase_order_id)
        ) x ON x.purchase_order_id=po.id SET po.site_id=x.site_id,po.requisition_id=x.requisition_id WHERE po.site_flow=0 AND po.site_id IS NULL");
        if($ok!==false)update_option('bsm_site_procurement_schema','1');
    }
    private function sp_access($views) {
        if($this->nt_admin())return;
        global $wpdb;$u=$wpdb->get_row($wpdb->prepare("SELECT status,allowed_views FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1",get_current_user_id()),ARRAY_A);
        $allowed=json_decode($u['allowed_views']??'[]',true);$hidden=$this->hidden_views();
        foreach($views as $view)if(($u['status']??'')==='active' && is_array($allowed) && in_array($view,$allowed,true) && !in_array($view,$hidden,true))return;
        wp_send_json_error(['message'=>'You do not have permission to access this operation.'],403);
    }
    private function sp_scope($alias) {
        $scope=$this->employee_site_scope();return $scope?' AND '.$alias.'.site_id IN ('.implode(',',array_map('intval',$scope)).')':'';
    }
    private function sp_site($id) {
        global $wpdb;if(!$id||!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}sites WHERE id=%d",$id)))throw new Exception('Select a valid Site.');
        $scope=$this->employee_site_scope();if($scope&&!in_array((int)$id,$scope,true))throw new Exception('You do not have access to this Site.');
    }
    private function sp_write($result) {if($result===false)throw new Exception('Unable to save the transaction. Please retry.');return $result;}
    private function sp_qty($value,$label='Quantity') {if(!is_numeric($value)||!is_finite((float)$value)||(float)$value<0)throw new Exception($label.' must be a valid non-negative number.');return round((float)$value,2);}
    private function sp_date($value,$optional=false) {if($optional&&$value==='')return null;return $this->ops_date($value);}
    private function sp_ordered($ri,$exclude=0) {
        global $wpdb;$p=$this->prefix;
        $value=$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(CASE WHEN poi.requisition_item_id=%d THEN poi.po_qty ELSE COALESCE((SELECT SUM(a.allocated_qty) FROM {$p}purchase_order_item_allocations a WHERE a.purchase_order_item_id=poi.id AND a.requisition_item_id=%d),0) END),0)
            FROM {$p}purchase_order_items poi JOIN {$p}purchase_orders po ON po.id=poi.purchase_order_id
            WHERE po.id<>%d AND LOWER(COALESCE(po.status,'')) NOT IN ('cancelled','rejected')",$ri,$ri,$exclude));
        if($value===null)throw new Exception('Unable to calculate ordered quantities.');return round((float)$value,2);
    }
    private function sp_requirements($site,$req=0,$exclude=0) {
        global $wpdb;$p=$this->prefix;if($site)$this->sp_site($site);
        $where=$site?$wpdb->prepare('r.site_id=%d',$site):'r.site_id>0'.$this->sp_scope('r');if($req)$where.=$wpdb->prepare(' AND r.id=%d',$req);
        $raw=$wpdb->get_results("SELECT ri.*,ri.id requisition_item_id,r.requisition_no,r.requisition_date,r.required_date,r.site_id,s.site_name,i.item_name,u.unit_name
            FROM {$p}material_requisition_items ri JOIN {$p}material_requisitions r ON r.id=ri.requisition_id JOIN {$p}sites s ON s.id=r.site_id LEFT JOIN {$p}items i ON i.id=ri.item_id LEFT JOIN {$p}units u ON u.id=ri.unit_id
            WHERE $where AND r.status IN ('approved','partially_dispatched','partially dispatched','fully_dispatched','fully dispatched') AND ri.approved_qty>0 ORDER BY r.required_date,r.id,ri.id",ARRAY_A);
        if($raw===null)throw new Exception('Unable to load approved requisitions.');
        $rows=[];foreach($raw as $x){$ordered=$this->sp_ordered((int)$x['id'],$exclude);$x['ordered_qty']=$ordered;$x['pending_po_qty']=max(0,round((float)$x['approved_qty']-$ordered,2));
            if($x['pending_po_qty']>0.009)$rows[]=$x;}
        return $rows;
    }
    public function ajax_pending_po_items() {
        $this->sp_access(['pending_po_items','purchase_order_form']);
        $this->verify_ajax();$this->site_procurement_upgrade();
        try{$site=(int)($_POST['site_id']??0);$req=(int)($_POST['requisition_id']??0);
            $all=$this->sp_requirements($site);$heads=[];foreach($all as $x){$id=(int)$x['requisition_id'];if(!isset($heads[$id]))$heads[$id]=['id'=>$id,'site_id'=>(int)$x['site_id'],'requisition_no'=>$x['requisition_no'],'requisition_date'=>$x['requisition_date'],'required_date'=>$x['required_date'],'site_name'=>$x['site_name'],'pending_items'=>0];$heads[$id]['pending_items']++;}
            $rows=$req?array_values(array_filter($all,fn($x)=>(int)$x['requisition_id']===$req)):[];
            $result=['rows'=>$rows,'requisitions'=>array_values($heads),'site_id'=>$site,'requisition_id'=>$req];
        }catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()]);}
        wp_send_json_success($result);
    }
    private function sp_po_create($d,$input) {
        global $wpdb;$p=$this->prefix;$site=(int)($d['site_id']??0);$req=(int)($d['requisition_id']??0);$this->sp_site($site);
        if(!$req)throw new Exception('Select an approved Requisition.');
        $date=$this->sp_date($d['po_date']??current_time('Y-m-d'));$required=$this->sp_date($d['required_date']??'',true);
        $lock=$this->ops_lock('site_procurement');
        try{$this->sp_write($wpdb->query('START TRANSACTION'));
            $request=sanitize_text_field($d['request_id']??'');$key=$request!==''?hash('sha256',$this->plugin_user_id().':'.$request):'';
            $fingerprint=hash('sha256',wp_json_encode([$site,$req,$date,$required,$input]));
            if($key!==''){
                $previous=$wpdb->get_results($wpdb->prepare("SELECT id,po_no,remarks FROM {$p}purchase_orders WHERE remarks LIKE %s ORDER BY id",'%'.$key.'%'),ARRAY_A)?:[];$replayed=[];
                foreach($previous as $prior){$meta=json_decode($prior['remarks'],true);if(($meta['submission_key']??'')!==$key)continue;
                    if(($meta['submission_hash']??'')!==$fingerprint)throw new Exception('This submission was already used with different PO details. Reload the form.');
                    $replayed[]=['id'=>(int)$prior['id'],'po_no'=>$prior['po_no']];
                }
                if($replayed){$this->sp_write($wpdb->query('COMMIT'));return ['created'=>$replayed,'message'=>'PO submission already processed.','replayed'=>true];}
            }
            $rows=$this->sp_requirements($site,$req);$by=[];foreach($rows as $x)$by[(int)$x['requisition_item_id']]=$x;
            $groups=[];$seen=[];
            foreach($input as $it){$ri=(int)($it['requisition_item_id']??0);$qty=$this->sp_qty($it['po_qty']??0,'PO Qty');if($qty<=0)continue;
                if(isset($seen[$ri])||!isset($by[$ri]))throw new Exception('Select unique pending items from the approved Requisition.');$seen[$ri]=true;$x=$by[$ri];
                if($qty>(float)$x['pending_po_qty']+0.009)throw new Exception('PO Qty exceeds pending approved quantity for '.$x['item_name'].'.');
                $vendor=(int)($it['vendor_id']??0);$quotes=[];$suppliers=[];$chosen=null;
                foreach((array)($it['quotations']??[]) as $q){$v=(int)($q['vendor_id']??0);$rate=$q['rate']??'';if(!$v&&$rate==='')continue;
                    if(!$v||!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}vendors WHERE id=%d AND status='active'",$v)))throw new Exception('Select active suppliers.');
                    $rate=$this->sp_qty($rate,'Rate');$suppliers[$v]=true;$quotes[]=['supplier_id'=>$v,'rate'=>$rate];if($v===$vendor)$chosen=$chosen===null?$rate:min($chosen,$rate);
                }
                if(count($quotes)<1||count($quotes)>3||$chosen===null)throw new Exception('Choose a supplier from one to three complete quotations.');
                $reason=sanitize_text_field($it['selection_reason']??'');if($chosen>min(array_column($quotes,'rate'))&&$reason==='')throw new Exception('Enter a reason for choosing a higher rate.');
                $groups[$vendor][]=['requisition_item_id'=>$ri,'item_id'=>(int)$x['item_id'],'unit_id'=>(int)$x['unit_id'],'po_qty'=>$qty,'rate'=>$chosen,'selected_rate'=>$chosen,'quotations'=>$quotes,'selection_reason'=>$reason];
            }
            if(!$groups)throw new Exception('Enter PO Qty and choose a Supplier for at least one pending item.');$created=[];
            foreach($groups as $vendor=>$items){$now=current_time('mysql');$no=$this->next_no('purchase_orders','PO');
                $this->sp_write($wpdb->insert($p.'purchase_orders',['po_no'=>$no,'po_date'=>$date,'vendor_id'=>$vendor,'site_id'=>$site,'requisition_id'=>$req,'site_flow'=>1,'warehouse_id'=>0,'required_date'=>$required?:($rows[0]['required_date']??null),'remarks'=>wp_json_encode(['supplier_quotations'=>$items,'submission_key'=>$key,'submission_hash'=>$fingerprint]),'status'=>'pending_approval','created_by'=>$this->plugin_user_id(),'created_at'=>$now,'updated_at'=>$now]));$id=(int)$wpdb->insert_id;
                foreach($items as $it){$this->sp_write($wpdb->insert($p.'purchase_order_items',['purchase_order_id'=>$id,'requisition_item_id'=>$it['requisition_item_id'],'item_id'=>$it['item_id'],'unit_id'=>$it['unit_id'],'po_qty'=>$it['po_qty'],'rate'=>$it['rate'],'created_at'=>$now]));$poi=(int)$wpdb->insert_id;
                    $this->sp_write($wpdb->insert($p.'purchase_order_item_allocations',['purchase_order_item_id'=>$poi,'requisition_item_id'=>$it['requisition_item_id'],'allocated_qty'=>$it['po_qty'],'created_at'=>$now]));}
                $created[]=['id'=>$id,'po_no'=>$no];}
            $this->sp_write($wpdb->query('COMMIT'));return ['created'=>$created,'message'=>count($created).' PO(s) created against the approved Requisition.'];
        }catch(Throwable $e){$wpdb->query('ROLLBACK');throw $e;}finally{$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));}
    }
    public function ajax_purchase_order_create() {
        $this->sp_access(['pending_po_items','purchase_order_form']);
        $this->verify_ajax();$this->site_procurement_upgrade();
        try{$items=json_decode(wp_unslash($_POST['items']??'[]'),true);if(!is_array($items))throw new Exception('Invalid PO items.');$result=$this->sp_po_create($_POST,$items);wp_send_json_success($result);}catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()]);}
    }
    public function ajax_purchase_order_manual_save() {$this->ajax_purchase_order_create();}
    private function sp_po_head($id) {
        global $wpdb;$p=$this->prefix;$h=$wpdb->get_row($wpdb->prepare("SELECT po.*,s.site_name,s.site_location,r.requisition_no,v.vendor_name,u.name created_by_name FROM {$p}purchase_orders po LEFT JOIN {$p}sites s ON s.id=po.site_id LEFT JOIN {$p}material_requisitions r ON r.id=po.requisition_id LEFT JOIN {$p}vendors v ON v.id=po.vendor_id LEFT JOIN {$p}users u ON u.id=po.created_by WHERE po.id=%d",$id),ARRAY_A);
        if(!$h)throw new Exception('PO not found.');if(!empty($h['site_id']))$this->sp_site((int)$h['site_id']);elseif($this->employee_site_scope())throw new Exception('This historical PO needs Site assignment by an administrator.');return $h;
    }
    public function ajax_purchase_order_get() {
        $this->sp_access(['purchase_order_master','pending_po_approval','purchase_order_items_master','purchase_order_pending_receipt','purchase_form']);
        $this->verify_ajax();$this->site_procurement_upgrade();global $wpdb;$p=$this->prefix;
        try{$h=$this->sp_po_head((int)($_POST['id']??0));$items=$wpdb->get_results($wpdb->prepare("SELECT poi.*,i.item_name,u.unit_name FROM {$p}purchase_order_items poi LEFT JOIN {$p}items i ON i.id=poi.item_id LEFT JOIN {$p}units u ON u.id=poi.unit_id WHERE purchase_order_id=%d ORDER BY poi.id",$h['id']),ARRAY_A)?:[];
            $h['received_qty']=0;foreach($items as &$it){$it['received_qty']=$this->po_item_received_qty($it['id']);$it['pending_qty']=max(0,round($it['po_qty']-$it['received_qty'],2));$h['received_qty']+=$it['received_qty'];}unset($it);wp_send_json_success(['head'=>$h,'items'=>$items]);
        }catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()]);}
    }

    private function po_share_eligible($status) {
        return in_array($status, ['approved', 'sent', 'partly_received', 'partially_received', 'fully_received'], true);
    }

    private function po_share_recipient($channel, $value) {
        $value = trim((string) $value);
        if ($channel === 'email') return is_email($value) ? $value : '';
        $mobile = preg_replace('/[\s().-]+/', '', $value);
        if (preg_match('/^[6-9][0-9]{9}$/D', $mobile)) return '91'.$mobile;
        return preg_match('/^\+?[1-9][0-9]{10,14}$/D', $mobile) ? ltrim($mobile, '+') : '';
    }

    private function po_share_key($id, $channel) {
        return 'bsm_po_share_'.(int) $id.'_'.$channel;
    }

    private function po_share_summary($attempt) {
        if (!$attempt) return null;
        return [
            'status' => $attempt['status'],
            'recipient' => $attempt['recipient'],
            'time' => wp_date('d/m/Y h:i A', $attempt['at'], new DateTimeZone('Asia/Kolkata')),
            'detail' => $attempt['detail'],
        ];
    }

    private function po_share_preview($po, $channel, $recipient) {
        global $wpdb;
        $company = $wpdb->get_row("SELECT company_name,email FROM {$this->prefix}company_settings ORDER BY id DESC LIMIT 1", ARRAY_A) ?: [];
        $companyName = sanitize_text_field($company['company_name'] ?? '') ?: 'BizSkill';
        $senderEmail = trim((string) ($company['email'] ?? ''));
        if ($channel === 'email' && !is_email($senderEmail)) {
            throw new Exception('Enter a valid Email in Company Settings before sending a PO.');
        }
        $number = sanitize_text_field($po['po_no']);
        $subject = 'Purchase Order '.$number.' | '.$companyName;
        $message = 'Dear '.($po['vendor_name'] ?: 'Supplier').",\n\n";
        $message .= $channel === 'email'
            ? "Please find attached approved Purchase Order ".$number.".\n"
            : "Please download approved Purchase Order ".$number." using the link below.\n";
        if ($channel === 'whatsapp') $message .= "Download PO PDF: {PO_PDF_URL}\n";
        $message .= "\nDelivery Site: ".($po['site_name'] ?: '—');
        if (!empty($po['site_location'])) $message .= "\nDelivery Address: ".$po['site_location'];
        $message .= "\nRequired Delivery Date: ".$this->nt_date($po['required_date']);
        $message .= "\n\nPlease confirm receipt and your expected delivery date.\n\nRegards,\n".$companyName;
        $revision = hash('sha256', wp_json_encode([$po['vendor_id'], $po['approved_at'], $po['updated_at'], $recipient, $subject, $message, $channel === 'email' ? $senderEmail : '']));
        return [
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message,
            'filename' => sanitize_file_name(str_replace('/', '-', $number).'.pdf') ?: 'Purchase-Order.pdf',
            'sender_email' => $senderEmail,
            'sender_name' => $companyName,
            'revision' => $revision,
        ];
    }

    public function ajax_po_share() {
        $this->verify_ajax();
        $this->sp_access(['purchase_order_master']);
        global $wpdb;
        $id = absint($_POST['id'] ?? 0);
        $channel = sanitize_key($_POST['channel'] ?? '');
        $send = ($_POST['mode'] ?? '') === 'send';
        if (!$id || !in_array($channel, ['email', 'whatsapp'], true)) {
            wp_send_json_error(['message' => 'Select a PO and a sharing channel.']);
        }

        $lock = null;
        try {
            if ($send) $lock = $this->ops_lock('site_procurement');
            $po = $this->sp_po_head($id);
            if (!$this->po_share_eligible($po['status'])) throw new Exception('Only approved POs can be shared.');
            $supplier = $wpdb->get_row($wpdb->prepare("SELECT mobile,email FROM {$this->prefix}vendors WHERE id=%d", $po['vendor_id']), ARRAY_A);
            $field = $channel === 'email' ? 'email' : 'mobile';
            $recipient = $this->po_share_recipient($channel, $supplier[$field] ?? '');
            if (!$recipient) throw new Exception('Update the supplier’s '.$field.' in Suppliers Master.');
            $preview = $this->po_share_preview($po, $channel, $recipient);
            $key = $this->po_share_key($id, $channel);
            $history = get_option($key, []);
            $last = $history ? end($history) : null;
            $preview['latest'] = $this->po_share_summary($last);

            if ($send) {
                $revision = sanitize_text_field($_POST['revision'] ?? '');
                if (!hash_equals($preview['revision'], $revision)) throw new Exception('The PO or recipient changed. Reopen the preview.');
                $request = sanitize_text_field($_POST['request'] ?? '');
                if (!preg_match('/^[a-zA-Z0-9-]{16,64}$/D', $request)) throw new Exception('Reopen the preview before sending.');
                foreach ($history as $attempt) {
                    if ($attempt['request'] === $request) throw new Exception('This send was already submitted. Reopen the preview to see its status.');
                }
                if (($last['request'] ?? '') !== sanitize_text_field($_POST['previous'] ?? '')) throw new Exception('The send history changed. Reopen the preview.');
                $confirmed = ($_POST['resend'] ?? '') === '1';
                if ($last && in_array($last['status'], ['accepted', 'review_required', 'sending'], true) && !$confirmed) {
                    throw new Exception('Confirm the resend before continuing.');
                }

                $attempt = [
                    'request' => $request, 'at' => time(), 'recipient' => $recipient,
                    'status' => 'sending', 'detail' => 'Send started.', 'user_id' => get_current_user_id(),
                ];
                $history[] = $attempt;
                if (!update_option($key, $history, false)) throw new Exception('Unable to record the send attempt.');
                try {
                    $document = $this->generate_purchase_order_pdf($id, $channel === 'email');
                    [$attempt['status'], $attempt['detail']] = !$document
                        ? ['failed', 'Unable to generate the PO PDF.']
                        : ($channel === 'email' ? $this->po_share_email($preview, $document) : $this->po_share_whatsapp($preview, $document));
                } catch (Throwable $error) {
                    $attempt['status'] = 'review_required';
                    $attempt['detail'] = 'Send interrupted. Check the provider before retrying.';
                }
                $history[count($history) - 1] = $attempt;
                if (!update_option($key, $history, false)) throw new Exception('Unable to save the result. Check the provider before retrying.');
                $preview['latest'] = $this->po_share_summary($attempt);
                $last = $attempt;
            }
            $preview['previous'] = $last['request'] ?? '';
            $preview['message'] = str_replace('{PO_PDF_URL}', '(link added when sent)', $preview['message']);
            unset($preview['sender_email'], $preview['sender_name']);
            $result = $preview;
        } catch (Throwable $error) {
            $failure = $error->getMessage();
        } finally {
            if ($lock) $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
        }
        if (isset($failure)) wp_send_json_error(['message' => $failure]);
        wp_send_json_success($result);
    }

    private function po_share_email($preview, $document) {
        require_once ABSPATH.'wp-admin/includes/file.php';
        $file = wp_tempnam($preview['filename']);
        if (!$file) return ['failed', 'Unable to prepare the PO PDF attachment.'];
        // Use a private temporary directory so the attachment retains its PDF filename.
        $dir = $file.'.d';
        $path = trailingslashit($dir).$preview['filename'];
        try {
            if (!mkdir($dir, 0700) || file_put_contents($path, $document) !== strlen($document)) {
                return ['failed', 'Unable to prepare the PO PDF attachment.'];
            }
            $fromEmail = static function () use ($preview) { return $preview['sender_email']; };
            $fromName = static function () use ($preview) { return $preview['sender_name']; };
            $headers = [
                'From: '.$preview['sender_name'].' <'.$preview['sender_email'].'>',
                'Reply-To: '.$preview['sender_email'],
            ];
            $reason = '';
            $capture = static function ($error) use (&$reason) {
                $message = $error->get_error_message();
                if (preg_match('/authenticat|credentials/i', $message)) {
                    $reason = 'SMTP authentication failed. Check the WordPress mail settings.';
                } elseif (preg_match('/connect|timed out|timeout/i', $message)) {
                    $reason = 'WordPress could not connect to the mail server. Check the SMTP settings.';
                } else {
                    $reason = 'WordPress mail failed. Check the mail plugin log for the transport error.';
                }
            };
            add_filter('wp_mail_from', $fromEmail, PHP_INT_MAX);
            add_filter('wp_mail_from_name', $fromName, PHP_INT_MAX);
            add_action('wp_mail_failed', $capture);
            try {
                $accepted = wp_mail($preview['recipient'], $preview['subject'], $preview['message'], $headers, [$path]);
                return $accepted
                    ? ['accepted', 'Accepted by the mail service.']
                    : ['failed', $reason ?: 'WordPress mail returned a failure. Check the mail configuration and retry.'];
            } finally {
                remove_action('wp_mail_failed', $capture);
                remove_filter('wp_mail_from_name', $fromName, PHP_INT_MAX);
                remove_filter('wp_mail_from', $fromEmail, PHP_INT_MAX);
            }
        } finally {
            if (is_file($path)) unlink($path);
            if (is_dir($dir)) rmdir($dir);
            unlink($file);
        }
    }

    private function po_share_whatsapp($preview, $pdfUrl) {
        $settings = $this->nt_settings();
        $password = $this->nt_password($settings['password']);
        if (!$settings['username'] || !$password || get_option('bsm_notifications_blocked')) {
            return ['failed', 'Check the MessageAutoSender credentials in Settings.'];
        }
        $response = wp_remote_post('https://app.messageautosender.com/api/v1/message/create', [
            'timeout' => 20,
            'redirection' => 0,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.base64_encode($settings['username'].':'.$password),
            ],
            'body' => wp_json_encode([
                'receiverMobileNo' => $preview['recipient'],
                'message' => [str_replace('{PO_PDF_URL}', $pdfUrl, $preview['message'])],
            ]),
        ]);
        if (is_wp_error($response)) return ['review_required', 'Provider outcome unknown. Check WhatsApp history before retrying.'];
        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status = strtolower((string) ($body['status'] ?? ''));
        if ($code >= 500) return ['review_required', 'Provider outcome unknown (HTTP '.$code.'). Check WhatsApp history.'];
        if ($code < 200 || $code >= 300 || !empty($body['error']) || (isset($body['success']) && !$body['success']) || in_array($status, ['failed', 'error'], true)) {
            return ['failed', 'MessageAutoSender rejected the request (HTTP '.$code.').'];
        }
        $confirmed = preg_match('/^2[0-9]{2}$/D', $status)
            && strtolower(trim((string) ($body['message'] ?? ''))) === 'success'
            && !empty($body['result']['id']);
        if ($confirmed || !empty($body['success']) || !empty($body['id']) || !empty($body['messageId']) || !empty($body['data']) || in_array($status, ['success', 'accepted', 'queued'], true)) {
            return ['accepted', 'Accepted by MessageAutoSender.'];
        }
        return ['review_required', 'Provider response was inconclusive. Check WhatsApp history before retrying.'];
    }

    public function ajax_po_share_pdf() {
        $token = sanitize_text_field($_GET['token'] ?? '');
        $file = preg_match('/^[0-9a-f]{48}$/D', $token) ? get_transient('bsm_po_share_pdf_'.$token) : false;
        if (!$file) wp_die('Attachment unavailable.', '', ['response' => 404]);
        nocache_headers();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'.$file['filename'].'"');
        echo base64_decode($file['pdf'], true);
        exit;
    }

    public function ajax_purchase_order_master() {
        $this->sp_access(['purchase_order_master','pending_po_approval']);
        $this->verify_ajax();$this->site_procurement_upgrade();global $wpdb;$p=$this->prefix;
        $where='1=1'.$this->sp_scope('po');$args=[];
        foreach(['site_id','vendor_id'] as $key)if(!empty($_POST[$key])){$where.=" AND po.$key=%d";$args[]=(int)$_POST[$key];}
        if(!empty($_POST['status'])){$where.=' AND po.status=%s';$args[]=sanitize_key($_POST['status']);}
        if(!empty($_POST['search'])){$where.=' AND (po.po_no LIKE %s OR s.site_name LIKE %s OR r.requisition_no LIKE %s OR v.vendor_name LIKE %s)';$term='%'.$wpdb->esc_like(sanitize_text_field($_POST['search'])).'%';array_push($args,$term,$term,$term,$term);}
        $sql="SELECT po.*,s.site_name,r.requisition_no,v.vendor_name,v.mobile supplier_mobile,v.email supplier_email,u.name created_by_name FROM {$p}purchase_orders po LEFT JOIN {$p}sites s ON s.id=po.site_id LEFT JOIN {$p}material_requisitions r ON r.id=po.requisition_id LEFT JOIN {$p}vendors v ON v.id=po.vendor_id LEFT JOIN {$p}users u ON u.id=po.created_by WHERE $where ORDER BY po.id DESC";
        $rows=$wpdb->get_results($args?$wpdb->prepare($sql,$args):$sql,ARRAY_A)?:[];
        foreach($rows as &$x){
            if ($this->po_share_eligible($x['status'])) {
                foreach (['whatsapp' => 'supplier_mobile', 'email' => 'supplier_email'] as $channel => $field) {
                    $history = get_option($this->po_share_key($x['id'], $channel), []);
                    $x['sharing'][$channel] = [
                        'available' => (bool) $this->po_share_recipient($channel, $x[$field]),
                        'latest' => $this->po_share_summary($history ? end($history) : null),
                    ];
                }
            }
            unset($x['supplier_mobile'], $x['supplier_email']);
            $x['approved_date']=$this->requisition_approved_date($x['approved_at']??'');$x['created_time_ist']='';if(!empty($x['created_at'])&&substr($x['created_at'],0,4)!=='0000'){try{$stamp=new DateTimeImmutable($x['created_at'],wp_timezone());$x['created_time_ist']=$stamp->setTimezone(new DateTimeZone('Asia/Kolkata'))->format('H:i');}catch(Exception $e){}}$x['approved_by_name']=$this->nt_name($x['approved_by']??0,true);$x['po_qty']=0;$x['received_qty']=0;$x['total_amount']=0;$its=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}purchase_order_items WHERE purchase_order_id=%d",$x['id']),ARRAY_A)?:[];$x['item_count']=count($its);foreach($its as $it){$x['po_qty']+=$it['po_qty'];$x['received_qty']+=$this->po_item_received_qty($it['id']);$x['total_amount']+=$it['po_qty']*$it['rate'];}$x['pending_qty']=max(0,round($x['po_qty']-$x['received_qty'],2));}unset($x);wp_send_json_success(['rows'=>$rows]);
    }
    public function ajax_purchase_order_items_master() {
        $this->sp_po_item_rows(false);
    }
    public function ajax_purchase_order_pending_receipt() {$this->sp_po_item_rows(true);}
    private function sp_po_item_rows($pending) {
        $this->sp_access($pending?['purchase_order_pending_receipt','purchase_form']:['purchase_order_items_master']);
        $this->verify_ajax();$this->site_procurement_upgrade();global $wpdb;$p=$this->prefix;$where='1=1'.$this->sp_scope('po');$args=[];
        if($pending)$where.=" AND po.site_id>0 AND po.requisition_id>0 AND LOWER(po.status) IN ('approved','partly_received','partially_received','fully_received')";
        foreach(['site_id','vendor_id'] as $key)if(!empty($_POST[$key])){$where.=" AND po.$key=%d";$args[]=(int)$_POST[$key];}
        if(!empty($_POST['search'])){$where.=' AND (po.po_no LIKE %s OR s.site_name LIKE %s OR r.requisition_no LIKE %s OR i.item_name LIKE %s)';$term='%'.$wpdb->esc_like(sanitize_text_field($_POST['search'])).'%';array_push($args,$term,$term,$term,$term);}
        $sql="SELECT poi.*,poi.id po_item_id,po.po_no,po.po_date,po.created_at po_created_at,po.approved_at,po.site_id,po.requisition_id,po.remarks,po.required_date,po.status,po.vendor_id,s.site_name,r.requisition_no,v.vendor_name,i.item_name,u.unit_name,COALESCE(i.gst_rate,0) gst_rate FROM {$p}purchase_order_items poi JOIN {$p}purchase_orders po ON po.id=poi.purchase_order_id LEFT JOIN {$p}sites s ON s.id=po.site_id LEFT JOIN {$p}material_requisitions r ON r.id=po.requisition_id LEFT JOIN {$p}vendors v ON v.id=po.vendor_id LEFT JOIN {$p}items i ON i.id=poi.item_id LEFT JOIN {$p}units u ON u.id=poi.unit_id WHERE $where ORDER BY po.po_date,po.id,poi.id";
        $raw=$wpdb->get_results($args?$wpdb->prepare($sql,$args):$sql,ARRAY_A)?:[];$rows=[];foreach($raw as $x){$x['approved_date']=$this->requisition_approved_date($x['approved_at']??'');$x['created_time_ist']='';if(!empty($x['po_created_at'])&&substr($x['po_created_at'],0,4)!=='0000'){try{$stamp=new DateTimeImmutable($x['po_created_at'],wp_timezone());$x['created_time_ist']=$stamp->setTimezone(new DateTimeZone('Asia/Kolkata'))->format('H:i');}catch(Exception $e){}}$x['received_qty']=$this->po_item_received_qty($x['id']);$x['pending_qty']=max(0,round($x['po_qty']-$x['received_qty'],2));if(!$pending||$x['pending_qty']>0.009)$rows[]=$x;}wp_send_json_success(['rows'=>$rows]);
    }
    public function ajax_purchase_order_update() {
        $this->sp_access(['purchase_order_master']);
        $this->verify_ajax();$this->site_procurement_upgrade();global $wpdb;$p=$this->prefix;$lock=null;
        try{$lock=$this->ops_lock('site_procurement');$this->sp_write($wpdb->query('START TRANSACTION'));$id=(int)($_POST['id']??0);$po=$this->sp_po_head($id);$d=json_decode(wp_unslash($_POST['data']??'{}'),true);$input=json_decode(wp_unslash($_POST['items']??'[]'),true);
            if(!$po['site_id']||!$po['requisition_id']||$po['status']==='cancelled'||!is_array($input)||!$input)throw new Exception('This PO cannot be edited. Historical POs require reference reconciliation.');
            $available=[];foreach($this->sp_requirements((int)$po['site_id'],(int)$po['requisition_id'],$id) as $r)$available[$r['requisition_item_id']]=$r;
            $db=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}purchase_order_items WHERE purchase_order_id=%d",$id),ARRAY_A)?:[];$map=[];foreach($db as $it)$map[(int)$it['id']]=$it;
            if(count($input)!==count($map))throw new Exception('Keep the existing PO items; set unused quantities to zero.');$seen=[];$used=[];$received=0;$financial_change=false;$snapshot=json_decode($po['remarks']??'{}',true);if(!is_array($snapshot))$snapshot=[];
            foreach($input as $it){$key=(int)($it['id']??0);if(!isset($map[$key])||isset($seen[$key]))throw new Exception('Invalid PO Item.');$seen[$key]=true;$old=$map[$key];$ri=(int)$old['requisition_item_id'];if(!$ri)throw new Exception('Legacy item linkage needs reconciliation.');$qty=$this->sp_qty($it['po_qty']??0);$rate=$this->sp_qty($it['rate']??0,'Rate');if(abs($rate-(float)$old['rate'])>0.009)$financial_change=true;$got=$this->po_item_received_qty($key);$received+=$got;
                if($qty+0.009<$got)throw new Exception('PO Qty cannot be less than Received Qty.');$used[$ri]=($used[$ri]??0)+$qty;
                if($used[$ri]>(float)($available[$ri]['pending_po_qty']??0)+0.009)throw new Exception('PO Qty exceeds the approved requisition balance.');
                if($got>0.009&&abs($rate-(float)$old['rate'])>0.009)throw new Exception('Rate cannot change after receipt.');
                $this->sp_write($wpdb->update($p.'purchase_order_items',['po_qty'=>$qty,'rate'=>$rate],['id'=>$key]));
                foreach(($snapshot['supplier_quotations']??[]) as $n=>$quoteLine)if((int)($quoteLine['requisition_item_id']??0)===$ri || (int)($quoteLine['item_id']??0)===(int)$old['item_id']){$snapshot['supplier_quotations'][$n]['selected_rate']=$rate;$snapshot['supplier_quotations'][$n]['po_qty']=$qty;}
                $this->sp_write($wpdb->delete($p.'purchase_order_item_allocations',['purchase_order_item_id'=>$key]));$this->sp_write($wpdb->insert($p.'purchase_order_item_allocations',['purchase_order_item_id'=>$key,'requisition_item_id'=>$ri,'allocated_qty'=>$qty,'created_at'=>current_time('mysql')]));}
            $vendor=(int)($d['vendor_id']??$po['vendor_id']);if(!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}vendors WHERE id=%d AND status='active'",$vendor)))throw new Exception('Select an active Supplier.');if($received>0.009&&$vendor!=(int)$po['vendor_id'])throw new Exception('Supplier cannot change after receipt.');
            $financial_change=$financial_change||$vendor!=(int)$po['vendor_id'];$note=sanitize_text_field($d['remarks']??'');if($financial_change&&$note==='')throw new Exception('Enter an amendment reason for Supplier or Rate changes.');$snapshot['last_amendment']=['reason'=>$note,'by'=>get_current_user_id(),'at'=>current_time('mysql')];
            $this->sp_write($wpdb->update($p.'purchase_orders',['remarks'=>wp_json_encode($snapshot),'po_date'=>$this->sp_date($d['po_date']??$po['po_date']),'required_date'=>$this->sp_date($d['required_date']??'',true),'vendor_id'=>$vendor,'status'=>'pending_approval','approved_by'=>null,'approved_at'=>null,'updated_at'=>current_time('mysql')],['id'=>$id]));
            $this->sp_write($wpdb->query('COMMIT'));$result=['message'=>'PO updated and sent for approval.'];
        }catch(Throwable $e){$wpdb->query('ROLLBACK');$error=$e->getMessage();}finally{if($lock)$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));}
        if(isset($error))wp_send_json_error(['message'=>$error]);wp_send_json_success($result);
    }
    public function ajax_purchase_order_cancel() {
        $this->sp_access(['purchase_order_master']);
        $this->verify_ajax();$this->site_procurement_upgrade();global $wpdb;$lock=null;
        try{$lock=$this->ops_lock('site_procurement');$po=$this->sp_po_head((int)($_POST['id']??0));$items=$wpdb->get_results($wpdb->prepare("SELECT id FROM {$this->prefix}purchase_order_items WHERE purchase_order_id=%d",$po['id']),ARRAY_A)?:[];foreach($items as $it)if($this->po_item_received_qty($it['id'])>0.009)throw new Exception('PO cannot be cancelled after receipt.');$this->sp_write($wpdb->update($this->prefix.'purchase_orders',['status'=>'cancelled','updated_at'=>current_time('mysql')],['id'=>$po['id']]));}
        catch(Throwable $e){$error=$e->getMessage();}finally{if($lock)$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));}
        if(isset($error))wp_send_json_error(['message'=>$error]);wp_send_json_success(['message'=>'PO cancelled; requisition quantities released.']);
    }
    private function sp_sync_site_ledger($type,$id,$site,$date,$items,$in) {
        global $wpdb;$p=$this->prefix;
        $this->sp_write($wpdb->delete($p.'stock_ledger',['reference_type'=>$type,'reference_id'=>$id]));
        foreach($items as $it)$this->sp_write($wpdb->insert($p.'stock_ledger',['transaction_date'=>$date,'location_type'=>'SITE','site_id'=>$site,'warehouse_id'=>null,'item_id'=>$it['item_id'],'unit_id'=>$it['unit_id'],'transaction_type'=>$in?'PURCHASE_INWARD':'SUPPLIER_RETURN','reference_type'=>$type,'reference_id'=>$id,'qty_in'=>$in?$it['qty']:0,'qty_out'=>$in?0:$it['qty'],'rate'=>$it['rate'],'created_at'=>current_time('mysql')]));
    }
    private function sp_purchase_save($d) {
        $this->sp_access(['purchase_form']);
        global $wpdb;$p=$this->prefix;$stockLock=$this->ops_lock('stock_dispatch');$poLock=null;
        try{$poLock=$this->ops_lock('site_procurement');$this->sp_write($wpdb->query('START TRANSACTION'));
            if(!empty($d['id']))throw new Exception('Posted receipts cannot be edited. Upload invoice documents separately.');
            $po=$this->sp_po_head((int)($d['purchase_order_id']??0));
            if(!$po['site_id']||!$po['requisition_id'])throw new Exception('Assign this historical PO to its Site and Requisition before receipt.');
            if(!in_array($po['status'],['approved','partly_received','partially_received'],true))throw new Exception('Approve the PO before recording receipt.');
            $date=$this->sp_date($d['inward_date']??current_time('Y-m-d'));if($date<$po['po_date'])throw new Exception('Receipt Date cannot precede PO Date.');
            $bill=sanitize_key($d['bill_type']??'');if(!in_array($bill,['','gst_bill','non_gst_bill'],true))throw new Exception('Invalid Bill Type.');
            $taxType=$wpdb->get_var($wpdb->prepare("SELECT gst_type FROM {$p}vendors WHERE id=%d",$po['vendor_id']));if($bill==='non_gst_bill')$taxType='no_gst';
            $items=[];$seen=[];$taxable=0;$cgst=0;$sgst=0;$igst=0;$total=0;
            foreach((array)($d['_items']??[]) as $it){$key=(int)($it['purchase_order_item_id']??0);$qty=$this->sp_qty($it['qty']??0,'Received Qty');if($qty<=0)continue;
                if(!$key||isset($seen[$key]))throw new Exception('Select unique PO items.');$seen[$key]=true;
                $x=$wpdb->get_row($wpdb->prepare("SELECT poi.*,i.gst_rate FROM {$p}purchase_order_items poi LEFT JOIN {$p}items i ON i.id=poi.item_id WHERE poi.id=%d AND poi.purchase_order_id=%d",$key,$po['id']),ARRAY_A);
                if(!$x)throw new Exception('Item does not belong to this PO.');if($qty>round($x['po_qty']-$this->po_item_received_qty($key),2)+0.009)throw new Exception('Received Qty exceeds pending PO quantity.');
                $rate=(float)$x['rate'];$base=round($qty*$rate,2);$gst=max(0,(float)$x['gst_rate']);$c=$s=$i=0;
                if($taxType==='intra_state'){$c=round($base*$gst/200,2);$s=$c;}elseif($taxType==='inter_state')$i=round($base*$gst/100,2);else $gst=0;
                $line=round($base+$c+$s+$i,2);$taxable+=$base;$cgst+=$c;$sgst+=$s;$igst+=$i;$total+=$line;
                $items[]=['purchase_order_item_id'=>$key,'item_id'=>$x['item_id'],'unit_id'=>$x['unit_id'],'qty'=>$qty,'approved_qty'=>$qty,'rate'=>$rate,'taxable_amount'=>$base,'gst_rate'=>$gst,'cgst_amount'=>$c,'sgst_amount'=>$s,'igst_amount'=>$i,'total_amount'=>$line,'remarks'=>sanitize_text_field($it['remarks']??'')];}
            if(!$items)throw new Exception('Enter Received Qty for at least one PO item.');
            $other=$this->sp_qty($d['other_expenses']??0,'Other Expenses');$otherGst=$this->sp_qty($d['other_expense_gst']??0,'GST on Other Expense');$round=$d['round_off']??0;if(!is_numeric($round)||!is_finite((float)$round))throw new Exception('Invalid Round Off.');$round=round((float)$round,2);
            $now=current_time('mysql');$this->sp_write($wpdb->insert($p.'material_inwards',['inward_no'=>$this->next_no('material_inwards','MIN'),'site_id'=>(int)$po['site_id'],'site_flow'=>1,'warehouse_id'=>0,'purchase_order_id'=>$po['id'],'vendor_id'=>$po['vendor_id'],'inward_type'=>'purchase','inward_date'=>$date,'supplier_invoice_no'=>sanitize_text_field($d['supplier_invoice_no']??''),'supplier_invoice_date'=>$this->sp_date($d['supplier_invoice_date']??'',true),'bill_type'=>$bill?:null,'taxable_amount'=>$taxable,'cgst_amount'=>$cgst,'sgst_amount'=>$sgst,'igst_amount'=>$igst,'other_expenses'=>$other,'other_expense_gst'=>$otherGst,'round_off'=>$round,'total_amount'=>round($total+$other+$otherGst+$round,2),'status'=>'approved','created_by'=>get_current_user_id(),'approved_by'=>$this->plugin_user_id(),'approved_at'=>$now,'created_at'=>$now,'updated_at'=>$now]));$id=(int)$wpdb->insert_id;
            foreach($items as $it){$it['inward_id']=$id;$this->sp_write($wpdb->insert($p.'material_inward_items',$it));}
            $this->sp_sync_site_ledger('material_inwards',$id,(int)$po['site_id'],$date,$items,true);$this->refresh_purchase_order_status($po['id']);$this->sp_write($wpdb->query('COMMIT'));return ['id'=>$id,'inward_id'=>$id,'message'=>'Purchase received into Site inventory.'];
        }catch(Throwable $e){$wpdb->query('ROLLBACK');throw $e;}finally{if($poLock)$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$poLock));$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$stockLock));}
    }
    public function ajax_purchase_order_receive_save() {
        $this->sp_access(['purchase_form']);
        $this->verify_ajax();$this->site_procurement_upgrade();global $wpdb;
        try{$poi=(int)($_POST['po_item_id']??0);$po=(int)$wpdb->get_var($wpdb->prepare("SELECT purchase_order_id FROM {$this->prefix}purchase_order_items WHERE id=%d",$poi));$d=$_POST;$d['purchase_order_id']=$po;$d['_items']=[['purchase_order_item_id'=>$poi,'qty'=>$_POST['qty']??0]];$result=$this->sp_purchase_save($d);wp_send_json_success($result);}catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()]);}
    }
    private function sp_return_save($d) {
        $this->sp_access(['return_form']);
        global $wpdb;$p=$this->prefix;$lock=$this->ops_lock('stock_dispatch');
        try{$this->sp_write($wpdb->query('START TRANSACTION'));if(!empty($d['id']))throw new Exception('Posted Supplier Returns cannot be edited.');$site=(int)($d['site_id']??0);$this->sp_site($site);$receipt=(int)($d['purchase_receipt_id']??0);
            $head=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}material_inwards WHERE id=%d AND site_id=%d AND site_flow=1 AND status='approved'",$receipt,$site),ARRAY_A);if(!$head)throw new Exception('Select an original Goods Receipt for this Site.');
            $date=$this->sp_date($d['return_date']??current_time('Y-m-d'));if($date<$head['inward_date'])throw new Exception('Return Date cannot precede Receipt Date.');$items=[];$seen=[];
            foreach((array)($d['_items']??[]) as $it){$item=(int)($it['item_id']??0);$qty=$this->sp_qty($it['qty']??0,'Return Qty');if($qty<=0)continue;if(!$item||isset($seen[$item]))throw new Exception('Select unique receipt items.');$seen[$item]=true;
                $original=$wpdb->get_row($wpdb->prepare("SELECT item_id,unit_id,SUM(qty) qty,SUM(qty*rate)/NULLIF(SUM(qty),0) rate FROM {$p}material_inward_items WHERE inward_id=%d AND item_id=%d GROUP BY item_id,unit_id",$receipt,$item),ARRAY_A);if(!$original)throw new Exception('Item is not on the selected Receipt.');
                $returned=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(ri.qty),0) FROM {$p}material_return_items ri JOIN {$p}material_returns r ON r.id=ri.return_id WHERE r.purchase_receipt_id=%d AND ri.item_id=%d AND r.status<>'cancelled'",$receipt,$item));
                if($qty>(float)$original['qty']-$returned+0.009)throw new Exception('Return Qty exceeds the unreturned Receipt quantity.');$stock=$this->site_available_stock($site,$item);if($qty>$stock['available_qty']+0.009)throw new Exception('Return Qty exceeds Site stock.');
                $items[]=['item_id'=>$item,'unit_id'=>$original['unit_id'],'qty'=>$qty,'rate'=>$original['rate'],'remarks'=>sanitize_text_field($it['remarks']??'')];}
            if(!$items)throw new Exception('Enter at least one Return item.');$now=current_time('mysql');$this->sp_write($wpdb->insert($p.'material_returns',['return_no'=>$this->next_no('material_returns','RET'),'site_id'=>$site,'warehouse_id'=>0,'purchase_receipt_id'=>$receipt,'vendor_id'=>$head['vendor_id'],'site_flow'=>1,'return_date'=>$date,'return_by'=>$this->plugin_user_id(),'status'=>'approved','created_by'=>get_current_user_id(),'created_at'=>$now,'updated_at'=>$now]));$id=(int)$wpdb->insert_id;
            foreach($items as $it){$it['return_id']=$id;$this->sp_write($wpdb->insert($p.'material_return_items',$it));}$this->sp_sync_site_ledger('material_returns',$id,$site,$date,$items,false);$this->sp_write($wpdb->query('COMMIT'));return ['id'=>$id,'message'=>'Supplier Return posted; Site stock reduced.'];
        }catch(Throwable $e){$wpdb->query('ROLLBACK');throw $e;}finally{$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));}
    }
    public function ajax_site_purchase_receipts() {
        $this->sp_access(['return_form']);
        $this->verify_ajax();$this->site_procurement_upgrade();global $wpdb;$p=$this->prefix;
        try{$site=(int)($_POST['site_id']??0);$this->sp_site($site);$rows=$wpdb->get_results($wpdb->prepare("SELECT mi.id,mi.inward_no,mi.inward_date,mi.vendor_id,v.vendor_name FROM {$p}material_inwards mi LEFT JOIN {$p}vendors v ON v.id=mi.vendor_id WHERE mi.site_flow=1 AND mi.site_id=%d AND mi.status='approved' ORDER BY mi.id DESC",$site),ARRAY_A)?:[];wp_send_json_success(['rows'=>$rows]);}catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()]);}
    }
    // One movement source for all stock consumers, balances and reports. Historical
    // warehouse purchases are excluded; their actual historical Site Receipts remain.
    private function sp_movements_sql() {
        $p=$this->prefix;return "
        SELECT a.site_id,a.item_id,a.unit_id,a.allocation_date movement_date,'opening' kind,a.qty,a.rate,0 ref_id FROM {$p}site_stock_allocations a
        UNION ALL SELECT so.site_id,so.item_id,so.unit_id,COALESCE(so.opening_date,'1900-01-01') movement_date,'opening' kind,so.opening_qty qty,so.opening_rate rate,0 ref_id FROM {$p}site_opening_stock so
        UNION ALL SELECT h.site_id,d.item_id,d.unit_id,h.inward_date,'receipt',d.qty,d.rate,h.id FROM {$p}material_inwards h JOIN {$p}material_inward_items d ON d.inward_id=h.id WHERE h.site_flow=1 AND h.status='approved'
        UNION ALL SELECT COALESCE(NULLIF(h.site_id,0),md.site_id),COALESCE(NULLIF(d.item_id,0),di.item_id),COALESCE(NULLIF(d.unit_id,0),di.unit_id),h.receipt_date,'receipt',d.received_qty,COALESCE(NULLIF(d.rate,0),di.rate,0),h.id FROM {$p}site_receipts h JOIN {$p}site_receipt_items d ON d.receipt_id=h.id LEFT JOIN {$p}material_dispatch_items di ON di.id=d.dispatch_item_id LEFT JOIN {$p}material_dispatches md ON md.id=h.dispatch_id WHERE h.status<>'cancelled'
        UNION ALL SELECT h.site_id,d.item_id,d.unit_id,h.consumption_date,'consumption',d.qty,0,h.id FROM {$p}site_consumptions h JOIN {$p}site_consumption_items d ON d.consumption_id=h.id WHERE h.status<>'cancelled'
        UNION ALL SELECT h.site_id,d.item_id,d.unit_id,h.return_date,'return_qty',d.qty,d.rate,h.id FROM {$p}material_returns h JOIN {$p}material_return_items d ON d.return_id=h.id WHERE h.status<>'cancelled'
        UNION ALL SELECT h.from_site_id,d.item_id,d.unit_id,h.dispatch_date,'site_transfer',d.qty,d.rate,h.id FROM {$p}material_dispatches h JOIN {$p}material_dispatch_items d ON d.dispatch_id=h.id WHERE h.from_site_id>0 AND h.status<>'cancelled'
        UNION ALL SELECT h.from_site_id,d.item_id,d.unit_id,DATE(h.dispatched_at),'site_transfer',d.qty,d.rate,h.id FROM {$p}site_transfers h JOIN {$p}site_transfer_items d ON d.transfer_id=h.id WHERE h.status IN ('dispatched','partially_received','received')
        UNION ALL SELECT h.to_site_id,d.item_id,d.unit_id,r.receipt_date,'receipt',r.received_qty,d.rate,h.id FROM {$p}site_transfer_receipts r JOIN {$p}site_transfer_items d ON d.id=r.transfer_item_id JOIN {$p}site_transfers h ON h.id=r.transfer_id WHERE h.status IN ('dispatched','partially_received','received')";
    }
    private function site_available_stock($site_id,$item_id,$consumption_id=0,$return_id=0) {
        global $wpdb;$p=$this->prefix;$union=$this->sp_movements_sql();
        $r=$wpdb->get_row($wpdb->prepare("SELECT COALESCE(SUM(CASE WHEN kind IN ('opening','receipt') THEN qty ELSE -qty END),0) available_qty,
            COALESCE(SUM(CASE WHEN kind='receipt' THEN qty ELSE 0 END),0) received,
            COALESCE(SUM(CASE WHEN kind='consumption' THEN qty ELSE 0 END),0) consumed,
            COALESCE(SUM(CASE WHEN kind='return_qty' THEN qty ELSE 0 END),0) returned,
            COALESCE(SUM(CASE WHEN kind='site_transfer' THEN qty ELSE 0 END),0) transfer_out,
            COALESCE(SUM(CASE WHEN kind IN ('opening','receipt') THEN qty*rate ELSE 0 END)/NULLIF(SUM(CASE WHEN kind IN ('opening','receipt') THEN qty ELSE 0 END),0),0) rate
            FROM ($union) m WHERE site_id=%d AND item_id=%d AND NOT(kind='consumption' AND ref_id=%d AND %d>0) AND NOT(kind='return_qty' AND ref_id=%d AND %d>0)",$site_id,$item_id,$consumption_id,$consumption_id,$return_id,$return_id),ARRAY_A)?:[];
        foreach(['available_qty','received','consumed','returned','transfer_out','rate'] as $k)$r[$k]=round((float)($r[$k]??0),2);$r['unit_id']=(int)$wpdb->get_var($wpdb->prepare("SELECT unit_id FROM {$p}items WHERE id=%d",$item_id));return $r;
    }
    private function sp_inventory_rows($site=0,$item=0,$category=0,$from='',$to='') {
        global $wpdb;$p=$this->prefix;$union=$this->sp_movements_sql();$where='m.site_id>0'.$this->sp_scope('m');$args=[];
        foreach(['site_id'=>$site,'item_id'=>$item] as $k=>$v)if($v){$where.=" AND m.$k=%d";$args[]=$v;}if($category){$where.=' AND i.category_id=%d';$args[]=$category;}
        if($from){$where.=' AND m.movement_date>=%s';$args[]=$from;}if($to){$where.=' AND m.movement_date<=%s';$args[]=$to;}
        $sql="SELECT 'Site' location_type,m.site_id location_id,s.site_name location,m.item_id,i.item_name,m.unit_id,u.unit_name unit,i.category_id,c.category_name,
          SUM(CASE WHEN kind='opening' THEN qty ELSE 0 END) opening,0 inward,
          SUM(CASE WHEN kind='receipt' THEN qty ELSE 0 END) receipt,0 dispatch,
          SUM(CASE WHEN kind='consumption' THEN qty ELSE 0 END) consumption,
          SUM(CASE WHEN kind='return_qty' THEN qty ELSE 0 END) return_qty,
          SUM(CASE WHEN kind='site_transfer' THEN qty ELSE 0 END) site_transfer,
          SUM(CASE WHEN kind IN ('opening','receipt') THEN qty ELSE -qty END) balance
          FROM ($union) m JOIN {$p}sites s ON s.id=m.site_id JOIN {$p}items i ON i.id=m.item_id LEFT JOIN {$p}units u ON u.id=m.unit_id LEFT JOIN {$p}item_categories c ON c.id=i.category_id WHERE $where GROUP BY m.site_id,s.site_name,m.item_id,i.item_name,m.unit_id,u.unit_name,i.category_id,c.category_name ORDER BY s.site_name,i.item_name";
        $rows=$wpdb->get_results($args?$wpdb->prepare($sql,$args):$sql,ARRAY_A);
        if(!empty($wpdb->last_error)){error_log('BSM site inventory: '.$wpdb->last_error);throw new Exception('Unable to load site inventory. Please check the server error log.');}
        return $rows?:[];
    }
    public function ajax_site_stock_items() {
        $this->sp_access(['consumption_form','site_consumptions','return_form','material_returns','site_transfer_form','site_transfer_master','site_inventory','boq_form','boq_items','pending_requisition_approval','requisition_form']);
        $this->verify_ajax();global $wpdb;$site=(int)($_POST['site_id']??0);if(!$site){wp_send_json_success(['rows'=>[]]);return;}
        try{$this->sp_site($site);$rows=$this->sp_inventory_rows($site);$out=[];foreach($rows as $x){$st=$this->site_available_stock($site,$x['item_id'],(int)($_POST['consumption_id']??0),(int)($_POST['return_id']??0));if($st['available_qty']>0.009)$out[]=['id'=>$x['item_id'],'label'=>$x['item_name'],'unit_id'=>$st['unit_id'],'available_qty'=>$st['available_qty']];}wp_send_json_success(['rows'=>$out]);}catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()]);}
    }
    public function ajax_site_stock_available() {
        $this->sp_access(['consumption_form','site_consumptions','return_form','material_returns','site_transfer_form','site_transfer_master','site_inventory','boq_form','boq_items','pending_requisition_approval','requisition_form']);
        $this->verify_ajax();try{$this->sp_site((int)($_POST['site_id']??0));wp_send_json_success($this->site_available_stock((int)($_POST['site_id']??0),(int)($_POST['item_id']??0),(int)($_POST['consumption_id']??0),(int)($_POST['return_id']??0)));}catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()]);}
    }
    public function ajax_site_procurement_review() {
        $this->verify_ajax();if(!$this->nt_admin())wp_send_json_error(['message'=>'Administrator access required.'],403);$this->site_procurement_upgrade();global $wpdb;$p=$this->prefix;
        $pos=$wpdb->get_results("SELECT po.id,po.po_no,po.status,s.site_name,r.requisition_no FROM {$p}purchase_orders po LEFT JOIN {$p}sites s ON s.id=po.site_id LEFT JOIN {$p}material_requisitions r ON r.id=po.requisition_id WHERE po.site_flow=0 ORDER BY po.id DESC",ARRAY_A)?:[];
        $stock=$wpdb->get_results("SELECT w.id warehouse_id,w.warehouse_name,i.id item_id,i.item_name FROM {$p}warehouses w CROSS JOIN {$p}items i ORDER BY w.id,i.item_name",ARRAY_A)?:[];$balances=[];foreach($stock as $x){$x['qty']=$this->warehouse_item_stock($x['warehouse_id'],$x['item_id']);if(abs($x['qty'])>0.009)$balances[]=$x;}
        wp_send_json_success(['purchase_orders'=>$pos,'unassigned_stock'=>$balances,'message'=>'Historical stock is retained separately. Review the source balance and select a Site before allocating it. Unlinked historical POs require explicit Site / Requisition assignment.']);
    }

    public function ajax_site_procurement_allocate() {
        $this->verify_ajax();if(!$this->nt_admin())wp_send_json_error(['message'=>'Administrator access required.'],403);$this->site_procurement_upgrade();global $wpdb;$p=$this->prefix;$lock=null;
        try{$lock=$this->ops_lock('stock_dispatch');$this->sp_write($wpdb->query('START TRANSACTION'));$site=(int)($_POST['site_id']??0);$this->sp_site($site);$warehouse=(int)($_POST['warehouse_id']??0);$item=(int)($_POST['item_id']??0);$qty=$this->sp_qty($_POST['qty']??0);$date=$this->sp_date($_POST['allocation_date']??current_time('Y-m-d'));
            if(!$warehouse||!$item||$qty<=0||$qty>$this->warehouse_item_stock($warehouse,$item)+0.009)throw new Exception('Allocation exceeds the unassigned historical balance.');
            $unit=(int)$wpdb->get_var($wpdb->prepare("SELECT unit_id FROM {$p}items WHERE id=%d",$item));$rate=$this->warehouse_item_rate($warehouse,$item);$now=current_time('mysql');
            $this->sp_write($wpdb->insert($p.'site_stock_allocations',['warehouse_id'=>$warehouse,'site_id'=>$site,'item_id'=>$item,'unit_id'=>$unit,'allocation_date'=>$date,'qty'=>$qty,'rate'=>$rate,'reviewed_by'=>get_current_user_id(),'created_at'=>$now]));$id=(int)$wpdb->insert_id;
            foreach([['SITE',$site,null,$qty,0],['WAREHOUSE',null,$warehouse,0,$qty]] as $entry)$this->sp_write($wpdb->insert($p.'stock_ledger',['transaction_date'=>$date,'location_type'=>$entry[0],'site_id'=>$entry[1],'warehouse_id'=>$entry[2],'item_id'=>$item,'unit_id'=>$unit,'transaction_type'=>'REVIEWED_SITE_ALLOCATION','reference_type'=>'site_stock_allocations','reference_id'=>$id,'qty_in'=>$entry[3],'qty_out'=>$entry[4],'rate'=>$rate,'created_at'=>$now]));
            $this->sp_write($wpdb->query('COMMIT'));
        }catch(Throwable $e){$wpdb->query('ROLLBACK');$error=$e->getMessage();}finally{if($lock)$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));}
        if(isset($error))wp_send_json_error(['message'=>$error]);wp_send_json_success(['message'=>'Reviewed stock allocated to the selected Site.']);
    }
    public function ajax_site_procurement_assign_po() {
        $this->verify_ajax();if(!$this->nt_admin())wp_send_json_error(['message'=>'Administrator access required.'],403);$this->site_procurement_upgrade();global $wpdb;$p=$this->prefix;$lock=null;
        try{$lock=$this->ops_lock('site_procurement');$this->sp_write($wpdb->query('START TRANSACTION'));$id=(int)($_POST['id']??0);$site=(int)($_POST['site_id']??0);$req=(int)($_POST['requisition_id']??0);$po=$this->sp_po_head($id);
            if(!empty($po['site_id'])||!empty($po['requisition_id'])||!empty($po['site_flow']))throw new Exception('Only unassigned historical POs can be assigned.');
            $requirements=$this->sp_requirements($site,$req,$id);if(!$req||!$requirements)throw new Exception('Select an approved Requisition with a sufficient balance.');$by=[];foreach($requirements as $x)$by[$x['item_id'].'|'.$x['unit_id']][]=$x;
            $items=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}purchase_order_items WHERE purchase_order_id=%d",$id),ARRAY_A)?:[];if(!$items)throw new Exception('PO has no items.');$used=[];
            foreach($items as $it){if($this->po_item_received_qty($it['id'])>0.009)throw new Exception('POs with historical receipts require manual reconciliation; automatic assignment is blocked.');$matches=$by[$it['item_id'].'|'.$it['unit_id']]??[];if(count($matches)!==1)throw new Exception('Each PO item must match exactly one approved Requisition item.');$x=$matches[0];$ri=(int)$x['requisition_item_id'];$used[$ri]=($used[$ri]??0)+(float)$it['po_qty'];if($used[$ri]>(float)$x['pending_po_qty']+0.009)throw new Exception('PO quantity exceeds the approved Requisition balance.');
                $this->sp_write($wpdb->update($p.'purchase_order_items',['requisition_item_id'=>$ri],['id'=>$it['id']]));$this->sp_write($wpdb->delete($p.'purchase_order_item_allocations',['purchase_order_item_id'=>$it['id']]));$this->sp_write($wpdb->insert($p.'purchase_order_item_allocations',['purchase_order_item_id'=>$it['id'],'requisition_item_id'=>$ri,'allocated_qty'=>$it['po_qty'],'created_at'=>current_time('mysql')]));}
            $review=json_decode($po['remarks']??'{}',true);if(!is_array($review))$review=['legacy_remarks'=>$po['remarks']??''];$review['site_reference_review']=['site_id'=>$site,'requisition_id'=>$req,'reviewed_by'=>get_current_user_id(),'reviewed_at'=>current_time('mysql')];
            $this->sp_write($wpdb->update($p.'purchase_orders',['remarks'=>wp_json_encode($review),'site_id'=>$site,'requisition_id'=>$req,'status'=>'pending_approval','approved_by'=>null,'approved_at'=>null,'updated_at'=>current_time('mysql')],['id'=>$id]));$this->sp_write($wpdb->query('COMMIT'));
        }catch(Throwable $e){$wpdb->query('ROLLBACK');$error=$e->getMessage();}finally{if($lock)$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));}
        if(isset($error))wp_send_json_error(['message'=>$error]);wp_send_json_success(['message'=>'Historical PO assigned and sent for approval.']);
    }
}
