<?php
/** Standalone notification tests. HTTP and WordPress APIs are mocked; no network sends. */
define('ABSPATH',__DIR__.'/');define('ARRAY_A','ARRAY_A');define('DAY_IN_SECONDS',86400);
require __DIR__.'/../includes/notifications.php';
$options=['bsm_notification_attachments_schema'=>'1'];$sent=[];$response=['code'=>200,'body'=>'{"success":true}'];$admin=true;
function get_option($k,$d=false){return $GLOBALS['options'][$k]??$d;}
function update_option($k,$v,$autoload=false){$GLOBALS['options'][$k]=$v;return true;}
function delete_option($k){unset($GLOBALS['options'][$k]);}
function wp_salt($x){return 'test-only-unique-auth-salt';}
function wp_json_encode($v){return json_encode($v,JSON_UNESCAPED_UNICODE);}
function esc_url_raw($url,$protocols=[]){return preg_match('~^https?://~',$url)?$url:'';}
function esc_sql($v){return str_replace("'","''",$v);}
function current_user_can($cap){return $GLOBALS['admin'];}
function get_userdata($id){return (object)['display_name'=>'Admin'];}
function wp_remote_post($url,$args){$GLOBALS['sent'][]=[$url,$args];return $GLOBALS['response'];}
function is_wp_error($r){return $r instanceof Error;}
function wp_remote_retrieve_response_code($r){return $r['code'];}
function wp_remote_retrieve_body($r){return $r['body'];}
function get_transient($k){return true;}
$cron_event=false;$cron_schedules=[];$cron_created=0;$cron_cleared=0;
function add_filter($name,$callback){if($name==='cron_schedules')$GLOBALS['cron_schedules']=$callback([]);}
function add_action(...$args){}
function wp_get_scheduled_event($hook){return $GLOBALS['cron_event'];}
function wp_clear_scheduled_hook($hook){$GLOBALS['cron_event']=false;$GLOBALS['cron_cleared']++;}
function wp_schedule_event($time,$schedule,$hook){$GLOBALS['cron_created']++;$GLOBALS['cron_event']=(object)['timestamp'=>$time,'schedule'=>$schedule,'interval'=>$GLOBALS['cron_schedules'][$schedule]['interval']];}
class NotificationDB {
 public $pdo,$insert_id;
 function __construct(){$this->pdo=new PDO('sqlite::memory:');$this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);}
 function prepare($q,...$a){if(count($a)===1&&is_array($a[0]))$a=$a[0];$i=0;return preg_replace_callback('/%[sdf]/',function($m)use(&$i,$a){$v=$a[$i++];return $m[0]==='%s'?$this->pdo->quote((string)$v):(string)(float)$v;},$q);}
 function query($q){return $this->pdo->exec(str_replace('INSERT IGNORE','INSERT OR IGNORE',$q));}
 function get_var($q){if(str_contains($q,'GET_LOCK')||str_contains($q,'RELEASE_LOCK'))return 1;$v=$this->pdo->query($q)->fetchColumn();return $v===false?null:$v;}
 function get_results($q,$mode=null){return $this->pdo->query($q)->fetchAll(PDO::FETCH_ASSOC);}
 function get_row($q,$mode=null){return $this->pdo->query($q)->fetch(PDO::FETCH_ASSOC)?:null;}
 function get_col($q){return $this->pdo->query($q)->fetchAll(PDO::FETCH_COLUMN);}
 function replace($t,$d){$q='INSERT OR REPLACE INTO '.$t.' ('.implode(',',array_keys($d)).') VALUES ('.implode(',',array_fill(0,count($d),'?')).')';return $this->pdo->prepare($q)->execute(array_values($d));}
 function update($t,$d,$w){return $this->pdo->prepare('UPDATE '.$t.' SET '.implode(',',array_map(fn($k)=>$k.'=?',array_keys($d))).' WHERE '.implode(' AND ',array_map(fn($k)=>$k.'=?',array_keys($w))))->execute(array_merge(array_values($d),array_values($w)));}
}
class NotificationHost {
 use BSM_Notifications;
 private $prefix='bsm_';
 function call($method,...$args){return $this->$method(...$args);}
 private function po_item_received_qty($id){global $wpdb;return (float)$wpdb->get_var($wpdb->prepare('SELECT COALESCE(SUM(qty),0) FROM bsm_material_inward_items WHERE purchase_order_item_id=%d',$id));}
}
$wpdb=new NotificationDB;$host=new NotificationHost;
$wpdb->query("CREATE TABLE bsm_notification_queue(id INTEGER PRIMARY KEY,event_key TEXT UNIQUE,notification_type TEXT,site_id INTEGER,group_id TEXT,reference_no TEXT,message TEXT,attachment_url TEXT,status TEXT,attempts INTEGER DEFAULT 0,cycle_attempts INTEGER DEFAULT 0,history TEXT,next_attempt TEXT,created_at TEXT,updated_at TEXT,response TEXT)");
$wpdb->query('CREATE TABLE bsm_notification_state(record_key TEXT PRIMARY KEY,state TEXT)');
$wpdb->query('CREATE TABLE bsm_sites(id INTEGER PRIMARY KEY,site_name TEXT,whatsapp_group_id TEXT,status TEXT)');
$wpdb->query("INSERT INTO bsm_sites VALUES(1,'Source','group-A','active'),(2,'Destination','group-B','active'),(3,'Same group','group-A','active'),(4,'Missing','','active')");
$wpdb->query('CREATE TABLE bsm_users(id INTEGER PRIMARY KEY,wp_user_id INTEGER,name TEXT)');$wpdb->query("INSERT INTO bsm_users VALUES(1,10,'Rahul')");
$wpdb->query('CREATE TABLE bsm_items(id INTEGER PRIMARY KEY,item_name TEXT)');$wpdb->query("INSERT INTO bsm_items VALUES(1,'Binding Wire')");
$wpdb->query('CREATE TABLE bsm_units(id INTEGER PRIMARY KEY,unit_name TEXT)');$wpdb->query("INSERT INTO bsm_units VALUES(1,'Kg')");
$wpdb->query('CREATE TABLE bsm_material_requisitions(id INTEGER PRIMARY KEY,requisition_no TEXT,site_id INTEGER,requisition_date TEXT,required_date TEXT,requested_by INTEGER,approved_by INTEGER,status TEXT,remarks TEXT,approval_remarks TEXT)');
$wpdb->query("INSERT INTO bsm_material_requisitions VALUES(1,'REQ-1',1,'2026-10-31','2026-11-02',1,10,'pending_approval','','')");
$wpdb->query('CREATE TABLE bsm_material_requisition_items(id INTEGER PRIMARY KEY,requisition_id INTEGER,item_id INTEGER,unit_id INTEGER,requested_qty REAL,approved_qty REAL)');$wpdb->query('INSERT INTO bsm_material_requisition_items VALUES(1,1,1,1,32,30)');
foreach(['material_dispatches','site_receipts','material_inwards','purchase_orders','site_transfers','site_transfer_receipts'] as $source)$wpdb->query("CREATE TABLE bsm_$source(id INTEGER PRIMARY KEY,status TEXT)");
$count=0;function check($condition,$label){global $count;if(!$condition)throw new Exception('FAIL: '.$label);$count++;echo "PASS: $label\n";}
check(count($host->call('nt_catalog'))===14,'All 14 notification types registered');
$secret=$host->call('nt_encrypt','secret-pass');check($secret!=='secret-pass'&&$host->call('nt_password',$secret)==='secret-pass','Password encrypted and recovered');check($host->call('nt_password',base64_encode('tampered'))==='','Tampered credentials rejected');
check($host->call('nt_admin')===true,'Admin allowed');$admin=false;check($host->call('nt_admin')===false,'Non-admin denied');$admin=true;
$options['bsm_notification_settings']=['enabled'=>true,'username'=>'sender','password'=>$secret,'office_group'=>'office','types'=>array_fill_keys(array_keys($host->call('nt_catalog')),true)];
$host->call('nt_queue','material_dispatched',[1,2,3],'DIS-1','test message','event-1');check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue')===2,'Source/destination routing deduplicates same group');
$host->call('nt_queue','material_dispatched',[1,2,3],'DIS-1','test message','event-1');check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue')===2,'Same saved event never queues twice');
$host->call('nt_queue','site_received',[4],'R-1','test','missing');check($wpdb->get_var("SELECT status FROM bsm_notification_queue WHERE reference_no='R-1'")==='skipped','Missing group skipped, never rerouted');
$options['bsm_notification_settings']['enabled']=false;$before=$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue');$host->call('nt_queue','site_received',[1],'R-2','test','disabled');check($before===$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue'),'Master switch prevents queueing');$options['bsm_notification_settings']['enabled']=true;
$options['bsm_notification_settings']['types']['site_received']=false;$host->call('nt_queue','site_received',[1],'R-3','test','disabled-type');check($before===$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue'),'Individual checkbox prevents queueing');
$parts=$host->call('nt_parts',implode("\n",array_fill(0,300,'A complete item line')));check(count($parts)>1&&max(array_map('strlen',$parts))<=2800,'Long summaries split into bounded messages');check(!str_contains($parts[0],"\nA complete item lin\n"),'Normal item lines remain whole');
$wpdb->query('DELETE FROM bsm_notification_queue');
$host->call('nt_queue','test',[0],'TEST','connection message','test1',true);$host->call('nt_worker');check(count($sent)===1,'One test queued and sent via mocked HTTP');$payload=json_decode($sent[0][1]['body'],true);check($payload===['recipientIds'=>['office'],'message'=>['connection message']],'Exact MessageAutoSender group payload');check($sent[0][1]['headers']['Authorization']==='Basic '.base64_encode('sender:secret-pass'),'Basic authentication format');check($wpdb->get_var('SELECT status FROM bsm_notification_queue')==='accepted','HTTP success recorded as accepted');$host->call('nt_worker');check(count($sent)===1,'Accepted message is not resent');
$response=new Error('timeout');$host->call('nt_queue','test',[0],'TIMEOUT','test','timeout',true);$host->call('nt_worker');check($wpdb->get_var("SELECT status FROM bsm_notification_queue WHERE reference_no='TIMEOUT'")==='review_required','Ambiguous timeout held for review, no automatic duplicate');
$response=['code'=>200,'body'=>'{"success":false,"error":"invalid group"}'];$host->call('nt_queue','test',[0],'BAD','test','bad',true);$host->call('nt_worker');check($wpdb->get_var("SELECT status FROM bsm_notification_queue WHERE reference_no='BAD'")==='failed','Provider business error inside HTTP 200 is not accepted');
$response=['code'=>429,'body'=>'retry later'];$host->call('nt_queue','test',[0],'RETRY','test','retry',true);$host->call('nt_worker');check($wpdb->get_var("SELECT status FROM bsm_notification_queue WHERE reference_no='RETRY'")==='queued','Rate limiting schedules controlled retry');
for($i=0;$i<2;$i++){$wpdb->query("UPDATE bsm_notification_queue SET next_attempt='2000-01-01' WHERE reference_no='RETRY'");$host->call('nt_worker');}
check($wpdb->get_var("SELECT status FROM bsm_notification_queue WHERE reference_no='RETRY'")==='failed','Automatic retries stop after three attempts');check(count(json_decode($wpdb->get_var("SELECT history FROM bsm_notification_queue WHERE reference_no='RETRY'"),true))===3,'Attempt history retained');
$response=['code'=>401,'body'=>'unauthorized'];$host->call('nt_queue','test',[0],'AUTH','test','auth',true);$host->call('nt_worker');check((bool)get_option('bsm_notifications_blocked'),'Invalid credentials pause sending');$n=count($sent);$host->call('nt_queue','test',[0],'BLOCKED','test','blocked',true);$host->call('nt_worker');check(count($sent)===$n,'Paused connection does not hammer API');delete_option('bsm_notifications_blocked');
$wpdb->query('DELETE FROM bsm_notification_queue');$host->call('nt_scan',true);check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue')===0,'Initial baseline does not send historic records');
$wpdb->query("UPDATE bsm_material_requisitions SET status='approved' WHERE id=1");$host->call('nt_scan');check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue')===1,'Pending-to-approved transition queues one event');$host->call('nt_scan');check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue')===1,'Repeated observer scan does not repeat event');
$record=$wpdb->get_row('SELECT * FROM bsm_material_requisitions WHERE id=1');$message=$host->call('nt_event_message','requisition_approved','material_requisitions',$record);check(str_contains($message['message'],'Binding Wire — 32 Kg | Approved: 30 Kg'),'Approval compares requested and approved quantities');check($message['sites']===[1],'Requisition routed only to its own site');
$wpdb->query("UPDATE bsm_material_requisitions SET status='cancelled' WHERE id=1");$queued=$wpdb->get_row('SELECT * FROM bsm_notification_queue LIMIT 1');check(!$host->call('nt_still_relevant',$queued),'Cancelled source is not sent from queue');

