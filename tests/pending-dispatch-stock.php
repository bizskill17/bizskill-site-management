<?php
require __DIR__.'/operations.php';
$src=file_get_contents(__DIR__.'/../bizskill-site-management.php');$a=strpos($src,'    public function ajax_pending_dispatch(){');$b=strpos($src,'    private function dispatch_source_stock(', $a);
eval('class PendingStockHost {private $prefix="bsm_";public $calls=0;private function verify_ajax(){}private function employee_site_scope(){return null;}private function warehouse_item_stock($w,$i){$this->calls++;return $w*10+$i;}'.substr($src,$a,$b-$a).'}');
class PendingStockDB extends DB {function get_col($sql){return array_column($this->get_results($sql),'id');}}
$wpdb=new PendingStockDB();
foreach(['warehouses(id INTEGER)','items(id INTEGER,item_name TEXT)','units(id INTEGER,unit_name TEXT)','sites(id INTEGER,site_name TEXT)','material_requisitions(id INTEGER,requisition_no TEXT,required_date TEXT,site_id INTEGER,status TEXT)','material_requisition_items(id INTEGER,requisition_id INTEGER,item_id INTEGER,unit_id INTEGER,approved_qty REAL)','material_dispatches(id INTEGER,status TEXT)','material_dispatch_items(requisition_item_id INTEGER,dispatch_id INTEGER,qty REAL)'] as $schema)$wpdb->query('CREATE TABLE bsm_'.$schema);
$wpdb->query('INSERT INTO bsm_warehouses VALUES(1),(2)');$wpdb->query("INSERT INTO bsm_items VALUES(1,'Cement'),(2,'Wire')");$wpdb->query("INSERT INTO bsm_units VALUES(1,'Bag'),(2,'Kg')");$wpdb->query("INSERT INTO bsm_sites VALUES(1,'Test')");
$wpdb->query("INSERT INTO bsm_material_requisitions VALUES(1,'REQ1','2026-11-05',1,'approved'),(2,'REQ2','2026-11-05',1,'partially_dispatched')");
$wpdb->query('INSERT INTO bsm_material_requisition_items VALUES(1,1,1,1,10),(2,1,2,2,20),(3,2,1,1,5)');
$wpdb->query("INSERT INTO bsm_material_dispatches VALUES(1,'dispatched'),(2,'cancelled')");$wpdb->query('INSERT INTO bsm_material_dispatch_items VALUES(2,1,20),(1,2,10)');
$host=new PendingStockHost();$host->ajax_pending_dispatch();$rows=$GLOBALS['result']['rows'];
check(count($rows)===2,'Pending requisitions retain their summary rows');check(count($rows[0]['stock_items'])===1,'Fully dispatched items are excluded from current stock display');check($rows[0]['stock_items'][0]['current_stock']===32.0,'Current stock sums the selected item across warehouses');check($rows[0]['stock_items'][0]['unit_name']==='Bag','Stock retains item unit');check($rows[1]['stock_items'][0]['current_stock']===32.0&&$host->calls===2,'Repeated item stock is cached without double counting');
