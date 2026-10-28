<?php
// Standalone regression harness: php tests/operations.php (PDO SQLite required).
define('ABSPATH',__DIR__.'/');define('ARRAY_A','ARRAY_A');
require __DIR__.'/../includes/workforce-transfers.php';
$admin=true;$wpuid=10;$scope=null;
function sanitize_text_field($s){return strip_tags((string)$s);}function sanitize_textarea_field($s){return strip_tags((string)$s);}function sanitize_key($s){return preg_replace('/[^a-z0-9_]/','',strtolower($s));}function wp_unslash($s){return stripslashes($s);}function wp_json_encode($s){return json_encode($s);}function current_user_can($s){return $GLOBALS['admin'];}function get_current_user_id(){return $GLOBALS['wpuid'];}function current_time($s){return $s==='mysql'?'2026-10-28 12:00:00':($s==='Y-m-d'?'2026-10-28':'2026-10');}function wp_send_json_success($r){$GLOBALS['result']=$r;}function wp_send_json_error($r){throw new Exception($r['message']);}
class DB {
    public $pdo,$insert_id,$last_error='';
    function __construct(){$this->pdo=new PDO('sqlite::memory:');$this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);}
    function prepare($s,...$a){if(count($a)===1&&is_array($a[0]))$a=$a[0];$i=0;return preg_replace_callback('/%[dsf]/',function($m)use(&$i,$a){$v=$a[$i++];return $m[0]==='%s'?$this->pdo->quote((string)$v):(string)(float)$v;},$s);}
    function fix($s){return str_replace([' FOR UPDATE','START TRANSACTION'],['','BEGIN TRANSACTION'],$s);}
    function query($s){try{return $this->pdo->exec($this->fix($s));}catch(Exception $e){$this->last_error=$e->getMessage();return false;}}
    function get_results($s,$type=null){return $this->pdo->query($this->fix($s))->fetchAll(PDO::FETCH_ASSOC);}
    function get_row($s,$type=null){return $this->pdo->query($this->fix($s))->fetch(PDO::FETCH_ASSOC)?:null;}
    function get_var($s){if(strpos($s,'GET_LOCK(')!==false||strpos($s,'RELEASE_LOCK(')!==false)return 1;return $this->pdo->query($this->fix($s))->fetchColumn();}
    function insert($t,$d){try{$sql='INSERT INTO '.$t.' ('.implode(',',array_keys($d)).') VALUES ('.implode(',',array_fill(0,count($d),'?')).')';$st=$this->pdo->prepare($sql);$ok=$st->execute(array_values($d));$this->insert_id=(int)$this->pdo->lastInsertId();return $ok;}catch(Exception $e){$this->last_error=$e->getMessage();return false;}}
    function update($t,$d,$w){$sql='UPDATE '.$t.' SET '.implode(',',array_map(fn($k)=>$k.'=?',array_keys($d))).' WHERE '.implode(' AND ',array_map(fn($k)=>$k.'=?',array_keys($w)));return $this->pdo->prepare($sql)->execute(array_merge(array_values($d),array_values($w)));}
    function delete($t,$w){return $this->pdo->prepare('DELETE FROM '.$t.' WHERE '.implode(' AND ',array_map(fn($k)=>$k.'=?',array_keys($w))))->execute(array_values($w));}
}
class Host {
    use BSM_Workforce_Transfers;
    private $prefix='bsm_';
    private function verify_ajax(){}
    private function allowance_admin_allowed(){return $GLOBALS['admin'];}
    private function employee_site_scope(){return $GLOBALS['scope'];}
    private function site_available_stock($site,$item){return ['available_qty'=>($site===1?100:0)+$this->ops_stock_delta($site,$item),'rate'=>25];}
    function stock($site,$item){return (float)$this->site_available_stock($site,$item)['available_qty'];}
    function saveContractor($id,$parent,$employees){return $this->ops_save_contractor_employees($id,$parent,$employees);}
    function holidayDates($employee,$a,$b){return $this->ops_holiday_dates($employee,$a,$b);}
}
$wpdb=new DB();$host=new Host();
// Build the production trait's table definitions using SQLite-compatible primary keys.
$source=file_get_contents(__DIR__.'/../includes/workforce-transfers.php');preg_match_all('/"CREATE TABLE \{\$p\}(.*?)ENGINE=InnoDB \$c;"/s',$source,$m);
foreach($m[1] as $sql){$sql='CREATE TABLE bsm_'.$sql;$sql=preg_replace('/id bigint unsigned NOT NULL AUTO_INCREMENT,/','id INTEGER PRIMARY KEY AUTOINCREMENT,',$sql);$sql=preg_replace('/^\s*(?:PRIMARY KEY|UNIQUE KEY|KEY)\s+[^\n]+\n/m','',$sql);$sql=preg_replace('/,\s*\)/',')',$sql);$sql=str_replace('bigint unsigned','INTEGER',$sql);if($wpdb->query($sql)===false)throw new Exception($wpdb->last_error);}
foreach([
 'CREATE TABLE bsm_users (id INTEGER PRIMARY KEY,name TEXT,wp_user_id INTEGER,role TEXT,status TEXT,allowed_views TEXT,assigned_sites TEXT)',
 'CREATE TABLE bsm_sites (id INTEGER PRIMARY KEY,site_name TEXT,status TEXT)',
 'CREATE TABLE bsm_items (id INTEGER PRIMARY KEY,item_name TEXT,unit_id INTEGER,status TEXT)',
 'CREATE TABLE bsm_units (id INTEGER PRIMARY KEY,unit_name TEXT)',
 "CREATE TABLE bsm_labour_contractors (id INTEGER PRIMARY KEY,contractor_name TEXT,payment_basis TEXT)",
 "CREATE TABLE bsm_contractor_bills (id INTEGER PRIMARY KEY AUTOINCREMENT,bill_no TEXT UNIQUE,bill_date TEXT,contractor_id INTEGER,site_id INTEGER,from_date TEXT,to_date TEXT,taxable_amount REAL,gst_amount REAL,total_amount REAL,payment_basis TEXT,bill_reference TEXT,remarks TEXT,status TEXT,created_by INTEGER,approved_by INTEGER,created_at TEXT,updated_at TEXT)",
 'CREATE TABLE bsm_documents (id INTEGER PRIMARY KEY,module TEXT,reference_id INTEGER,file_url TEXT,original_filename TEXT)',
 'CREATE TABLE bsm_stock_ledger (id INTEGER PRIMARY KEY,transaction_date TEXT,location_type TEXT,site_id INTEGER,item_id INTEGER,unit_id INTEGER,transaction_type TEXT,reference_type TEXT,reference_id INTEGER,qty_in REAL,qty_out REAL,rate REAL,created_at TEXT)'
] as $sql)$wpdb->query($sql);
$wpdb->insert('bsm_users',['id'=>1,'name'=>'Office Employee','wp_user_id'=>10,'role'=>'bsm_employee','status'=>'active','allowed_views'=>'["time_sheet","time_sheet_master","site_transfer_form","site_transfer_master","holiday_list"]']);
$wpdb->insert('bsm_users',['id'=>2,'name'=>'Other Employee','wp_user_id'=>20,'role'=>'bsm_employee','status'=>'active','allowed_views'=>'[]']);
foreach([1,2,3] as $id)$wpdb->insert('bsm_sites',['id'=>$id,'site_name'=>'Site '.$id,'status'=>'active']);
$wpdb->insert('bsm_units',['id'=>1,'unit_name'=>'Kg']);$wpdb->insert('bsm_items',['id'=>1,'item_name'=>'Binding Wire','unit_id'=>1,'status'=>'active']);
$wpdb->insert('bsm_labour_contractors',['id'=>1,'contractor_name'=>'Contractor A','payment_basis'=>'time']);
$passes=0;
function check($ok,$message){if(!$ok)throw new Exception('FAIL: '.$message);$GLOBALS['passes']++;echo "PASS: $message\n";}
function callop($op,$data=[]){$GLOBALS['result']=null;$_POST=['op'=>$op,'data'=>addslashes(json_encode($data))];$GLOBALS['host']->ajax_operations();return $GLOBALS['result'];}
function fails($op,$data,$contains){try{callop($op,$data);}catch(Exception $e){check(stripos($e->getMessage(),$contains)!==false,$op.' rejects '.$contains);return;}throw new Exception('FAIL: '.$op.' unexpectedly succeeded');}
callop('holiday_save',['holiday_date'=>'2026-10-28','holiday_name'=>'All Employee Holiday','employee_ids'=>[]]);
callop('holiday_save',['holiday_date'=>'2026-10-29','holiday_name'=>'Selected Holiday','employee_ids'=>[2]]);
check(count($host->holidayDates(1,'2026-10-01','2026-10-31'))===1,'Holiday applies to all employees');
check(count($host->holidayDates(2,'2026-10-01','2026-10-31'))===2,'Selected holiday applies only to selected employee');
fails('holiday_save',['holiday_date'=>'2026-02-30','holiday_name'=>'Bad date'],'valid date');
callop('worker_save',['contractor_id'=>1,'employee_name'=>'Worker A','daily_rate'=>600,'hourly_rate'=>100]);
$worker=(int)$wpdb->get_var('SELECT id FROM bsm_contractor_employees');
$day=['work_date'=>'2026-10-28','worker_id'=>$worker,'site_id'=>1,'rows'=>[['from_time'=>'09:00','to_time'=>'11:00','activity'=>'Construction'],['from_time'=>'11:00','to_time'=>'12:30','activity'=>'Finishing']]];
fails('time_save',array_merge($day,['site_id'=>0]),'Site is required');
callop('time_save',$day);$rows=callop('time_list',['worker_id'=>$worker])['rows'];
check(count($rows)===2&&(int)array_sum(array_column($rows,'minutes'))===210,'Time duration and adjacent intervals');
$bad=$day;$bad['rows'][1]['from_time']='10:30';fails('time_save',$bad,'overlap');
check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_time_entries')===2,'Overlapping replacement rolls back all rows');
$night=['work_date'=>'2026-10-25','employee_id'=>2,'rows'=>[['from_time'=>'23:00','to_time'=>'01:00','activity'=>'Overnight']]];
callop('time_save',$night);check((int)$wpdb->get_var("SELECT minutes FROM bsm_time_entries WHERE employee_id=2 AND work_date='2026-10-25'")===120,'Overnight entry calculates two hours');
fails('time_save',['work_date'=>'2026-10-26','employee_id'=>2,'rows'=>[['from_time'=>'00:30','to_time'=>'02:00','activity'=>'Overlap']]],'overlap');
callop('time_save',['work_date'=>'2026-10-26','employee_id'=>2,'rows'=>[['from_time'=>'01:00','to_time'=>'02:00','activity'=>'Adjacent']]]);
check((int)$wpdb->get_var("SELECT minutes FROM bsm_time_entries WHERE employee_id=2 AND work_date='2026-10-26'")===60,'Next-day adjacent entry is allowed');
callop('time_save',['work_date'=>'2026-10-24','employee_id'=>2,'rows'=>[['from_time'=>'11:00','to_time'=>'00:00','activity'=>'Until midnight']]]);
check((int)$wpdb->get_var("SELECT minutes FROM bsm_time_entries WHERE employee_id=2 AND work_date='2026-10-24'")===780,'11 AM to midnight calculates thirteen hours');
$bad=$day;$bad['rows']=[['from_time'=>'11:00','to_time'=>'11:00','activity'=>'Zero']];fails('time_save',$bad,'different From');
foreach($rows as $r)callop('time_status',['id'=>$r['id'],'status'=>'approved']);
$bill=['contractor_id'=>1,'site_id'=>1,'from'=>'2026-10-01','to'=>'2026-10-28','bill_date'=>'2026-10-28','bill_no'=>'CB-TEST','gst_amount'=>0];
$preview=callop('bill_preview',$bill);check($preview['total']===350.0,'Time-based bill uses approved 3.5 hours × 100');
$r=callop('bill_create',$bill);check((int)$r['bill_id']===1,'Calculated bill stored in existing contractor bill table');
check(callop('bill_preview',$bill)['total']===0.0,'Billed time entries excluded from subsequent billing');
fails('bill_create',$bill,'No approved');fails('time_status',['id'=>$rows[0]['id'],'status'=>'submitted'],'locked');
callop('time_save',['work_date'=>'2026-10-28','worker_id'=>$worker,'site_id'=>1,'rows'=>[]]);check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_time_entries WHERE bill_id=1')===2,'Billed entries cannot be removed by replacement');
// Daily-rate billing groups multiple activities to one employee-day.
$wpdb->update('bsm_labour_contractors',['payment_basis'=>'employee'],['id'=>1]);$day['work_date']='2026-10-27';callop('time_save',$day);
$unbilled=callop('time_list',['worker_id'=>$worker,'from'=>'2026-10-27','to'=>'2026-10-27'])['rows'];foreach($unbilled as $r)callop('time_status',['id'=>$r['id'],'status'=>'approved']);
$preview=callop('bill_preview',$bill);check($preview['total']===600.0&&count($preview['rows'])===1,'Contractor + Employee bills one daily rate for multiple activities');
// Office ownership and permissions.
$admin=false;fails('holiday_delete',['id'=>1],'Only HO/Admin');fails('time_save',['work_date'=>'2026-10-28','employee_id'=>2,'rows'=>[]],'own Time Sheet');
callop('time_save',['work_date'=>'2026-10-28','employee_id'=>1,'rows'=>[['from_time'=>'09:00','to_time'=>'10:00','activity'=>'Office work']]]);
check(count(callop('time_list')['rows'])===1,'Employee sees only own office time entries');
$admin=true;
// Transfer dispatch, partial receipt, damage and duplicate protection.
$draft=['transfer_date'=>'2026-10-28','from_site_id'=>1,'to_site_id'=>2,'items'=>[['item_id'=>1,'qty'=>30]]];
$id=callop('transfer_save',$draft)['id'];check($host->stock(1,1)===100.0,'Draft does not change source stock');
fails('transfer_save',array_merge($draft,['to_site_id'=>1]),'different destination');
callop('transfer_action',['id'=>$id,'action'=>'dispatch']);check($host->stock(1,1)===70.0&&$host->stock(2,1)===0.0,'Dispatch deducts source stock and keeps destination unchanged');
fails('transfer_action',['id'=>$id,'action'=>'dispatch'],'Only a Draft');fails('transfer_action',['id'=>$id,'action'=>'cancel'],'Only Draft');
$t=callop('transfer_get',['id'=>$id]);$detail=$t['items'][0]['id'];
callop('transfer_action',['id'=>$id,'action'=>'receive','receipt_date'=>'2026-10-28','items'=>[['id'=>$detail,'received_qty'=>10,'damaged_qty'=>2]]]);
check($host->stock(2,1)===10.0,'Only good receipts increase destination stock');
check(callop('transfer_get',['id'=>$id])['status']==='partially_received','Partial receipt retains pending transfer');
fails('transfer_action',['id'=>$id,'action'=>'receive','receipt_date'=>'2026-10-28','items'=>[['id'=>$detail,'received_qty'=>19]]],'remaining quantity');
check($host->stock(2,1)===10.0,'Over-receipt rolls back stock');
callop('transfer_action',['id'=>$id,'action'=>'receive','receipt_date'=>'2026-10-28','items'=>[['id'=>$detail,'received_qty'=>18]]]);
check($host->stock(2,1)===28.0&&callop('transfer_get',['id'=>$id])['status']==='received','Final receipt closes transfer with damage separately recorded');
fails('transfer_action',['id'=>$id,'action'=>'receive','receipt_date'=>'2026-10-28','items'=>[['id'=>$detail,'received_qty'=>1]]],'cannot receive');
check((float)$wpdb->get_var('SELECT SUM(qty_out)-SUM(qty_in) FROM bsm_stock_ledger')===2.0,'Ledger reconciliation equals damaged quantity');
$draft['items'][0]['qty']=80;fails('transfer_save',$draft,'available stock');
$admin=false;$scope=[3];fails('transfer_get',['id'=>$id],'access denied');
check(count(callop('transfer_list')['rows'])===0,'Site-scoped transfer list hides other sites');
// Exercise the actual monthly-salary method with holiday/date overlap cases.
$source=file_get_contents(__DIR__.'/../bizskill-site-management.php');
$begin=strpos($source,'    public function ajax_monthly_salary()');$end=strpos($source,'    public function ajax_company_logo_upload()',$begin);
$method=substr($source,$begin,$end-$begin);
eval('class SalaryHost {use BSM_Workforce_Transfers;private $prefix="bsm_";private function verify_ajax(){}'.$method.'}');
$wpdb->query('ALTER TABLE bsm_users ADD designation_id INTEGER');$wpdb->query('ALTER TABLE bsm_users ADD designation TEXT');$wpdb->query('ALTER TABLE bsm_users ADD salary REAL');$wpdb->query('ALTER TABLE bsm_users ADD salary_effective_date TEXT');$wpdb->query('ALTER TABLE bsm_users ADD attendance_applicable INTEGER');
$wpdb->query('CREATE TABLE bsm_designations(id INTEGER PRIMARY KEY,designation_name TEXT)');
$wpdb->query('CREATE TABLE bsm_user_salary_history(id INTEGER PRIMARY KEY,user_id INTEGER,effective_date TEXT,monthly_salary REAL)');
$wpdb->query('CREATE TABLE bsm_office_attendance(id INTEGER PRIMARY KEY,user_id INTEGER,attendance_date TEXT,attendance_status TEXT)');
foreach([1,2] as $eid){$wpdb->update('bsm_users',['salary'=>31000,'salary_effective_date'=>'2026-10-01','attendance_applicable'=>1],['id'=>$eid]);$wpdb->insert('bsm_user_salary_history',['id'=>$eid,'user_id'=>$eid,'effective_date'=>'2026-10-01','monthly_salary'=>31000]);}
$wpdb->insert('bsm_office_attendance',['id'=>1,'user_id'=>1,'attendance_date'=>'2026-10-28','attendance_status'=>'P']);
$wpdb->insert('bsm_office_attendance',['id'=>2,'user_id'=>1,'attendance_date'=>'2026-10-29','attendance_status'=>'A']);
$_POST=['month'=>'2026-10'];(new SalaryHost())->ajax_monthly_salary();$salary=$GLOBALS['result']['rows'];
check((float)$salary[0]['payable_salary']===1000.0,'Present day overlapping holiday is paid once');
check((float)$salary[1]['payable_salary']===2000.0,'Unmarked applicable holidays are payable at effective salary');
check((int)$salary[0]['absent_days']===1,'Holiday applicability preserves unrelated absence');
// Contractor parent and employee table are saved atomically, retaining used employee IDs.
$admin=true;$scope=null;
$employees=[['employee_name'=>'Employee One','mobile'=>'9876543210','address'=>'Guwahati','bank_name'=>'State Bank','ifsc'=>'sbin0001234','account_number'=>'001234567890'],['employee_name'=>'Employee Two','mobile'=>'9876543211','address'=>'Assam','bank_name'=>'Bank','ifsc'=>'HDFC0001234','account_number'=>'00000042']];
$cid=$host->saveContractor(0,['contractor_name'=>'New Contractor','payment_basis'=>'employee'],$employees);
$children=$wpdb->get_results('SELECT * FROM bsm_contractor_employees WHERE contractor_id='.(int)$cid.' ORDER BY id');
check(count($children)===2,'One contractor saves multiple employee rows');
check($children[0]['account_number']==='001234567890'&&$children[0]['ifsc']==='SBIN0001234','Account leading zeros and uppercase IFSC persist');
$employees[0]['id']=(int)$children[0]['id'];$employees[1]['id']=(int)$children[1]['id'];$employees[0]['address']='Updated Address';
$wpdb->update('bsm_contractor_employees',['daily_rate'=>700,'hourly_rate'=>125],['id'=>$children[0]['id']]);
$host->saveContractor($cid,['contractor_name'=>'Updated Contractor'],$employees);
$updated=$wpdb->get_row('SELECT * FROM bsm_contractor_employees WHERE id='.(int)$children[0]['id']);
check($updated['address']==='Updated Address'&&(float)$updated['hourly_rate']===125.0,'Employee edits retain ID and existing rates');
$bad=$employees;$bad[0]['mobile']='123';try{$host->saveContractor($cid,['contractor_name'=>'Should Roll Back'],$bad);throw new Exception('Expected Mobile validation');}catch(Exception $e){check(strpos($e->getMessage(),'10 digits')!==false,'Employee Mobile validates server-side');}
check($wpdb->get_var('SELECT contractor_name FROM bsm_labour_contractors WHERE id='.(int)$cid)==='Updated Contractor','Invalid child rolls back parent changes');
$bad=$employees;$bad[0]['id']=$worker;try{$host->saveContractor($cid,['contractor_name'=>'Should Roll Back'],$bad);throw new Exception('Expected ownership validation');}catch(Exception $e){check(strpos($e->getMessage(),'does not belong')!==false,'Employee cannot be reassigned through another contractor table');}
callop('time_save',['work_date'=>'2026-10-28','worker_id'=>$children[0]['id'],'site_id'=>1,'rows'=>[['from_time'=>'14:00','to_time'=>'15:00','activity'=>'Contract work']]]);
$host->saveContractor($cid,['contractor_name'=>'Updated Contractor'],[]);
check($wpdb->get_var('SELECT status FROM bsm_contractor_employees WHERE id='.(int)$children[0]['id'])==='inactive','Removing used employee preserves history as inactive');
check(!$wpdb->get_var('SELECT id FROM bsm_contractor_employees WHERE id='.(int)$children[1]['id']),'Removing unused employee deletes only that row');
// Time Sheet Master edit/delete actions preserve ownership and billed-entry locks.
$admin=true;
fails('time_update',['id'=>$rows[0]['id'],'from_time'=>'09:00','to_time'=>'10:00','activity'=>'Changed'],'Billed');
fails('time_delete',['id'=>$rows[0]['id']],'Billed');
$office_id=(int)$wpdb->get_var("SELECT id FROM bsm_time_entries WHERE employee_id=1 AND work_date='2026-10-28'");
callop('time_update',['id'=>$office_id,'from_time'=>'09:30','to_time'=>'11:00','activity'=>'Edited activity']);
check((int)$wpdb->get_var('SELECT minutes FROM bsm_time_entries WHERE id='.$office_id)===90,'Master edit recalculates duration');
$first=(int)$wpdb->get_var("SELECT id FROM bsm_time_entries WHERE worker_id=$worker AND work_date='2026-10-27' ORDER BY from_time LIMIT 1");
fails('time_update',['id'=>$first,'site_id'=>0,'from_time'=>'09:00','to_time'=>'10:00','activity'=>'No Site'],'Site is required');
fails('time_update',['id'=>$first,'from_time'=>'09:00','to_time'=>'11:30','activity'=>'Overlap'],'overlap');
$admin=false;
callop('time_update',['id'=>$office_id,'from_time'=>'09:30','to_time'=>'10:30','activity'=>'Employee edit']);
check($wpdb->get_var('SELECT activity FROM bsm_time_entries WHERE id='.$office_id)==='Employee edit','Employee may edit own unapproved entry');
$other=(int)$wpdb->get_var('SELECT id FROM bsm_time_entries WHERE employee_id=2 LIMIT 1');fails('time_delete',['id'=>$other],'own unapproved');
callop('time_delete',['id'=>$office_id]);check(!$wpdb->get_var('SELECT id FROM bsm_time_entries WHERE id='.$office_id),'Employee may delete own unapproved entry');
echo "\n{$GLOBALS['passes']} regression checks passed.\n";
