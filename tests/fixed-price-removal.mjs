import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
const root=process.argv[2]||path.resolve(import.meta.dirname,'..');
const js=fs.readFileSync(path.join(root,'assets/app.js'),'utf8');
const php=fs.readFileSync(path.join(root,'bizskill-site-management.php'),'utf8');
let checks=0;function check(ok,label){assert.ok(ok,label);checks++;console.log('PASS: '+label);}
function source(name){const re=new RegExp('(?:async )?function '+name+'\\(');const m=re.exec(js);assert.ok(m,name);const lineEnd=js.indexOf('\n',m.index);if(js.slice(m.index,lineEnd).endsWith('}'))return js.slice(m.index,lineEnd);const end=js.indexOf('\n}',m.index);return js.slice(m.index,end+2);}
function compile(name,env){return new Function(...Object.keys(env),source(name)+';return '+name+';')(...Object.values(env));}
check(!/Fixed Price|fixed_price|item_price|po-direct-fixed-price|master_price/.test(js),'No retired benchmark UI, payload or autofill remains');
check(!js.includes("F('opening_rate','Price'")&&!js.includes("C('opening_rate','Price'"),'Item Master and quick-add no longer show Price');
check(js.includes("F('opening_rate','Opening Rate'"),'Site opening-stock valuation input remains');
check(php.includes('opening_qty,opening_rate')&&php.includes("$item['opening_rate']"),'Existing opening-stock valuation is preserved');
const direct=compile('directPoItemRow',{poItemOptions:()=>'<option>Binding Wire</option>'})();
check((direct.match(/<td/g)||[]).length===6&&!direct.includes('fixed'),'Direct PO row has six aligned cells without benchmark');
const details=compile('purchaseItemDetailsHtml',{labelFor:()=> 'Binding Wire',esc:String})([{item_id:1,qty:25,unit_id:1,rate:150,taxable_amount:3750,total_amount:3750,item_price:99999}]);
check((details.match(/<th[ >]/g)||[]).length===(details.match(/<td[ >]/g)||[]).length&&!details.includes('99,999'),'Purchase detail headers align and ignore legacy benchmark');
const row={item_id:1,item_name:'Binding Wire',unit_name:'Kg',po_qty:25,rate:150,received_qty:0,pending_qty:25,fixed_price:99999};
let output='';
const $=()=>({val:()=>'',html:v=>{output=v;return this;}});
const env={$,ajax:async()=>({success:true,data:{rows:[row],page:1}}),esc:String,dateFmt:String,dateSql:String,poItemQuotes:()=>Array(8).fill(''),App:{page:1}};
await compile('loadPurchaseOrderItemsMaster',env)();
check((output.match(/<th[ >]/g)||[]).length===20&&(output.match(/<td[ >]/g)||[]).length===20,'PO Items Master headers align after column removal');
check(output.includes('3,750.00')&&!output.includes('99,999'),'PO Items Master amount is quantity times actual rate');
await compile('loadPurchaseItemsRegister',env)();
check((output.match(/<th[ >]/g)||[]).length===(output.match(/<td[ >]/g)||[]).length,'Purchase Items Master header and row counts align');
const helper=compile('poLowestQuotation',{});
check(Number(helper([{vendor_id:'1',rate:220},{vendor_id:'1',rate:150},{vendor_id:'2',rate:1}],'1').rate)===150,'Repeated supplier still uses its lowest quoted rate');
check(!/opening_rate|fixed_price/.test(source('refreshDirectPoItemRow')),'Direct PO no longer reads Item Master pricing');
check(!php.includes("'fixed_price'=>")&&!php.includes("'item_price'=>"),'New PO and receipt writes omit retired price snapshots');

const exportLine=js.split('\n').find(line=>line.includes("on('click','#po_items_export'"));
let handler,exported;
new Function('$','document','loadPurchaseOrderItemsMaster','downloadExcelFile','poItemQuotes','dateFmt',exportLine)(()=>({on:(event,selector,fn)=>handler=fn}),{},async()=>[row],(name,headers,rows)=>exported={headers,rows},()=>Array(8).fill(''),String);
await handler();
check(exported.headers.length===exported.rows[0].length&&exported.headers.length===20,'PO Excel columns align after benchmark removal');
check(!exported.rows[0].includes(99999)&&exported.rows[0].includes(3750),'PO Excel preserves actual rate amount and excludes legacy benchmark');
console.log(checks+' fixed-price removal checks passed');
