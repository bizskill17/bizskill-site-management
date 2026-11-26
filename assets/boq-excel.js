/* BOQ workbook IO. Bundled JSZip keeps this independent of PHP ZIP/XML extensions. */
(function(global){
'use strict';
const NS='http://schemas.openxmlformats.org/spreadsheetml/2006/main',REL='http://schemas.openxmlformats.org/officeDocument/2006/relationships';
const xml=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&apos;'}[c]));
const declaration='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
function zipClass(){if(!global.JSZip)throw Error('Excel tools could not load. Refresh the page and try again.');return global.JSZip;}
function doc(text){const d=new DOMParser().parseFromString(text,'application/xml');if(d.getElementsByTagName('parsererror').length)throw Error('The Excel workbook contains invalid XML.');return d;}
const nodes=(d,tag)=>Array.from(d.getElementsByTagNameNS('*',tag));
async function template(items=[]){
 const zip=new (zipClass())();
 zip.file('[Content_Types].xml',declaration+'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
 zip.file('_rels/.rels',declaration+'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="'+REL+'/officeDocument" Target="xl/workbook.xml"/></Relationships>');
 zip.file('xl/workbook.xml',declaration+'<workbook xmlns="'+NS+'" xmlns:r="'+REL+'"><sheets><sheet name="BOQ Upload" sheetId="1" r:id="rId1"/><sheet name="Item List" sheetId="2" state="hidden" r:id="rId2"/></sheets><definedNames><definedName name="BOQItemNames">&apos;Item List&apos;!$A$2:$A$'+Math.max(2,items.length+1)+'</definedName></definedNames></workbook>');
 zip.file('xl/_rels/workbook.xml.rels',declaration+'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="'+REL+'/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="'+REL+'/worksheet" Target="worksheets/sheet2.xml"/></Relationships>');
 zip.file('xl/worksheets/sheet1.xml',declaration+'<worksheet xmlns="'+NS+'"><cols><col min="1" max="1" width="48" customWidth="1"/><col min="2" max="2" width="18" customWidth="1"/></cols><sheetData><row r="1"><c r="A1" t="inlineStr"><is><t>Item</t></is></c><c r="B1" t="inlineStr"><is><t>BOQ Qty</t></is></c></row></sheetData><dataValidations count="2"><dataValidation type="list" allowBlank="1" showErrorMessage="0" sqref="A2:A1001"><formula1>BOQItemNames</formula1></dataValidation><dataValidation type="decimal" operator="greaterThan" allowBlank="1" showErrorMessage="1" error="Enter a quantity greater than zero" sqref="B2:B1001"><formula1>0</formula1></dataValidation></dataValidations></worksheet>');
 zip.file('xl/worksheets/sheet2.xml',declaration+'<worksheet xmlns="'+NS+'"><sheetData><row r="1"><c r="A1" t="inlineStr"><is><t>Item</t></is></c></row>'+items.map((item,i)=>'<row r="'+(i+2)+'"><c r="A'+(i+2)+'" t="inlineStr"><is><t>'+xml(item.label??item.item_name??'')+'</t></is></c></row>').join('')+'</sheetData></worksheet>');
 return zip.generateAsync({type:'blob',compression:'DEFLATE',mimeType:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
}
async function read(file,kind='boq'){
 if(!file||!/^.+\.xlsx$/i.test(file.name))throw Error('Choose an .xlsx Excel workbook.');if(file.size>2*1024*1024)throw Error('Excel file must not exceed 2 MB.');
 const Zip=zipClass();let zip;try{zip=await Zip.loadAsync(await file.arrayBuffer());}catch(e){throw Error('This is not a valid .xlsx workbook.');}
 if(Object.keys(zip.files).length>2000)throw Error('The workbook contains too many files.');
 const text=async(path,required=true)=>{const entry=zip.file(path);if(!entry){if(required)throw Error('The workbook is missing '+path+'.');return null;}if(entry._data?.uncompressedSize>2500000)throw Error('The worksheet is too large.');const value=await entry.async('string');if(value.length>2500000)throw Error('The worksheet is too large.');return value;};
 const workbook=doc(await text('xl/workbook.xml')),sheet=nodes(workbook,'sheet')[0];if(!sheet)throw Error('The workbook has no worksheets.');
 const id=sheet.getAttributeNS(REL,'id')||sheet.getAttribute('r:id'),relationships=doc(await text('xl/_rels/workbook.xml.rels')),relation=nodes(relationships,'Relationship').find(x=>x.getAttribute('Id')===id);
 if(!relation||relation.getAttribute('TargetMode')==='External')throw Error('The worksheet link is invalid.');
 const target=relation.getAttribute('Target'),resolved=new URL(target,'https://workbook.invalid/xl/workbook.xml');if(resolved.origin!=='https://workbook.invalid')throw Error('External worksheets are not supported.');const path=resolved.pathname.slice(1);
 const sharedRelation=nodes(relationships,'Relationship').find(x=>(x.getAttribute('Type')||'').endsWith('/sharedStrings'));
 const sharedPath=sharedRelation?new URL(sharedRelation.getAttribute('Target'),'https://workbook.invalid/xl/workbook.xml').pathname.slice(1):'xl/sharedStrings.xml';
 const sharedText=await text(sharedPath,false),shared=sharedText?nodes(doc(sharedText),'si').map(x=>nodes(x,'t').map(t=>t.textContent).join('')):[];
 const worksheet=doc(await text(path)),rows=[];let itemCol='A',qtyCol='B',header=false;
 for(const row of nodes(worksheet,'row')){
  const cells={};let position=0;
  for(const cell of Array.from(row.children).filter(x=>x.localName==='c')){const col=(cell.getAttribute('r')||String.fromCharCode(65+position)).replace(/\d/g,'');position++;const type=cell.getAttribute('t'),raw=nodes(cell,'v')[0]?.textContent||'';cells[col]=(type==='s'?(shared[Number(raw)]??''):type==='inlineStr'?nodes(cell,'t').map(x=>x.textContent).join(''):raw).trim();}
  if(!Object.values(cells).some(Boolean))continue;
  if(!header){const entries=Object.entries(cells),item=entries.find(([,v])=> (kind==='holiday'?/^(date|holiday date)$/i:/^(item|item name|material|material name)$/i).test(v)),qty=entries.find(([,v])=> (kind==='holiday'?/^(holiday|holiday name)$/i:/^(boq qty|boq quantity|quantity|qty)$/i).test(v));if(!item||!qty)throw Error(kind==='holiday'?'Use the Holiday template with Date and Holiday columns.':'Use the BOQ template with Item and BOQ Qty columns.');itemCol=item[0];qtyCol=qty[0];header=true;continue;}
  const name=cells[itemCol]||'',quantity=cells[qtyCol]||'';if(!name&&!quantity)continue;
  rows.push(kind==='holiday'?{holiday_date:holidayDate(name),holiday_name:quantity}:{item_name:name,boq_qty:quantity});if(rows.length>1000)throw Error('Maximum 1000 rows allowed.');
 }
 if(!rows.length)throw Error('No filled rows found below the headings.');return rows;
}

function holidayDate(value){
 const v=String(value).trim();
 if(/^\d+(\.\d+)?$/.test(v)){const n=Number(v);if(n<1||n>2958465)throw Error('Invalid holiday date.');return new Date(Date.UTC(1899,11,30)+Math.floor(n)*86400000).toISOString().slice(0,10);}
 if(/^\d{4}-\d{2}-\d{2}$/.test(v))return v;
 const m=v.match(/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/);if(m)return m[3]+'-'+m[2].padStart(2,'0')+'-'+m[1].padStart(2,'0');
 throw Error('Use DD/MM/YYYY for holiday dates.');
}
async function holidayTemplate(){
 const blob=await template([]),zip=await (zipClass()).loadAsync(blob);
 zip.file('xl/workbook.xml',declaration+'<workbook xmlns="'+NS+'" xmlns:r="'+REL+'"><sheets><sheet name="Holidays" sheetId="1" r:id="rId1"/></sheets></workbook>');
 zip.file('xl/worksheets/sheet1.xml',declaration+'<worksheet xmlns="'+NS+'"><cols><col min="1" max="1" width="22" customWidth="1"/><col min="2" max="2" width="48" customWidth="1"/></cols><sheetData><row r="1"><c r="A1" t="inlineStr"><is><t>Date</t></is></c><c r="B1" t="inlineStr"><is><t>Holiday</t></is></c></row></sheetData></worksheet>');
 return zip.generateAsync({type:'blob',compression:'DEFLATE',mimeType:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
}
global.BSMExcel={template,read,holidayTemplate};
})(window);