// Daily summaries must separate office employees from contractor employees.
$wpdb->query('CREATE TABLE bsm_contractor_employees(id INTEGER PRIMARY KEY,employee_name TEXT)');$wpdb->query("INSERT INTO bsm_contractor_employees VALUES(1,'Contract Worker')");
$wpdb->query('CREATE TABLE bsm_labour_contractors(id INTEGER PRIMARY KEY,contractor_name TEXT)');$wpdb->query("INSERT INTO bsm_labour_contractors VALUES(1,'ABC Contractor')");
$wpdb->query('CREATE TABLE bsm_time_entries(id INTEGER PRIMARY KEY,work_date TEXT,employee_id INTEGER,worker_id INTEGER,contractor_id INTEGER,site_id INTEGER,from_time TEXT,to_time TEXT,minutes INTEGER,activity TEXT)');
$today=(new DateTimeImmutable('now',new DateTimeZone('Asia/Kolkata')))->format('Y-m-d');
$wpdb->query($wpdb->prepare("INSERT INTO bsm_time_entries VALUES(1,%s,1,0,0,1,'09:00:00','11:00:00',120,'Site inspection'),(2,%s,0,1,1,1,'09:00:00','13:00:00',240,'Plumbing')",$today,$today));
$office=$host->call('nt_summary','office_timesheet',0,$today);check(str_contains($office,'Rahul')&&!str_contains($office,'Contract Worker'),'Office timesheet excludes contractor employees');check(str_contains($office,'2 hours'),'Office hours total is correct');
$site=$host->call('nt_summary','site_timesheet',1,$today);check(str_contains($site,'Rahul')&&str_contains($site,'Contract Worker'),'Site timesheet includes employees assigned to the site');check(str_contains($site,'Total: 2 hours')&&str_contains($site,'Total: 4 hours')&&!str_contains($site,'Total Recorded Hours:'),'Site timesheet shows per-employee totals only');check($host->call('nt_summary','site_timesheet',2,$today)==='','Empty site summaries are skipped');
$wpdb->query('CREATE TABLE bsm_labour_attendance(id INTEGER PRIMARY KEY,site_id INTEGER,attendance_date TEXT,contractor_id INTEGER,no_of_labour INTEGER,remarks TEXT,updated_by INTEGER,created_by INTEGER)');
$hours=wp_json_encode(['employee_hours'=>[['employee_name'=>'Worker A','working_hours'=>5],['employee_name'=>'Worker B','working_hours'=>9.2]]]);$wpdb->query($wpdb->prepare('INSERT INTO bsm_labour_attendance VALUES(1,1,%s,1,2,%s,10,10)',$today,$hours));
$attendance=$host->call('nt_summary','labour_attendance',1,$today);check(str_contains($attendance,'1.ABC Contractor')&&str_contains($attendance,'* Worker A — 5 hours')&&str_contains($attendance,'* Worker B — 9.2 hours')&&!str_contains($attendance,'Updated By:')&&!str_contains($attendance,'Total Working Hours:'),'Labour summary uses employee names and Working Hours');
$options['bsm_notification_settings']['days']=[(int)(new DateTimeImmutable('now',new DateTimeZone('Asia/Kolkata')))->format('N')];$options['bsm_notification_settings']['types']=array_fill_keys(array_keys($host->call('nt_catalog')),false);$options['bsm_notification_settings']['types']['office_timesheet']=true;$options['bsm_notification_settings']['times']=['office_timesheet'=>['00:00','00:01']];
$wpdb->query('DELETE FROM bsm_notification_queue');$host->call('nt_scheduled');check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue')===1,'Missed schedules catch up only the latest due slot');$host->call('nt_scheduled');check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue')===1,'Scheduled summary sent only once per IST slot');
$wpdb->query("DELETE FROM bsm_notification_state WHERE record_key LIKE 'schedule:%'");$options['bsm_notification_settings']['days']=[];$host->call('nt_scheduled');check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue')===1,'Working days control scheduled messages');
$unicode=$host->call('nt_parts',str_repeat('क',1100));check(count($unicode)>1&&array_reduce($unicode,fn($ok,$part)=>$ok&&preg_match('//u',$part),true),'Long Unicode activities split without corrupting characters');

