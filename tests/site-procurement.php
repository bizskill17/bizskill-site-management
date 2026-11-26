<?php
/** Run: php tests/site-procurement.php. SQLite executes the production SQL and trait. */
define('ARRAY_A',1);define('ABSPATH','/');
class JsonReply extends Exception {public $ok,$data;function __construct($ok,$data){$this->ok=$ok;$this->data=$data;parent::__construct($data['message']??'');}}
function wp_send_json_success($d){throw new JsonReply(true,$d);}function wp_send_json_error($d,$status=200){throw new JsonReply(false,$d);}
function current_time($type){return $type==='Y-m-d'?'2026-11-25':'2026-11-25 12:00:00';}
function get_current_user_id(){return 1;}function sanitize_text_field($v){return trim((string)$v);}function sanitize_key($v){return (string)$v;}function wp_json_encode($v){return json_encode($v);}function wp_unslash($v){return $v;}
require __DIR__.'/../includes/site-procurement.php';
class DB {
    public $pdo,$insert_id,$last_error='',$fail='';
    function __construct(){$this->pdo=new PDO('sqlite::memory:');$this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);}
    function prepare($q,...$args){if(count($args)===1&&is_array($args[0]))$args=$args[0];foreach($args as $v)$q=preg_replace_callback('/%[dsf]/',fn($m)=>$m[0]==='%s'?$this->pdo->quote((string)$v):(string)(float)$v,$q,1);return $q;}
    function query($q){if($q==='START TRANSACTION')$q='BEGIN';if($q==='ROLLBACK'&&!$this->pdo->inTransaction()){try{return $this->pdo->exec($q);}catch(Throwable $e){return 0;}}return $this->pdo->exec($q);}
    function get_results($q,$mode=1){return $this->pdo->query(str_replace(' FOR UPDATE','',$q))->fetchAll(PDO::FETCH_ASSOC);}
    function get_row($q,$mode=1){return $this->get_results($q)[0]??null;}
    function get_var($q){if(strpos($q,'GET_LOCK')!==false||strpos($q,'RELEASE_LOCK')!==false)return 1;$r=$this->get_row($q);return $r?array_values($r)[0]:null;}
    function insert($table,$data){if($this->fail===$table)return false;$cols=implode(',',array_keys($data));$marks=implode(',',array_fill(0,count($data),'?'));$st=$this->pdo->prepare("INSERT INTO $table ($cols) VALUES ($marks)");$st->execute(array_values($data));$this->insert_id=(int)$this->pdo->lastInsertId();return 1;}
    function update($table,$data,$where){$cols=implode(',',array_map(fn($k)=>"$k=?",array_keys($data)));$cond=implode(' AND ',array_map(fn($k)=>"$k=?",array_keys($where)));$st=$this->pdo->prepare("UPDATE $table SET $cols WHERE $cond");$st->execute(array_merge(array_values($data),array_values($where)));return $st->rowCount();}
    function delete($table,$where){$cond=implode(' AND ',array_map(fn($k)=>"$k=?",array_keys($where)));$st=$this->pdo->prepare("DELETE FROM $table WHERE $cond");$st->execute(array_values($where));return $st->rowCount();}
}
$wpdb=new DB;
$schema=[
'site_stock_allocations'=>'id INTEGER PRIMARY KEY,warehouse_id INT,site_id INT,item_id INT,unit_id INT,allocation_date TEXT,qty REAL,rate REAL,reviewed_by INT,created_at TEXT',
'stock_ledger'=>'id INTEGER PRIMARY KEY,transaction_date TEXT,location_type TEXT,site_id INT,warehouse_id INT,item_id INT,unit_id INT,transaction_type TEXT,reference_type TEXT,reference_id INT,qty_in REAL,qty_out REAL,rate REAL,created_at TEXT',
'sites'=>'id INTEGER PRIMARY KEY,site_name TEXT,site_location TEXT',
'users'=>'id INTEGER PRIMARY KEY,name TEXT,wp_user_id INT,status TEXT,allowed_views TEXT',
'vendors'=>'id INTEGER PRIMARY KEY,vendor_name TEXT,status TEXT,gst_type TEXT',
'items'=>'id INTEGER PRIMARY KEY,item_name TEXT,unit_id INT,gst_rate REAL,category_id INT',
'units'=>'id INTEGER PRIMARY KEY,unit_name TEXT',
'item_categories'=>'id INTEGER PRIMARY KEY,category_name TEXT',
'material_requisitions'=>'id INTEGER PRIMARY KEY,requisition_no TEXT,requisition_date TEXT,required_date TEXT,site_id INT,status TEXT',
'material_requisition_items'=>'id INTEGER PRIMARY KEY,requisition_id INT,item_id INT,unit_id INT,approved_qty REAL',
'purchase_orders'=>'id INTEGER PRIMARY KEY AUTOINCREMENT,po_no TEXT,po_date TEXT,vendor_id INT,site_id INT,requisition_id INT,site_flow INT,warehouse_id INT,required_date TEXT,remarks TEXT,status TEXT,created_by INT,created_at TEXT,updated_at TEXT,approved_by INT,approved_at TEXT',
'purchase_order_items'=>'id INTEGER PRIMARY KEY AUTOINCREMENT,purchase_order_id INT,requisition_item_id INT,item_id INT,unit_id INT,po_qty REAL,rate REAL,created_at TEXT',
'purchase_order_item_allocations'=>'id INTEGER PRIMARY KEY AUTOINCREMENT,purchase_order_item_id INT,requisition_item_id INT,allocated_qty REAL,created_at TEXT',
'material_inwards'=>'id INTEGER PRIMARY KEY AUTOINCREMENT,inward_no TEXT,site_id INT,site_flow INT,warehouse_id INT,purchase_order_id INT,vendor_id INT,inward_type TEXT,inward_date TEXT,supplier_invoice_no TEXT,supplier_invoice_date TEXT,bill_type TEXT,taxable_amount REAL,cgst_amount REAL,sgst_amount REAL,igst_amount REAL,other_expenses REAL,other_expense_gst REAL,round_off REAL,total_amount REAL,status TEXT,created_by INT,approved_by INT,approved_at TEXT,created_at TEXT,updated_at TEXT',
'material_inward_items'=>'id INTEGER PRIMARY KEY AUTOINCREMENT,inward_id INT,purchase_order_item_id INT,item_id INT,unit_id INT,qty REAL,approved_qty REAL,rate REAL,taxable_amount REAL,gst_rate REAL,cgst_amount REAL,sgst_amount REAL,igst_amount REAL,total_amount REAL,remarks TEXT',
'material_returns'=>'id INTEGER PRIMARY KEY AUTOINCREMENT,return_no TEXT,site_id INT,warehouse_id INT,purchase_receipt_id INT,vendor_id INT,site_flow INT,return_date TEXT,return_by INT,status TEXT,created_by INT,created_at TEXT,updated_at TEXT',
'material_return_items'=>'id INTEGER PRIMARY KEY AUTOINCREMENT,return_id INT,item_id INT,unit_id INT,qty REAL,rate REAL,remarks TEXT',
'site_opening_stock'=>'id INTEGER PRIMARY KEY,site_id INT,item_id INT,unit_id INT,opening_date TEXT,opening_qty REAL,opening_rate REAL',
'site_receipts'=>'id INTEGER PRIMARY KEY,site_id INT,dispatch_id INT,receipt_date TEXT,status TEXT',
'site_receipt_items'=>'id INTEGER PRIMARY KEY,receipt_id INT,dispatch_item_id INT,item_id INT,unit_id INT,received_qty REAL,rate REAL',
'material_dispatches'=>'id INTEGER PRIMARY KEY,site_id INT,from_site_id INT,dispatch_date TEXT,status TEXT',
'material_dispatch_items'=>'id INTEGER PRIMARY KEY,dispatch_id INT,item_id INT,unit_id INT,qty REAL,rate REAL',
'site_consumptions'=>'id INTEGER PRIMARY KEY,site_id INT,consumption_date TEXT,status TEXT',
'site_consumption_items'=>'id INTEGER PRIMARY KEY,consumption_id INT,item_id INT,unit_id INT,qty REAL',
'site_transfers'=>'id INTEGER PRIMARY KEY,from_site_id INT,to_site_id INT,dispatched_at TEXT,status TEXT',
'site_transfer_items'=>'id INTEGER PRIMARY KEY,transfer_id INT,item_id INT,unit_id INT,qty REAL,rate REAL',
'site_transfer_receipts'=>'id INTEGER PRIMARY KEY,transfer_id INT,transfer_item_id INT,receipt_date TEXT,received_qty REAL'
];foreach($schema as $table=>$cols)$wpdb->query("CREATE TABLE bsm_$table ($cols)");
class Subject {
    use BSM_Site_Procurement;
    public $prefix='bsm_',$scope=null,$sequence=0,$ledger=[],$admin=true,$hidden=[];
    function verify_ajax(){}function site_procurement_upgrade(){}
    function employee_site_scope(){return $this->scope;}function nt_admin(){return $this->admin;}function hidden_views(){return $this->hidden;}function access($v){$this->sp_access($v);return true;}
    function warehouse_item_stock($w,$i){global $wpdb;return (float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(qty_in-qty_out),0) FROM bsm_stock_ledger WHERE location_type='WAREHOUSE' AND warehouse_id=%d AND item_id=%d",$w,$i));}function warehouse_item_rate($w,$i){return 10;}
    function ops_date($d){if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$d))throw new Exception('Invalid date');return $d;}
    function ops_lock($k){return $k;}function plugin_user_id(){return 1;}function next_no($t,$p){return $p.'-'.++$this->sequence;}
    function sync_ledgers($type,$id){$this->ledger[]=[$type,$id];}
    function po_item_received_qty($id){global $wpdb;return (float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(d.qty),0) FROM bsm_material_inward_items d JOIN bsm_material_inwards h ON h.id=d.inward_id WHERE d.purchase_order_item_id=%d AND h.status<>'cancelled'",$id));}
    function refresh_purchase_order_status($id){global $wpdb;$items=$wpdb->get_results("SELECT * FROM bsm_purchase_order_items WHERE purchase_order_id=$id");$qty=0;$got=0;foreach($items as $x){$qty+=$x['po_qty'];$got+=$this->po_item_received_qty($x['id']);}$wpdb->update('bsm_purchase_orders',['status'=>$got>=$qty?'fully_received':'partly_received'],['id'=>$id]);}
    function create($d,$i){return $this->sp_po_create($d,$i);}function receive($d){return $this->sp_purchase_save($d);}function ret($d){return $this->sp_return_save($d);}function requirements($s,$r=0){return $this->sp_requirements($s,$r);}function stock($s,$i){return $this->site_available_stock($s,$i);}function inventory(){return $this->sp_inventory_rows();}
}
function eq($a,$b,$label){if($a!=$b)throw new Exception($label.': '.json_encode($a).' != '.json_encode($b));}
function rejects($f,$label){try{$f();}catch(Throwable $e){return;}throw new Exception('Expected rejection: '.$label);}
foreach([1,2] as $id){$wpdb->insert('bsm_sites',['id'=>$id,'site_name'=>'Site '.$id]);$wpdb->insert('bsm_vendors',['id'=>$id,'vendor_name'=>'Supplier '.$id,'status'=>'active','gst_type'=>'intra_state']);$wpdb->insert('bsm_items',['id'=>$id,'item_name'=>'Item '.$id,'unit_id'=>1,'gst_rate'=>18,'category_id'=>1]);}
$wpdb->insert('bsm_units',['id'=>1,'unit_name'=>'Bag']);$wpdb->insert('bsm_item_categories',['id'=>1,'category_name'=>'Materials']);$wpdb->insert('bsm_users',['id'=>1,'name'=>'Admin']);
foreach([1,2,3] as $id){$wpdb->insert('bsm_material_requisitions',['id'=>$id,'requisition_no'=>'REQ-'.$id,'requisition_date'=>'2026-11-21','required_date'=>'2026-11-26','site_id'=>$id===2?2:1,'status'=>$id===3?'pending_approval':'approved']);foreach([1,2] as $item)$wpdb->insert('bsm_material_requisition_items',['id'=>$id*10+$item,'requisition_id'=>$id,'item_id'=>$item,'unit_id'=>1,'approved_qty'=>100]);}
$s=new Subject;$header=['site_id'=>1,'requisition_id'=>1,'po_date'=>'2026-11-25'];$line=['requisition_item_id'=>11,'po_qty'=>40,'vendor_id'=>1,'quotations'=>[['vendor_id'=>1,'rate'=>10]]];
$r=$s->create($header,[$line]);eq(count($r['created']),1,'One quotation allowed');eq($s->requirements(1,1)[0]['pending_po_qty'],60,'Partial ordering');eq($s->requirements(2,2)[0]['pending_po_qty'],100,'Other site unchanged');eq($s->stock(1,1)['available_qty'],0,'PO does not change stock');
rejects(fn()=>$s->create($header,[array_merge($line,['po_qty'=>61])]),'Over-order');rejects(fn()=>$s->create(array_merge($header,['site_id'=>2]),[$line]),'Cross-site requisition');rejects(fn()=>$s->create(array_merge($header,['requisition_id'=>3]),[array_merge($line,['requisition_item_id'=>31])]),'Unapproved requisition');
$po=$r['created'][0]['id'];$wpdb->update('bsm_purchase_orders',['status'=>'approved'],['id'=>$po]);$poi=(int)$wpdb->get_var("SELECT id FROM bsm_purchase_order_items WHERE purchase_order_id=$po");
$rec=$s->receive(['purchase_order_id'=>$po,'inward_date'=>'2026-11-25','_items'=>[['purchase_order_item_id'=>$poi,'qty'=>25]]]);eq($s->stock(1,1)['available_qty'],25,'Direct Site receipt');eq($s->stock(2,1)['available_qty'],0,'Receipt isolated by site');eq((int)$wpdb->get_var("SELECT COUNT(*) FROM bsm_stock_ledger WHERE reference_type='material_inwards'"),1,'One direct Site ledger entry');eq((int)$wpdb->get_var("SELECT COUNT(*) FROM bsm_stock_ledger WHERE location_type='WAREHOUSE'"),0,'No warehouse stock entry');
rejects(fn()=>$s->receive(['purchase_order_id'=>$po,'inward_date'=>'2026-11-25','_items'=>[['purchase_order_item_id'=>$poi,'qty'=>16]]]),'Over-receipt');rejects(fn()=>$s->receive(['purchase_order_id'=>$po,'inward_date'=>'2026-11-25','_items'=>[['purchase_order_item_id'=>$poi,'qty'=>10],['purchase_order_item_id'=>$poi,'qty'=>10]]]),'Duplicate receipt lines');
$s->ret(['site_id'=>1,'purchase_receipt_id'=>$rec['id'],'return_date'=>'2026-11-25','_items'=>[['item_id'=>1,'qty'=>5]]]);eq($s->stock(1,1)['available_qty'],20,'Supplier Return');eq($s->requirements(1,1)[0]['pending_po_qty'],60,'Return does not reopen Requisition');
$wpdb->insert('bsm_site_consumptions',['id'=>1,'site_id'=>1,'consumption_date'=>'2026-11-25','status'=>'approved']);$wpdb->insert('bsm_site_consumption_items',['id'=>1,'consumption_id'=>1,'item_id'=>1,'unit_id'=>1,'qty'=>3]);
$wpdb->insert('bsm_site_transfers',['id'=>1,'from_site_id'=>1,'to_site_id'=>2,'dispatched_at'=>'2026-11-25 12:00:00','status'=>'partially_received']);$wpdb->insert('bsm_site_transfer_items',['id'=>1,'transfer_id'=>1,'item_id'=>1,'unit_id'=>1,'qty'=>7,'rate'=>10]);$wpdb->insert('bsm_site_transfer_receipts',['id'=>1,'transfer_id'=>1,'transfer_item_id'=>1,'receipt_date'=>'2026-11-25','received_qty'=>4]);
eq($s->stock(1,1)['available_qty'],10,'Consumption and transfer out');eq($s->stock(2,1)['available_qty'],4,'Partial transfer receipt');eq($s->inventory()[0]['balance'],10,'Report reconciles with availability');
$wpdb->update('bsm_purchase_orders',['status'=>'cancelled'],['id'=>$po]);eq($s->requirements(1,1)[0]['pending_po_qty'],100,'Cancellation releases order quantity');$wpdb->update('bsm_purchase_orders',['status'=>'approved'],['id'=>$po]);
$s->scope=[2];rejects(fn()=>$s->create($header,[$line]),'Site permission');$s->scope=null;
$wpdb->fail='bsm_purchase_order_items';rejects(fn()=>$s->create($header,[array_merge($line,['po_qty'=>10])]),'Rollback on item write failure');$wpdb->fail='';eq($s->requirements(1,1)[0]['pending_po_qty'],60,'Rollback restores balances');
$splitHeader=array_merge($header,['request_id'=>'supplier-split-retry']);$r=$s->create($splitHeader,[array_merge($line,['po_qty'=>10]),['requisition_item_id'=>12,'po_qty'=>5,'vendor_id'=>2,'quotations'=>[['vendor_id'=>2,'rate'=>20]]]]);eq(count($r['created']),2,'Supplier split within one requisition');$splitAgain=$s->create($splitHeader,[array_merge($line,['po_qty'=>10]),['requisition_item_id'=>12,'po_qty'=>5,'vendor_id'=>2,'quotations'=>[['vendor_id'=>2,'rate'=>20]]]]);eq($splitAgain['created'],$r['created'],'Retry returns every supplier-split PO');
// Exercise the real mutation endpoints: their JSON replies occur after lock release.
function endpoint($host,$method,$post){$_POST=$post;try{$host->$method();}catch(JsonReply $r){return $r;}throw new Exception('Missing JSON response');}
$update=['id'=>$po,'data'=>json_encode(['po_date'=>'2026-11-25','vendor_id'=>1]),'items'=>json_encode([['id'=>$poi,'po_qty'=>24,'rate'=>10]])];
eq(endpoint($s,'ajax_purchase_order_update',$update)->ok,false,'Edit cannot reduce below received quantity');
$update['items']=json_encode([['id'=>$poi,'po_qty'=>91,'rate'=>10]]);eq(endpoint($s,'ajax_purchase_order_update',$update)->ok,false,'Edit cannot exceed remaining approved quantity');
$update['items']=json_encode([['id'=>$poi,'po_qty'=>45,'rate'=>10]]);eq(endpoint($s,'ajax_purchase_order_update',$update)->ok,true,'Valid partial quantity amendment');eq($wpdb->get_var("SELECT status FROM bsm_purchase_orders WHERE id=$po"),'pending_approval','Amendment requires reapproval');
eq(endpoint($s,'ajax_purchase_order_cancel',['id'=>$po])->ok,false,'Cannot cancel received PO');
$stockBefore=$s->stock(1,1)['available_qty'];$countBefore=(int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_material_inwards');$newPo=$r['created'][0]['id'];$wpdb->update('bsm_purchase_orders',['status'=>'approved'],['id'=>$newPo]);$newItem=(int)$wpdb->get_var("SELECT id FROM bsm_purchase_order_items WHERE purchase_order_id=$newPo");
$wpdb->fail='bsm_stock_ledger';rejects(fn()=>$s->receive(['purchase_order_id'=>$newPo,'inward_date'=>'2026-11-25','_items'=>[['purchase_order_item_id'=>$newItem,'qty'=>5]]]),'Ledger failure rolls back receipt');$wpdb->fail='';eq((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_material_inwards'),$countBefore,'No orphan receipt after ledger error');eq($s->stock(1,1)['available_qty'],$stockBefore,'Stock unchanged after failed receipt');
$cancelPo=$r['created'][1]['id'];eq(endpoint($s,'ajax_purchase_order_cancel',['id'=>$cancelPo])->ok,true,'Unused PO cancellation');
// Execute the migration selection query against mixed legacy references. An unlinked
// item must prevent automatic assignment of the whole PO.
$source=file_get_contents(__DIR__.'/../includes/site-procurement.php');$start=strpos($source,'SELECT z.purchase_order_id');$end=strpos($source,') x ON x.purchase_order_id',$start);$sql=str_replace('{$p}','bsm_',substr($source,$start,$end-$start));
$wpdb->insert('bsm_purchase_orders',['id'=>900,'status'=>'approved']);$wpdb->insert('bsm_purchase_order_items',['id'=>900,'purchase_order_id'=>900,'requisition_item_id'=>11,'item_id'=>1,'unit_id'=>1,'po_qty'=>1]);$wpdb->insert('bsm_purchase_order_items',['id'=>901,'purchase_order_id'=>900,'requisition_item_id'=>null,'item_id'=>2,'unit_id'=>1,'po_qty'=>1]);
$mapped=array_column($wpdb->get_results($sql),'purchase_order_id');eq(in_array(900,$mapped),false,'Partial historical linkage is not auto-assigned');
$s->admin=false;$wpdb->update('bsm_users',['wp_user_id'=>1,'status'=>'active','allowed_views'=>'["vendors"]'],['id'=>1]);rejects(fn()=>$s->access(['purchase_order_form']),'Supplier-only user cannot procure');$wpdb->update('bsm_users',['allowed_views'=>'["purchase_order_form"]'],['id'=>1]);eq($s->access(['purchase_order_form']),true,'Explicit procurement permission');$s->hidden=['purchase_order_form'];rejects(fn()=>$s->access(['purchase_order_form']),'Globally hidden view denied');$s->hidden=[];$s->admin=true;
$wpdb->insert('bsm_stock_ledger',['location_type'=>'WAREHOUSE','warehouse_id'=>1,'item_id'=>1,'unit_id'=>1,'qty_in'=>10,'qty_out'=>0,'rate'=>10]);$before=$s->stock(1,1)['available_qty'];
eq(endpoint($s,'ajax_site_procurement_allocate',['site_id'=>1,'warehouse_id'=>1,'item_id'=>1,'qty'=>11,'allocation_date'=>'2026-11-25'])->ok,false,'Cannot over-allocate historical stock');eq(endpoint($s,'ajax_site_procurement_allocate',['site_id'=>1,'warehouse_id'=>1,'item_id'=>1,'qty'=>6,'allocation_date'=>'2026-11-25'])->ok,true,'Reviewed historical allocation');eq($s->warehouse_item_stock(1,1),4,'Historical source balance reduced');eq($s->stock(1,1)['available_qty'],$before+6,'Reviewed Site stock increased once');eq(endpoint($s,'ajax_site_procurement_allocate',['site_id'=>2,'warehouse_id'=>1,'item_id'=>1,'qty'=>5,'allocation_date'=>'2026-11-25'])->ok,false,'Second allocation cannot reuse stock');
eq(endpoint($s,'ajax_site_procurement_assign_po',['id'=>900,'site_id'=>1,'requisition_id'=>1])->ok,true,'Explicit historical PO assignment');eq($wpdb->get_var('SELECT status FROM bsm_purchase_orders WHERE id=900'),'pending_approval','Assigned historical PO needs approval');eq(endpoint($s,'ajax_site_procurement_assign_po',['id'=>900,'site_id'=>2,'requisition_id'=>2])->ok,false,'Cannot silently reassign a linked historical PO');
$retryHeader=array_merge($header,['request_id'=>'retry-test-1']);
$retryLine=array_merge($line,['po_qty'=>1,'quotations'=>[['vendor_id'=>1,'rate'=>12],['vendor_id'=>1,'rate'=>8]]]);
$first=$s->create($retryHeader,[$retryLine]);$count=(int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_purchase_orders');
$again=$s->create($retryHeader,[$retryLine]);eq($again['created'],$first['created'],'Retry returns original POs');eq((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_purchase_orders'),$count,'Retry creates no duplicate');
$id=$first['created'][0]['id'];eq((float)$wpdb->get_var("SELECT rate FROM bsm_purchase_order_items WHERE purchase_order_id=$id"),8,'Repeated supplier uses lowest quoted rate');
rejects(fn()=>$s->create($retryHeader,[array_merge($retryLine,['po_qty'=>2])]),'Same request cannot change payload');
echo "Site procurement lifecycle passed: partial POs, one quotation, supplier split, site isolation, receipt limits, supplier return, consumption, transfers, inventory reconciliation and rollback.\n";
