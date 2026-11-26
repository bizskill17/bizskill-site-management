<?php
/** Execute the production BOQ endpoints against SQLite fixtures. */
require __DIR__.'/site-procurement.php';
function add_action(...$a){}function add_filter(...$a){}function add_shortcode(...$a){}function register_activation_hook(...$a){}function register_deactivation_hook(...$a){}
function check_ajax_referer(...$a){}function is_user_logged_in(){return true;}function current_user_can($c){return true;}function get_option($k,$d=false){return $k==='bsm_boq_editor_schema'?'1':$d;}
function wp_generate_uuid4(){static $i=0;return 'fixture-'.++$i;}function wp_date($f,$t=null){return date($f,$t??strtotime('2026-11-26'));}
class BoqDB extends DB {
 public $prefix='';
 function get_var($sql){if(strpos($sql,'SHOW COLUMNS')===0)return 1;return parent::get_var($sql);}
 function get_results($sql,$mode=1){if(strpos($sql,'SHOW INDEX')===0)return [];return parent::get_results($sql,$mode);}
 function get_col($sql){return array_map(fn($r)=>array_values($r)[0],$this->get_results($sql));}
}
$db=new BoqDB;$db->pdo=$wpdb->pdo;$wpdb=$db;
$wpdb->query('CREATE TABLE bsm_boqs (id INTEGER PRIMARY KEY AUTOINCREMENT,boq_no TEXT,boq_date TEXT,site_id INT,section TEXT,boq_name TEXT,remarks TEXT,prepared_by INT,created_by INT,created_at TEXT,updated_at TEXT)');
$wpdb->query('CREATE TABLE bsm_boq_items (id INTEGER PRIMARY KEY AUTOINCREMENT,boq_id INT,item_id INT,section TEXT,unit_id INT,boq_qty REAL)');
require __DIR__.'/../bizskill-site-management.php';$app=BSM_Site_Management::instance();
$payload=['site_id'=>1,'boq_date'=>'2026-11-26','prepared_by'=>1,'items'=>json_encode([['item_id'=>2,'boq_qty'=>25],['item_id'=>1,'boq_qty'=>100]])];
$created=endpoint($app,'ajax_boq_save',$payload);eq($created->ok,true,'BOQ saves');$id=$created->data['ids'][0];eq((int)$wpdb->get_var("SELECT prepared_by FROM bsm_boqs WHERE id=$id"),1,'Prepared By persisted');eq(array_map('intval',$wpdb->get_col("SELECT item_id FROM bsm_boq_items WHERE boq_id=$id ORDER BY id")),[2,1],'Reordered rows saved');
eq(endpoint($app,'ajax_boq_save',array_merge($payload,['prepared_by'=>999]))->ok,false,'Invalid preparer blocked');
eq(endpoint($app,'ajax_boq_save',array_merge($payload,['items'=>json_encode([['item_id'=>1,'boq_qty'=>0]])]))->ok,false,'Zero quantity blocked');
eq(endpoint($app,'ajax_boq_save',array_merge($payload,['items'=>json_encode([['item_id'=>1,'boq_qty'=>1],['item_id'=>1,'boq_qty'=>2]])]))->ok,false,'Duplicate item blocked');
eq(endpoint($app,'ajax_boq_save',array_merge($payload,['id'=>$id,'items'=>json_encode([['item_id'=>1,'boq_qty'=>101]])]))->ok,false,'Used rows cannot be changed or removed');
eq(endpoint($app,'ajax_boq_save',array_merge($payload,['id'=>$id,'site_id'=>2]))->ok,false,'Used BOQ Site locked');
eq(endpoint($app,'ajax_boq_delete',['id'=>$id])->ok,false,'Used BOQ deletion blocked');
$loaded=endpoint($app,'ajax_boq_get',['id'=>$id]);eq($loaded->ok,true,'BOQ reload');eq((int)$loaded->data['prepared_by'],1,'Preparer reload');eq(in_array(1,$loaded->data['used_items']),true,'Requisition locks supplied to form');
echo "BOQ endpoints passed: preparer, row order, quantities, duplicate prevention, used-item and Site protection.\n";
$parser=new ReflectionMethod($app,'boq_import_rows');
$xml='<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData><row r="1"><c r="A1" t="inlineStr"><is><t>Item</t></is></c><c r="B1" t="inlineStr"><is><t>BOQ Qty</t></is></c></row><row r="2"><c r="A2" t="inlineStr"><is><t>Item 1</t></is></c><c r="B2"><v>25</v></c></row><row r="3"><c r="A3" t="inlineStr"><is><t>New Uploaded Pipe</t></is></c><c r="B3"><v>40</v></c></row><row r="4"><c r="A4" t="inlineStr"><is><t>Item 1</t></is></c><c r="B4"><v>10</v></c></row></sheetData></worksheet>';
$import=$parser->invoke($app,$xml);eq(count($import),3,'Import retains all review rows');eq($import[1]['item_id'],0,'Unknown imported item stays unresolved');eq($import[1]['item_name'],'New Uploaded Pipe','Unknown item name retained');eq($import[0]['item_id'],$import[2]['item_id'],'Duplicate import retained for correction');
rejects(fn()=>$parser->invoke($app,str_replace('<v>25</v>','<v>-1</v>',$xml)),'Invalid spreadsheet quantity');
echo "BOQ spreadsheet parser passed: known, unknown and duplicate rows, and invalid quantities.\n";