// Exercise every material/PO builder against realistic partial-receipt fixtures.
foreach(['site_transfers'=>['to_site_id INTEGER','transfer_date TEXT'],'material_dispatches'=>['dispatch_no TEXT','site_id INTEGER','from_site_id INTEGER','warehouse_id INTEGER','requisition_id INTEGER','dispatch_date TEXT','created_by INTEGER','vehicle_no TEXT','driver_name TEXT','driver_mobile TEXT'], 'site_receipts'=>['receipt_no TEXT','site_id INTEGER','dispatch_id INTEGER','receipt_date TEXT','received_by INTEGER','remarks TEXT'], 'purchase_orders'=>['po_no TEXT','po_date TEXT','vendor_id INTEGER','warehouse_id INTEGER','created_by INTEGER','approved_by INTEGER','approved_at TEXT'], 'material_inwards'=>['purchase_order_id INTEGER','inward_no TEXT','inward_date TEXT','warehouse_id INTEGER','created_by INTEGER']] as $table=>$columns)foreach($columns as $column)$wpdb->query('ALTER TABLE bsm_'.$table.' ADD '.$column);
$wpdb->query('CREATE TABLE bsm_material_dispatch_items(id INTEGER PRIMARY KEY,dispatch_id INTEGER,requisition_item_id INTEGER,item_id INTEGER,unit_id INTEGER,qty REAL)');
$wpdb->query('CREATE TABLE bsm_site_receipt_items(id INTEGER PRIMARY KEY,receipt_id INTEGER,dispatch_item_id INTEGER,item_id INTEGER,unit_id INTEGER,received_qty REAL,damaged_qty REAL)');
$wpdb->query('CREATE TABLE bsm_warehouses(id INTEGER PRIMARY KEY,warehouse_name TEXT)');$wpdb->query("INSERT INTO bsm_warehouses VALUES(1,'Main Warehouse')");
$wpdb->query('CREATE TABLE bsm_vendors(id INTEGER PRIMARY KEY,vendor_name TEXT)');$wpdb->query("INSERT INTO bsm_vendors VALUES(1,'ABC Traders')");
$wpdb->query('CREATE TABLE bsm_purchase_order_items(id INTEGER PRIMARY KEY,purchase_order_id INTEGER,item_id INTEGER,unit_id INTEGER,po_qty REAL,rate REAL)');
$wpdb->query('CREATE TABLE bsm_material_inward_items(id INTEGER PRIMARY KEY,inward_id INTEGER,purchase_order_item_id INTEGER,item_id INTEGER,unit_id INTEGER,qty REAL)');
$wpdb->query("INSERT INTO bsm_material_dispatches(id,status,dispatch_no,site_id,from_site_id,warehouse_id,requisition_id,dispatch_date,created_by) VALUES(1,'dispatched','DIS-1',2,1,0,1,'2026-10-31',10)");$wpdb->query('INSERT INTO bsm_material_dispatch_items VALUES(1,1,1,1,1,20)');
$wpdb->query("INSERT INTO bsm_site_receipts(id,status,receipt_no,site_id,dispatch_id,receipt_date,received_by,remarks) VALUES(1,'received','R-1',2,1,'2026-10-31',1,'Balance awaited')");$wpdb->query('INSERT INTO bsm_site_receipt_items VALUES(1,1,1,1,1,18,0)');
check($host->call('nt_dispatch_pending',1)===2.0,'Partial site receipt leaves only remaining quantity');
$dispatch=$wpdb->get_row('SELECT * FROM bsm_material_dispatches WHERE id=1');$event=$host->call('nt_event_message','material_dispatched','material_dispatches',$dispatch);check($event['sites']===[2,1]&&str_contains($event['message'],'Binding Wire — 20 Kg'),'Site-source dispatch routes both sites and formats quantities');check(!str_contains($event['message'],'Driver Mobile:'),'Blank optional driver fields omitted');
$receipt=$wpdb->get_row('SELECT * FROM bsm_site_receipts WHERE id=1');$event=$host->call('nt_event_message','site_received','site_receipts',$receipt);check($event['sites']===[2,1]&&str_contains($event['message'],'18 Kg | 2 Kg'),'Site receipt shows received now and pending, routed both sites');check(str_contains($event['message'],"\n\n1.Item | Received Now | Pending Receipt\n* Binding Wire | 18 Kg | 2 Kg\nReceived By: "),'Site receipt matches requested item header and asterisk format');
$pending=$host->call('nt_summary','pending_receipt',2,'2026-10-31');check(str_contains($pending,'2 Kg pending receipt'),'Pending site receipt excludes already received quantity');
$wpdb->query("UPDATE bsm_material_requisitions SET status='approved' WHERE id=1");$pending=$host->call('nt_summary','pending_dispatch',1,'2026-10-31');check(str_contains($pending,'10 Kg'),'Pending dispatch uses approved minus dispatched quantity');$wpdb->query("UPDATE bsm_material_requisitions SET status='pending_approval' WHERE id=1");check(str_contains($host->call('nt_summary','pending_requisition',1,'2026-10-31'),'32 Kg'),'Pending approval includes requested quantities');
$wpdb->query("INSERT INTO bsm_purchase_orders(id,status,po_no,po_date,vendor_id,warehouse_id,created_by,approved_by,approved_at) VALUES(1,'approved','PO-1','2026-10-31',1,1,10,10,'2026-10-31 10:00:00')");$wpdb->query('INSERT INTO bsm_purchase_order_items VALUES(1,1,1,1,50,100)');$wpdb->query("INSERT INTO bsm_material_inwards(id,status,purchase_order_id,inward_no,inward_date,warehouse_id,created_by) VALUES(1,'pending_approval',1,'MIN-1','2026-10-31',1,10)");$wpdb->query('INSERT INTO bsm_material_inward_items VALUES(1,1,1,1,1,40)');
$po=$wpdb->get_row('SELECT * FROM bsm_purchase_orders WHERE id=1');$event=$host->call('nt_event_message','po_approved','purchase_orders',$po);check($event['sites']===[0]&&str_contains($event['message'],'5,000.00'),'Approved PO message routes Office with total amount');
$inward=$wpdb->get_row('SELECT * FROM bsm_material_inwards WHERE id=1');$event=$host->call('nt_event_message','po_received','material_inwards',$inward);check(str_contains($event['message'],'40 Kg | 10 Kg')&&str_contains($event['message'],'Partially Received'),'PO receipt handles partial quantities');
check(str_contains($host->call('nt_summary','pending_po',0,'2026-10-31'),'10 Kg pending'),'Pending PO reminder excludes received quantity');
$wpdb->query("UPDATE bsm_purchase_orders SET status='pending_approval' WHERE id=1");check(str_contains($host->call('nt_summary','pending_po_approval',0,'2026-10-31'),'ABC Traders'),'Pending PO approval includes supplier');check($host->call('nt_summary','pending_po',0,'2026-10-31')==='','Unapproved PO excluded from pending receipt reminder');

