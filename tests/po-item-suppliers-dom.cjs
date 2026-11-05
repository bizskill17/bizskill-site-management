const fs=require('node:fs'),assert=require('node:assert/strict'),{JSDOM}=require('jsdom');
const w=new JSDOM('<div id="bsm-content"></div>',{runScripts:'outside-only'}).window;
w.eval(fs.readFileSync(require.resolve('jquery'),'utf8'));
const src=fs.readFileSync('assets/app.js','utf8'),$=w.$;
w.App={options:{vendors:[{id:1,label:'ABC Suppliers'},{id:2,label:'XYZ Traders'}],items:[{id:1,label:'Cement',unit_id:1},{id:2,label:'Binding Wire',unit_id:2}],warehouses:[{id:1,label:'Main'}],units:[{id:1,label:'Bag'},{id:2,label:'Kg'}]}};
w.BSM={today:'2026-11-05',currentUser:{name:'Admin'}};w.esc=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));w.labelFor=(k,id)=>w.App.options[k]?.find(x=>String(x.id)===String(id))?.label||'';w.setTitle=()=>{};w.bsmEnhanceSearchableSelects=()=>{};w.bsmRefreshSearchableSelect=()=>{};let error='',payload;w.showToast=(m,bad)=>{if(bad)error=m;};w.openView=async()=>{};w.ajax=async(action,data)=>{payload={action,data};return {success:true,data:{message:'2 POs created'}};};
w.eval(src.slice(src.indexOf('function poVendorOptions('),src.indexOf('async function renderPendingPoItems(')));
w.eval(src.split('\n').find(l=>l.startsWith('function poLowestQuotation(')));
w.eval(src.slice(src.indexOf('function directPoQuotations('),src.indexOf("$(document).on('input','#po_receipt_search'")));
(async()=>{
 await w.renderPurchaseOrderForm();assert.equal($('.po-header-quotes').length,0);$('.po-direct-add').trigger('click');assert.equal($('.po-direct-row').length,2);
 const first=$('.po-direct-row').eq(0),second=$('.po-direct-row').eq(1);
 function quote(row,n,id,rate){row.find(`.po-direct-quote-supplier[data-slot=${n}]`).val(id).trigger('change');row.find(`.po-direct-quote-rate[data-slot=${n}]`).val(rate).trigger('input');}
 first.find('.po-direct-item').val('1').trigger('change');first.find('.po-direct-qty').val(100).trigger('input');quote(first,1,'1',350);quote(first,2,'2',340);first.find('.po-direct-vendor').val('2').trigger('change');
 second.find('.po-direct-item').val('2').trigger('change');second.find('.po-direct-qty').val(50).trigger('input');quote(second,1,'1',65);second.find('.po-direct-vendor').val('1').trigger('change');
 assert.equal(first.find('.po-direct-rate').val(),'340');assert.equal(second.find('.po-direct-rate').val(),'65');assert.equal(first.find('.is-lowest').attr('data-slot'),'2');assert.equal($('#po_direct_preview tbody tr').length,2);assert.equal($('#po_direct_save').text(),'Create 2 POs');
 assert.match($('#po_direct_preview').text(),/34,000.00/);assert.match($('#po_direct_preview').text(),/3,250.00/);
 first.find('.po-direct-vendor').val('1').trigger('change');assert.equal(first.find('.po-direct-rate').val(),'350');assert.equal(second.find('.po-direct-rate').val(),'65');assert.equal(first.find('.po-selection-reason').prop('hidden'),false);assert.equal($('#po_direct_preview tbody tr').length,1);
 $('#po_direct_warehouse').val('1');$('#po_direct_save').trigger('click');await new Promise(r=>setTimeout(r,0));assert.match(error,/reason/);assert.equal(payload,undefined);
 first.find('.po-direct-reason').val('Delivery availability');$('#po_direct_save').trigger('click');await new Promise(r=>setTimeout(r,0));assert.equal(payload.action,'bsm_purchase_order_manual_save');const lines=JSON.parse(payload.data.items);assert.equal(lines[0].vendor_id,'1');assert.equal(lines[1].quotations.length,1);assert.equal(lines[0].selection_reason,'Delivery availability');assert.equal($('#po_direct_save').prop('disabled'),false);
 first.find('.po-direct-remove').trigger('click');assert.equal($('.po-direct-row').length,1);assert.equal($('.po-direct-rate').val(),'65');$('.po-direct-remove').trigger('click');assert.equal($('.po-direct-row').length,1);assert.equal($('.po-direct-item').val(),'');assert.equal($('#po_direct_preview tbody tr').length,0);
 console.log('PO DOM: 25 checks passed for item isolation, lowest quotes, supplier preview, validation, payload and removal');
})().catch(e=>{console.error(e);process.exitCode=1;});
