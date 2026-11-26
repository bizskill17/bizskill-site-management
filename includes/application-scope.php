<?php
if (!defined('ABSPATH')) exit;

/** The active application surface. Historical tables are never removed here. */
trait BSM_Application_Scope {
    private function app_pages() {
        $keep=['dashboard','sites','items','item_categories','units','vendors','labour_contractors','contractor_employees','users','designations','gst_rates','states','holiday_list','boq_form','boq_master','boq_items','boq_monitoring','requisition_form','pending_requisition_approval','material_requisitions','material_requisition_item','pending_po_items','purchase_order_form','pending_po_approval','purchase_order_master','purchase_order_items_master','purchase_order_pending_receipt','purchase_form','purchase_register','purchase_items_register','pending_invoice_copy','site_inventory','site_opening_stock','labour_attendance','report_labour','office_daily_attendance','office_attendance_master','time_sheet','time_sheet_master','company_settings','notifications','view_settings','data_reset'];
        $pages=array_intersect_key($this->legacy_app_pages(),array_flip($keep));
        $pages['purchase_order_form']=['Create PO','purchase-order-form'];
        $pages['stock_ledger']=['Stock Ledger','stock-ledger'];
        foreach(['dashboard'=>'Dashboard','users'=>'Users','purchase_form'=>'Goods Receipt Form','purchase_register'=>'Goods Receipt Master','purchase_items_register'=>'Goods Receipt Items','purchase_order_pending_receipt'=>'Pending Goods Receipts','office_daily_attendance'=>'Daily Office Attendance','office_attendance_master'=>'Office Attendance Master','view_settings'=>'View Permissions'] as $key=>$label)$pages[$key][0]=$label;
        return $pages;
    }
    private function scope_removed_modules() {
        return ['site_consumptions','material_returns','site_transfers','bank_accounts','customers','warehouses','expense_categories','allowance_types','employee_allowance_limits','employee_allowances','salary_slips','salary_batches','vendor_payments','contractor_bills','contractor_bill_items','contractor_payments','contractor_advances','site_fund_allocations','site_expenses','customer_receipts','gst_invoices','quotations','sales_quotations','sales_invoices','sales_documents','tax_invoices','tax_invoice_items','gst_invoice_items','quotation_items','material_dispatches','site_receipts'];
    }
    private function scope_guard_request() {
        $action=sanitize_key($_POST['action']??$_GET['action']??'');
        if(in_array($action,['bsm_historical_records','bsm_site_procurement_review','bsm_site_procurement_allocate','bsm_site_procurement_assign_po'],true))wp_send_json_error(['message'=>'This section is no longer available.'],403);
        $module=sanitize_key($_POST['module']??'');
        if(in_array($module,['material_inwards','material_returns'],true)){
            $data=json_decode(wp_unslash($_POST['data']??'{}'),true);
            $id=(int)($_POST['id']??$_POST['reference_id']??(is_array($data)?($data['id']??0):0));
            if($id){global $wpdb;if(!(int)$wpdb->get_var($wpdb->prepare("SELECT site_flow FROM {$this->prefix}$module WHERE id=%d",$id)))wp_send_json_error(['message'=>'Use Historical Records to review this transaction.'],403);}
        }
        if(in_array($module,$this->scope_removed_modules(),true))wp_send_json_error(['message'=>'This module is no longer available. Historical records are preserved.'],403);
        $blocked=['bsm_consumption_items_register','bsm_material_returns_item_master','bsm_daily_outward_summary','bsm_project_tracker','bsm_project_tracker_save','bsm_pending_contractor_bill_copy','bsm_purchase_pending_approval','bsm_purchase_approval_get','bsm_purchase_approval_save','bsm_vendor_outstanding','bsm_vendor_payment_save','bsm_dispatch_items_register','bsm_site_material_item_receipt','bsm_return_receipt_master','bsm_return_receipt_item_master','bsm_sales_register','bsm_sales_pdf','bsm_daily_expense_summary','bsm_pending_site_receive_challans','bsm_pending_material_returns','bsm_update_contractor_payment_status','bsm_update_contractor_paid_amount','bsm_contractor_advance_available','bsm_monthly_salary','bsm_allowance_submit','bsm_allowance_list','bsm_allowance_decide','bsm_allowance_limits','bsm_salary_template_rows','bsm_salary_upload_preview','bsm_salary_commit','bsm_salary_slips','bsm_pending_dispatch','bsm_dispatch_source_stock','bsm_dispatch_prefill','bsm_site_receipt_prefill'];
        if(in_array($action,$blocked,true))wp_send_json_error(['message'=>'This section is no longer available.'],403);
        if($action==='bsm_operations'&&strpos(sanitize_key($_POST['op']??''),'transfer')===0)wp_send_json_error(['message'=>'Site transfers are no longer available.'],403);
        if($action==='bsm_operations'&&strpos(sanitize_key($_POST['op']??''),'bill')===0)wp_send_json_error(['message'=>'Contractor billing is no longer available.'],403);
        if($action==='bsm_report'&&!in_array(sanitize_key($_POST['report']??''),['material_stock','stock','labour','labour_summary'],true))wp_send_json_error(['message'=>'This report is no longer available.'],403);
    }
    public function ajax_scope_history() {
        $this->verify_ajax();if(!$this->nt_admin())wp_send_json_error(['message'=>'Only administrators can review historical records.'],403);
        global $wpdb;$module=sanitize_key($_POST['module']??'');
        $definitions=[
          'site_consumptions'=>['id','consumption_no','consumption_date','site_id','status'],
          'material_returns'=>['id','return_no','return_date','site_id','status'],
          'site_transfers'=>['id','transfer_no','transfer_date','from_site_id','to_site_id','status'],
          'material_dispatches'=>['id','dispatch_no','dispatch_date','site_id','status'],
          'site_receipts'=>['id','receipt_no','receipt_date','site_id','status'],
          'material_inwards'=>['id','inward_no','inward_date','site_id','vendor_id','status'],
          'vendor_payments'=>['id','payment_date','payment_type','amount'],
          'contractor_bills'=>['id','bill_no','bill_date','site_id','total_amount'],
          'site_expenses'=>['id','expense_date','site_id','particulars','amount'],
          'customer_receipts'=>['id','receipt_no','receipt_date','site_id','amount'],
          'salary_slips'=>['id','employee_id','net_salary']];
        if(!isset($definitions[$module]))wp_send_json_error(['message'=>'Invalid historical record type.'],400);
        // Derive optional columns from the existing schema; historical installations vary.
        $existing=$wpdb->get_col("SHOW COLUMNS FROM {$this->prefix}$module");$columns=array_values(array_intersect($definitions[$module],$existing?:[]));
        if(!$columns)wp_send_json_success(['columns'=>['id'],'rows'=>[]]);
        $sql='SELECT '.implode(',',$columns)." FROM {$this->prefix}$module".($module==='material_inwards'&&in_array('site_flow',$existing,true)?' WHERE COALESCE(site_flow,0)=0':'').' ORDER BY id DESC LIMIT 500';
        wp_send_json_success(['columns'=>$columns,'rows'=>$wpdb->get_results($sql,ARRAY_A)?:[]]);
    }
    public function ajax_scope_stock_ledger() {
        $this->verify_ajax();$this->sp_access(['stock_ledger','site_inventory']);global $wpdb;$p=$this->prefix;
        $site=(int)($_POST['site_id']??0);if($site)$this->sp_site($site);$where='m.site_id>0'.$this->sp_scope('m');$args=[];
        foreach(['site_id'=>$site,'item_id'=>(int)($_POST['item_id']??0)] as $k=>$v)if($v){$where.=" AND m.$k=%d";$args[]=$v;}
        foreach(['date_from'=>'>=','date_to'=>'<='] as $key=>$op)if(!empty($_POST[$key])){$where.=" AND m.movement_date$op%s";$args[]=$this->sp_date($_POST[$key]);}
        $sql="SELECT m.*,s.site_name,i.item_name,u.unit_name,CASE WHEN m.kind IN ('opening','receipt') THEN m.qty ELSE 0 END qty_in,CASE WHEN m.kind NOT IN ('opening','receipt') THEN m.qty ELSE 0 END qty_out FROM (".$this->sp_movements_sql().") m JOIN {$p}sites s ON s.id=m.site_id JOIN {$p}items i ON i.id=m.item_id LEFT JOIN {$p}units u ON u.id=m.unit_id WHERE $where ORDER BY m.movement_date DESC,m.ref_id DESC LIMIT 2000";
        wp_send_json_success(['rows'=>$wpdb->get_results($args?$wpdb->prepare($sql,$args):$sql,ARRAY_A)?:[]]);
    }
    public function ajax_dashboard() {
        $this->verify_ajax();$this->sp_access(['dashboard']);global $wpdb;$p=$this->prefix;
        try{$date=$this->sp_date($_POST['date']??current_time('Y-m-d'));$site=(int)($_POST['site_id']??0);if($site)$this->sp_site($site);
            $sites=$wpdb->get_results("SELECT s.id site_id,s.site_name FROM {$p}sites s WHERE s.status='active'".$this->sp_scope('s')." ORDER BY s.site_name",ARRAY_A)?:[];
            $selected=array_values(array_filter($sites,fn($s)=>!$site||(int)$s['site_id']===$site));$cards=['requisitions'=>0,'pending_po'=>0,'po_approval'=>0,'pending_receipt'=>0,'low_stock'=>0,'labour'=>0,'office_present'=>0,'hours'=>0];
            foreach($selected as $s){$id=(int)$s['site_id'];$cards['requisitions']+=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$p}material_requisitions WHERE site_id=%d AND status='pending_approval'",$id));
                $cards['pending_po']+=count(array_unique(array_column($this->sp_requirements($id),'requisition_id')));
                $cards['po_approval']+=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$p}purchase_orders WHERE site_id=%d AND status IN ('created','pending_approval')",$id));
                $cards['pending_receipt']+=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT po.id) FROM {$p}purchase_orders po JOIN {$p}purchase_order_items it ON it.purchase_order_id=po.id WHERE po.site_id=%d AND po.status IN ('approved','partly_received','partially_received') AND it.po_qty>COALESCE((SELECT SUM(d.qty) FROM {$p}material_inward_items d JOIN {$p}material_inwards h ON h.id=d.inward_id WHERE d.purchase_order_item_id=it.id AND h.status='approved'),0)+0.009",$id));
                $cards['labour']+=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(no_of_labour),0) FROM {$p}labour_attendance WHERE site_id=%d AND attendance_date=%s",$id,$date));
                $stock=$this->sp_inventory_rows($id);$balances=[];foreach($stock as $r)$balances[$r['item_id']]=$r['balance'];
                $items=$wpdb->get_results("SELECT id,minimum_stock FROM {$p}items WHERE status='active' AND minimum_stock>0",ARRAY_A)?:[];foreach($items as $i)if((float)($balances[$i['id']]??0)<(float)$i['minimum_stock'])$cards['low_stock']++;
            }
            if(!$site)$cards['office_present']=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$p}office_attendance WHERE attendance_date=%s AND attendance_status='P'",$date));
            // Use the same row-level restrictions as the Time Sheet Master.
            $entries=$this->ops_time_rows(['from'=>$date,'to'=>$date,'site_id'=>$site],$this->ops_user());foreach($entries as $e)$cards['hours']+=(int)$e['minutes']/60;$cards['hours']=round($cards['hours'],2);
            $result=['date'=>$date,'sites'=>$sites,'cards'=>$cards];
        }catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()]);}
        wp_send_json_success($result);
    }
}
