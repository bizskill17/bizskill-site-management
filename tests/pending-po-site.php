<?php
define('ARRAY_A',1);
class DB {
 public $raw=[],$pos=[],$links=[];
 function prepare($q,...$a){foreach($a as $v)$q=preg_replace('/%d/',(string)(int)$v,$q,1);return $q;}
 function get_results($q,$mode){if(strpos($q,'SELECT ri.id')!==false)return $this->raw;if(strpos($q,'SELECT poi.id')!==false)return $this->pos;if(preg_match('/purchase_order_item_id=(\d+)/',$q,$m))return $this->links[$m[1]]??[];throw new Exception($q);}
}
$source=file_get_contents(__DIR__.'/../bizskill-site-management.php');
$start=strpos($source,'    private function pending_po_summary(');
$end=strpos($source,'    public function ajax_pending_po_items(',$start);
if($start===false||$end===false)throw new Exception('Pending PO summary method missing');
$method=substr($source,$start,$end-$start);
$wrapper= <<<'SUBJECT'
class Subject {public $prefix='bsm_',$stock=0,$received=[];
 function default_warehouse_id(){return 1;}
 function warehouse_item_stock($w,$i){return $this->stock;}
 function po_item_received_qty($i){return $this->received[$i]??0;}
 function run($site){return $this->pending_po_summary(1,$site);}

SUBJECT;
eval($wrapper.$method.'}');
function eq($actual,$expected,$label){if($actual!=$expected)throw new Exception($label.': '.json_encode($actual).' != '.json_encode($expected));}
$wpdb=new DB;$s=new Subject;
foreach([1,2] as $site)$wpdb->raw[]=['requisition_item_id'=>$site,'requisition_id'=>$site,'requisition_no'=>'R'.$site,'requisition_date'=>'2026-11-25','required_date'=>'2026-11-26','site_id'=>$site,'site_name'=>'Site '.$site,'requested_by_name'=>'User','approved_by_name'=>'Admin','item_id'=>1,'item_name'=>'Cement','unit_id'=>1,'unit_name'=>'Bag','approved_qty'=>100,'dispatched_qty'=>0];
eq($s->run(1)[0]['pending_po_qty'],100,'Site A only');eq($s->run(2)[0]['pending_po_qty'],100,'Site B only');
$s->stock=120;eq($s->run(1),[],'Stock covers earlier site');eq($s->run(2)[0]['pending_po_qty'],80,'Stock shared once');eq($s->run(2)[0]['current_stock'],20,'Coverage attributed to B');
$s->stock=0;$wpdb->pos=[['id'=>5,'po_qty'=>100]];$wpdb->links[5]=[['requisition_item_id'=>2,'allocated_qty'=>100]];
eq($s->run(1)[0]['pending_po_qty'],100,'B PO does not cover A');eq($s->run(2),[],'B PO covers B');
$s->received[5]=40;$s->stock=40;eq($s->run(1)[0]['pending_po_qty'],60,'Received physical stock shared');eq($s->run(2)[0]['pending_po_qty'],40,'Only outstanding PO reserved');
$wpdb->links[5]=[];$s->received=[];$s->stock=0;eq($s->run(1),[],'Legacy unallocated PO covers earlier A');eq($s->run(2)[0]['pending_po_qty'],100,'Legacy PO counted once');
$wpdb->pos=[];$wpdb->raw[0]['dispatched_qty']=30;eq($s->run(1)[0]['approved_qty'],70,'Dispatched quantity excluded');
echo "Site isolation, shared stock, reserved POs, receipts and dispatch tests passed\n";

