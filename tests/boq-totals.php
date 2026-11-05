<?php
require __DIR__.'/operations.php';
$main=file_get_contents(__DIR__.'/../bizskill-site-management.php');
$start=strpos($main,'    private function boq_item_totals(');$end=strpos($main,'    public function ajax_boq_list()', $start);
eval('class BoqTotalsHost {private $prefix="bsm_";public $stock=["received"=>20,"consumed"=>4,"returned"=>2,"available_qty"=>27];public $movements=[["in"=>6,"out"=>0],["in"=>0,"out"=>3]];'.substr($main,$start,$end-$start).' public function totals($s,$i){return $this->boq_item_totals($s,$i);} private function site_available_stock($s,$i){return $this->stock;} private function ops_transfer_movements($s,$i){return $this->movements;}}');
$wpdb=new DB();
$wpdb->query('CREATE TABLE bsm_material_requisitions (id INTEGER,site_id INTEGER,status TEXT)');
$wpdb->query('CREATE TABLE bsm_material_requisition_items (requisition_id INTEGER,item_id INTEGER,requested_qty REAL,approved_qty REAL,rejected_qty REAL)');
$wpdb->query('CREATE TABLE bsm_site_opening_stock (site_id INTEGER,item_id INTEGER,opening_qty REAL)');
foreach(['pending_approval','approved','rejected','cancelled','draft','submitted','dispatched'] as $n=>$status){
 $wpdb->insert('bsm_material_requisitions',['id'=>$n+1,'site_id'=>1,'status'=>$status]);
 $wpdb->insert('bsm_material_requisition_items',['requisition_id'=>$n+1,'item_id'=>2,'requested_qty'=>10,'approved_qty'=>7,'rejected_qty'=>$status==='rejected'?0:3]);
}
$wpdb->insert('bsm_material_requisitions',['id'=>99,'site_id'=>2,'status'=>'approved']);
$wpdb->insert('bsm_material_requisition_items',['requisition_id'=>99,'item_id'=>2,'requested_qty'=>100,'approved_qty'=>100,'rejected_qty'=>0]);
$wpdb->insert('bsm_material_requisition_items',['requisition_id'=>2,'item_id'=>3,'requested_qty'=>100,'approved_qty'=>100,'rejected_qty'=>0]);
$wpdb->insert('bsm_site_opening_stock',['site_id'=>1,'item_id'=>2,'opening_qty'=>10]);
$result=(new BoqTotalsHost())->totals(1,2);
$expected=['site_opening_qty'=>10,'requisition_qty'=>50,'approved_qty'=>14,'rejected_qty'=>16,'received_qty'=>26,'consumption_qty'=>4,'return_qty'=>2,'transfer_out_qty'=>3,'balance'=>27];
foreach($expected as $key=>$value)if(abs($result[$key]-$value)>0.001)throw new Exception("BOQ $key: expected $value, got ".$result[$key]);
echo "BOQ totals: 9 checks passed\n";

$host=new BoqTotalsHost();$wpdb->query('DELETE FROM bsm_site_opening_stock');
$host->stock=['received'=>5,'consumed'=>2,'returned'=>0,'available_qty'=>999];$host->movements=[];
$r=$host->totals(1,2);check($r['balance']===3.0,'Binding Wire without transfers: 0 + 5 - 2 - 0 = 3');
$host->movements=[['in'=>0,'out'=>3]];$r=$host->totals(1,2);check($r['balance']===0.0&&$r['transfer_out_qty']===3.0,'Binding Wire with transfer: transfer deduction is visible and leaves zero');
$host->stock=['received'=>43,'consumed'=>0,'returned'=>20];$host->movements=[];$wpdb->insert('bsm_site_opening_stock',['site_id'=>1,'item_id'=>2,'opening_qty'=>50]);
$r=$host->totals(1,2);check($r['balance']===73.0,'CPVC example: 50 + 43 - 0 - 20 = 73');
$host->movements=[['in'=>7,'out'=>4]];$r=$host->totals(1,2);check($r['received_qty']===50.0&&$r['balance']===76.0,'Inbound transfers included once in Received; outbound transfers deducted once');
$host->stock=['received'=>0,'consumed'=>60,'returned'=>0];$host->movements=[];$r=$host->totals(1,2);check($r['balance']===-10.0,'Negative balance is preserved so stock discrepancies remain visible');
