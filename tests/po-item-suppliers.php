<?php
require __DIR__.'/operations.php';
$source=file_get_contents(__DIR__.'/../bizskill-site-management.php');
$a=strpos($source,'    private function manual_po_groups(');$b=strpos($source,'    public function ajax_purchase_order_master()', $a);
$c=strpos($source,'    private function create_purchase_order_record(');$d=strpos($source,'    public function ajax_purchase_order_create()', $c);
eval('class ItemPoHost {private $prefix="bsm_";private $seq=0;private function verify_ajax(){}private function migrate_purchase_orders(){}private function plugin_user_id(){return 10;}private function next_no($t,$p){return "PO-".(++$this->seq);}public function groups($items){return $this->manual_po_groups($items);}'.substr($source,$c,$d-$c).substr($source,$a,$b-$a).'}');
class PoDB extends DB {public $failVendor=0;function insert($t,$data){if($t==='bsm_purchase_orders'&&$data['vendor_id']===$this->failVendor){$this->last_error='Simulated write failure';return false;}return parent::insert($t,$data);}}
$wpdb=new PoDB();
foreach([
'items(id INTEGER,unit_id INTEGER)', 'vendors(id INTEGER,status TEXT)', 'warehouses(id INTEGER)',
'purchase_orders(id INTEGER PRIMARY KEY,po_no TEXT,po_date TEXT,vendor_id INTEGER,warehouse_id INTEGER,required_date TEXT,remarks TEXT,status TEXT,created_by INTEGER,created_at TEXT,updated_at TEXT)',
'purchase_order_items(id INTEGER PRIMARY KEY,purchase_order_id INTEGER,requisition_item_id INTEGER,item_id INTEGER,unit_id INTEGER,approved_qty_snapshot REAL,stock_snapshot REAL,pending_qty_snapshot REAL,po_qty REAL,rate REAL,created_at TEXT)'
] as $schema)$wpdb->query('CREATE TABLE bsm_'.$schema);
$wpdb->query('INSERT INTO bsm_items VALUES(1,1),(2,2),(3,1)');$wpdb->query("INSERT INTO bsm_vendors VALUES(1,'active'),(2,'active'),(3,'inactive')");$wpdb->query('INSERT INTO bsm_warehouses VALUES(1)');
$host=new ItemPoHost();
$lines=[['item_id'=>1,'po_qty'=>100,'vendor_id'=>2,'rate'=>1,'quotations'=>[['vendor_id'=>1,'rate'=>350],['vendor_id'=>2,'rate'=>340]]],['item_id'=>2,'po_qty'=>50,'vendor_id'=>1,'quotations'=>[['vendor_id'=>1,'rate'=>65]]],['item_id'=>3,'po_qty'=>2,'vendor_id'=>1,'selection_reason'=>'Reliable delivery','quotations'=>[['vendor_id'=>1,'rate'=>70],['vendor_id'=>2,'rate'=>60]]]];
$g=$host->groups($lines);check(count($g)===2&&count($g[1])===2,'Items group into one PO per supplier');check($g[2][0]['rate']===340.0,'Server derives selected rate from quotation instead of client rate');check(count($g[1][0]['quotations'])===1,'One quotation allowed; other quotations optional');check($g[1][1]['selection_reason']==='Reliable delivery','Higher-rate reason retained per item');
function rejectPo($host,$lines,$text){try{$host->groups($lines);throw new Exception('Expected validation failure');}catch(Exception $e){check(str_contains($e->getMessage(),$text),'PO validation: '.$text);}}
$x=$lines;$x[2]['selection_reason']='';rejectPo($host,$x,'reason');
$x=$lines;$x[0]['vendor_id']=3;rejectPo($host,$x,'Choose a supplier');
$x=$lines;$x[0]['quotations'][1]['vendor_id']=1;rejectPo($host,$x,'distinct');
$x=$lines;$x[0]['quotations'][1]['vendor_id']=3;rejectPo($host,$x,'active');
$x=$lines;$x[0]['quotations'][0]['rate']=-1;rejectPo($host,$x,'valid supplier');
$x=$lines;$x[0]['po_qty']=0;rejectPo($host,$x,'valid PO quantity');
$x=$lines;$x[1]['item_id']=1;rejectPo($host,$x,'one row');
$x=$lines;$x[0]['item_id']=99;rejectPo($host,$x,'does not exist');
$_POST=['warehouse_id'=>1,'po_date'=>'2026-11-05','required_date'=>'2026-11-10','items'=>json_encode($lines)];
$host->ajax_purchase_order_manual_save();check(count($GLOBALS['result']['created'])===2,'Manual save returns both generated POs');check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_purchase_order_items')===3,'Every item saved exactly once');check((int)$wpdb->get_var("SELECT COUNT(*) FROM bsm_purchase_orders WHERE status='pending_approval'")===2,'Both supplier POs require approval');
check((float)$wpdb->get_var('SELECT SUM(po_qty*rate) FROM bsm_purchase_order_items')===37390.0,'Supplier quantities and selected rates produce correct total');
$meta=json_decode($wpdb->get_var('SELECT remarks FROM bsm_purchase_orders WHERE vendor_id=1'),true);check(count($meta['supplier_quotations'])===2&&$meta['supplier_quotations'][1]['selection_reason']==='Reliable delivery','PO retains item-level quotation metadata');
$wpdb->failVendor=1;try{$host->ajax_purchase_order_manual_save();}catch(Exception $e){check(str_contains($e->getMessage(),'Simulated'),'A supplier write failure is reported');}
check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_purchase_orders')===2&&(int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_purchase_order_items')===3,'Failed second supplier rolls back all new POs and items');
$_POST['po_date']='2026-02-30';try{$host->ajax_purchase_order_manual_save();}catch(Exception $e){check(str_contains($e->getMessage(),'valid PO Date'),'Invalid calendar dates rejected');}
echo "PO item supplier tests complete.\n";