$wpdb->query('CREATE TABLE bsm_holidays(id INTEGER PRIMARY KEY,holiday_date TEXT,employee_ids TEXT)');
$today=(new DateTimeImmutable('now',new DateTimeZone('Asia/Kolkata')))->format('Y-m-d');
$options['bsm_notification_settings']['days']=[(int)(new DateTimeImmutable('now',new DateTimeZone('Asia/Kolkata')))->format('N')];
$options['bsm_notification_settings']['include_holidays']=false;
$wpdb->query($wpdb->prepare("INSERT INTO bsm_holidays VALUES(1,%s,'[]')",$today));
$wpdb->query('DELETE FROM bsm_notification_queue');$wpdb->query("DELETE FROM bsm_notification_state WHERE record_key LIKE 'schedule:%'");
$host->call('nt_scheduled');check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue')===0,'Unchecked Holidays skips global holidays');
$options['bsm_notification_settings']['include_holidays']=true;
$host->call('nt_scheduled');check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue')===1,'Checked Holidays allows scheduled summaries');
$wpdb->query('DELETE FROM bsm_notification_queue');$wpdb->query("DELETE FROM bsm_notification_state WHERE record_key LIKE 'schedule:%'");
$options['bsm_notification_settings']['include_holidays']=false;
$wpdb->query("UPDATE bsm_holidays SET employee_ids='[1]'");
$host->call('nt_scheduled');check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue')===1,'Individual employee holidays do not stop all summaries');


