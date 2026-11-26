<?php
/** Import transport works without PHP ZIP, SimpleXML or mbstring. */
require __DIR__.'/site-procurement.php';
function add_action(...$a){}function add_filter(...$a){}function add_shortcode(...$a){}function register_activation_hook(...$a){}function register_deactivation_hook(...$a){}
function check_ajax_referer(...$a){}function is_user_logged_in(){return true;}function current_user_can($c){return true;}
class ExcelDB extends DB {public $prefix='';}$database=new ExcelDB;$database->pdo=$wpdb->pdo;$wpdb=$database;$wpdb->query("ALTER TABLE bsm_items ADD status TEXT DEFAULT 'active'");
require __DIR__.'/../bizskill-site-management.php';$app=BSM_Site_Management::instance();
$input=[['item_name'=>'Item 1','boq_qty'=>'25'],['item_name'=>'Unknown Pipe','boq_qty'=>'10']];
$reply=endpoint($app,'ajax_boq_import',['site_id'=>1,'rows'=>json_encode($input)]);eq($reply->ok,true,'JSON workbook import without ZIP');eq($reply->data['items'][0]['item_id'],1,'Server resolves master item');eq($reply->data['items'][1]['item_id'],0,'Unknown item retained for review');
eq(endpoint($app,'ajax_boq_import',['site_id'=>1,'rows'=>json_encode([['item_name'=>'Item 1','boq_qty'=>'-2']])])->ok,false,'Invalid uploaded quantity rejected');
eq(endpoint($app,'ajax_boq_import',['site_id'=>1,'rows'=>'null'])->ok,false,'Invalid upload rejected');
eq(endpoint($app,'ajax_boq_import',['rows'=>json_encode($input)])->ok,false,'Site required for upload');
echo 'BOQ Excel transport passed without PHP ZIP: '.(!class_exists('ZipArchive')?'confirmed':'ZIP is present').'; XML: '.(!function_exists('simplexml_load_string')?'absent':'present').".\n";
