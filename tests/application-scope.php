<?php
require __DIR__.'/site-procurement.php';
require __DIR__.'/../includes/application-scope.php';
class ScopeSubject extends Subject {
    use BSM_Site_Procurement;
    use BSM_Application_Scope;
    function legacy_app_pages(){
        $s=file_get_contents(__DIR__.'/../bizskill-site-management.php');$a=strpos($s,'private function legacy_app_pages()');$a=strpos($s,'return [',$a);$b=strpos($s,'];',$a);return eval(substr($s,$a,$b+2-$a));
    }
    function manifest(){return $this->app_pages();}
    function guard($post){$_POST=$post;$this->scope_guard_request();return true;}
    function ops_user(){return ['id'=>1];}function ops_time_rows($d,$u){return [['minutes'=>90]];}
}
$host=new ScopeSubject;$pages=$host->manifest();
foreach(['purchase_order_form','stock_ledger','historical_records','site_transfer_form','contractor_employees','holiday_list'] as $v)eq(isset($pages[$v]),true,'Retained screen '.$v);
foreach(['bank_accounts','customers','vendor_payments','salary_slips','invoice_form','quotation_form','site_expenses','material_dispatches','site_receipts','office_monthly_salary'] as $v)eq(isset($pages[$v]),false,'Removed screen '.$v);
eq($pages['purchase_form'][0],'Goods Receipt Form','Receipt title');
foreach(['vendor_payments','salary_slips','warehouses','customers','site_receipts','material_dispatches'] as $module){try{$host->guard(['action'=>'bsm_save','module'=>$module]);throw new Exception('Not blocked');}catch(JsonReply $r){eq($r->ok,false,'Blocked direct module '.$module);}}
foreach(['bsm_vendor_payment_save','bsm_monthly_salary','bsm_sales_pdf','bsm_project_tracker_save'] as $action){try{$host->guard(['action'=>$action]);throw new Exception('Not blocked');}catch(JsonReply $r){eq($r->ok,false,'Blocked direct action '.$action);}}
eq($host->guard(['action'=>'bsm_operations','op'=>'transfer_save']),true,'Retained transfer');
try{$host->guard(['action'=>'bsm_operations','op'=>'bill_create']);throw new Exception('Not blocked');}catch(JsonReply $r){eq($r->ok,false,'Blocked bill operation');}
$ledger=endpoint($host,'ajax_scope_stock_ledger',['site_id'=>1]);eq($ledger->ok,true,'Ledger loads');$net=0;foreach($ledger->data['rows'] as $row)if($row['item_id']==1)$net+=$row['qty_in']-$row['qty_out'];eq($net,$s->stock(1,1)['available_qty'],'Ledger reconciles with inventory');
$host->scope=[2];$ledger=endpoint($host,'ajax_scope_stock_ledger',[]);foreach($ledger->data['rows'] as $row)eq((int)$row['site_id'],2,'Ledger site isolation');$host->scope=null;
$host->admin=false;eq(endpoint($host,'ajax_scope_history',['module'=>'vendor_payments'])->ok,false,'History Admin only');$host->admin=true;
$wpdb->query("ALTER TABLE bsm_sites ADD COLUMN status TEXT DEFAULT 'active'");$wpdb->query("ALTER TABLE bsm_items ADD COLUMN status TEXT DEFAULT 'active'");$wpdb->query('ALTER TABLE bsm_items ADD COLUMN minimum_stock REAL DEFAULT 1');$wpdb->query('CREATE TABLE bsm_labour_attendance(id INTEGER PRIMARY KEY,site_id INT,attendance_date TEXT,no_of_labour REAL)');$wpdb->query('CREATE TABLE bsm_office_attendance(id INTEGER PRIMARY KEY,attendance_date TEXT,attendance_status TEXT)');
$dashboard=endpoint($host,'ajax_dashboard',['date'=>'2026-11-25','site_id'=>1]);if(!$dashboard->ok)throw new Exception($dashboard->data['message']);eq($dashboard->ok,true,'Dashboard SQL executes');eq($dashboard->data['cards']['hours'],1.5,'Dashboard recorded hours');eq(isset($dashboard->data['cards']['received']),false,'No financial dashboard');
echo "Application scope passed: active screens, blocked financial requests, historical access, ledger reconciliation, site isolation and dashboard SQL.\n";