$host->notifications_boot();$options['bsm_notifications_schema']='1';
$cron_event=(object)['timestamp'=>time()+60,'schedule'=>'bsm_minute','interval'=>60];
$host->notifications_upgrade();
check($cron_event->interval===300&&$cron_event->schedule==='bsm_five_minutes','Existing one-minute cron migrates to five minutes');
check($cron_cleared===1&&$cron_created===1,'Migration replaces old event exactly once');
$host->notifications_upgrade();check($cron_created===1,'Repeated page loads do not duplicate cron events');
$wpdb->query('DELETE FROM bsm_notification_queue');$wpdb->query("DELETE FROM bsm_notification_state WHERE record_key LIKE 'schedule:%'");
$host->call('nt_scheduled');
$before=(int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue');
$wpdb->query("DELETE FROM bsm_notification_state WHERE record_key LIKE 'schedule:%'");
$host->call('nt_scheduled');
check($before===1&&(int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue')===1,'Unique event key prevents duplicates even if schedule marker is missing');


$wpdb->query("INSERT INTO bsm_material_requisitions VALUES(2,'REQ-2',1,'2026-10-31','2026-11-03',1,10,'pending_approval','','')");
$wpdb->query('INSERT INTO bsm_material_requisition_items VALUES(2,2,1,1,100,0)');
$message=$host->call('nt_summary','pending_requisition',1,$today);
check(str_contains($message,"\n\n1.REQ-1 | ")&&str_contains($message,"\n\n2.REQ-2 | "),'Pending requisitions numbered consecutively');
$wpdb->query("UPDATE bsm_material_requisitions SET status='approved'");
check($host->call('nt_summary','pending_requisition',1,$today)==='','No pending requisitions produces no message');
$options['bsm_notification_settings']['types']=['pending_requisition'=>true];
$options['bsm_notification_settings']['times']=['pending_requisition'=>['00:00']];
$wpdb->query('DELETE FROM bsm_notification_queue');$wpdb->query("DELETE FROM bsm_notification_state WHERE record_key LIKE 'schedule:%'");
$host->call('nt_scheduled');
check((int)$wpdb->get_var('SELECT COUNT(*) FROM bsm_notification_queue')===0,'Empty pending approval report is never queued');


$dispatch['dispatch_photo_url']='https://example.com/dispatch.jpg';
$event=$host->call('nt_event_message','material_dispatched','material_dispatches',$dispatch);
check($event['attachment_url']===$dispatch['dispatch_photo_url'],'Dispatch event includes uploaded photo');
$options['bsm_notification_settings']['types']=['material_dispatched'=>true];
$options['bsm_notification_settings']['enabled']=true;
$options['bsm_notification_settings']['username']='test';
$options['bsm_notification_settings']['password']=$host->call('nt_encrypt','test');
delete_option('bsm_notifications_blocked');
$response=['code'=>200,'body'=>'{"success":true}'];
$wpdb->query('DELETE FROM bsm_notification_queue');
$host->call('nt_queue','material_dispatched',[2],'DIS-1',str_repeat("Item detail\n",400),'photo-test',false,$event['attachment_url']);
check((int)$wpdb->get_var("SELECT COUNT(*) FROM bsm_notification_queue WHERE attachment_url<>''")===1,'Split messages attach photo only once');
$host->call('nt_worker');
$photoPayload=json_decode($sent[count($sent)-count($host->call('nt_parts',str_repeat("Item detail\n",400)))][1]['body'],true);
check(($photoPayload['filePathUrl']??[])===[$dispatch['dispatch_photo_url']],'Provider payload sends photo with message');
$sentBefore=count($sent);$host->call('nt_worker');
check(count($sent)===$sentBefore,'Accepted photo message is not sent twice');
$dispatch['dispatch_photo_url']='';
$event=$host->call('nt_event_message','material_dispatched','material_dispatches',$dispatch);
check($event['attachment_url']==='','Dispatch without photo remains text only');


$wpdb->query("INSERT INTO bsm_material_dispatches(id,status,dispatch_no,site_id,dispatch_date) VALUES(2,'dispatched','DIS-2',2,'2026-10-31')");
$wpdb->query('INSERT INTO bsm_material_dispatch_items VALUES(2,2,1,1,1,25)');
$receiptSummary=$host->call('nt_summary','pending_receipt',2,'2026-10-31');
check(str_contains($receiptSummary,"\n\n1.DIS-1 | 31/10/2026")&&str_contains($receiptSummary,"\n\n2.DIS-2 | 31/10/2026"),'Pending receipt dispatches numbered consecutively');
check(str_contains($receiptSummary,"\n* Binding Wire — 25 Kg pending receipt"),'Pending receipt item lines use requested asterisk format');
$wpdb->query('UPDATE bsm_site_receipt_items SET received_qty=20 WHERE id=1');
$wpdb->query('INSERT INTO bsm_site_receipt_items VALUES(2,1,2,1,1,25,0)');
check($host->call('nt_summary','pending_receipt',2,'2026-10-31')==='','Fully received dispatches produce no pending receipt message');


$options['bsm_notification_settings']['types']=['labour_attendance'=>true,'site_timesheet'=>true];
$options['bsm_notification_settings']['times']=['labour_attendance'=>['00:00'],'site_timesheet'=>['00:00']];
$options['bsm_notification_settings']['include_holidays']=true;
$wpdb->query('DELETE FROM bsm_notification_queue');$wpdb->query("DELETE FROM bsm_notification_state WHERE record_key LIKE 'schedule:%'");
$wpdb->query('UPDATE bsm_labour_attendance SET site_id=2');
$wpdb->query('UPDATE bsm_time_entries SET site_id=2');
foreach(['labour_attendance','site_timesheet'] as $type)$wpdb->replace('bsm_notification_state',['record_key'=>'schedule:'.$type.':'.$today.':00:00:1','state'=>'done']);
$host->call('nt_scheduled');
check((int)$wpdb->get_var("SELECT COUNT(*) FROM bsm_notification_queue WHERE site_id=1")===0,'Empty daily reports send no message');
$wpdb->query('UPDATE bsm_labour_attendance SET site_id=1');
$wpdb->query('UPDATE bsm_time_entries SET site_id=1');
$host->call('nt_scheduled');
check((int)$wpdb->get_var("SELECT COUNT(*) FROM bsm_notification_queue WHERE site_id=1 AND notification_type='labour_attendance'")===1,'Labour attendance recovers an empty completed slot when data is added');
check((int)$wpdb->get_var("SELECT COUNT(*) FROM bsm_notification_queue WHERE site_id=1 AND notification_type='site_timesheet'")===1,'Site timesheet recovers an empty completed slot when data is added');
$host->call('nt_scheduled');
check((int)$wpdb->get_var("SELECT COUNT(*) FROM bsm_notification_queue WHERE site_id=1")===2,'Repeated daily checks do not queue duplicate summaries');


$siteMessage=$host->call('nt_summary','site_timesheet',1,$today);
check(str_contains($siteMessage,'09:00 AM–11:00 AM | Site inspection')&&!str_contains($siteMessage,'Site inspection | 2 hours'),'Site timesheet activity lines omit individual hours');
check(str_contains($siteMessage,"\n\n1.Rahul\n* 09:00 AM–11:00 AM | Site inspection\nTotal: 2 hours")&&str_contains($siteMessage,'2.Contract Worker')&&!str_contains($siteMessage,'Total Recorded Hours:'),'Site timesheet numbers employees and uses asterisk activities with individual totals');
$officeMessage=$host->call('nt_summary','office_timesheet',0,$today);
check(str_contains($officeMessage,"* 09:00 AM–11:00 AM | Site inspection\nTotal: 2 hours")&&!str_contains($officeMessage,' | Office | '),'Office timesheet shows only time and activity with employee total');


$wpdb->query('DELETE FROM bsm_notification_queue');
check($host->call('nt_last_sent')==='','No successful sends has no last send time');
$wpdb->query("INSERT INTO bsm_notification_queue(event_key,status,updated_at) VALUES('accepted-time','accepted','2026-10-31 12:10:00'),('failed-time','failed','2026-10-31 13:00:00')");
check($host->call('nt_last_sent')==='31/10/2026 5:40PM','Last accepted send uses IST and ignores later failures');

$wpdb->query("INSERT INTO bsm_labour_contractors VALUES(2,'Tutan Deb')");
$moreHours=wp_json_encode(['employee_hours'=>[['employee_name'=>'Atul Roy','working_hours'=>3.3]]]);
$wpdb->query($wpdb->prepare('INSERT INTO bsm_labour_attendance VALUES(2,1,%s,2,1,%s,10,10)',$today,$moreHours));
$attendance=$host->call('nt_summary','labour_attendance',1,$today);
check(str_contains($attendance,"\n\n1.ABC Contractor\n* Worker A — 5 hours")&&str_contains($attendance,"\n\n2.Tutan Deb\n* Atul Roy — 3.3 hours"),'Attendance numbers contractors and preserves decimal employee hours');

$wpdb->query("INSERT INTO bsm_users VALUES(2,20,'Priya')");
$wpdb->query($wpdb->prepare("INSERT INTO bsm_time_entries VALUES(3,%s,2,0,0,0,'10:00:00','11:00:00',60,'Invoice checking')",$today));
$office=$host->call('nt_summary','office_timesheet',0,$today);
check(str_contains($office,"\n\n1.Rahul\n* ")&&str_contains($office,"\n\n2.Priya\n* 10:00 AM–11:00 AM | Invoice checking"),'Office summary numbers multiple employees and uses asterisk activity lines');
check(str_ends_with($office,'Total Employees Reported: 2')&&!str_contains($office,'Total Recorded Hours:'),'Office summary ends with employee count only');
echo "\n$count notification checks passed. No network calls made.\n";
