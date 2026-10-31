(function($){
'use strict';

const App = {
  current: 'dashboard', page: 1, search: '', editId: 0, options: {},
  nav: [
    {group:'Main', items:[['dashboard','Site Dashboard']]},
    {group:'Masters', items:[['bank_accounts','Bank Master'],['customers','Customers'],['designations','Designations'],['expense_categories','Expense Categories'],['gst_rates','GST Rates'],['item_categories','Item Categories'],['items','Items'],['labour_contractors','Labour Contractors'],['sections','Section'],['sites','Site Master'],['states','States'],['units','Units'],['users','Users'],['vendors','Suppliers'],['warehouses','Warehouses']]},
    {group:'Sites', items:[['project_tracker','Site Tracker'],['report_customer_outstanding','Site Balance'],['customer_receipts','Site Payment Receipts']]},
    {group:'BOQ', items:[['boq_form','BOQ Form'],['boq_master','BOQ Master'],['boq_items','BOQ Items Master'],['boq_monitoring','BOQ Monitoring']]},
    {group:'Site Requisition', items:[['requisition_form','Requisition Form'],['material_requisitions','Requisitions Master'],['material_requisition_item','Requisition Item Master'],['pending_requisition_approval','Requisitions Pending Approval']]},
    {group:'Purchase Order', items:[['pending_po_items','Approved Requisition Pending PO'],['purchase_order_form','PO Form'],['purchase_order_master','PO Master'],['purchase_order_items_master','PO Items Master'],['purchase_order_pending_receipt','Ordered but Not Received']]},
    {group:'Purchase', items:[['purchase_form','Purchase Form'],['purchase_pending_approval','Pending Approval'],['purchase_register','Purchase Master'],['purchase_items_register','Purchase Items Master'],['pending_invoice_copy','Pending Invoice Copy']]},
    {group:'Dispatch', items:[['pending_material_dispatch','Pending Dispatch'],['material_dispatches','Dispatch Master'],['dispatch_items_register','Dispatch Items Master'],['site_transfer_form','Site-to-Site Transfer Form'],['site_transfer_master','Site-to-Site Transfer Master']]},
    {group:'Site Material Receipt', items:[['pending_site_receipt','Pending Site Material Receipt'],['site_receipts','Material Receipts Master'],['site_material_item_receipt','Material Receipts Item Master'],['pending_site_receive_challans','Pending Site Receive Challans']]},
    {group:'Consumption', items:[['consumption_form','Consumption Form'],['site_consumptions','Consumption Master'],['consumption_items_register','Consumption Items Master']]},
    {group:'Material Return', items:[['return_form','Return Form'],['material_returns','Material Returns Master'],['material_returns_item_master','Material Returns Item Master'],['pending_material_receipt','Pending Site Return Approval','Receipt'],['return_receipt_master','Return Receipt Master','Receipt'],['return_receipt_item_master','Return Receipt Item Master','Receipt']]},
    {group:'Inventory', items:[['ho_inventory','HO Inventory'],['site_opening_stock','Site Opening Stock'],['site_inventory','Site Inventory'],['daily_outward_summary','Daily Outward Summary']]},
    {group:'Labour Attendance', items:[['labour_attendance','Daily Labour Attendance'],['report_labour','Daily Labour Report']]},
    {group:'Office Attendance', items:[['office_daily_attendance','Daily Attendance'],['office_attendance_master','Attendance Master'],['office_monthly_salary','Monthly Salary'],['holiday_list','Holiday List']]},
    {group:'Time Sheet', items:[['time_sheet','Time Sheet'],['time_sheet_master','Time Sheet Master']]},
    {group:'Employee Allowance', items:[['allowance_form','Allowance Form'],['pending_allowance_approval','Pending Allowance Approval'],['pending_allowance_payment','Pending Allowance Payment'],['allowance_master','Allowance Master'],['employee_allowance_limits','Employee Allowance Limit'],['allowance_types','Allowance Types']]},
    {group:'Salary', items:[['monthly_salary_upload','Monthly Salary Upload'],['salary_slips','Salary Slips'],['pending_salary_payment','Pending Salary Payment'],['my_salary_slips','My Salary Slips']]},
    {group:'Labour Contractor', items:[['contractor_employees','Contractor Employees'],['contractor_calculated_bill','Employee / Time Based Bill'],['contractor_form','Labour Contractor Bill Form','Bill'],['pending_contractor_bill_copy','Pending Bill Copy','Bill'],['contractor_bills','Labour Contractor Bills','Bill'],['contractor_advance','Contractor Payment Form','Payment'],['contractor_advance_register','Contractor Payment Master','Payment'],['report_contractor_outstanding','Sitewise Contractor Outstanding','Payment']]},
    {group:'Miscellaneous Expense', items:[['site_fund_allocations','Site Opening'],['site_expenses','Site Expenses'],['daily_expense_summary','Daily Expense Summary'],['report_site_fund','Site Balance']]},
    {group:'Payment', items:[['report_vendor_outstanding','Supplier Outstanding'],['pending_allowance_payment','Pending Allowance Payment'],['report_contractor_outstanding','Contractor Outstanding'],['pending_salary_payment','Pending Salary Payment'],['vendor_payments','Payment Master']]},
    {group:'Quotation', items:[['quotation_form','Quotation Form'],['quotation_master','Quotation Master'],['quotation_items','Quotation Items Master']]},
    {group:'Tax Invoice', items:[['invoice_form','Invoice Form'],['invoice_master','Invoice Master'],['invoice_items','Invoice Items Master']]},
    {group:'Settings', items:[['company_settings','Company Settings'],['view_settings','Show / Hide Views']]}
  ],
  configs: {}
};

const status = (opts=['active','inactive']) => ({type:'select', options:opts.map(x=>[x,x.replaceAll('_',' ').replace(/\b\w/g,c=>c.toUpperCase())])});
const money = {type:'number', step:'0.01', cls:'num'};
const qty = {type:'number', step:'0.01', cls:'num'};
const date = {type:'date'};
const text = {type:'text'};
const textarea = {type:'textarea', wide:true};
const ref = (type) => ({type:'select_ref', ref:type});

function F(name,label,def={}){ return Object.assign({name,label},def); }
function C(key,label,type='text'){ return {key,label,type}; }

App.configs = {
  bank_accounts:{title:'Bank Master', fields:[F('bank_name','Bank Name',{required:true}),F('account_holder_name','Account Holder Name',{required:true}),F('account_number','Account Number',{required:true}),F('ifsc_code','IFSC Code'),F('branch_name','Branch'),F('account_type','Account Type',{type:'select',options:[['Current','Current'],['Savings','Savings'],['OD','OD'],['CC','CC'],['Other','Other']]}),F('upi_id','UPI ID'),F('status','Status',status())], columns:[C('bank_name','Bank Name'),C('account_holder_name','Account Holder'),C('account_number','Account Number'),C('ifsc_code','IFSC'),C('branch_name','Branch'),C('account_type','Account Type'),C('upi_id','UPI ID'),C('status','Status')]},
  company_settings:{title:'Company Settings', singleton:true, fields:[F('company_name','Company Name'),F('address','Address',textarea),F('city','City'),F('state_id','State',Object.assign({addMaster:'states'},ref('states'))),F('pin_code','PIN Code'),F('gstin','GSTIN'),F('pan','PAN'),F('phone','Phone'),F('email','Email',{type:'email'}),F('bank_account_holder','Account Holder'),F('bank_name','Bank Name'),F('bank_branch','Branch'),F('bank_ifsc','IFSC Code'),F('bank_account_no','Account No.'),F('pdf_theme_color','PDF Theme Color',{type:'color'})], columns:[C('company_name','Company'),C('gstin','GSTIN'),C('state_id','State','ref:states')]},
  sections:{title:'Section',fields:[F('section_name','Section Name',{required:true}),F('status','Status',status())],columns:[C('section_name','Section Name'),C('status','Status')]},
  designations:{title:'Designations', fields:[F('designation_name','Designation Name',{required:true}),F('status','Status',status())], columns:[C('designation_name','Designation'),C('status','Status')]},
  states:{title:'States', fields:[F('state_name','State Name',{required:true}),F('status','Status',status())], columns:[C('state_name','State'),C('status','Status')]},
  customers:{title:'Customers', fields:[F('customer_name','Customer Name',{required:true}),F('contact_person','Contact Person'),F('mobile','Mobile'),F('email','Email',{type:'email'}),F('address','Address',textarea),F('city','City'),F('state_id','State',Object.assign({addMaster:'states'},ref('states'))),F('pin_code','PIN Code'),F('gstin','GSTIN'),F('pan','PAN'),F('gst_type','GST Type',{type:'select',options:[['','Select'],['intra_state','Intra-State'],['inter_state','Inter-State'],['no_gst','No GST']]}),F('status','Status',status())], columns:[C('customer_name','Customer'),C('contact_person','Contact'),C('mobile','Mobile'),C('address','Address'),C('state_id','State','ref:states'),C('city','City'),C('gstin','GSTIN'),C('gst_type','GST Type'),C('status','Status')]},
  sites:{title:'Sites', fields:[F('site_name','Site Name', {required:true}),F('customer_id','Customer/Project',Object.assign({required:true},ref('customers'))),F('site_location','Site Location',{required:true}),F('site_sections','Section',{type:'site_sections'}),F('site_manager_user_id','Site Manager',Object.assign({required:true},ref('users'))),F('site_supervisor_user_id','Site Supervisor',Object.assign({required:true},ref('users'))),F('start_date','Start Date',Object.assign({required:true},date)),F('expected_end_date','Expected Completion Date',Object.assign({required:true},date)),F('work_order_value','Work Order Value',Object.assign({required:true},money)),F('status','Status',status(['active','on_hold','completed'])),F('remarks','Remarks',textarea)], columns:[C('site_code','Site Code'),C('site_name','Site'),C('customer_id','Customer/Project','ref:customers'),C('site_location','Site Location'),C('site_sections','Section'),C('site_manager_user_id','Site Manager','ref:users'),C('start_date','Start Date','date'),C('work_order_value','Work Order Value','money'),C('status','Status')]},
  vendors:{title:'Suppliers', fields:[F('vendor_name','Supplier Name',{required:true}),F('contact_person','Contact Person',{required:true}),F('mobile','Mobile',{required:true}),F('email','Email',{type:'email'}),F('address','Address',Object.assign({required:true},textarea)),F('city','City',{required:true}),F('state_id','State',Object.assign({required:true,addMaster:'states'},ref('states'))),F('gstin','GSTIN'),F('gst_type','GST Type',{type:'select',required:true,options:[['intra_state','Intra-State'],['inter_state','Inter-State'],['no_gst','No GST']]}),F('pan','PAN'),F('bank_name','Bank Name'),F('bank_account','Bank Account'),F('ifsc','IFSC'),F('status','Status',status())], columns:[C('vendor_name','Supplier'),C('contact_person','Contact'),C('mobile','Mobile'),C('gstin','GSTIN'),C('gst_type','GST Type'),C('status','Status')]},
  labour_contractors:{title:'Labour Contractors', fields:[F('payment_basis','Payment Basis',{type:'select',options:[['direct','Direct Payment to Contractor'],['employee','Contractor + Employee'],['time','According to Time']]}),F('contractor_name','Contractor Name',{required:true}),F('contact_person','Contact Person',{required:true}),F('mobile','Mobile',{required:true}),F('email','Email',{type:'email'}),F('address','Address',Object.assign({required:true},textarea)),F('city','City',{required:true}),F('state_id','State',Object.assign({required:true,addMaster:'states'},ref('states'))),F('gstin','GSTIN'),F('pan','PAN'),F('bank_name','Bank Name'),F('bank_account','Bank Account'),F('ifsc','IFSC'),F('status','Status',status())], columns:[C('payment_basis','Payment Basis'),C('contractor_name','Contractor'),C('contact_person','Contact'),C('mobile','Mobile'),C('gstin','GSTIN'),C('status','Status')]},
  labour_attendance:{title:'Daily Labour Attendance', attendanceTable:true, fields:[F('site_id','Site',Object.assign({required:true},ref('sites'))),F('attendance_date','Date',Object.assign({required:true},date))], items:[F('contractor_id','Labour Contractor',Object.assign({required:true},ref('contractors'))),F('no_of_labour','Employees / Working Hours',{type:'number',min:0,step:'1'})], columns:[C('attendance_date','Date','date'),C('updated_by_name','Updated By'),C('site_id','Site','ref:sites'),C('contractor_id','Contractor','ref:contractors'),C('no_of_labour','Attend','number')]},
  item_categories:{title:'Item Categories', fields:[F('category_name','Category Name',{required:true}),F('status','Status',status())], columns:[C('category_name','Category'),C('status','Status')]},
  units:{title:'Units', fields:[F('unit_name','Unit Name',{required:true}),F('status','Status',status())], columns:[C('unit_name','Unit'),C('status','Status')]},
  gst_rates:{title:'GST Rates', fields:[F('gst_rate','GST Rate %',Object.assign({required:true},money)),F('status','Status',status())], columns:[C('gst_rate','GST Rate %','number'),C('status','Status')]},
  items:{title:'Items', fields:[F('item_name','Item Name',{required:true}),F('category_id','Category',ref('categories')),F('unit_id','Unit',ref('units')),F('hsn_code','HSN Code'),F('gst_rate','GST Rate %',Object.assign({addMaster:'gst_rates'},ref('gst_rates'))),F('opening_qty','Opening Qty',qty),F('opening_rate','Price',money),F('minimum_stock','Minimum Stock',qty),F('status','Status',status())], columns:[C('item_name','Item'),C('category_id','Category','ref:categories'),C('unit_id','Unit','ref:units'),C('hsn_code','HSN'),C('opening_qty','Opening Qty','number'),C('opening_rate','Price','money'),C('gst_rate','GST %','number'),C('status','Status')]},
  site_opening_stock:{title:'Site Opening Stock', fields:[F('site_id','Site',Object.assign({required:true},ref('sites'))),F('opening_date','Opening Date',Object.assign({required:true},date)),F('item_id','Item',Object.assign({required:true},ref('items'))),F('opening_qty','Opening Qty',Object.assign({required:true},qty)),F('opening_rate','Opening Rate',money),F('unit_id','Unit',Object.assign({readonly:true},ref('units'))),F('remarks','Remarks')], columns:[C('site_id','Site','ref:sites'),C('opening_date','Opening Date','date'),C('item_id','Item','ref:items'),C('opening_qty','Opening Qty','number'),C('opening_rate','Opening Rate','money'),C('unit_id','Unit','ref:units')]},
  warehouses:{title:'Warehouses', fields:[F('warehouse_name','Warehouse Name',{required:true}),F('address','Address',textarea),F('manager_user_id','Manager',ref('users')),F('status','Status',status())], columns:[C('warehouse_name','Warehouse'),C('manager_user_id','Manager','ref:users'),C('status','Status')]},
  expense_categories:{title:'Expense Categories', fields:[F('category_name','Category Name',{required:true}),F('status','Status',status())], columns:[C('category_name','Category'),C('status','Status')]},
  allowance_types:{title:'Allowance Types', fields:[F('allowance_type_name','Allowance Type',{required:true}),F('status','Status',status())], columns:[C('allowance_type_name','Allowance Type'),C('status','Status')]},
  employee_allowance_limits:{title:'Employee Allowance Limit', fields:[F('employee_id','Employee',Object.assign({required:true},ref('users'))),F('allowance_type_id','Allowance Type',Object.assign({required:true},ref('allowance_types'))),F('limit_amount','Limit Amount',Object.assign({required:true},money)),F('status','Status',status())], columns:[C('employee_id','Employee','ref:users'),C('allowance_type_id','Allowance Type','ref:allowance_types'),C('limit_amount','Limit Amount','money'),C('status','Status')]},

  material_requisitions:{title:'Requisitions Master', fields:[F('site_id','Site',Object.assign({required:true},ref('sites'))),F('requisition_date','Requisition Date',Object.assign({required:true},date)),F('required_date','Required Date',Object.assign({required:true},date)),F('requested_by','Requested By',Object.assign({required:true},ref('users')))], columns:[C('requisition_no','Requisition'),C('requisition_date','Date','date'),C('required_date','Required Date','date'),C('site_id','Site','ref:sites'),C('requested_by','Requested By','ref:users'),C('status','Status')], items:[F('item_id','Item',Object.assign({required:true,addMaster:'items'},ref('items'))),F('boq_qty_display','BOQ Qty',{type:'display'}),F('already_req_qty_display','Already Req. Qty',{type:'display'}),F('requested_qty','Req. Qty',Object.assign({required:true},qty)),F('unit_id','Unit',Object.assign({required:true,readonly:true},ref('units'))),F('remarks','Remarks',{type:'textarea'}),F('document_url','',{type:'hidden'}),F('document_name','',{type:'hidden'})]},
  quotations:{title:'Quotation', fields:[F('quotation_no','Quotation No.',{type:'hidden'}),F('quotation_date','Date',Object.assign({required:true},date)),F('customer_id','Customer Name',Object.assign({required:true},ref('customers'))),F('site_id','Site',Object.assign({required:true},ref('sites'))),F('billing_address','Billing Address',{type:'hidden'}),F('customer_gstin','GSTIN',{type:'hidden'}),F('billing_state_id','State',{type:'hidden'}),F('place_of_supply_state_id','Place of Supply',{type:'hidden'}),F('valid_till','Valid Till',date),F('taxable_amount','Amount',{type:'hidden'}),F('cgst_amount','CGST',{type:'hidden'}),F('sgst_amount','SGST',{type:'hidden'}),F('igst_amount','GST',{type:'hidden'}),F('other_charges','Other Charge',money),F('gst_on_other_charge','GST on Other Charge',money),F('round_off','Round Off',money),F('grand_total','Total Amount',{type:'hidden'}),F('terms','Terms & Conditions',textarea),F('remarks','Notes',{type:'hidden'})], columns:[C('quotation_no','Quotation No.'),C('quotation_date','Date','date'),C('customer_id','Customer','ref:customers'),C('taxable_amount','Amount','money'),C('grand_total','Total','money'),C('pdf_url','PDF','document')], items:[F('item_id','Item',Object.assign({required:true},ref('items'))),F('description','Description'),F('hsn_sac','HSN / SAC',{type:'hidden'}),F('qty','Qty',Object.assign({required:true},qty)),F('unit_id','Unit',Object.assign({required:true},ref('units'))),F('rate','Rate',Object.assign({required:true},money)),F('gst_rate','GST %',ref('gst_rates')),F('cgst_amount','CGST',{type:'hidden'}),F('sgst_amount','SGST',{type:'hidden'}),F('igst_amount','GST',Object.assign({readonly:true},money)),F('amount','Amount',Object.assign({readonly:true},money)),F('total_amount','Total',{type:'hidden'})]},
  tax_invoices:{title:'Tax Invoice', fields:[F('invoice_no','Invoice No.',{type:'hidden'}),F('invoice_date','Date',Object.assign({required:true},date)),F('customer_id','Customer Name',Object.assign({required:true},ref('customers'))),F('site_id','Site',Object.assign({required:true},ref('sites'))),F('billing_address','Billing Address',{type:'hidden'}),F('customer_gstin','GSTIN',{type:'hidden'}),F('billing_state_id','State',{type:'hidden'}),F('place_of_supply_state_id','Place of Supply',{type:'hidden'}),F('taxable_amount','Amount',{type:'hidden'}),F('cgst_amount','CGST',{type:'hidden'}),F('sgst_amount','SGST',{type:'hidden'}),F('igst_amount','GST',{type:'hidden'}),F('other_charges','Other Charge',money),F('gst_on_other_charge','GST on Other Charge',money),F('round_off','Round Off',money),F('invoice_total','Total Amount',{type:'hidden'}),F('terms','Terms & Conditions',textarea),F('remarks','Notes',{type:'hidden'})], columns:[C('invoice_no','Invoice No.'),C('invoice_date','Date','date'),C('customer_id','Customer','ref:customers'),C('site_id','Site','ref:sites'),C('taxable_amount','Taxable','money'),C('invoice_total','Total','money'),C('pdf_url','PDF','document')], items:[F('item_id','Item',Object.assign({required:true},ref('items'))),F('description','Description'),F('hsn_sac','HSN / SAC',{type:'hidden'}),F('qty','Qty',Object.assign({required:true},qty)),F('unit_id','Unit',Object.assign({required:true},ref('units'))),F('rate','Rate',Object.assign({required:true},money)),F('gst_rate','GST %',ref('gst_rates')),F('igst_amount','IGST',Object.assign({readonly:true},money)),F('cgst_amount','CGST',Object.assign({readonly:true},money)),F('sgst_amount','SGST',Object.assign({readonly:true},money)),F('amount','Amount',Object.assign({readonly:true},money)),F('total_amount','Total',{type:'hidden'})]},

  material_inwards:{title:'Material Inward', fields:[F('purchase_order_id','',{type:'hidden'}),F('inward_type','Inward Type',{type:'select',required:true,options:[['return','Return'],['purchase','Purchase']]}),F('return_id','Return No.',ref('returns')),F('site_id','Site',Object.assign({readonly:true},ref('sites'))),F('warehouse_id','Warehouse',Object.assign({required:true,readonly:true},ref('warehouses'))),F('received_by','Received By',Object.assign({readonly:true},ref('users'))),F('vendor_id','Supplier',ref('vendors')),F('inward_date','Inward Date',Object.assign({required:true},date)),F('supplier_invoice_no','Supplier Invoice No.'),F('supplier_invoice_date','Supplier Invoice Date',date),F('bill_type','Bill Type',{type:'select',options:[['','Select Bill Type'],['gst_bill','GST Bill'],['non_gst_bill','Non GST Bill']]}),F('taxable_amount','Taxable Amount',Object.assign({readonly:true},money)),F('other_expenses','Other Expenses',money),F('other_expense_gst','GST on Other Expense',money),F('round_off','Round Off',Object.assign({step:'0.01'},money)),F('total_amount','Total Amount',Object.assign({readonly:true},money))], columns:[C('inward_no','Inward'),C('inward_date','Date','date'),C('vendor_id','Supplier','ref:vendors'),C('total_amount','Total','money'),C('status','Status')], items:[F('purchase_order_item_id','',{type:'hidden'}),F('item_id','Item',ref('items')),F('return_qty','Return Qty',Object.assign({readonly:true},qty)),F('qty','Qty',qty),F('unit_id','Unit',Object.assign({readonly:true},ref('units'))),F('item_price','Fixed Price',{type:'number',readonly:true,step:'0.01'}),F('rate','Rate',money),F('remarks','Remark'),F('taxable_amount','Taxable',Object.assign({readonly:true},money)),F('gst_rate','GST %',Object.assign({addMaster:'gst_rates'},ref('gst_rates'))),F('cgst_amount','CGST',Object.assign({readonly:true},money)),F('sgst_amount','SGST',Object.assign({readonly:true},money)),F('igst_amount','IGST',Object.assign({readonly:true},money)),F('total_amount','Total',Object.assign({readonly:true},money))]},
  material_dispatches:{title:'Dispatch Master', fields:[F('dispatch_from','Dispatch From',{type:'select',required:true,options:[['warehouse','Warehouse'],['site','Site']]}),F('warehouse_id','Warehouse',ref('warehouses')),F('from_site_id','From Site',ref('sites')),F('site_id','To Site',Object.assign({required:true},ref('sites'))),F('requisition_id','Requisition',ref('requisitions')),F('dispatch_date','Dispatch Date',Object.assign({required:true},date)),F('vehicle_no','Vehicle No.'),F('driver_name','Driver Name'),F('driver_mobile','Driver Mobile'),F('courier_no','Courier No.'),F('courier_name','Courier Name')], columns:[C('dispatch_no','Dispatch'),C('dispatch_date','Date','date'),C('dispatch_from','Dispatch From'),C('warehouse_id','Warehouse','ref:warehouses'),C('from_site_id','From Site','ref:sites'),C('site_id','To Site','ref:sites'),C('vehicle_no','Vehicle No.'),C('driver_name','Driver Name'),C('driver_mobile','Driver Mobile'),C('dispatch_photo_url','Photo','photo'),C('courier_no','Courier No.'),C('courier_name','Courier Name'),C('status','Status')], items:[F('requisition_item_id','',{type:'hidden'}),F('item_id','Item',ref('items')),F('approved_qty','Approved Qty',{type:'number',readonly:true,step:'0.01'}),F('already_dispatched','Already Dispatched',{type:'number',readonly:true,step:'0.01'}),F('pending_qty','Pending Qty',{type:'number',readonly:true,step:'0.01'}),F('available_qty','Available Stock',{type:'number',readonly:true,step:'0.01'}),F('qty','Dispatch Qty',qty),F('unit_id','Unit',Object.assign({readonly:true},ref('units')))]},
  site_receipts:{title:'Site Receipts', fields:[F('dispatch_id','Dispatch',Object.assign({required:true},ref('dispatches'))),F('site_id','Site',Object.assign({required:true,readonly:true},ref('sites'))),F('receipt_date','Receipt Date',Object.assign({required:true},date)),F('received_by','Received By',Object.assign({readonly:true},ref('users')))], columns:[C('receipt_no','Receipt'),C('receipt_date','Date','date'),C('site_id','Site','ref:sites'),C('dispatch_id','Dispatch','ref:dispatches')], items:[F('dispatch_item_id','',{type:'hidden'}),F('item_id','Item',Object.assign({readonly:true},ref('items'))),F('dispatch_qty','Dispatch Qty',Object.assign({readonly:true},qty)),F('received_qty','Received Qty',qty),F('damaged_qty','Damaged Qty',qty),F('short_qty','Short Qty',qty),F('unit_id','Unit',Object.assign({readonly:true},ref('units')))]},
  site_consumptions:{title:'Consumption Master', fields:[F('site_id','Site',Object.assign({required:true},ref('sites'))),F('consumption_date','Date',Object.assign({required:true},date)),F('created_by_display','Consumption Entry By',{type:'display'})], columns:[C('consumption_no','Consumption'),C('consumption_date','Date','date'),C('site_id','Site','ref:sites'),C('created_by_name','Consumption Entry By'),C('status','Status')], items:[F('item_id','Item',Object.assign({required:true},ref('items'))),F('available_qty','Available Qty',{readonly:true}),F('qty','Qty',Object.assign({required:true},qty)),F('unit_id','Unit',Object.assign({required:true,readonly:true},ref('units'))),F('remarks','Remarks')]},
  material_returns:{title:'Material Returns Master', fields:[F('site_id','Site',Object.assign({required:true},ref('sites'))),F('warehouse_id','Warehouse',Object.assign({required:true},ref('warehouses'))),F('return_date','Return Date',Object.assign({required:true},date)),F('return_by','Return By',Object.assign({readonly:true},ref('users')))], columns:[C('return_no','Return'),C('return_date','Date','date'),C('site_id','Site','ref:sites'),C('status','Status')], items:[F('item_id','Item',Object.assign({required:true},ref('items'))),F('available_qty','Available Qty',{readonly:true}),F('qty','Qty',Object.assign({required:true},qty)),F('unit_id','Unit',Object.assign({required:true,readonly:true},ref('units'))),F('remarks','Remarks')]},

  site_fund_allocations:{title:'Site Opening', fields:[F('site_id','Site',Object.assign({required:true},ref('sites'))),F('fund_date','Opening Date',Object.assign({required:true},date)),F('amount','Opening Petty Cash',Object.assign({required:true},money))], columns:[C('site_id','Site','ref:sites'),C('fund_date','Opening Date','date'),C('amount','Opening Petty Cash','money')]},
  site_expenses:{title:'Site Expenses', fields:[F('site_id','Site',Object.assign({required:true},ref('sites'))),F('expense_date','Date',Object.assign({required:true},date)),F('category_id','Expense Category',ref('expense_categories')),F('vendor_id','Supplier',ref('vendors')),F('paid_to','Paid To'),F('particulars','Particulars',textarea),F('amount','Amount',Object.assign({required:true},money)),F('payment_mode','Payment Mode',{type:'select',options:[['Cash','Cash'],['Bank','Bank'],['UPI','UPI'],['Cheque','Cheque'],['Other','Other']]}),F('transaction_reference','Transaction Reference'),F('remarks','Remarks',textarea)], columns:[C('expense_date','Date','date'),C('site_id','Site','ref:sites'),C('category_id','Expense Category','ref:expense_categories'),C('vendor_id','Supplier','ref:vendors'),C('paid_to','Paid To'),C('particulars','Particulars'),C('amount','Amount','money')]},
  customer_receipts:{title:'Site Payment Receipts', fields:[F('site_id','Site',Object.assign({required:true},ref('sites'))),F('receipt_date','Date',Object.assign({required:true},date)),F('amount','Amount',Object.assign({required:true},money)),F('payment_mode','Payment Mode',{type:'select',options:[['Cash','Cash'],['Bank','Bank'],['UPI','UPI'],['Cheque','Cheque'],['Other','Other']]}),F('bank_account_id','Bank',ref('banks')),F('transaction_reference','Transaction Reference'),F('remarks','Remarks',textarea)], columns:[C('receipt_no','Receipt'),C('receipt_date','Date','date'),C('site_id','Site','ref:sites'),C('bank_account_id','Received In Account','ref:banks'),C('amount','Amount','money')]},
  vendor_payments:{title:'Payment Master', fields:[F('payment_type','Payment Type',{type:'select',required:true,options:[['head_office_vendor','Head Office Supplier Payment'],['site_vendor','Supplier Payment for Site'],['site_fund','Site Petty Cash Transfer'],['employee_allowance','Employee Allowance'],['salary','Salary']]}),F('vendor_id','Supplier',ref('vendors')),F('material_inward_id','Material Inward / Bill',ref('inwards')),F('site_id','Site',ref('sites')),F('employee_id','Employee',ref('users')),F('allowance_id','',{type:'hidden'}),F('allowance_type_id','',{type:'hidden'}),F('salary_slip_id','',{type:'hidden'}),F('payment_date','Payment Date',Object.assign({required:true},date)),F('amount','Amount',Object.assign({required:true},money)),F('payment_mode','Payment Mode',{type:'select',options:[['Cash','Cash'],['Bank','Bank'],['UPI','UPI'],['Cheque','Cheque'],['Other','Other']]}),F('bank_account_id','Bank',ref('banks')),F('transaction_reference','Transaction Reference'),F('remarks','Remarks',textarea)], columns:[C('payment_date','Date','date'),C('payment_type','Payment Type'),C('party_name','Party'),C('site_name','Site'),C('amount','Amount','money'),C('document_url','Payment Document','document')]},
  contractor_bills:{title:'Labour Contractor Bills', fields:[F('contractor_id','Labour Contractor',Object.assign({required:true},ref('contractors'))),F('site_id','Site',Object.assign({required:true},ref('sites'))),F('bill_no','Bill No.',{required:true}),F('bill_date','Bill Date',Object.assign({required:true},date)),F('from_date','From Date',Object.assign({required:true},date)),F('to_date','To Date',Object.assign({required:true},date)),F('bill_reference','Contractor Bill Reference'),F('taxable_amount','Amount',Object.assign({required:true},money)),F('gst_amount','GST Amount',money),F('total_amount','Total Amount',Object.assign({readonly:true},money)),F('remarks','Remarks',textarea)], columns:[C('bill_no','Bill'),C('bill_date','Date','date'),C('contractor_id','Contractor','ref:contractors'),C('site_id','Site','ref:sites'),C('total_amount','Total','money'),C('document_url','Bill','document')]},
  contractor_payments:{title:'Contractor Payments', fields:[F('payment_no','Payment No.',{readonly:true}),F('contractor_id','Labour Contractor',Object.assign({required:true},ref('contractors'))),F('contractor_bill_id','Contractor Bill',ref('contractor_bills')),F('site_id','Site',ref('sites')),F('payment_date','Payment Date',Object.assign({required:true},date)),F('amount','Amount',Object.assign({required:true},money)),F('paid_by','Paid By',{type:'select',options:[['HEAD_OFFICE','Head Office'],['SITE','Site']]}),F('payment_mode','Payment Mode',{type:'select',options:[['Cash','Cash'],['Bank','Bank'],['UPI','UPI'],['Cheque','Cheque'],['Other','Other']]}),F('bank_account_id','Bank',ref('banks')),F('transaction_reference','Transaction Reference'),F('status','Status',status(['paid','cancelled'])),F('remarks','Remarks',textarea)], columns:[C('payment_no','Payment'),C('payment_date','Date','date'),C('contractor_id','Contractor','ref:contractors'),C('site_id','Site','ref:sites'),C('amount','Amount','money'),C('paid_by','Paid By')]},
  contractor_advances:{title:'Contractor Payment', fields:[F('contractor_id','Labour Contractor',Object.assign({required:true},ref('contractors'))),F('site_id','Site',Object.assign({required:true},ref('sites'))),F('payment_date','Date',Object.assign({required:true},date)),F('amount','Payment Amount',Object.assign({required:true},money)),F('payment_mode','Payment Mode',{type:'select',required:true,options:[['Cash','Cash'],['Bank','Bank'],['UPI','UPI'],['Cheque','Cheque'],['Other','Other']]}),F('bank_account_id','Bank',ref('banks')),F('transaction_reference','Transaction Reference'),F('remarks','Purpose / Remarks',textarea)], columns:[]},
  gst_invoices:{title:'GST Invoices', fields:[F('invoice_no','Invoice No.',{readonly:true}),F('financial_year','Financial Year'),F('invoice_date','Invoice Date',Object.assign({required:true},date)),F('customer_id','Customer',Object.assign({required:true},ref('customers'))),F('site_id','Site',Object.assign({required:true},ref('sites'))),F('billing_address','Billing Address',textarea),F('customer_gstin','Customer GSTIN'),F('customer_state','Customer State'),F('place_of_supply_state','Place of Supply'),F('taxable_amount','Taxable Amount',money),F('cgst_amount','CGST',money),F('sgst_amount','SGST',money),F('igst_amount','IGST',money),F('round_off','Round Off',money),F('invoice_total','Invoice Total',money),F('status','Status',status(['draft','issued','cancelled']))], columns:[C('invoice_no','Invoice'),C('invoice_date','Date','date'),C('customer_id','Customer','ref:customers'),C('site_id','Site','ref:sites'),C('invoice_total','Total','money'),C('status','Status')], items:[F('description','Description'),F('hsn_sac','HSN/SAC'),F('qty','Qty',qty),F('unit_id','Unit',Object.assign({readonly:true},ref('units'))),F('rate','Rate',money),F('taxable_amount','Taxable',money),F('gst_rate','GST %',Object.assign({addMaster:'gst_rates'},ref('gst_rates'))),F('cgst_amount','CGST',money),F('sgst_amount','SGST',money),F('igst_amount','IGST',money),F('total_amount','Total',money)]}
};


const masterModules = ['users','designations','customers','sites','vendors','labour_contractors','states','item_categories','sections','units','gst_rates','items','warehouses','expense_categories','allowance_types'];
function excelIcon(){return `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h9l5 5v13H5z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M14 3v5h5M8 10l4 7m0-7-4 7m6-6h3m-3 3h3m-3 3h3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>`;}
function excelButton(id='bsm-export-excel'){return `<button id="${id}" class="bsm-icon-btn bsm-excel-btn" title="Download Excel" aria-label="Download Excel">${excelIcon()}</button>`;}
function plainCell(row,col){let v=row[col.key];if(col.key==='payment_basis')return {direct:'Direct Payment to Contractor',employee:'Contractor + Employee',time:'According to Time'}[v]||v||'Direct Payment to Contractor';if(col.key==='payment_type') return row.payment_type_label||({head_office_vendor:'Head Office Supplier Payment',site_vendor:'Supplier Payment for Site',site_fund:'Site Petty Cash Transfer',contractor_payment:'Labour Contractor Payment',contractor_advance:'Labour Contractor Advance',employee_allowance:'Employee Allowance',salary:'Salary'}[v]||String(v||'').replaceAll('_',' ').replace(/\b\w/g,c=>c.toUpperCase()));if(col.key==='gst_type') return gstTypeLabel(v);if(col.key==='bill_type') return billTypeLabel(v);if(col.type==='money') return Number(v||0).toFixed(2);if(col.type==='date') return dateFmt(v);if(col.type==='number') return String(Number(v||0));if(col.type==='percent') return Number(v||0).toFixed(2)+'%';if(col.type&&col.type.startsWith('ref:')){const lbl=labelFor(col.type.split(':')[1],v);if(col.key==='state_id'&&!lbl&&row.state)return row.state;return lbl;}if(col.key==='status'||col.key==='paid_by') return String(v||'').replaceAll('_',' ').replace(/\b\w/g,c=>c.toUpperCase());return v==null?'':String(v);}
function downloadExcelFile(filename,headers,rows){
  const escHtml=v=>String(v??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  const table=`<table><thead><tr>${headers.map(h=>`<th>${escHtml(h)}</th>`).join('')}</tr></thead><tbody>${rows.map(r=>`<tr>${r.map(v=>`<td>${escHtml(v)}</td>`).join('')}</tr>`).join('')}</tbody></table>`;
  const html=`<html><head><meta charset="UTF-8"></head><body>${table}</body></html>`;
  const blob=new Blob(['\ufeff',html],{type:'application/vnd.ms-excel;charset=utf-8;'});
  const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download=(filename||'Export').replace(/[\/:*?"<>|]/g,'-')+'.xls';document.body.appendChild(a);a.click();setTimeout(()=>{URL.revokeObjectURL(a.href);a.remove();},300);
}

const reportConfigs = {
  report_stock:{title:'Stock Report', report:'stock', columns:[C('location_type','Type'),C('location','Location'),C('item_name','Item'),C('unit','Unit'),C('stock','Stock','number')], filters:['site']},
  report_site_fund:{title:'Site Balance', report:'site_fund', columns:[C('site_name','Site'),C('opening_amount','Opening','money'),C('funds_received','Funds Received','money'),C('site_expenses','Expenses','money'),C('balance','Balance','money')], filters:['site']},
  report_site_cost:{title:'Site Cost Report', report:'site_cost', columns:[C('site_name','Site'),C('direct_expenses','Direct Expenses','money'),C('contractor_cost','Contractor Cost','money'),C('ho_paid_expenses','HO Paid Expenses','money'),C('total_site_cost','Total Site Cost','money')], filters:['site']},
  report_labour:{title:'Daily Labour Report', report:'labour', columns:[C('attendance_date','Date','date'),C('site_name','Site'),C('contractor_name','Labour Contractor'),C('no_of_labour','No. of Labour','number')], filters:['date','site','contractor']},
  report_labour_summary:{title:'Labour Summary', report:'labour_summary', columns:[C('site_name','Site'),C('contractor_name','Labour Contractor'),C('labour_days','Labour-Days','number'),C('avg_labour','Avg. Labour/Day','number'),C('max_labour','Maximum Labour','number'),C('days_reported','Days Reported','number')], filters:['date','site','contractor']},
  report_customer_outstanding:{title:'Site Balance', report:'customer_outstanding', columns:[C('site_name','Site'),C('work_order_value','Work Order Value','money'),C('received','Received','money'),C('outstanding','Outstanding','money'),C('received_percentage','Received %','percent'),C('outstanding_percentage','Outstanding %','percent')], filters:['site']},
  report_vendor_outstanding:{title:'Supplier Outstanding', report:'vendor_outstanding', columns:[C('vendor_name','Supplier'),C('bills','Bills','money'),C('payments','Payments','money'),C('outstanding','Outstanding','money')], filters:[]},
  report_contractor_outstanding:{title:'Sitewise Contractor Outstanding', report:'contractor_outstanding', columns:[C('site_name','Site'),C('contractor_name','Contractor'),C('bills','Total Bills','money'),C('payments','Payments','money'),C('outstanding','Outstanding','money')], filters:['site','contractor']},
  contractor_advance_register:{title:'Contractor Payment Master', report:'contractor_advance_register', columns:[C('payment_date','Date','date'),C('contractor_name','Contractor'),C('site_name','Site'),C('payment_amount','Payment Amount','money'),C('payment_mode','Payment Mode'),C('bank_account_id','Bank','ref:banks'),C('transaction_reference','Transaction Reference'),C('remarks','Remarks')], filters:['site','contractor']},
  pending_contractor_payment:{title:'Pending Labour Contractor Payment', report:'pending_contractor_payment', columns:[C('bill_no','Bill No.'),C('bill_date','Date','date'),C('contractor_name','Contractor'),C('site_name','Site'),C('total_amount','Bill Amount','money'),C('advance_adjusted','Advance Adjusted','money'),C('paid_amount','Already Paid','money'),C('pending_amount','Balance','money'),C('document_url','Bill','document'),C('available_advance','Advance','money')], filters:['site','contractor']}
};

function ajax(action,data={}){ return $.post(BSM.ajax,Object.assign({action,nonce:BSM.nonce},data)); }
function esc(s){ return $('<div>').text(s==null?'':String(s)).html(); }
function moneyFmt(v){ return '₹' + Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function dateFmt(v){ if(!v||v==='0000-00-00') return ''; const p=String(v).slice(0,10).split('-'); return p.length===3?`${p[2]}/${p[1]}/${p[0]}`:v; }
function dateSql(v){ if(!v) return ''; const x=String(v).trim(); if(/^\d{4}-\d{2}-\d{2}$/.test(x)) return x; const m=x.match(/^(\d{1,2})[\/.-](\d{1,2})[\/.-](\d{4})$/); if(!m) return x; const d=String(m[1]).padStart(2,'0'),mo=String(m[2]).padStart(2,'0'),y=m[3]; return `${y}-${mo}-${d}`; }
function normalizeDateInput(v){return dateSql(v);}
function isDateFieldName(n){return /(^|_)(date)$/.test(n)||['attendance_date','requisition_date','inward_date','dispatch_date','receipt_date','consumption_date','return_date','fund_date','expense_date','payment_date','bill_date','invoice_date','required_date','supplier_invoice_date','from_date','to_date','start_date','expected_end_date','financial_year_start'].includes(n);}
function bsmGstRateOptions(){
  const src=Array.isArray(App.options.gst_rates)?App.options.gst_rates:[];
  const map=new Map();
  src.forEach(x=>{const n=Number(x?.id);if(Number.isFinite(n))map.set(String(n),{id:String(n),label:(x.label||(`${n}%`))});});
  // Standard GST rates are kept as a safe fallback so Quotation/Invoice GST dropdowns never render empty.
  [0,5,12,18,28].forEach(n=>{if(!map.has(String(n)))map.set(String(n),{id:String(n),label:`${n}%`});});
  (App.options.items||[]).forEach(x=>{const n=Number(x?.gst_rate);if(Number.isFinite(n)&&n>=0&&!map.has(String(n)))map.set(String(n),{id:String(n),label:`${n}%`});});
  return [...map.values()].sort((a,b)=>Number(a.id)-Number(b.id));
}
function bsmOptionsForRef(type){return type==='gst_rates'?bsmGstRateOptions():(App.options[type]||[]);}
function labelFor(type,id){ const arr=bsmOptionsForRef(type); const x=arr.find(r=>String(r.id)===String(id)||Number(r.id)===Number(id)); return x?x.label:(id||''); }
function renderCell(row,col){ let v=row[col.key]; if(col.type==='photo')return v?`<a href="${esc(v)}" target="_blank" rel="noopener" title="View Dispatch Photo"><img src="${esc(v)}" alt="Dispatch Photo" loading="lazy" style="width:56px;height:42px;object-fit:cover;border:1px solid #b9ccec;border-radius:5px;display:block"></a>`:'—'; if(col.key==='payment_type') return esc(row.payment_type_label||({head_office_vendor:'Head Office Supplier Payment',site_vendor:'Supplier Payment for Site',site_fund:'Site Petty Cash Transfer',contractor_payment:'Labour Contractor Payment',contractor_advance:'Labour Contractor Advance',employee_allowance:'Employee Allowance',salary:'Salary'}[v]||String(v||'').replaceAll('_',' ').replace(/\b\w/g,c=>c.toUpperCase()))); if(col.key==='gst_type') return esc(gstTypeLabel(v));if(col.key==='bill_type') return esc(billTypeLabel(v)); if(col.type==='money') return moneyFmt(v); if(col.type==='date') return dateFmt(v); if(col.type==='number') return Number(v||0).toLocaleString('en-IN'); if(col.type==='percent') return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2})+'%'; if(col.type==='document') return v?`<a href="${esc(v)}" target="_blank" rel="noopener" class="bsm-document-link">Link</a>`:'-'; if(col.type&&col.type.startsWith('ref:')){const lbl=labelFor(col.type.split(':')[1],v);if(col.key==='state_id'&&!lbl&&row.state)return esc(row.state);return esc(lbl);} if(col.key==='status'||col.key==='paid_by') return `<span class="bsm-pill">${esc(String(v||'').replaceAll('_',' '))}</span>`; return esc(v); }

function optionTypesForView(key){
  const needed=new Set();
  if(key==='sites') needed.add('sections');
  if(key==='purchase_pending_approval') return ['vendors','warehouses','items','units'];
  if(key==='pending_requisition_approval') return ['sites','users','items','units'];
  if(key==='pending_material_dispatch') return ['sites','warehouses','items','units'];
  if(key==='pending_material_receipt') return ['sites','warehouses','users','items','units'];
  if(key==='pending_site_receipt') return ['sites','warehouses','items','units'];
  if(key==='material_stock') return ['sites','warehouses','items'];
  const cfg=App.configs[key];
  const scan=(arr=[])=>arr.forEach(f=>{ if(f && f.type==='select_ref' && f.ref) needed.add(f.ref); });
  if(cfg){
    scan(cfg.fields); scan(cfg.items);
    (cfg.columns||[]).forEach(c=>{ if(c.type && c.type.startsWith('ref:')) needed.add(c.type.split(':')[1]); });
  }
  if(['labour_attendance','material_requisitions','material_dispatches','site_receipts','site_consumptions','material_returns','site_opening_stock','site_fund_allocations','site_expenses','customer_receipts','vendor_payments','contractor_bills','contractor_payments','contractor_advances','gst_invoices','quotations','tax_invoices'].includes(key)) needed.add('sites');
  if(['customer_receipts','vendor_payments','contractor_payments','contractor_advances'].includes(key)) needed.add('banks');
  if(['quotations','tax_invoices'].includes(key)){needed.add('customers');needed.add('states');needed.add('items');needed.add('units');needed.add('gst_rates');}
  if(['material_inwards','vendor_payments'].includes(key)) needed.add('vendors');
  if(key==='vendor_payments'){needed.add('users');needed.add('allowance_types');}
  if(key==='material_inwards'){needed.add('returns');needed.add('users');needed.add('sites');}
  if(['labour_attendance','contractor_bills','contractor_payments','contractor_advances'].includes(key)) needed.add('contractors');
  if(['material_inwards','material_dispatches'].includes(key)) needed.add('warehouses');
  const rc=reportConfigs[key];
  if(rc){
    if(rc.filters.includes('site')) needed.add('sites');
    if(rc.filters.includes('contractor')) needed.add('contractors');
  }
  return [...needed];
}

async function ensureOptions(types=[],force=false){
  const missing=[...new Set(types)].filter(t=>force || !Object.prototype.hasOwnProperty.call(App.options,t));
  if(!missing.length) return;
  try{
    const r=await ajax('bsm_options',{types:missing.join(',')});
    if(r.success && r.data){
      Object.assign(App.options,r.data);
      if(BSM.currentUser && !BSM.currentUser.is_admin && Array.isArray(BSM.currentUser.assigned_sites) && BSM.currentUser.assigned_sites.length && App.options.sites){
        const allowedSites=new Set(BSM.currentUser.assigned_sites.map(String));
        App.options.sites=App.options.sites.filter(x=>allowedSites.has(String(x.id)));
      }
    }
  }catch(e){}
}

const BSM_NAV_SCROLL_KEY='bsm_nav_scroll_top';
function saveNavScroll(){
  const nav=document.getElementById('bsm-nav');
  if(!nav)return;
  try{sessionStorage.setItem(BSM_NAV_SCROLL_KEY,String(nav.scrollTop||0));}catch(e){}
}
function restoreNavScroll(){
  const nav=document.getElementById('bsm-nav');
  if(!nav)return;
  let y=0;
  try{y=Number(sessionStorage.getItem(BSM_NAV_SCROLL_KEY)||0)||0;}catch(e){}
  requestAnimationFrame(()=>{nav.scrollTop=y;requestAnimationFrame(()=>{nav.scrollTop=y;});});
}

// ---- Show / Hide Views (Settings) ----
const BSM_PROTECTED_VIEWS=['company_settings','view_settings'];
function bsmHiddenSet(){return new Set((BSM.hiddenViews||[]).filter(k=>!BSM_PROTECTED_VIEWS.includes(k)));}
function isViewHidden(k){return bsmHiddenSet().has(k);}
function firstVisibleView(){
  const allowed=(BSM.currentUser&&BSM.currentUser.is_admin)?null:new Set((BSM.currentUser&&BSM.currentUser.allowed_views)||[]);
  for(const g of App.nav){if(allowed!==null&&['Masters','Settings'].includes(g.group))continue;for(const [k] of g.items){if(isViewHidden(k))continue;if(allowed===null||allowed.has(k)||k==='my_salary_slips')return k;}}
  return '';
}
function renderViewSettings(){
  setTitle('Show / Hide Views');
  if(!(BSM.currentUser&&BSM.currentUser.is_admin)){$('#bsm-content').html('<div class="bsm-empty">Only administrators can change this setting.</div>');return;}
  const hidden=bsmHiddenSet();
  let h='<div class="bsm-panel bsm-viewvis"><p class="bsm-viewvis-help">Ticked views are visible in the menu. Untick a view to hide it for all users. A menu group with no ticked views is hidden completely.</p>';
  h+='<div class="bsm-viewvis-tools"><button type="button" class="bsm-btn" id="bsm-viewvis-all">Tick All</button><button type="button" class="bsm-btn" id="bsm-viewvis-none">Untick All</button><div class="bsm-spacer"></div><button type="button" class="bsm-btn bsm-btn-primary" id="bsm-viewvis-save">Save</button></div><div class="bsm-viewvis-grid">';
  App.nav.forEach((g,gi)=>{
    const items=g.items.filter(([k])=>!(g.group==='Payment'&&k==='payment_form'));
    if(!items.length)return;
    const title=g.group==='Main'?'Dashboard':g.group;
    h+=`<div class="bsm-viewvis-group"><label class="bsm-viewvis-group-head"><input type="checkbox" class="bsm-viewvis-group-cb" data-group="${gi}"><span>${esc(title)}</span></label><div class="bsm-viewvis-items">`;
    items.forEach(([k,l])=>{
      const locked=BSM_PROTECTED_VIEWS.includes(k);
      h+=`<label class="bsm-viewvis-item${locked?' is-locked':''}"><input type="checkbox" class="bsm-viewvis-cb" data-group="${gi}" value="${esc(k)}" ${hidden.has(k)?'':'checked'} ${locked?'disabled title="Always visible"':''}><span>${esc(l)}</span>${locked?'<small>always visible</small>':''}</label>`;
    });
    h+='</div></div>';
  });
  h+='</div></div>';
  $('#bsm-content').html(h);
  syncViewVisGroups();
}
function syncViewVisGroups(){
  $('.bsm-viewvis-group-cb').each(function(){
    const cbs=$(`.bsm-viewvis-cb[data-group="${$(this).data('group')}"]`),on=cbs.filter(':checked').length;
    this.checked=on===cbs.length;this.indeterminate=on>0&&on<cbs.length;
  });
}
// The same view can appear in two groups (e.g. Payment). Keep its boxes in step.
$(document).on('change','.bsm-viewvis-cb',function(){$(`.bsm-viewvis-cb[value="${this.value}"]`).not(':disabled').prop('checked',this.checked);syncViewVisGroups();});
$(document).on('change','.bsm-viewvis-group-cb',function(){const on=this.checked;$(`.bsm-viewvis-cb[data-group="${$(this).data('group')}"]`).not(':disabled').each(function(){$(`.bsm-viewvis-cb[value="${this.value}"]`).not(':disabled').prop('checked',on);});syncViewVisGroups();});
$(document).on('click','#bsm-viewvis-all,#bsm-viewvis-none',function(){$('.bsm-viewvis-cb').not(':disabled').prop('checked',this.id==='bsm-viewvis-all');syncViewVisGroups();});
$(document).on('click','#bsm-viewvis-save',async function(){
  const btn=$(this),hidden=[...new Set($('.bsm-viewvis-cb').not(':disabled').not(':checked').map(function(){return this.value;}).get())];
  btn.prop('disabled',true).text('Saving…');
  try{const r=await ajax('bsm_save_view_visibility',{hidden});if(!r.success)return showToast(r.data?.message||'Unable to save',true);BSM.hiddenViews=r.data.hidden||[];buildNav();$('.bsm-nav-item[data-view="view_settings"]').addClass('active');showToast('Saved. Menu updated.');}
  catch(e){showToast('Unable to save. Please try again.',true);}
  finally{btn.prop('disabled',false).text('Save');}
});

function buildNav(){
  const groupIcons={'Project Tracker':'▥',BOQ:'▤',Main:'⌂',Masters:'▦',Purchase:'↓','Site Requisition':'≡',Dispatch:'→','Site Material Receipt':'✓',Consumption:'−','Material Return':'↩','Labour Attendance':'♟',Contractor:'▤','HO Receipts and Payments':'₹','Employee Allowance':'₹',Salary:'₹',Inventory:'▥','Contractor Outstanding':'₹',Sites:'▥',Settings:'⚙'};
  const itemIcons={view_settings:'☑',project_tracker:'▤',boq_form:'+',boq_master:'▤',boq_items:'▥',boq_monitoring:'◫',dashboard:'⌂',bank_accounts:'₹',users:'●',designations:'◆',customers:'▤',sites:'⌖',vendors:'▣',labour_contractors:'♟',states:'⌖',item_categories:'▦',units:'◢',items:'◇',warehouses:'⌂',expense_categories:'▤',purchase_form:'+',payment_form:'₹',purchase_pending_approval:'✓',purchase_register:'▤',purchase_items_register:'▥',pending_invoice_copy:'▧',purchase_order_form:'+',pending_po_items:'✓',purchase_order_pending_receipt:'↓',purchase_order_master:'▤',purchase_order_items_master:'▥',pending_contractor_bill_copy:'▧',requisition_form:'+',material_requisitions:'≡',material_requisition_item:'▥',pending_requisition_approval:'✓',pending_material_receipt:'✓',material_inwards:'↓',material_dispatches:'→',pending_material_dispatch:'⇢',dispatch_items_register:'▥',pending_site_receipt:'✓',site_receipts:'✓',site_material_item_receipt:'▥',pending_site_receive_challans:'▧',ho_inventory:'▥',site_inventory:'▥',site_opening_stock:'+',daily_outward_summary:'→',consumption_form:'+',site_consumptions:'−',consumption_items_register:'▥',return_form:'+',material_returns:'↩',material_returns_item_master:'▥',material_stock:'▥',labour_attendance:'♟',office_daily_attendance:'✓',office_attendance_master:'▤',office_monthly_salary:'₹',allowance_form:'+',pending_allowance_approval:'✓',pending_allowance_payment:'₹',allowance_master:'▤',employee_allowance_limits:'▥',allowance_types:'▤',monthly_salary_upload:'↑',salary_slips:'▤',pending_salary_payment:'₹',my_salary_slips:'▤',site_fund_allocations:'₹',site_expenses:'₹',daily_expense_summary:'▤',customer_receipts:'↓',vendor_payments:'↑',contractor_form:'+',contractor_advance:'₹',contractor_advance_register:'▤',pending_contractor_payment:'₹',contractor_bills:'▤',contractor_payments:'↑',gst_invoices:'▤',report_stock:'▥',report_site_fund:'▥',report_site_cost:'▥',report_labour:'▥',report_labour_summary:'▥',report_customer_outstanding:'▥',report_vendor_outstanding:'▥',report_contractor_outstanding:'▥',quotation_form:'+',quotation_master:'▤',quotation_items:'▥',invoice_form:'+',invoice_master:'▤',invoice_items:'▥',company_settings:'⚙'};
  let h='';
  const allowed=(BSM.currentUser&&BSM.currentUser.is_admin)?null:new Set((BSM.currentUser&&BSM.currentUser.allowed_views)||[]);
  App.nav.forEach((g,idx)=>{
    if(allowed!==null && ['Masters','Settings'].includes(g.group)) return;
    const visibleItems=(allowed===null?g.items:g.items.filter(([k])=>allowed.has(k)||k==='my_salary_slips')).filter(([k])=>!(g.group==='Payment'&&k==='payment_form')).filter(([k])=>!isViewHidden(k));
    if(!visibleItems.length) return;

    // Dashboard is a standalone first-level menu item. Do not show the old "Main" section.
    if(g.group==='Main'){
      visibleItems.forEach(([k,l])=>{
        const url=(BSM.pages&&BSM.pages[k])?BSM.pages[k]:'#';
        h+=`<a class="bsm-nav-item bsm-nav-standalone ${k===App.current?'active':''}" data-view="${k}" href="${esc(url)}"><span class="bsm-nav-item-icon">${itemIcons[k]||'•'}</span><span>${esc(l)}</span></a>`;
      });
      return;
    }

    const isOpen=visibleItems.some(([k])=>k===App.current);
    const gid=`bsm-nav-group-${idx}`;
    h+=`<details class="bsm-nav-group" data-group="${idx}" ${isOpen?'open':''}>`;
    h+=`<summary class="bsm-nav-label" aria-controls="${gid}"><span class="bsm-nav-label-main"><span class="bsm-nav-section-icon">${groupIcons[g.group]||'•'}</span><span>${esc(g.group)}</span></span><span class="bsm-nav-chevron">⌄</span></summary>`;
    h+=`<div class="bsm-nav-items" id="${gid}">`;
    let lastSubsection='';
    let receiptOpened=false;
    visibleItems.forEach(([k,l,subsection])=>{
      const isReceipt=g.group==='Material Return' && subsection==='Receipt';
      if(receiptOpened && !isReceipt){h+='</div></div>';receiptOpened=false;}
      if(isReceipt && !receiptOpened){
        h+=`<div class="bsm-nav-receipt"><div class="bsm-nav-receipt-label"><span>▤ &nbsp;Receipt</span></div><div class="bsm-nav-receipt-items">`;
        receiptOpened=true;
      }
      if(g.group==='Labour Contractor' && subsection && subsection!==lastSubsection){
        h+=`<div class="bsm-nav-subsection">${esc(subsection)}</div>`;
      }
      lastSubsection=subsection||'';
      const url=(BSM.pages&&BSM.pages[k])?BSM.pages[k]:'#';
      h+=`<a class="bsm-nav-item ${(g.group==='Labour Contractor'||isReceipt)?'bsm-nav-subitem ':''}${k===App.current?'active':''}" data-view="${k}" href="${esc(url)}"><span class="bsm-nav-item-icon">${itemIcons[k]||'•'}</span><span>${esc(l)}</span></a>`;
    });
    if(receiptOpened)h+='</div></div>';
    h+='</div></details>';
  });
  $('#bsm-nav').html(h);
  restoreNavScroll();
}

function setTitle(t,sub=''){ $('#bsm-page-title').text(t); }
function showToast(msg,err=false){ const el=$(`<div class="bsm-toast ${err?'error':''}">${esc(msg)}</div>`).appendTo('body'); setTimeout(()=>el.fadeOut(250,()=>el.remove()),2600); }

const directFormViews=new Set(['purchase_form','requisition_form','consumption_form','return_form','contractor_form','contractor_advance','quotation_form','invoice_form']);
// After completing a direct-entry form, take the user to the related register/master view.
// This keeps the workflow consistent across all direct forms and future edits only need this one map.
const formMasterViews={
  purchase_form:'purchase_pending_approval',
  requisition_form:'material_requisitions',
  consumption_form:'site_consumptions',
  return_form:'material_returns',
  contractor_form:'contractor_bills',
  contractor_advance:'contractor_advance_register',
  quotation_form:'quotation_master',
  invoice_form:'invoice_master'
};
function relatedMasterView(viewKey,moduleKey=''){
  if(viewKey && formMasterViews[viewKey]) return formMasterViews[viewKey];
  const moduleMap={
    material_requisitions:'material_requisitions',
    site_consumptions:'site_consumptions',
    material_returns:'material_returns',
    contractor_bills:'contractor_bills',
    contractor_advances:'contractor_advance_register',
    quotations:'quotation_master',
    tax_invoices:'invoice_master'
  };
  return moduleMap[moduleKey]||'';
}
function exitInlineFormMode(){
  const $panel=$('.bsm-modal-panel').first();
  if($panel.hasClass('bsm-inline-panel')){
    $panel.removeClass('bsm-inline-panel');
    $('#bsm-modal').append($panel).hide();
    $('#bsm-modal-close,#bsm-form-cancel').show();
    $('.bsm-modal-head').show();
  }
}
function enterInlineFormMode(){
  const $host=$('#bsm-inline-form-host');
  const $panel=$('.bsm-modal-panel').first();
  if(!$host.length||!$panel.length)return;
  $('#bsm-modal').hide();
  $panel.addClass('bsm-inline-panel');
  $host.append($panel);
  $('#bsm-modal-close').hide();
}
function hidePurchaseItemUnusedColumns(){
  $('#bsm-line-items .bsm-col-return_qty,#bsm-line-items .bsm-col-remarks').hide();
}
async function openDirectForm(viewKey,moduleKey,title,mode=''){
  await ensureOptions(optionTypesForView(moduleKey));
  setTitle(title);
  $('#bsm-form-save').prop('disabled',false);
  $('#bsm-content').html('<div id="bsm-inline-form-host" class="bsm-inline-form-host"></div>');
  await openForm(moduleKey,0);
  if(viewKey==='requisition_form') await refreshRequisitionBoqItems();
  if(viewKey==='contractor_form'||viewKey==='consumption_form'||viewKey==='quotation_form'||viewKey==='invoice_form'||viewKey==='purchase_form'||viewKey==='requisition_form'||viewKey==='return_form'){
    $('.bsm-modal-head').hide();
  } else {
    $('.bsm-modal-head').show();
  }
  if(viewKey==='quotation_form'){
    $('#bsm-form-save').text('Save Quotation');
  }
  if(viewKey==='purchase_form'){
    $('#bsm-modal-title').text('Add Purchase');
    const $t=$('#bsm-form-fields select[name="inward_type"]');
    if($t.length){$t.val('purchase').prop('disabled',true);$t.closest('label').hide();setMaterialInwardMode('purchase');}
    hidePurchaseItemUnusedColumns();
    if(App.poReceiptPrefill){
      const p=App.poReceiptPrefill;App.poReceiptPrefill=null;
      const $po=$('#bsm-form-fields input[name="purchase_order_id"]');if($po.length)$po.val(p.purchase_order_id||'');
      const $v=$('#bsm-form-fields select[name="vendor_id"]');if($v.length){$v.val(String(p.vendor_id||'')).prop('disabled',true);bsmRefreshSearchableSelect($v[0]);}
      const $w=$('#bsm-form-fields select[name="warehouse_id"]');if($w.length){$w.val(String(p.warehouse_id||'')).prop('disabled',true);bsmRefreshSearchableSelect($w[0]);}
      const cfg=App.configs.material_inwards;const lines=(p.items||[]).filter(it=>Number(it.pending_qty||0)>0.009).map(it=>({purchase_order_item_id:it.po_item_id,item_id:it.item_id,qty:Number(it.pending_qty||0),unit_id:it.unit_id,item_price:Number(it.fixed_price||0),rate:Number(it.rate||0),gst_rate:Number(it.gst_rate||0)}));renderItems(cfg,lines);
      hidePurchaseItemUnusedColumns();
      $('#bsm-lines-body .bsm-line').each(function(){const $tr=$(this);const $item=$tr.find('select[name="item_id"]');if($item.length){$item.prop('disabled',true);bsmRefreshSearchableSelect($item[0]);}autoSelectUnitFromItem($tr);syncPurchaseUnitDisplay($tr);syncPurchaseItemPrice($tr);});
      $('#bsm-lines-body .bsm-add-line-inline').hide();
      const $grid=$('#bsm-form-fields');if(!$grid.find('.bsm-po-source-display').length)$grid.prepend(`<label class="bsm-po-source-display"><span class="bsm-field-label">PO No. <span class="bsm-required">*</span></span><input value="${esc(p.po_no||'')}" readonly required></label>`);
      setTimeout(()=>{if($v.length)loadMaterialInwardVendorGST($v.val()||'');},0);
    }else{
      await prepareRequiredPurchasePOSelector();
    }
  }
  enterInlineFormMode();
}

// BOQ is quantity-only: values in requisitions/receipts/consumption/returns are live site/item aggregates.
const boqColumns=[['site_name','Site'],['section','Section'],['boq_no','BOQ No.'],['boq_date','Date'],['item_name','Item'],['unit_name','Unit'],['boq_qty','BOQ Qty'],['requisition_qty','Requisition Qty'],['site_qty','Site Qty'],['consumption_qty','Consumption Qty'],['return_qty','Return Qty']];
function boqOptions(type,selected=''){return '<option value="">Select '+(type==='sites'?'Site':'Item')+'</option>'+(App.options[type]||[]).map(o=>`<option value="${esc(o.id)}" ${String(o.id)===String(selected)?'selected':''}>${esc(o.label)}</option>`).join('');}
function boqNum(v){return Number(v||0).toLocaleString('en-IN',{maximumFractionDigits:2});}
function boqItemRow(item={}){return `<tr class="bsm-boq-row"><td><select class="bsm-boq-row-section" required><option value="">Select Section</option>${(App.boqSiteSections||[]).map(x=>`<option value="${esc(x.label)}" ${String(item.section||'')===String(x.label)?'selected':''}>${esc(x.label)}</option>`).join('')}</select></td><td><select class="bsm-boq-item" required>${boqOptions('items',item.item_id)}</select></td><td class="bsm-boq-unit">${esc(labelFor('units',(App.options.items||[]).find(x=>String(x.id)===String(item.item_id))?.unit_id)||'-')}</td><td><input class="bsm-boq-qty" type="number" min="0" step="0.01" required value="${esc(item.boq_qty??'')}"></td><td><button type="button" class="bsm-btn bsm-boq-remove">Delete</button></td></tr>`;}
async function bsmLoadBoqSiteSections(siteId, existingItems=[]) {
  App.boqSiteSections=[];
  if(!siteId){$('#bsm-boq-rows .bsm-boq-row-section').html('<option value="">Select Site First</option>').prop('disabled',true);return;}
  const r=await ajax('bsm_boq_site_sections',{site_id:siteId});
  if(String($('#bsm-boq-site').val())!==String(siteId))return;
  if(!r.success){showToast(r.data?.message||'Unable to load Sections',true);return;}
  App.boqSiteSections=r.data.sections||[];
  $('#bsm-boq-rows tr').each(function(i){const value=existingItems[i]?.section||'';const $select=$(this).find('.bsm-boq-row-section');const opts='<option value="">'+(App.boqSiteSections.length?'Select Section':'No Sections assigned')+'</option>'+App.boqSiteSections.map(x=>`<option value="${esc(x.label)}" ${x.label===value?'selected':''}>${esc(x.label)}</option>`).join('');$select.html(opts).prop('disabled',!App.boqSiteSections.length);bsmRefreshSearchableSelect($select[0]);});
}
function bsmBoqSiteUI(){const has=!!$('#bsm-boq-site').val();$('#bsm-boq-template,#bsm-boq-upload').toggle(has);}
async function renderBoqForm(id=0){
  setTitle(id?'Edit BOQ':'BOQ Form');let b={};if(id){const r=await ajax('bsm_boq_get',{id});if(!r.success){showToast(r.data?.message||'Could not load BOQ',true);return;}b=r.data;}
  $('#bsm-content').html(`<div class="bsm-panel bsm-boq-form-panel"><form id="bsm-boq-form"><div class="bsm-boq-header-row"><label class="bsm-boq-date-field">BOQ Date<input id="bsm-boq-date" type="date" lang="en-GB" required value="${esc(b.boq_date||BSM.today||new Date().toISOString().slice(0,10))}"></label><label class="bsm-boq-site-field">Site *<select id="bsm-boq-site" required>${boqOptions('sites',b.site_id)}</select></label></div><div class="bsm-boq-header-tools"><button type="button" id="bsm-boq-template" class="bsm-btn bsm-boq-btn-template">⇩ Download Template</button><button type="button" id="bsm-boq-upload" class="bsm-btn bsm-boq-btn-upload">⇧ Upload Template</button><input id="bsm-boq-upload-file" type="file" accept=".xlsx" style="display:none"><button type="button" id="bsm-boq-add" class="bsm-btn bsm-boq-btn-add">+ Add Item</button></div><div class="bsm-table-wrap bsm-boq-table-wrap"><table class="bsm-table"><thead><tr><th>Section *</th><th>Item *</th><th>Unit</th><th>BOQ Qty *</th><th>Action</th></tr></thead><tbody id="bsm-boq-rows">${(b.items?.length?b.items:[{}]).map(boqItemRow).join('')}</tbody></table></div><div class="bsm-form-actions"><button type="submit" class="bsm-btn bsm-btn-primary">Save BOQ</button><button type="button" id="bsm-boq-cancel" class="bsm-btn bsm-btn-cancel">Cancel</button></div></form></div>`);
  $('#bsm-boq-form').data('id',id);bsmEnhanceSearchableSelects($('#bsm-content'));bsmBoqSiteUI();
  await bsmLoadBoqSiteSections(b.site_id||'',b.items||[]);
}
$(document).on('change','#bsm-boq-site',function(){bsmBoqSiteUI();bsmLoadBoqSiteSections($(this).val());});
// Monitoring is a site/item summary: multiple sections and BOQs are consolidated.
// Operational quantities already represent the entire site/item, so take them once.
function bsmBoqMonitoringSummary(rows){
  const grouped=new Map();
  for(const row of rows){
    const key=String(row.site_id)+'|'+String(row.item_id);
    if(!grouped.has(key))grouped.set(key,{...row,boq_qty:0,_sections:new Set(),_numbers:new Set()});
    const result=grouped.get(key);
    result.boq_qty+=Number(row.boq_qty||0);
    if(row.section)result._sections.add(row.section);
    if(row.boq_no)result._numbers.add(row.boq_no);
  }
  return [...grouped.values()].map(row=>{
    row.section=[...row._sections].join(', ');
    row.boq_no=[...row._numbers].join(', ');
    delete row._sections;delete row._numbers;
    return row;
  });
}
async function renderBoqListing(key){
  const names={boq_master:'BOQ Master',boq_items:'BOQ Items Master',boq_monitoring:'BOQ Monitoring'};setTitle(names[key]);
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-boq-search" class="bsm-search" aria-label="Search" placeholder=""><select id="bsm-boq-filter-site">${boqOptions('sites').replace('Select Site','All Sites')}</select><div class="bsm-spacer"></div><button id="bsm-boq-export" class="bsm-btn">Excel Download</button><button id="bsm-boq-new" class="bsm-btn bsm-btn-primary"><span class="bsm-add-icon" aria-hidden="true">+</span><span>Add</span></button></div><div id="bsm-boq-list" class="bsm-panel"></div>`);
  if(key==='boq_monitoring')$('#bsm-boq-new').remove();
  await loadBoqRows(key);
}
async function loadBoqRows(key=App.current){
  const r=await ajax('bsm_boq_list',{type:key,site_id:$('#bsm-boq-filter-site').val()||'',search:$('#bsm-boq-search').val()||''});if(!r.success){$('#bsm-boq-list').text(r.data?.message||'Unable to load BOQ');return;}
  const sourceRows=r.data.rows||[];const rows=key==='boq_monitoring'?bsmBoqMonitoringSummary(sourceRows):sourceRows;App.boqRows=rows;
  const columns=key==='boq_master'?[['site_name','Site'],['section','Section'],['boq_no','BOQ No.'],['boq_date','Date'],['item_name','Item'],['boq_qty','BOQ Qty']]:boqColumns;
  const by=key==='boq_master'?[...new Map(rows.map(x=>[x.id,x])).values()].map(x=>({...x,section:[...new Set(rows.filter(z=>z.id===x.id).map(z=>z.section).filter(Boolean))].join(', ')})):rows;
  $('#bsm-boq-list').html(`<div class="bsm-table-wrap"><table class="bsm-table"><thead><tr>${(key==='boq_master'?[['site_name','Site'],['section','Section'],['boq_no','BOQ No.'],['boq_date','Date']]:columns).map(c=>`<th>${esc(c[1])}</th>`).join('')}${key==='boq_master'?'<th>Action</th>':''}</tr></thead><tbody>${by.map(row=>`<tr>${(key==='boq_master'?[['site_name'],['section'],['boq_no'],['boq_date']]:columns).map(c=>`<td>${esc(c[0]==='boq_date'?dateFmt(row[c[0]]):c[0].endsWith('qty')||c[0]==='balance'?boqNum(row[c[0]]):row[c[0]]||'-')}</td>`).join('')}${key==='boq_master'?`<td><button type="button" class="bsm-btn bsm-boq-pdf" data-id="${row.id}" title="Download BOQ PDF">⇩ PDF</button> <button class="bsm-btn bsm-boq-edit" data-id="${row.id}">Edit</button> <button class="bsm-btn bsm-boq-delete" data-id="${row.id}">Delete</button></td>`:''}</tr>`).join('')||`<tr><td colspan="13">No BOQ records found.</td></tr>`}</tbody></table></div>`);
}
$(document).on('click','.bsm-boq-pdf',async function(){
  const $btn=$(this);const id=Number($btn.attr('data-id')||0);if(!id)return;
  $btn.prop('disabled',true);
  try{const r=await ajax('bsm_boq_pdf',{id});if(!r.success)throw Error(r.data?.message||'Unable to generate BOQ PDF');
    const a=document.createElement('a');a.href=r.data.url;a.download=r.data.filename||'BOQ.pdf';document.body.appendChild(a);a.click();a.remove();
  }catch(e){showToast(e.message||'Unable to download BOQ PDF',true);}finally{$btn.prop('disabled',false);}
});
$(document).on('click','#bsm-boq-template',async function(){
  const fd=new FormData();fd.append('action','bsm_boq_template');fd.append('nonce',BSM.nonce);fd.append('site_id',$('#bsm-boq-site').val());
  try{const response=await fetch(BSM.ajax,{method:'POST',body:fd,credentials:'same-origin'});const blob=await response.blob();if(!response.ok||blob.size<500)throw Error('Template could not be downloaded');const a=document.createElement('a');const url=URL.createObjectURL(blob);a.href=url;a.download='BOQ-Upload-Template.xlsx';document.body.appendChild(a);a.click();setTimeout(()=>{URL.revokeObjectURL(url);a.remove();},1000);}catch(e){showToast(e.message,true);}
});
$(document).on('click','#bsm-boq-upload',()=>$('#bsm-boq-upload-file').trigger('click'));
$(document).on('change','#bsm-boq-upload-file',async function(){
  const file=this.files?.[0];if(!file)return;const fd=new FormData();fd.append('action','bsm_boq_import');fd.append('nonce',BSM.nonce);fd.append('site_id',$('#bsm-boq-site').val());fd.append('file',file);
  try{const r=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});if(!r.success)throw Error(r.data?.message||'Upload failed');const items=r.data.items||[];$('#bsm-boq-rows').html(items.map(boqItemRow).join(''));bsmEnhanceSearchableSelects($('#bsm-boq-rows'));await bsmLoadBoqSiteSections($('#bsm-boq-site').val(),items);showToast(items.length+' BOQ items loaded. Click Save BOQ to save.');}catch(e){showToast(e.message||'Upload failed',true);}this.value='';
});
$(document).on('click','#bsm-boq-add',()=>{$('#bsm-boq-rows').append(boqItemRow());bsmEnhanceSearchableSelects($('#bsm-boq-rows'));});
$(document).on('click','.bsm-boq-remove',function(){if($('#bsm-boq-rows tr').length>1)$(this).closest('tr').remove();else showToast('At least one BOQ item is required',true);});
$(document).on('change','.bsm-boq-item',function(){const item=(App.options.items||[]).find(o=>String(o.id)===String($(this).val()));$(this).closest('tr').find('.bsm-boq-unit').text(labelFor('units',item?.unit_id)||'-');});
$(document).on('submit','#bsm-boq-form',async function(e){e.preventDefault();const items=$('#bsm-boq-rows tr').map(function(){return {item_id:$(this).find('.bsm-boq-item').val(),section:$(this).find('.bsm-boq-row-section').val(),boq_qty:$(this).find('.bsm-boq-qty').val()};}).get();if(items.some(x=>!x.section)){showToast('Select a Section for each BOQ item',true);return;}if(new Set(items.map(x=>x.section+'|'+x.item_id)).size!==items.length||items.some(x=>!x.item_id||x.boq_qty===''||Number(x.boq_qty)<0)){showToast('Select unique items and valid BOQ quantities',true);return;}const r=await ajax('bsm_boq_save',{id:$(this).data('id')||0,site_id:$('#bsm-boq-site').val(),boq_date:$('#bsm-boq-date').val(),items:JSON.stringify(items)});if(!r.success)return showToast(r.data?.message||'Save failed',true);showToast((r.data?.count||1)+' Section BOQ(s) saved');openView('boq_master');});
$(document).on('click','#bsm-boq-cancel',()=>openView('boq_master'));
$(document).on('click','#bsm-boq-new',()=>openView('boq_form'));
$(document).on('click','.bsm-boq-edit',function(){renderBoqForm($(this).data('id'));});
$(document).on('click','.bsm-boq-delete',async function(){if(!confirm('Delete this BOQ?'))return;const r=await ajax('bsm_boq_delete',{id:$(this).data('id')});if(!r.success)return showToast(r.data?.message||'Delete failed',true);loadBoqRows();});
$(document).on('change','#bsm-boq-filter-site',()=>loadBoqRows());
$(document).on('input','#bsm-boq-search',()=>loadBoqRows());
$(document).on('click','#bsm-boq-export',()=>{const columns=boqColumns;downloadExcelFile('BOQ '+App.current,columns.map(x=>x[1]),(App.boqRows||[]).map(r=>columns.map(x=>x[0]==='boq_date'?dateFmt(r[x[0]]):r[x[0]]??'')));});

let trackerSite=0;
async function renderProjectTracker(){
  setTitle('Site Tracker');
  $('#bsm-content').html('<div class="bsm-panel" id="bsm-tracker"><div class="bsm-loading">Loading Sites…</div></div>');
  await trackerLoad();
}
async function trackerLoad(){
  const r=await ajax('bsm_project_tracker');if(!r.success){$('#bsm-tracker').html('<div class="bsm-empty">Unable to load Project Tracker.</div>');return;}
  App.trackerSites=r.data.sites||[];
  trackerDraw();
}
function trackerPct(v){return Number(v||0).toFixed(1).replace(/\.0$/,'')+'%';}
function trackerBar(v){return `<div class="tracker-progress"><span style="width:${Math.min(100,Math.max(0,Number(v)||0))}%"></span></div>`;}
function trackerDraw(){
  const sites=App.trackerSites||[],$box=$('#bsm-tracker');
  if(!trackerSite){
    let html=`<div class="bsm-toolbar"><input class="bsm-search" id="tracker-search" aria-label="Search" placeholder="" value="${esc(App.trackerSearch||'')}"><div class="bsm-spacer"></div><button class="bsm-btn" id="tracker-excel">Excel Download</button></div><div class="bsm-table-wrap"><table><thead><tr><th>Site</th><th>Sections</th><th>Tasks</th><th>Sub-tasks</th><th>Completion</th><th>Status</th></tr></thead><tbody>`;
    const filtered=sites.filter(s=>String(s.site_name).toLowerCase().includes(String(App.trackerSearch||'').toLowerCase()));
    filtered.forEach(s=>{const count=(s.tasks||[]).reduce((n,t)=>n+(t.subtasks||[]).length,0);html+=`<tr class="tracker-site-row" data-id="${s.id}"><td><a href="#" class="tracker-open" data-id="${s.id}">${esc(s.site_name)}</a></td><td>${(s.sections||[]).length}</td><td>${(s.tasks||[]).length}</td><td>${count}</td><td>${s.has_tasks?trackerPct(s.completion):'—'}${trackerBar(s.completion)}</td><td>${!s.has_tasks?'Not Started':Number(s.completion)>=100?'Completed':'In Progress'}</td></tr>`;});
    html+='</tbody></table></div>';if(!filtered.length)html+='<div class="bsm-empty">No sites found.</div>';$box.html(html);return;
  }
  const site=sites.find(s=>+s.id===+trackerSite);if(!site){trackerSite=0;return trackerDraw();}
  let html='';
  const sectionFilter=App.trackerSectionFilter||'';
  const taskFilter=App.trackerTaskFilter||'';
  const availableSections=(site.sections||[]);
  const availableTasks=(site.tasks||[]).filter(t=>!sectionFilter||t.section===sectionFilter);
  const sectionOpts=availableSections.map(v=>`<option value="${esc(v)}" ${v===sectionFilter?'selected':''}>${esc(v)}</option>`).join('');
  const taskOpts=availableTasks.map(t=>`<option value="${t.id}" ${String(t.id)===String(taskFilter)?'selected':''}>${esc(t.task_name)}</option>`).join('');
  const detailSearch=String(App.trackerDetailSearch||'').toLowerCase().trim();
  html+=`<div class="tracker-head tracker-single-toolbar"><button type="button" class="bsm-btn tracker-back-icon" id="tracker-back" title="All Sites" aria-label="Back to All Sites">←</button><input type="text" class="bsm-search tracker-detail-search" id="tracker-detail-search" aria-label="Search" placeholder="" value="${esc(App.trackerDetailSearch||'')}"><label class="tracker-toolbar-label">Section<span class="tracker-select-wrap"><select id="tracker-filter-section"><option value="">All Sections</option>${sectionOpts}</select>${sectionFilter?'<button type="button" class="tracker-clear-filter" data-filter="section" title="Clear Section filter" aria-label="Clear Section filter">×</button>':''}</span></label><label class="tracker-toolbar-label">Task<span class="tracker-select-wrap"><select id="tracker-filter-task"><option value="">All Tasks</option>${taskOpts}</select>${taskFilter?'<button type="button" class="tracker-clear-filter" data-filter="task" title="Clear Task filter" aria-label="Clear Task filter">×</button>':''}</span></label><div class="bsm-spacer"></div>${excelButton('tracker-excel')}<button type="button" class="bsm-icon-btn bsm-btn-primary tracker-add-task-icon" id="tracker-add-task-main" title="Add Task" aria-label="Add Task">+</button></div>`;
  html+='<div class="bsm-table-wrap tracker-flat-wrap"><table class="tracker-flat-table"><thead><tr><th>Section</th><th>Task</th><th>Sub-task</th><th>Progress %</th><th>Action</th></tr></thead><tbody>';
  let count=0,band=-1;
  (site.sections||[]).filter(section=>!sectionFilter||section===sectionFilter).forEach(section=>{
    const tasks=(site.tasks||[]).filter(t=>t.section===section&&(!taskFilter||String(t.id)===String(taskFilter)));
    tasks.forEach(t=>{
      const ss=t.subtasks||[];
      // Each Task gets its own background colour (cycles through a palette).
      const startCount=count;band++;const bandCls=`tracker-task-band-${band%6}`;
      if(!ss.length){
        const matches=!detailSearch||[section,t.task_name,'Add Sub-task','—'].some(v=>String(v||'').toLowerCase().includes(detailSearch));
        if(matches){count++;html+=`<tr class="${bandCls}"><td>${esc(section)}</td><td><strong>${esc(t.task_name)}</strong></td><td><button class="tracker-inline tracker-add-subtask" data-task="${t.id}" title="Add Sub-task">+ Add Sub-task</button></td><td>—</td><td>${trackerActions(t.id,0)}</td></tr>`;}
      }
      ss.forEach(sub=>{
        const pct=Number(sub.completion)||0;
        const matches=!detailSearch||[section,t.task_name,sub.subtask_name,pct===0?'':String(pct),pct===0?'%':`${pct}%`].some(v=>String(v||'').toLowerCase().includes(detailSearch));
        if(!matches)return;
        count++;html+=`<tr class="${bandCls}"><td>${esc(section)}</td><td><strong>${esc(t.task_name)}</strong></td><td>${esc(sub.subtask_name)}</td><td><div class="tracker-edit"><input type="number" min="0" max="100" step="0.01" class="tracker-value" data-id="${sub.id}" value="${pct===0?'':pct}" placeholder=""><span>%</span></div></td><td>${trackerActions(t.id,sub.id)}</td></tr>`;
      });
      if(count===startCount)band--; // task hidden by search: don't use up a colour
    });
  });
  if(!count)html+='<tr><td colspan="5">No matching Tasks or Sub-tasks found.</td></tr>';
  html+='</tbody></table></div>';
  if(!site.sections.length)html+='<div class="bsm-empty">Assign Sections to this Site in Site Master first.</div>';
  $box.html(html);
}
$(document).on('click','.tracker-open',function(e){e.preventDefault();trackerSite=Number($(this).data('id'));App.trackerSectionFilter='';App.trackerTaskFilter='';App.trackerDetailSearch='';trackerDraw();});
$(document).on('change','#tracker-filter-section',function(){
  App.trackerSectionFilter=$(this).val()||'';
  App.trackerTaskFilter='';
  trackerDraw();
});
$(document).on('change','#tracker-filter-task',function(){
  App.trackerTaskFilter=$(this).val()||'';
  trackerDraw();
});
$(document).on('click','.tracker-clear-filter',function(e){e.preventDefault();e.stopPropagation();const filter=$(this).data('filter');if(filter==='section'){App.trackerSectionFilter='';App.trackerTaskFilter='';}else if(filter==='task'){App.trackerTaskFilter='';}trackerDraw();});
$(document).on('click','#tracker-back',function(){trackerSite=0;App.trackerDetailSearch='';trackerDraw();});
$(document).on('input','#tracker-search',function(){App.trackerSearch=$(this).val();const start=this.selectionStart;trackerDraw();const x=document.getElementById('tracker-search');if(x){x.focus();x.setSelectionRange(start,start);}});
$(document).on('input','#tracker-detail-search',function(){App.trackerDetailSearch=$(this).val();const start=this.selectionStart;trackerDraw();const x=document.getElementById('tracker-detail-search');if(x){x.focus();x.setSelectionRange(start,start);}});
function trackerActions(taskId,subId=0){return `<div class="tracker-actions"><button type="button" class="tracker-action-btn tracker-action-edit" data-task="${taskId}" data-sub="${subId}" title="Edit" aria-label="Edit"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z"/></svg></button><button type="button" class="tracker-action-btn tracker-action-delete" data-task="${taskId}" data-sub="${subId}" title="Delete" aria-label="Delete"><svg viewBox="0 0 24 24" width="19" height="19" fill="currentColor" aria-hidden="true"><path d="M8 2h8l1 2h4v2H3V4h4l1-2Zm-3 6h14l-1 13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 8Zm4 3v8h2v-8H9Zm4 0v8h2v-8h-2Z"/></svg></button></div>`;}
function trackerModal({mode='task',taskId=0,section='',taskName='' }={}){
  const site=(App.trackerSites||[]).find(s=>+s.id===+trackerSite);
  if(!site)return showToast('Select a Site first',true);
  const sections=site.sections||[];
  if(mode==='task'&&!sections.length)return showToast('Assign Sections to the Site in Site Master first',true);
  $('#tracker-entry-overlay').remove();
  const isTask=mode==='task';
  const sectionOptions=sections.map(x=>`<option value="${esc(x)}" ${x===section?'selected':''}>${esc(x)}</option>`).join('');
  const sectionBlock=isTask?`<label><span class="tracker-field-label">Section <span class="tracker-required">*</span></span><select name="section" required><option value="">Select Section</option>${sectionOptions}</select></label>`:`<label>Section<input value="${esc(section)}" readonly></label><label>Task<input value="${esc(taskName)}" readonly></label>`;
  const nameBlock=isTask?`<label><span class="tracker-field-label">Task Name <span class="tracker-required">*</span></span><input name="task_name" maxlength="255" required placeholder="Enter Task name"></label>`:'';
  const modal=`<div id="tracker-entry-overlay" class="tracker-modal-overlay"><div class="tracker-modal" role="dialog" aria-modal="true" aria-labelledby="tracker-modal-title"><div class="tracker-modal-title"><h3 id="tracker-modal-title">${isTask?'Add Task':'Add Sub-task'}</h3><button type="button" class="tracker-modal-close" aria-label="Close">×</button></div><form id="tracker-entry-form" data-mode="${mode}" data-task="${taskId}">${sectionBlock}${nameBlock}<div class="tracker-subtask-group"><span class="tracker-field-label">Sub-task Name <span class="tracker-required">*</span></span><div class="tracker-subtask-rows"><div class="tracker-subtask-row"><input name="subtask_names[]" maxlength="255" required placeholder="Enter Sub-task name"><button type="button" class="tracker-subtask-add" title="Add another Sub-task" aria-label="Add another Sub-task">＋</button></div></div></div><div class="tracker-modal-actions"><button type="button" class="bsm-btn tracker-modal-cancel">Cancel</button><button type="submit" class="bsm-btn bsm-btn-primary" id="tracker-modal-save">Save</button></div></form></div></div>`;
  $('body').append(modal);
  $('#tracker-entry-form input[name="task_name"],#tracker-entry-form input[name="subtask_names[]"]').first().trigger('focus');
}
$(document).on('click','.tracker-add-task,#tracker-add-task-main',function(){trackerModal({mode:'task'});});
$(document).on('click','.tracker-add-subtask',function(){const id=Number($(this).data('task'));const site=(App.trackerSites||[]).find(s=>+s.id===+trackerSite);const task=(site?.tasks||[]).find(t=>+t.id===id);if(!task)return showToast('Task not found',true);trackerModal({mode:'subtask',taskId:id,section:task.section,taskName:task.task_name});});

$(document).on('click','.tracker-action-edit',function(){
 const taskId=Number($(this).data('task'));
 const site=(App.trackerSites||[]).find(x=>+x.id===+trackerSite),task=(site?.tasks||[]).find(x=>+x.id===taskId);
 if(!task)return showToast('Task not found',true);
 $('#tracker-edit-overlay').remove();
 const allSub=(task.subtasks||[]).map(sub=>`<div class="tracker-subtask-row tracker-edit-row" data-id="${sub.id}"><input name="existing_subtask" maxlength="255" required value="${esc(sub.subtask_name)}" aria-label="Sub-task name"></div>`).join('');
 const opts=(site.sections||[]).map(x=>`<option value="${esc(x)}" ${x===task.section?'selected':''}>${esc(x)}</option>`).join('');
 const body=`<label><span class="tracker-field-label">Section <span class="tracker-required">*</span></span><select name="section" required>${opts}</select></label><label><span class="tracker-field-label">Task Name <span class="tracker-required">*</span></span><input name="task_name" maxlength="255" required value="${esc(task.task_name)}"></label><div class="tracker-subtask-group"><div class="tracker-edit-subtitle"><span class="tracker-field-label">Sub-task Names</span><button type="button" class="tracker-edit-add" title="Add Sub-task" aria-label="Add Sub-task">＋</button></div><div class="tracker-subtask-rows">${allSub}</div></div>`;
 $('body').append(`<div id="tracker-edit-overlay" class="tracker-modal-overlay"><div class="tracker-modal" role="dialog" aria-modal="true" aria-labelledby="tracker-edit-heading"><div class="tracker-modal-title"><h3 id="tracker-edit-heading">EDIT</h3><button type="button" class="tracker-edit-cancel" aria-label="Close">×</button></div><form id="tracker-edit-form" data-task="${taskId}">${body}<div class="tracker-modal-actions"><button type="button" class="bsm-btn tracker-edit-cancel">Cancel</button><button type="submit" class="bsm-btn bsm-btn-primary">Save Changes</button></div></form></div></div>`);
 $('#tracker-edit-form [name="task_name"]').trigger('focus');
});
$(document).on('click','.tracker-edit-add',function(){
 const rows=$('#tracker-edit-form .tracker-subtask-rows');
 if(rows.children().length>=100)return showToast('Maximum 100 Sub-tasks per Task',true);
 rows.append('<div class="tracker-subtask-row tracker-edit-row"><input name="new_subtask" maxlength="255" required placeholder="Enter Sub-task name"><button type="button" class="tracker-edit-remove" title="Remove new Sub-task" aria-label="Remove new Sub-task">×</button></div>');
 rows.children().last().find('input').trigger('focus');
});
$(document).on('click','.tracker-edit-remove',function(){$(this).closest('.tracker-edit-row').remove();});
// Enter must NOT save the Site Tracker forms. Only the Save button saves.
// Block both this handler's old behaviour and the browser's default Enter-to-submit.
$(document).on('keydown','#tracker-entry-form input,#tracker-entry-form select,#tracker-edit-form input,#tracker-edit-form select',function(e){
 if(e.key!=='Enter'||e.isComposing||e.originalEvent?.isComposing)return;
 e.preventDefault();
});

$(document).on('click','.tracker-edit-cancel',()=>$('#tracker-edit-overlay').remove());
$(document).on('submit','#tracker-edit-form',async function(e){
 e.preventDefault();const form=$(this),taskId=Number(form.data('task'));
 const taskName=(form.find('[name="task_name"]').val()||'').trim(),section=form.find('[name="section"]').val()||'';
 const existing=[],added=[];
 form.find('.tracker-edit-row').each(function(){const name=$(this).find('input').val().trim(),id=Number($(this).data('id'));if(id)existing.push({id,name});else added.push(name);});
 if(!taskName||!section||existing.some(x=>!x.name)||added.some(x=>!x))return showToast('Fill in all required fields',true);
 const btn=form.find('[type="submit"]');btn.prop('disabled',true).text('Saving…');
 try{const r=await ajax('bsm_project_tracker_save',{type:'edit_task_full',id:taskId,task_name:taskName,section,existing_subtasks:existing,new_subtasks:added});if(!r.success)return showToast(r.data?.message||'Unable to save',true);$('#tracker-edit-overlay').remove();await trackerLoad();showToast('Updated successfully');}
 catch(err){showToast('Unable to update',true);}finally{btn.prop('disabled',false).text('Save Changes');}
});
$(document).on('click','.tracker-action-delete',async function(){const taskId=Number($(this).data('task')),subId=Number($(this).data('sub'));const isSub=!!subId;if(!window.confirm(isSub?'Delete this Sub-task permanently?':'Delete this Task and all its Sub-tasks permanently?'))return;const btn=$(this);btn.prop('disabled',true);try{const r=await ajax('bsm_project_tracker_save',{type:isSub?'delete_subtask':'delete_task',id:isSub?subId:taskId});if(!r.success)return showToast(r.data?.message||'Unable to delete',true);await trackerLoad();showToast('Deleted successfully');}catch(err){showToast('Unable to delete',true);}finally{btn.prop('disabled',false);}});
$(document).on('click','.tracker-modal-close,.tracker-modal-cancel',()=>$('#tracker-entry-overlay').remove());
$(document).on('click','.tracker-subtask-add',function(){
  const rows=$(this).closest('.tracker-subtask-group').find('.tracker-subtask-rows');
  if(rows.children().length>=100)return showToast('Maximum 100 Sub-tasks at a time',true);
  rows.append('<div class="tracker-subtask-row"><input name="subtask_names[]" maxlength="255" required placeholder="Enter Sub-task name"><button type="button" class="tracker-subtask-remove" aria-label="Remove Sub-task">−</button></div>');
  rows.children().last().find('input').trigger('focus');
});
$(document).on('click','.tracker-subtask-remove',function(){$(this).closest('.tracker-subtask-row').remove();});
$(document).on('submit','#tracker-entry-form',async function(e){
  e.preventDefault();const form=$(this),mode=form.data('mode'),taskId=Number(form.data('task'));
  const names=form.find('input[name="subtask_names[]"]').map(function(){return $(this).val().trim();}).get();
  if(!names.length||names.some(x=>!x))return showToast('Enter all Sub-task Names',true);
  const params=mode==='task'?{type:'task_with_subtasks',site_id:trackerSite,section:form.find('[name="section"]').val(),name:form.find('[name="task_name"]').val().trim(),subtask_names:names}:{type:'subtasks',task_id:taskId,subtask_names:names};
  if(mode==='task'&&(!params.section||!params.name))return showToast('Select Section and enter Task name',true);
  const btn=form.find('#tracker-modal-save');btn.prop('disabled',true).text('Saving…');
  try{const r=await ajax('bsm_project_tracker_save',params);if(!r.success)return showToast(r.data?.message||'Unable to save',true);$('#tracker-entry-overlay').remove();await trackerLoad();showToast('Saved successfully');}
  catch(err){showToast('Unable to save. Please try again.',true);}
  finally{btn.prop('disabled',false).text('Save');}
});
$(document).on('change','.tracker-value',async function(){const el=this,id=Number($(el).data('id')),value=Number(el.value);if(!Number.isFinite(value)||value<0||value>100){showToast('Progress must be 0 to 100',true);return trackerDraw();}el.disabled=true;const r=await ajax('bsm_project_tracker_save',{type:'progress',id,completion:value});if(!r.success)showToast(r.data?.message||'Unable to save Progress',true);await trackerLoad();});
$(document).on('click','#tracker-excel',function(){const sites=(App.trackerSites||[]).filter(s=>!trackerSite||+s.id===+trackerSite);const rows=[];sites.forEach(site=>(site.tasks||[]).filter(t=>!trackerSite||((!App.trackerSectionFilter||t.section===App.trackerSectionFilter)&&(!App.trackerTaskFilter||String(t.id)===String(App.trackerTaskFilter)))).forEach(t=>(t.subtasks||[]).forEach(sub=>rows.push([site.site_name,t.section,t.task_name,sub.subtask_name,sub.completion]))));downloadExcelFile('Project Tracker',['Site','Section','Task','Sub-task','Progress %'],rows);});

async function openView(key){
  const allowed=(BSM.currentUser&&BSM.currentUser.is_admin)?null:new Set((BSM.currentUser&&BSM.currentUser.allowed_views)||[]);
  if(allowed!==null && key!=='my_salary_slips' && (key==='company_settings' || key==='view_settings' || !allowed.has(key))){ showToast('You do not have permission to access this view.',true); return; }
  if(isViewHidden(key)){const fb=firstVisibleView();if(fb&&fb!==key)return openView(fb);$('#bsm-content').html('<div class="bsm-empty">This view is hidden. An administrator can turn it on in Settings → Show / Hide Views.</div>');return;}
  exitInlineFormMode();
  App.current=key;App.page=1;App.search=''; $('.bsm-nav-item').removeClass('active');$(`.bsm-nav-item[data-view="${key}"]`).addClass('active');$('.bsm-sidebar').removeClass('open');
  if(['holiday_list','time_sheet','time_sheet_master','contractor_employees','contractor_calculated_bill','site_transfer_form','site_transfer_master'].includes(key)){await ensureOptions(['sites','contractors','units']);return renderOperations(key);}
  if(key==='view_settings')return renderViewSettings();
  if(key==='project_tracker')return renderProjectTracker();
  if(key==='dashboard'){await ensureOptions(['sites']);return renderDashboard();}
  if(key.startsWith('boq_')){await ensureOptions(['sites','items','units','sections']);return key==='boq_form'?renderBoqForm():renderBoqListing(key);}
  if(key==='purchase_form') return openDirectForm(key,'material_inwards','Purchase Form','purchase');
  if(key==='purchase_pending_approval'){await ensureOptions(optionTypesForView(key));return renderPurchasePendingApproval();}
  if(key==='purchase_register'){await ensureOptions(optionTypesForView('material_inwards'));return renderPurchaseRegister();}
  if(key==='purchase_items_register'){await ensureOptions(['vendors','categories','items','units']);return renderPurchaseItemsRegister();}
  if(key==='payment_form'){await ensureOptions(['vendors','banks']);return renderVendorPaymentForm();}
  if(key==='report_vendor_outstanding'){await ensureOptions(['vendors','sites']);return renderVendorOutstanding();}
  if(key==='pending_invoice_copy'){await ensureOptions(optionTypesForView('material_inwards'));return renderPendingInvoiceCopy();}
  if(key==='purchase_order_form'){await ensureOptions(['warehouses','vendors','items','units']);return renderPurchaseOrderForm();}
  if(key==='pending_po_items'){await ensureOptions(['warehouses','vendors','items','units']);return renderPendingPoItems();}
  if(key==='purchase_order_pending_receipt'){return renderPurchaseOrderPendingReceipt();}
  if(key==='purchase_order_master'){await ensureOptions(['vendors','warehouses','items']);return renderPurchaseOrderMaster();}
  if(key==='purchase_order_items_master'){await ensureOptions(['vendors']);return renderPurchaseOrderItemsMaster();}
  if(key==='pending_contractor_bill_copy'){await ensureOptions(['contractors','sites']);return renderPendingContractorBillCopy();}
  if(key==='requisition_form') return openDirectForm(key,'material_requisitions','Requisition Form');
  if(key==='material_requisition_item'){await ensureOptions(['sites','categories','items','units']);return renderMaterialRequisitionItem();}
  if(key==='allowance_form'){await ensureOptions(['users','allowance_types','sites','customers']);return renderAllowanceForm();}
  if(key==='pending_allowance_approval'){await ensureOptions(['users','allowance_types']);return renderAllowanceQueue('approval');}
  if(key==='pending_allowance_payment'){await ensureOptions(['users','allowance_types','vendors','sites','inwards']);return renderAllowanceQueue('payment');}
  if(key==='allowance_master'){await ensureOptions(['users','allowance_types']);return renderAllowanceQueue('all');}
  if(key==='employee_allowance_limits'){await ensureOptions(['users','allowance_types']);return renderEmployeeAllowanceLimits();}
  if(key==='dispatch_items_register'){await ensureOptions(['sites','categories','items','units']);return renderDispatchItemsRegister();}
  if(key==='consumption_form') return openDirectForm(key,'site_consumptions','Consumption Form');
  if(key==='consumption_items_register'){await ensureOptions(['sites','categories','items','units']);return renderConsumptionItemsRegister();}
  if(key==='return_form') return openDirectForm(key,'material_returns','Return Form');
  if(key==='return_receipt_master'){await ensureOptions(['sites','warehouses','users']);return renderReturnReceiptMaster();}
  if(key==='return_receipt_item_master'){await ensureOptions(['sites','warehouses','categories','items','units','users']);return renderReturnReceiptItemMaster();}
  if(key==='material_returns_item_master'){await ensureOptions(['sites','categories','items','units']);return renderMaterialReturnsItemMaster();}
  if(key==='contractor_form') return openDirectForm(key,'contractor_bills','Labour Contractor Bill Form');
  if(key==='quotation_form') return openDirectForm(key,'quotations','Quotation Form');
  if(key==='invoice_form') return openDirectForm(key,'tax_invoices','Invoice Form');
  if(key==='quotation_master') return renderSalesRegister('quotation_master');
  if(key==='quotation_items') return renderSalesRegister('quotation_items');
  if(key==='invoice_master') return renderSalesRegister('invoice_master');
  if(key==='invoice_items') return renderSalesRegister('invoice_items');
  if(key==='contractor_advance') return openDirectForm(key,'contractor_advances','Contractor Payment Form');
  if(key==='users') return renderUsers();
  if(key==='office_daily_attendance'){await ensureOptions(['users']);return renderOfficeDailyAttendance();}
  if(key==='office_attendance_master'){await ensureOptions(['users']);return renderOfficeAttendanceMaster();}
  if(key==='office_monthly_salary'){return renderOfficeMonthlySalary();}
  if(key==='company_settings') return renderCompanySettings();
  if(key==='monthly_salary_upload'){await ensureOptions(['users']);return renderMonthlySalaryUpload();}
  if(key==='salary_slips'){await ensureOptions(['users']);return renderSalarySlips('all');}
  if(key==='pending_salary_payment'){await ensureOptions(['users','vendors','sites','allowance_types']);return renderSalarySlips('pending');}
  if(key==='my_salary_slips'){return renderSalarySlips('my');}
  if(key==='pending_requisition_approval'){await ensureOptions(optionTypesForView(key));return renderPendingApproval();}
  if(key==='pending_material_dispatch'){await ensureOptions(optionTypesForView(key));return renderPendingDispatch();}
  if(key==='pending_material_receipt'){await ensureOptions(optionTypesForView(key));return renderPendingMaterialReceipt();}
  if(key==='pending_site_receipt'){await ensureOptions(optionTypesForView(key));return renderPendingSiteReceipt();}
  if(key==='site_material_item_receipt'){await ensureOptions(['sites','categories','items','units']);return renderSiteMaterialItemReceipt();}
  if(key==='pending_site_receive_challans'){await ensureOptions(['sites','dispatches']);return renderPendingSiteReceiveChallans();}
  if(key==='ho_inventory'){await ensureOptions(['warehouses','categories','items']);return renderInventoryView('ho');}
  if(key==='site_inventory'){await ensureOptions(['sites','categories','items']);return renderInventoryView('site');}
  if(key==='daily_outward_summary'){await ensureOptions(['sites','items']);return renderDailyOutwardSummary();}
  if(key==='daily_expense_summary'){await ensureOptions(['sites','expense_categories','vendors']);return renderDailyExpenseSummary();}
  if(key==='material_stock'){await ensureOptions(optionTypesForView(key));return renderMaterialStock();}
  await ensureOptions(optionTypesForView(key));
  if(reportConfigs[key]) return renderReport(key);
  if(App.configs[key]) return renderModule(key);
}



function poVendorOptions(selected=''){
  return `<option value="">Select Supplier</option>`+(App.options.vendors||[]).map(x=>`<option value="${esc(x.id)}" ${String(x.id)===String(selected)?'selected':''}>${esc(x.label)}</option>`).join('');
}
function poItemOptions(selected=''){
  return `<option value="">Select Item</option>`+(App.options.items||[]).map(x=>`<option value="${esc(x.id)}" ${String(x.id)===String(selected)?'selected':''}>${esc(x.label)}</option>`).join('');
}

function directPoItemRow(){
  return `<tr class="po-direct-row">
    <td><select class="po-direct-item">${poItemOptions()}</select></td>
    <td class="po-direct-unit">-</td>
    <td><input type="number" class="po-direct-qty num" min="0.01" step="0.01" value=""></td>
    <td><input type="number" class="po-direct-fixed-price num" min="0" step="0.01" value="" readonly></td>
    <td><input type="number" class="po-direct-rate num" readonly></td>
    <td class="num po-direct-amount">0.00</td>
    <td class="po-direct-actions">
      <button type="button" class="bsm-icon-btn bsm-icon-primary po-direct-add" title="Add Item">+</button>
      <button type="button" class="bsm-icon-btn bsm-icon-danger po-direct-remove" title="Remove">×</button>
    </td>
  </tr>`;
}
async function renderPurchaseOrderForm(){
  setTitle('PO Form');
  const vendorOpts=poVendorOptions();
  const whOpts=`<option value="">Select Warehouse</option>`+(App.options.warehouses||[]).map(x=>`<option value="${esc(x.id)}">${esc(x.label)}</option>`).join('');
  $('#bsm-content').html(`
    <div class="bsm-panel bsm-direct-po-panel">
      <div class="bsm-form-grid">
        <label><span class="bsm-field-label">PO Date <span class="bsm-required">*</span></span><input type="date" lang="en-GB" id="po_direct_date" value="${esc(BSM.today||'')}"></label>
        <label><span class="bsm-field-label">Warehouse <span class="bsm-required">*</span></span><select id="po_direct_warehouse">${whOpts}</select></label>
        <label><span class="bsm-field-label">Required Date</span><input type="date" lang="en-GB" id="po_direct_required"></label>
        <label><span class="bsm-field-label">Created By</span><input type="text" id="po_direct_created_by" value="${esc((BSM.currentUser&&BSM.currentUser.name)||'')}" readonly></label>
      </div>
      <div class="bsm-form-grid po-header-quotes">
        ${[1,2,3].map(n=>`<label>Supplier ${n} <select class="po-direct-quote-supplier" data-slot="${n}">${vendorOpts}</select></label><label>Rate ${n} <input type="number" class="po-direct-quote-rate" data-slot="${n}" min="0" step="0.01"></label>`).join('')}
        <label>Chosen Supplier <span class="bsm-required">*</span><select id="po_direct_vendor"><option value="">Select quoted supplier</option></select></label><label>Chosen Rate <input type="number" class="po-direct-chosen-rate" readonly></label>
      </div>
      <div class="bsm-lines">
        <div class="bsm-lines-head"><h3>Items</h3></div>
        <div class="bsm-table-wrap">
          <table>
            <thead><tr><th>Item</th><th>Unit</th><th class="num">PO Qty</th><th class="num">Fixed Price</th><th class="num">Rate</th><th class="num">Amount</th><th></th></tr></thead>
            <tbody id="po_direct_items">${directPoItemRow()}</tbody>
          </table>
        </div>
      </div>
      <div class="bsm-form-actions">
        <button type="button" class="bsm-btn bsm-btn-secondary" id="po_direct_clear">Clear</button>
        <button type="button" class="bsm-btn bsm-btn-primary" id="po_direct_save">Create PO</button>
      </div>
    </div>`);
  bsmEnhanceSearchableSelects($('#bsm-content'));
}
async function refreshDirectPoItemRow($row){
  const iid=$row.find('.po-direct-item').val();
  if(!iid){$row.find('.po-direct-unit').text('-');$row.find('.po-direct-fixed-price,.po-direct-rate').val('');$row.find('.po-direct-amount').text('0.00');return;}
  const item=(App.options.items||[]).find(x=>String(x.id)===String(iid))||{};
  const unitLabel=labelFor('units',item.unit_id)||item.unit_name||'-';
  $row.find('.po-direct-unit').text(unitLabel);
  const fixedPrice=Number(item.opening_rate||item.price||0);
  if(!$row.find('.po-direct-fixed-price').val())$row.find('.po-direct-fixed-price').val(fixedPrice.toFixed(2));
  syncDirectPoChosen($row);
  updateDirectPoAmount($row);
}
function updateDirectPoAmount($row){
  const qty=Number($row.find('.po-direct-qty').val()||0);
  const rate=Number($row.find('.po-direct-rate').val()||0);
  $row.find('.po-direct-amount').text((qty*rate).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}));
}

async function renderPendingPoItems(){
  setTitle('Approved Requisition Pending PO');
  $('#bsm-content').html(`<div id="po_pending_list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);await loadPendingPoItems();
}
async function loadPendingPoItems(exportAll=false){
  const r=await ajax('bsm_pending_po_items',{});if(!r.success){if(!exportAll)$('#po_pending_list').html('<div class="bsm-empty">Unable to load Pending PO Items.</div>');return null;}const rows=r.data.rows||[];if(exportAll)return rows;App.pendingPoRows=rows;App.pendingPoWarehouse=r.data.warehouse_id||'';const num=v=>Number(v||0).toLocaleString('en-IN',{maximumFractionDigits:2});
  let h='<div class="bsm-table-wrap po-pending-table-wrap"><table class="po-pending-table po-three-quotes"><thead><tr><th>Item</th><th>Unit</th><th class="num">Approved Qty</th><th class="num">Current Stock</th><th class="num">Pending PO Qty</th><th>Supplier 1</th><th>Rate 1</th><th>Supplier 2</th><th>Rate 2</th><th>Supplier 3</th><th>Rate 3</th><th>Choose Supplier</th><th>Choose Rate</th><th>PO Qty</th></tr></thead><tbody>';
  rows.forEach(x=>{
    h+=`<tr class="po-pending-summary-row" data-item="${x.item_id}" tabindex="0" aria-expanded="false" title="Click to view pending requisition details"><td><strong>${esc(x.item_name||'')}</strong></td><td>${esc(x.unit_name||'')}</td><td class="num">${num(x.approved_qty)}</td><td class="num" title="Physical Stock ${num(x.physical_stock)} + PO in Progress ${num(x.po_in_progress_qty)}">${num(x.current_stock)}</td><td class="num"><strong>${num(x.pending_po_qty)}</strong></td>${[1,2,3].map(n=>`<td><select class="po-quote-supplier" data-slot="${n}">${poVendorOptions()}</select></td><td><input type="number" class="po-quote-rate num" data-slot="${n}" min="0" step="0.01"></td>`).join('')}<td><select class="po-row-vendor"><option value="">Select quoted supplier</option></select></td><td><input type="number" class="po-row-rate num" readonly></td><td><input type="number" class="po-row-qty num" min="0" step="0.01" max="${Number(x.pending_po_qty||0)}" value="${Number(x.pending_po_qty||0).toFixed(2)}"></td></tr>`;
    const details=x.details||[];
    let d='<div class="bsm-po-pending-details"><table><thead><tr><th>Site</th><th>Requisition No.</th><th>Item</th><th class="num">Approved Qty</th><th>Approved By</th><th>Requested By</th><th>Requisition Date</th><th>Required Date</th></tr></thead><tbody>';
    details.forEach(y=>{d+=`<tr><td>${esc(y.site_name||'')}</td><td>${esc(y.requisition_no||'')}</td><td>${esc(y.item_name||x.item_name||'')}</td><td class="num">${num(y.approved_qty)}</td><td>${esc(y.approved_by_name||'-')}</td><td>${esc(y.requested_by_name||'-')}</td><td>${dateFmt(y.requisition_date)}</td><td>${dateFmt(y.required_date)}</td></tr>`;});
    if(!details.length)d+='<tr><td colspan="9" class="bsm-empty">No pending requisition details.</td></tr>';d+='</tbody></table></div>';
    h+=`<tr class="po-pending-detail-row" data-detail-for="${x.item_id}" style="display:none"><td colspan="14">${d}</td></tr>`;
  });
  if(!rows.length)h+='<tr><td colspan="14" class="bsm-empty">No approved items require a PO.</td></tr>';h+='</tbody></table></div>';if(rows.length)h+='<div class="bsm-form-actions"><button class="bsm-btn bsm-btn-primary" id="po_create_selected">Create PO</button></div>';$('#po_pending_list').html(h);bsmEnhanceSearchableSelects($('#po_pending_list'));poWrapSupplierFields();return rows;
}
async function renderPurchaseOrderPendingReceipt(){
  setTitle('Ordered but Not Received');$('#bsm-content').html(`<div class="bsm-toolbar"><input id="po_receipt_search" class="bsm-search" aria-label="Search Ordered but Not Received" placeholder=""><div class="bsm-spacer"></div></div><div id="po_receipt_list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);await loadPurchaseOrderPendingReceipt();
}
async function loadPurchaseOrderPendingReceipt(){
  const r=await ajax('bsm_purchase_order_pending_receipt',{});if(!r.success){$('#po_receipt_list').html('<div class="bsm-empty">Unable to load Pending Receipt.</div>');return;}
  let itemRows=r.data.rows||[];const q=String($('#po_receipt_search').val()||'').toLowerCase().trim();const groups={};
  itemRows.forEach(x=>{const k=String(x.purchase_order_id||'');if(!groups[k])groups[k]={purchase_order_id:x.purchase_order_id,po_no:x.po_no,po_date:x.po_date,vendor_id:x.vendor_id,vendor_name:x.vendor_name,warehouse_id:x.warehouse_id,warehouse_name:x.warehouse_name,items:[],po_qty:0,received_qty:0,pending_qty:0};groups[k].items.push(x);groups[k].po_qty+=Number(x.po_qty||0);groups[k].received_qty+=Number(x.received_qty||0);groups[k].pending_qty+=Number(x.pending_qty||0);});
  let rows=Object.values(groups);if(q)rows=rows.filter(x=>`${x.po_no||''} ${x.vendor_name||''} ${(x.items||[]).map(i=>i.item_name||'').join(' ')}`.toLowerCase().includes(q));App.pendingReceiptGroups=rows;
  const num=v=>Number(v||0).toLocaleString('en-IN',{maximumFractionDigits:2});let h='<div class="bsm-table-wrap"><table><thead><tr><th>PO No.</th><th>PO Date</th><th>Supplier</th><th class="num">PO Qty</th><th class="num">Received Qty</th><th class="num">Pending Receipt Qty</th><th>Action</th></tr></thead><tbody>';
  rows.forEach(x=>{h+=`<tr class="po-receipt-summary-row" data-po="${x.purchase_order_id}" tabindex="0" aria-expanded="false" title="Click to view PO Items"><td><strong>${esc(x.po_no||'')}</strong></td><td>${dateFmt(x.po_date)}</td><td>${esc(x.vendor_name||'')}</td><td class="num">${num(x.po_qty)}</td><td class="num">${num(x.received_qty)}</td><td class="num"><strong>${num(x.pending_qty)}</strong></td><td><button class="bsm-btn bsm-btn-small bsm-btn-primary po-receive-po" data-po="${x.purchase_order_id}">Receive</button></td></tr>`;let d='<div class="bsm-po-pending-details"><table><thead><tr><th>Item</th><th>Unit</th><th class="num">PO Qty</th><th class="num">Received Qty</th><th class="num">Pending Receipt Qty</th></tr></thead><tbody>';(x.items||[]).forEach(it=>{d+=`<tr><td>${esc(it.item_name||'')}</td><td>${esc(it.unit_name||'')}</td><td class="num">${num(it.po_qty)}</td><td class="num">${num(it.received_qty)}</td><td class="num"><strong>${num(it.pending_qty)}</strong></td></tr>`;});d+='</tbody></table></div>';h+=`<tr class="po-receipt-detail-row" data-detail-for="${x.purchase_order_id}" style="display:none"><td colspan="7">${d}</td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="7" class="bsm-empty">No Purchase Orders pending receipt.</td></tr>';h+='</tbody></table></div>';$('#po_receipt_list').html(h);
}
async function prepareRequiredPurchasePOSelector(){
  const $grid=$('#bsm-form-fields');
  $('#bsm-form-save').prop('disabled',true);
  const r=await ajax('bsm_purchase_order_pending_receipt',{});
  if(!r.success){
    $grid.prepend(`<label class="bsm-po-required-select"><span class="bsm-field-label">PO No. <span class="bsm-required">*</span></span><select id="bsm-purchase-required-po" required><option value="">Unable to load PO</option></select></label>`);
    return;
  }
  const groups={};
  (r.data.rows||[]).forEach(x=>{
    if(Number(x.pending_qty||0)<=0.009)return;
    const k=String(x.purchase_order_id||'');
    if(!groups[k])groups[k]={purchase_order_id:x.purchase_order_id,po_no:x.po_no,po_date:x.po_date,vendor_id:x.vendor_id,vendor_name:x.vendor_name,warehouse_id:x.warehouse_id,warehouse_name:x.warehouse_name,items:[]};
    groups[k].items.push(x);
  });
  App.pendingReceiptGroups=Object.values(groups);
  const opts=App.pendingReceiptGroups.map(x=>`<option value="${esc(x.purchase_order_id)}">${esc(x.po_no||'')} — ${esc(x.vendor_name||'')}</option>`).join('');
  $grid.prepend(`<label class="bsm-po-required-select"><span class="bsm-field-label">PO No. <span class="bsm-required">*</span></span><select id="bsm-purchase-required-po" required><option value="">Select PO</option>${opts}</select></label>`);
  if(!App.pendingReceiptGroups.length)showToast('No Purchase Order is pending receipt. Purchase cannot be entered without a PO.',true);
}
async function openPurchaseFormFromPO(poId){const g=(App.pendingReceiptGroups||[]).find(x=>String(x.purchase_order_id)===String(poId));if(!g)return showToast('Purchase Order not found',true);App.poReceiptPrefill=g;await openView('purchase_form');}
async function renderPurchaseOrderMaster(){
  setTitle('PO Master');
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="po_master_search" class="bsm-search" aria-label="Search PO Master" placeholder="">${selectHtml('po_master_vendor','Supplier','vendors','')}<label>Status<select id="po_master_status"><option value="">All</option><option value="created">Created</option><option value="sent">Sent</option><option value="partly_received">Partly Received</option><option value="fully_received">Fully Received</option><option value="cancelled">Cancelled</option></select></label><div class="bsm-spacer"></div>${excelButton('po_master_export')}</div><div id="po_master_list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);await loadPurchaseOrderMaster();
}
function poMasterQuotes(row){
  let parsed={};try{parsed=JSON.parse(row.remarks||'{}');}catch(e){}
  const lines=Array.isArray(parsed.supplier_quotations)?parsed.supplier_quotations:[];
  const unique=values=>[...new Set(values.filter(v=>v!==''&&v!=null))].join(' / ')||'—';
  const out=[];
  for(let n=0;n<3;n++){const quotes=lines.map(line=>Array.isArray(line.quotations)?line.quotations[n]:null).filter(Boolean);out.push(unique(quotes.map(q=>labelFor('vendors',q.supplier_id)||'')));out.push(unique(quotes.map(q=>q.rate==null?'':Number(q.rate).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}))));}
  out.push(row.vendor_name||'—');out.push(unique(lines.map(line=>line.selected_rate==null?'':Number(line.selected_rate).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}))));return out;
}
async function loadPurchaseOrderMaster(exportAll=false){
  const r=await ajax('bsm_purchase_order_master',{search:$('#po_master_search').val()||'',vendor_id:$('#po_master_vendor').val()||'',status:$('#po_master_status').val()||''});if(!r.success){if(!exportAll)$('#po_master_list').html('<div class="bsm-empty">Unable to load PO Master.</div>');return null;}const rows=r.data.rows||[];if(exportAll)return rows;const amt=v=>Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>PO No.</th><th>PO Date</th><th>Supplier</th><th>Warehouse</th><th>Required Date</th><th>Items</th><th class="num">PO Qty</th><th class="num">Received</th><th class="num">Pending</th><th class="num">Total</th><th>Status</th><th>Created By</th><th>Action</th></tr></thead><tbody>';rows.forEach((x,i)=>{const pdf=`<button type="button" class="bsm-pdf-link po-pdf" data-id="${x.id}" title="Download PO PDF" aria-label="Download PO PDF"><svg class="bsm-pdf-icon" viewBox="0 0 32 36" aria-hidden="true"><path d="M6.5 1.5h12l7 7v25H6.5z" fill="#fff" stroke="#ef2020" stroke-width="2" stroke-linejoin="round"/><path d="M18.5 1.5v7h7" fill="none" stroke="#ef2020" stroke-width="2" stroke-linejoin="round"/><rect x="3" y="19" width="26" height="11" rx="2" fill="#ef2020"/><text x="16" y="27.1" text-anchor="middle" font-size="8.3" font-weight="700" font-family="Arial,Helvetica,sans-serif" fill="#fff">PDF</text></svg></button>`;const edit=String(x.status||'')!=='cancelled'?`<button type="button" class="bsm-action-edit po-edit" data-id="${x.id}" title="Edit PO">✎</button>`:'';const cancel=(Number(x.received_qty||0)<=0.009&&!['cancelled','fully_received'].includes(String(x.status||'')))?`<button class="bsm-btn bsm-btn-small bsm-btn-danger po-cancel" data-id="${x.id}">Cancel</button>`:'';h+=`<tr><td>${i+1}</td><td>${esc(x.po_no||'')}</td><td>${dateFmt(x.po_date)}</td><td>${esc(x.vendor_name||'')}</td><td>${esc(x.warehouse_name||'')}</td><td>${dateFmt(x.required_date)}</td><td class="num">${Number(x.item_count||0)}</td><td class="num">${Number(x.po_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td class="num">${Number(x.received_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td class="num">${Number(x.pending_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td class="num">${amt(x.total_amount)}</td><td><span class="bsm-pill">${esc(String(x.status||'').replaceAll('_',' '))}</span></td><td>${esc(x.created_by_name||'-')}</td><td class="bsm-actions">${pdf}${edit}${cancel}</td></tr>`;});if(!rows.length)h+='<tr><td colspan="14" class="bsm-empty">No Purchase Orders found.</td></tr>';h+='</tbody></table></div>';$('#po_master_list').html(h);return rows;
}
async function renderPurchaseOrderItemsMaster(){
  setTitle('PO Items Master');
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="po_items_search" class="bsm-search" aria-label="Search PO Items Master" placeholder="">${selectHtml('po_items_vendor','Supplier','vendors','')}<div class="bsm-spacer"></div>${excelButton('po_items_export')}</div><div id="po_items_list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);await loadPurchaseOrderItemsMaster();
}
function poItemQuotes(row){
  let parsed={};try{parsed=JSON.parse(row.remarks||'{}');}catch(e){}
  const all=Array.isArray(parsed.supplier_quotations)?parsed.supplier_quotations:[];
  const lines=all.filter(line=>String(line.item_id)===String(row.item_id));
  return poMasterQuotes({...row,remarks:JSON.stringify({supplier_quotations:lines})});
}
async function loadPurchaseOrderItemsMaster(exportAll=false){
  const r=await ajax('bsm_purchase_order_items_master',{search:$('#po_items_search').val()||'',vendor_id:$('#po_items_vendor').val()||''});if(!r.success){if(!exportAll)$('#po_items_list').html('<div class="bsm-empty">Unable to load PO Items Master.</div>');return null;}const rows=r.data.rows||[];if(exportAll)return rows;const amt=v=>Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>PO No.</th><th>PO Date</th><th>Supplier</th><th>Item</th><th>Unit</th><th>Supplier 1</th><th>Rate 1</th><th>Supplier 2</th><th>Rate 2</th><th>Supplier 3</th><th>Rate 3</th><th>Chosen Supplier</th><th>Chosen Rate</th><th class="num">Fixed Price</th><th class="num">Rate</th><th class="num">PO Qty</th><th class="num">Received Qty</th><th class="num">Pending Qty</th><th class="num">Amount</th><th>Status</th></tr></thead><tbody>';rows.forEach((x,i)=>h+=`<tr><td>${i+1}</td><td>${esc(x.po_no||'')}</td><td>${dateFmt(x.po_date)}</td><td>${esc(x.vendor_name||'')}</td><td>${esc(x.item_name||'')}</td><td>${esc(x.unit_name||'')}</td>${poItemQuotes(x).map(v=>`<td>${esc(v)}</td>`).join('')}<td class="num">${amt(x.fixed_price)}</td><td class="num">${amt(x.rate)}</td><td class="num">${Number(x.po_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td class="num">${Number(x.received_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td class="num">${Number(x.pending_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td class="num">${amt(Number(x.po_qty||0)*Number(x.rate||0))}</td><td>${esc(String(x.status||'').replaceAll('_',' '))}</td></tr>`);if(!rows.length)h+='<tr><td colspan="21" class="bsm-empty">No PO Item records found.</td></tr>';h+='</tbody></table></div>';$('#po_items_list').html(h);return rows;
}

function vendorOptionHtml(selected=''){
  return `<option value="">Select Supplier</option>`+(App.options.vendors||[]).map(x=>`<option value="${esc(x.id)}" ${String(x.id)===String(selected)?'selected':''}>${esc(x.label)}</option>`).join('');
}
async function fetchVendorOutstanding(extra={}){
  return ajax('bsm_vendor_outstanding',Object.assign({site_id:$('#bsm-vo-site').val()||'',vendor_id:$('#bsm-vo-vendor').val()||'',date_from:dateSql($('#bsm-vo-date-from').val()||''),date_to:dateSql($('#bsm-vo-date-to').val()||''),status:'',search:$('#bsm-vo-search').val()||'',summary:'1'},extra));
}
async function renderVendorOutstanding(){
  setTitle('Supplier Outstanding');
  const filters=`<div class="bsm-filters report bsm-vendor-outstanding-filters">${selectHtml('bsm-vo-site','Site','sites','')}<label>Supplier<select id="bsm-vo-vendor">${vendorOptionHtml()}</select></label><label>Date From<input type="date" lang="en-GB" id="bsm-vo-date-from"></label><label>Date To<input type="date" lang="en-GB" id="bsm-vo-date-to"></label><button class="bsm-icon-btn bsm-purchase-clear-btn" id="bsm-vo-clear" title="Clear Filters">${clearIcon()}</button></div>`;
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-vo-search" class="bsm-search" aria-label="Search Supplier Outstanding" placeholder=""><div class="bsm-spacer"></div>${excelButton('bsm-vo-export')}</div>${filters}<div id="bsm-vendor-outstanding-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  await loadVendorOutstanding();
}
async function loadVendorOutstanding(exportAll=false){
  const r=await fetchVendorOutstanding();if(!r.success){if(!exportAll)$('#bsm-vendor-outstanding-list').html('<div class="bsm-empty">Unable to load Supplier Outstanding.</div>');return null;}
  const rows=r.data.rows||[];if(exportAll)return rows;
  let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Supplier</th><th class="num">Pending POs</th><th class="num">Purchase Amount</th><th class="num">Paid Amount</th><th class="num">Outstanding</th><th>Action</th></tr></thead><tbody>';
  rows.forEach((x,i)=>{h+=`<tr><td>${i+1}</td><td>${esc(x.vendor_name||'')}</td><td class="num">${Number(x.purchase_count||0).toLocaleString('en-IN')}</td><td class="num">${moneyFmt(x.purchase_amount)}</td><td class="num">${moneyFmt(x.paid_amount)}</td><td class="num">${moneyFmt(x.outstanding)}</td><td>${Number(x.outstanding||0)>0.009?`<button class="bsm-btn bsm-btn-primary bsm-btn-small bsm-vendor-pay" data-vendor="${x.vendor_id}">Pay</button>`:'-'}</td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="7" class="bsm-empty">No pending vendor outstanding.</td></tr>';h+='</tbody></table></div>';$('#bsm-vendor-outstanding-list').html(h);return rows;
}
async function renderVendorPaymentForm(prefill={}){
  // Supplier Outstanding launches this form without loading Bank Master options first.
  await ensureOptions(['vendors','banks'],true);
  setTitle('Payment Form');
  const today=BSM.today||'';
  const bankOptions=(App.options.banks||[]).map(x=>`<option value="${esc(x.id)}">${esc(x.label)}</option>`).join('');
  const missingBanks=!(App.options.banks||[]).length;
  $('#bsm-content').html(`<div class="bsm-panel bsm-vendor-payment-form"><div class="bsm-form-grid bsm-payment-top-grid"><label><span class="bsm-field-label">Payment Date</span><input type="date" lang="en-GB" id="bsm-pay-date" value="${esc(today)}"></label><label><span class="bsm-field-label">Supplier</span><select id="bsm-pay-vendor">${vendorOptionHtml(prefill.vendor_id||'')}</select></label><label><span class="bsm-field-label">Payment Mode</span><select id="bsm-pay-mode"><option value="Bank">Bank</option><option value="UPI">UPI</option><option value="Cheque">Cheque</option><option value="Cash">Cash</option><option value="Other">Other</option></select></label><label id="bsm-pay-bank-wrap"><span class="bsm-field-label">Bank</span><select id="bsm-pay-bank" required><option value="">${missingBanks?'No active banks in Bank Master':'Select Bank'}</option>${bankOptions}</select></label><label><span class="bsm-field-label">Reference / UTR / Cheque No.</span><input id="bsm-pay-ref"></label><label><span class="bsm-field-label">Payment Amount</span><input id="bsm-pay-amount" class="num" type="number" min="0" step="0.01" value="${Number(prefill.amount||0).toFixed(2)}"></label><label><span class="bsm-field-label">Made By</span><input value="${esc(BSM.currentUser?.name||'')}" readonly></label><label class="wide"><span class="bsm-field-label">Remarks</span><textarea id="bsm-pay-remarks"></textarea></label><label class="wide"><span class="bsm-field-label">Upload Payment Photo</span><input id="bsm-vendor-payment-photo" type="file" accept="image/jpeg,image/png,image/webp,application/pdf"></label></div><div class="bsm-form-actions"><button class="bsm-btn bsm-btn-primary" id="bsm-save-vendor-payment">Save Payment</button><button class="bsm-btn bsm-btn-cancel" id="bsm-cancel-vendor-payment">Cancel</button></div></div>`);
  bsmEnhanceSearchableSelects($('#bsm-content'));
  $('#bsm-pay-bank-wrap').show();
  if(missingBanks) $('#bsm-pay-bank-wrap').append('<small class="bsm-field-help">No active bank accounts available. Please add or activate an account in Bank Master.</small>');
}
async function saveVendorPayment(){
  const vendor=$('#bsm-pay-vendor').val()||'',date=$('#bsm-pay-date').val()||'',amount=Number($('#bsm-pay-amount').val()||0),mode=$('#bsm-pay-mode').val()||'',bank=$('#bsm-pay-bank').val()||'';
  if(!vendor)return showToast('Please select Supplier',true);if(!date)return showToast('Payment Date is required',true);if(amount<=0)return showToast('Payment Amount must be greater than zero',true);if(mode==='Bank'&&!bank)return showToast('Please select Bank',true);
  const photo=$('#bsm-vendor-payment-photo')[0]?.files?.[0]||null;
  if(photo){
    const allowed=['image/jpeg','image/png','image/webp','application/pdf'];
    if(!allowed.includes(photo.type))return showToast('Please select a JPG, PNG, WebP or PDF file',true);
    if(photo.size>10*1024*1024)return showToast('Payment attachment must not exceed 10 MB',true);
  }
  const $save=$('#bsm-save-vendor-payment');$save.prop('disabled',true);
  try{
    const r=await ajax('bsm_vendor_payment_save',{vendor_id:vendor,payment_date:date,payment_mode:mode,bank_account_id:mode==='Bank'?bank:'',transaction_reference:$('#bsm-pay-ref').val()||'',remarks:$('#bsm-pay-remarks').val()||'',amount:amount});
    if(!r.success)return showToast(r.data?.message||'Unable to save Payment',true);
    if(photo){
      try{
        const fd=new FormData();fd.append('action','bsm_upload');fd.append('nonce',BSM.nonce);fd.append('module','vendor_payments');fd.append('reference_id',r.data.id);fd.append('document_type','Payment Document');fd.append('file',photo);
        const ur=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});
        if(!ur.success){showToast('Payment saved, but attachment upload failed. Add it from Payment Master.',true);return openView('report_vendor_outstanding');}
      }catch(e){showToast('Payment saved, but attachment upload failed. Add it from Payment Master.',true);return openView('report_vendor_outstanding');}
    }
    showToast(`${r.data.payment_no||'Payment'} saved successfully`);return openView('report_vendor_outstanding');
  }finally{$save.prop('disabled',false);}
}


function drawPurchasePendingApproval(){
  const q=String(App.specialSearch||'').toLowerCase().trim();
  const rows=(App.purchasePendingApprovalRows||[]).filter(x=>!q||[x.inward_no,dateFmt(x.inward_date),x.vendor_name,x.warehouse_name,x.total_amount,x.item_count].join(' ').toLowerCase().includes(q));
  let h='<div class="bsm-table-wrap"><table id="bsm-purchase-pending-approval-table"><thead><tr><th>Sl</th><th>PO No.</th><th>Date</th><th>Supplier</th><th>Warehouse</th><th class="num">Total Amount</th><th>Items</th><th>Action</th></tr></thead><tbody>';
  rows.forEach((x,i)=>{h+=`<tr><td>${i+1}</td><td>${esc(x.inward_no||'')}</td><td>${dateFmt(x.inward_date)}</td><td>${esc(x.vendor_name||'')}</td><td>${esc(x.warehouse_name||'')}</td><td class="num">${moneyFmt(x.total_amount)}</td><td>${esc(x.item_count||0)}</td><td><button class="bsm-btn bsm-btn-small bsm-btn-success bsm-review-purchase" data-id="${x.id}">Approve</button></td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="9" class="bsm-empty">No purchases pending approval.</td></tr>';
  h+='</tbody></table></div>';$('#bsm-purchase-pending-approval').html(h);
}
async function renderPurchasePendingApproval(){
  setTitle('Requisitions Pending Approval');App.specialSearch='';
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-purchase-approval-search" class="bsm-search" aria-label="Search Requisitions Pending Approval" placeholder=""><div class="bsm-spacer"></div></div><div id="bsm-purchase-pending-approval" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  const r=await ajax('bsm_purchase_pending_approval');if(!r.success){$('#bsm-purchase-pending-approval').html('<div class="bsm-empty">Unable to load pending purchases.</div>');return;}App.purchasePendingApprovalRows=r.data.rows||[];drawPurchasePendingApproval();
}
async function openPurchaseApprovalForm(id){
  const r=await ajax('bsm_purchase_approval_get',{id});if(!r.success)return showToast(r.data?.message||'Unable to load',true);const row=r.data;
  App.editId=id;$('#bsm-modal-title').text('Approve Purchase');
  $('#bsm-form-fields').removeClass('bsm-requisition-summary bsm-dispatch-summary').addClass('bsm-approval-summary').html(`<label><span class="bsm-field-label">PO No.</span><input value="${esc(row.inward_no||'')}" readonly></label><label><span class="bsm-field-label">Supplier</span><input value="${esc(row.vendor_name||'')}" readonly></label><label><span class="bsm-field-label">Warehouse</span><input value="${esc(row.warehouse_name||'')}" readonly></label><label><span class="bsm-field-label">Date</span><input value="${dateFmt(row.inward_date)}" readonly></label><label><span class="bsm-field-label">Approved By</span><input value="${esc(BSM.currentUser?.name||'')}" readonly></label>`);
  let h='<div class="bsm-lines bsm-approval-lines"><div class="bsm-table-wrap"><table><thead><tr><th>Item</th><th class="num">Qty</th><th class="num">Approved Qty</th><th>Unit</th><th class="num">Fixed Price</th><th class="num">Rate</th></tr></thead><tbody id="bsm-purchase-approval-lines">';
  (row._items||[]).forEach(it=>{const q=Number(it.qty||0),saved=Number(it.approved_qty||0),initial=saved>0?saved:q;h+=`<tr data-id="${it.id}" data-qty="${q}"><td>${esc(it.item_name||'')}</td><td class="num">${q.toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td><input class="bsm-purchase-approved-qty num" type="number" min="0" max="${q}" step="0.01" value="${initial}"></td><td>${esc(it.unit_name||'')}</td><td class="num"><input class="bsm-purchase-master-price num" type="text" value="${Number(it.master_price||0).toFixed(2)}" readonly></td><td><input class="bsm-purchase-approval-rate num" type="number" min="0" step="0.01" value="${Number(it.rate||0).toFixed(2)}"></td></tr>`;});
  h+='</tbody></table></div></div>';$('#bsm-line-items').html(h);$('#bsm-documents').empty();$('#bsm-form').data('module','purchase_approval');$('#bsm-modal').show();
}
async function savePurchaseApproval(){
  const items=[];let invalid=false;$('#bsm-purchase-approval-lines tr').each(function(){const q=Number($(this).data('qty')||0),$inp=$(this).find('.bsm-purchase-approved-qty'),aq=Number($inp.val()||0),$rate=$(this).find('.bsm-purchase-approval-rate'),rate=Number($rate.val()||0);if(aq<0||aq>q){$inp.addClass('bsm-invalid');invalid=true;}if(rate<0){$rate.addClass('bsm-invalid');invalid=true;}items.push({id:$(this).data('id'),approved_qty:aq,rate:rate});});
  if(invalid)return showToast('Approved Qty cannot exceed Qty',true);
  const id=App.editId,r=await ajax('bsm_purchase_approval_save',{id,items:JSON.stringify(items)});
  if(!r.success)return showToast(r.data?.message||'Unable to approve purchase',true);
  showToast(r.data.message||'Purchase approved');$('#bsm-modal').hide();App.purchasePendingApprovalRows=(App.purchasePendingApprovalRows||[]).filter(x=>String(x.id)!==String(id));return openView('report_vendor_outstanding');
}

async function renderPurchaseRegister(){
  const key='material_inwards',cfg=App.configs[key];
  setTitle('Purchase Master');
  const filters=`<div id="bsm-purchase-filters" class="bsm-filters" style="display:none">
    <label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="purchase_date_from"></label>
    <label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="purchase_date_to"></label>
    ${selectHtml('purchase_vendor','Supplier','vendors','')}
    <button class="bsm-icon-btn bsm-purchase-clear-btn" id="purchase_clear_filters" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button>
  </div>`;
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-search" class="bsm-search" aria-label="Search Purchase Master" placeholder="" value="${esc(App.search)}"><button id="bsm-purchase-filter-toggle" class="bsm-icon-btn" title="Filters">☷</button><div class="bsm-spacer"></div>${excelButton('bsm-export-excel')}</div>${filters}<div id="bsm-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  await loadPurchaseRegister();
}
function billTypeLabel(v){return ({gst_bill:'GST Bill',non_gst_bill:'Non GST Bill'}[String(v||'')]||'—');}
function gstTypeLabel(value){return ({intra_state:'Intra-State',inter_state:'Inter-State',no_gst:'No GST'}[String(value||'')]||'—');}
function purchaseApprovedByName(row){
  const id=String(row?.approved_by||'');
  if(!id)return '-';
  const users=App.options.users||[];
  const u=users.find(x=>String(x.id)===id)||users.find(x=>String(x.wp_user_id||'')===id);
  return u?.label||u?.name||'-';
}

async function loadPurchaseRegister(){
  const key='material_inwards',cfg=App.configs[key];
  const purchaseCols=[...cfg.columns];
  const vendorIndex=purchaseCols.findIndex(c=>c.key==='vendor_id');
  const extraCols=[C('gst_type','GST Type'),C('bill_type','Bill Type'),C('supplier_invoice_no','Supplier Invoice No.'),C('supplier_invoice_date','Supplier Invoice Date','date')];
  if(vendorIndex>=0) purchaseCols.splice(vendorIndex+1,0,...extraCols); else purchaseCols.push(...extraCols);
  const r=await ajax('bsm_list',{module:key,page:App.page,search:App.search,inward_type:'purchase',vendor_id:$('#purchase_vendor').val()||'',date_from:dateSql($('#purchase_date_from').val()||''),date_to:dateSql($('#purchase_date_to').val()||''),visible_columns:JSON.stringify(purchaseCols.map(c=>({key:c.key,type:c.type||'text'})))});
  if(!r.success){$('#bsm-list').html('<div class="bsm-empty">Unable to load Purchase Master.</div>');return;}
  const rows=r.data.rows||[]; let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th>'+purchaseCols.map(c=>`<th>${esc(c.label)}</th>`).join('')+'<th>Approved By</th><th>Invoice</th><th>Action</th></tr></thead><tbody>';
  rows.forEach((row,i)=>{
    const isApproved=String(row.status||'').toLowerCase()==='approved';
    const edit=isApproved?'':`<button class="bsm-action-edit" data-module="material_inwards" data-id="${row.id}" data-allowed="${row._can_edit!==0?1:0}" data-message="${esc(row._edit_message||'This record cannot be edited.')}" title="Edit">✎</button>`;
    const del=isApproved?'':`<button class="bsm-action-delete" data-module="material_inwards" data-id="${row.id}" data-allowed="${row._can_delete!==0?1:0}" data-message="${esc(row._delete_message||'This record cannot be deleted.')}" title="Delete"><img class="bsm-delete-icon" src="${BSM.deleteIcon}" alt="Delete"></button>`;
    const invoice=row.invoice_url?`<a class="bsm-pdf-link" href="${esc(row.invoice_url)}" target="_blank" rel="noopener" title="Open Supplier Invoice" aria-label="Open Supplier Invoice"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h8l4 4v16H6z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M14 2v5h5M8 16h8M8 12h8" fill="none" stroke="currentColor" stroke-width="1.8"/></svg></a>`:'-';
    h+=`<tr class="bsm-purchase-row" data-id="${row.id}" tabindex="0" aria-expanded="false" title="Click to view purchase items"><td>${(r.data.page-1)*20+i+1}</td>${purchaseCols.map(c=>`<td class="${c.type==='money'||c.type==='number'||c.type==='percent'?'num':''}">${renderCell(row,c)}</td>`).join('')}<td>${esc(purchaseApprovedByName(row))}</td><td class="bsm-invoice-cell">${invoice}</td><td class="bsm-actions">${edit}${del}</td></tr>`;
  });
  if(!rows.length)h+=`<tr><td colspan="${purchaseCols.length+4}" class="bsm-empty">No purchase records found.</td></tr>`;
  h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-list').html(h);
}

function purchaseItemsFilterItemOptions(){
  const categoryId=String($('#purchase_items_category').val()||'');
  const current=String($('#purchase_items_item').val()||'');
  const items=(App.options.items||[]).filter(x=>!categoryId || String(x.category_id||'')===categoryId);
  const $sel=$('#purchase_items_item');
  if(!$sel.length)return;
  $sel.html('<option value="">Select</option>'+items.map(x=>`<option value="${esc(x.id)}">${esc(x.label)}</option>`).join(''));
  if(items.some(x=>String(x.id)===current))$sel.val(current);else $sel.val('');
  bsmRefreshSearchableSelect($sel[0]);
}
function purchaseItemsSyncCategoryFromItem(){
  const itemId=String($('#purchase_items_item').val()||'');
  if(!itemId)return;
  const item=(App.options.items||[]).find(x=>String(x.id)===itemId);
  if(item && item.category_id){
    $('#purchase_items_category').val(String(item.category_id));
    bsmRefreshSearchableSelect($('#purchase_items_category')[0]);
  }
}
async function renderPurchaseItemsRegister(){
  setTitle('Purchase Items Master');
  const filters=`<div id="bsm-purchase-items-filters" class="bsm-filters" style="display:none">
    <label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="purchase_items_date_from"></label>
    <label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="purchase_items_date_to"></label>
    ${selectHtml('purchase_items_vendor','Supplier','vendors','')}
    ${selectHtml('purchase_items_category','Item Category','categories','')}
    ${selectHtml('purchase_items_item','Item','items','')}
    <button class="bsm-icon-btn bsm-purchase-clear-btn" id="purchase_items_clear_filters" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button>
  </div>`;
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-purchase-items-search" class="bsm-search" aria-label="Search Purchase Items Master" placeholder=""><button id="bsm-purchase-items-filter-toggle" class="bsm-icon-btn" title="Filters">☷</button><div class="bsm-spacer"></div>${excelButton('bsm-purchase-items-export')}</div>${filters}<div id="bsm-purchase-items-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  purchaseItemsFilterItemOptions();
  await loadPurchaseItemsRegister();
}
async function loadPurchaseItemsRegister(exportAll=false){
  const r=await ajax('bsm_purchase_items_register',{
    page:exportAll?1:App.page,
    export_all:exportAll?'1':'',
    search:$('#bsm-purchase-items-search').val()||'',
    date_from:dateSql($('#purchase_items_date_from').val()||''),
    date_to:dateSql($('#purchase_items_date_to').val()||''),
    vendor_id:$('#purchase_items_vendor').val()||'',
    category_id:$('#purchase_items_category').val()||'',
    item_id:$('#purchase_items_item').val()||''
  });
  if(!r.success){if(!exportAll)$('#bsm-purchase-items-list').html('<div class="bsm-empty">Unable to load Purchase Items Master.</div>');return null;}
  if(exportAll)return r.data.rows||[];
  const rows=r.data.rows||[];
  const amt=v=>Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
  let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Purchase</th><th>Date</th><th>Supplier</th><th>GST Type</th><th>Bill Type</th><th>Supplier Invoice No.</th><th>Item Category</th><th>Item</th><th class="num">Qty</th><th class="num">Approved Qty</th><th>Unit</th><th class="num">Fixed Price</th><th class="num">Rate</th><th class="num">Taxable</th><th class="num">GST %</th><th class="num">CGST</th><th class="num">SGST</th><th class="num">IGST</th><th class="num">Total</th><th>Approved By</th></tr></thead><tbody>';
  rows.forEach((x,i)=>{h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.inward_no||'')}</td><td>${dateFmt(x.inward_date)}</td><td>${esc(x.vendor_name||'')}</td><td>${esc(x.supplier_invoice_no||'')}</td><td>${esc(x.category_name||'')}</td><td>${esc(x.item_name||'')}</td><td class="num">${Number(x.qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td class="num">${Number((x.approved_qty||0)!==0?x.approved_qty:x.qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td>${esc(x.unit_name||'')}</td><td class="num">${amt(x.master_price)}</td><td class="num">${amt(x.rate)}</td><td class="num">${amt(x.taxable_amount)}</td><td class="num">${Number(x.gst_rate||0).toFixed(2)}%</td><td class="num">${amt(x.cgst_amount)}</td><td class="num">${amt(x.sgst_amount)}</td><td class="num">${amt(x.igst_amount)}</td><td class="num">${amt(x.total_amount)}</td><td>${esc(x.approved_by_name||'-')}</td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="19" class="bsm-empty">No purchase item records found.</td></tr>';
  h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-purchase-items-list').html(h);
  return rows;
}
async function exportPurchaseItemsRegister(){
  const rows=await loadPurchaseItemsRegister(true); if(!rows)return;
  const headers=['Sl','Purchase','Date','Supplier','Supplier Invoice No.','Item Category','Item','Qty','Approved Qty','Unit','Fixed Price','Rate','Taxable','GST %','CGST','SGST','IGST','Total','Approved By'];
  const data=rows.map((x,i)=>[i+1,x.inward_no||'',dateFmt(x.inward_date),x.vendor_name||'',x.supplier_invoice_no||'',x.category_name||'',x.item_name||'',x.qty||0,((x.approved_qty||0)!==0?x.approved_qty:x.qty||0),x.unit_name||'',x.master_price||0,x.rate||0,x.taxable_amount||0,x.gst_rate||0,x.cgst_amount||0,x.sgst_amount||0,x.igst_amount||0,x.total_amount||0,x.approved_by_name||'']);
  downloadExcelFile('Purchase Items Master',headers,data);
}

function purchaseItemDetailsHtml(items){
  const rows=items||[];
  const amt=v=>Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
  let h='<div class="bsm-purchase-item-details"><div class="bsm-purchase-detail-wrap"><table class="bsm-purchase-detail-table"><thead><tr><th>Item</th><th class="num">Qty</th><th>Unit</th><th class="num">Fixed Price</th><th class="num">Rate</th><th class="num">Taxable</th><th class="num">GST %</th><th class="num">CGST</th><th class="num">SGST</th><th class="num">IGST</th><th class="num">Total</th></tr></thead><tbody>';
  rows.forEach(it=>{
    const price=Number(it.item_price||0);
    h+=`<tr><td>${esc(labelFor('items',it.item_id)||'')}</td><td class="num">${Number(it.qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td>${esc(labelFor('units',it.unit_id)||'')}</td><td class="num">${amt(price)}</td><td class="num">${amt(it.rate)}</td><td class="num">${amt(it.taxable_amount)}</td><td class="num">${Number(it.gst_rate||0).toFixed(2)}%</td><td class="num">${amt(it.cgst_amount)}</td><td class="num">${amt(it.sgst_amount)}</td><td class="num">${amt(it.igst_amount)}</td><td class="num">${amt(it.total_amount)}</td></tr>`;
  });
  if(!rows.length) h+='<tr><td colspan="11" class="bsm-empty">No item data found.</td></tr>';
  h+='</tbody></table></div></div>';
  return h;
}
async function togglePurchaseDetails($row){
  const id=Number($row.data('id')||0);
  if(!id)return;
  const $existing=$row.next('.bsm-purchase-details-row');
  if($existing.length){
    const isOpen=$existing.is(':visible');
    $existing.toggle(!isOpen);
    $row.attr('aria-expanded',String(!isOpen)).toggleClass('is-expanded',!isOpen);
    return;
  }
  const colspan=$row.children('td').length;
  const $detail=$(`<tr class="bsm-purchase-details-row"><td colspan="${colspan}"><div class="bsm-loading">Loading items…</div></td></tr>`);
  $row.after($detail).attr('aria-expanded','true').addClass('is-expanded');
  const r=await ajax('bsm_get',{module:'material_inwards',id});
  if(!r.success){$detail.find('td').html('<div class="bsm-empty">Unable to load purchase items.</div>');return;}
  $detail.find('td').html(purchaseItemDetailsHtml(r.data?._items||[]));
}


function dispatchItemsFilterItemOptions(){
  const categoryId=String($('#dispatch_items_category').val()||'');
  const current=String($('#dispatch_items_item').val()||'');
  const items=(App.options.items||[]).filter(x=>!categoryId || String(x.category_id||'')===categoryId);
  const $sel=$('#dispatch_items_item');
  if(!$sel.length)return;
  $sel.html('<option value="">Select</option>'+items.map(x=>`<option value="${esc(x.id)}">${esc(x.label)}</option>`).join(''));
  if(items.some(x=>String(x.id)===current))$sel.val(current);else $sel.val('');
  bsmRefreshSearchableSelect($sel[0]);
}
function dispatchItemsSyncCategoryFromItem(){
  const itemId=String($('#dispatch_items_item').val()||'');
  if(!itemId)return;
  const item=(App.options.items||[]).find(x=>String(x.id)===itemId);
  if(item && item.category_id){
    $('#dispatch_items_category').val(String(item.category_id));
    bsmRefreshSearchableSelect($('#dispatch_items_category')[0]);
  }
}
async function renderDispatchItemsRegister(){
  setTitle('Dispatch Items Master');
  const filters=`<div id="bsm-dispatch-items-filters" class="bsm-filters" style="display:none">
    <label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="dispatch_items_date_from"></label>
    <label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="dispatch_items_date_to"></label>
    ${selectHtml('dispatch_items_site','Site','sites','')}
    ${selectHtml('dispatch_items_category','Item Category','categories','')}
    ${selectHtml('dispatch_items_item','Item','items','')}
    <button class="bsm-icon-btn bsm-purchase-clear-btn" id="dispatch_items_clear_filters" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button>
  </div>`;
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-dispatch-items-search" class="bsm-search" aria-label="Search Dispatch Items Master" placeholder=""><button id="bsm-dispatch-items-filter-toggle" class="bsm-icon-btn" title="Filters">☷</button><div class="bsm-spacer"></div>${excelButton('bsm-dispatch-items-export')}</div>${filters}<div id="bsm-dispatch-items-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  dispatchItemsFilterItemOptions();
  await loadDispatchItemsRegister();
}
async function loadDispatchItemsRegister(exportAll=false){
  const r=await ajax('bsm_dispatch_items_register',{
    page:exportAll?1:App.page,
    export_all:exportAll?'1':'',
    search:$('#bsm-dispatch-items-search').val()||'',
    date_from:dateSql($('#dispatch_items_date_from').val()||''),
    date_to:dateSql($('#dispatch_items_date_to').val()||''),
    site_id:$('#dispatch_items_site').val()||'',
    category_id:$('#dispatch_items_category').val()||'',
    item_id:$('#dispatch_items_item').val()||''
  });
  if(!r.success){if(!exportAll)$('#bsm-dispatch-items-list').html('<div class="bsm-empty">Unable to load Dispatch Items Master.</div>');return null;}
  if(exportAll)return r.data.rows||[];
  const rows=r.data.rows||[];
  const amt=v=>Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
  let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Dispatch</th><th>Date</th><th>Requisition</th><th>To Site</th><th>From</th><th>Item Category</th><th>Item</th><th class="num">Qty</th><th>Unit</th><th>Vehicle No.</th><th>Driver Name</th><th>Driver Mobile</th><th>Courier No.</th><th>Courier Name</th><th>Status</th><th>Remarks</th></tr></thead><tbody>';
  rows.forEach((x,i)=>{h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.dispatch_no||'')}</td><td>${dateFmt(x.dispatch_date)}</td><td>${esc(x.requisition_no||'')}</td><td>${esc(x.site_name||'')}</td><td>${esc(x.from_site_name||x.warehouse_name||'')}</td><td>${esc(x.category_name||'')}</td><td>${esc(x.item_name||'')}</td><td class="num">${Number(x.qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td>${esc(x.unit_name||'')}</td><td>${esc(x.vehicle_no||'')}</td><td>${esc(x.driver_name||'')}</td><td>${esc(x.driver_mobile||'')}</td><td>${esc(x.courier_no||'')}</td><td>${esc(x.courier_name||'')}</td><td><span class="bsm-pill">${esc(String(x.status||'').replaceAll('_',' '))}</span></td><td>${esc(x.remarks||'')}</td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="18" class="bsm-empty">No dispatch item records found.</td></tr>';
  h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-dispatch-items-list').html(h);
  return rows;
}
async function exportDispatchItemsRegister(){
  const rows=await loadDispatchItemsRegister(true); if(!rows)return;
  const headers=['Sl','Dispatch','Date','Requisition','To Site','From','Item Category','Item','Qty','Unit','Vehicle No.','Driver Name','Driver Mobile','Courier No.','Courier Name','Status','Remarks'];
  const data=rows.map((x,i)=>[i+1,x.dispatch_no||'',dateFmt(x.dispatch_date),x.requisition_no||'',x.site_name||'',x.from_site_name||x.warehouse_name||'',x.category_name||'',x.item_name||'',x.qty||0,x.unit_name||'',x.vehicle_no||'',x.driver_name||'',x.driver_mobile||'',x.courier_no||'',x.courier_name||'',String(x.status||'').replaceAll('_',' '),x.remarks||'']);
  downloadExcelFile('Dispatch Items Master',headers,data);
}
function dispatchItemDetailsHtml(items){
  const rows=items||[];
  const amt=v=>Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
  let h='<div class="bsm-purchase-item-details"><div class="bsm-purchase-detail-wrap"><table class="bsm-purchase-detail-table"><thead><tr><th>Item</th><th class="num">Dispatch Qty</th><th>Unit</th><th>Remarks</th></tr></thead><tbody>';
  rows.forEach(it=>{h+=`<tr><td>${esc(labelFor('items',it.item_id)||'')}</td><td class="num">${Number(it.qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td>${esc(labelFor('units',it.unit_id)||'')}</td><td>${esc(it.remarks||'')}</td></tr>`;});
  if(!rows.length) h+='<tr><td colspan="4" class="bsm-empty">No item data found.</td></tr>';
  h+='</tbody></table></div></div>';
  return h;
}
async function toggleDispatchDetails($row){
  const id=Number($row.data('id')||0);
  if(!id)return;
  const $existing=$row.next('.bsm-dispatch-details-row');
  if($existing.length){
    const isOpen=$existing.is(':visible');
    $existing.toggle(!isOpen);
    $row.attr('aria-expanded',String(!isOpen)).toggleClass('is-expanded',!isOpen);
    return;
  }
  const colspan=$row.children('td').length;
  const $detail=$(`<tr class="bsm-dispatch-details-row"><td colspan="${colspan}"><div class="bsm-loading">Loading items…</div></td></tr>`);
  $row.after($detail).attr('aria-expanded','true').addClass('is-expanded');
  const r=await ajax('bsm_get',{module:'material_dispatches',id});
  if(!r.success){$detail.find('td').html('<div class="bsm-empty">Unable to load dispatch items.</div>');return;}
  $detail.find('td').html(dispatchItemDetailsHtml(r.data?._items||[]));
}


function salesKindMeta(kind){
  const quote=kind.startsWith('quotation');
  const items=kind.endsWith('items');
  return {quote,items,title:quote?(items?'Quotation Items Master':'Quotation Master'):(items?'Invoice Items Master':'Invoice Master'),module:quote?'quotations':'tax_invoices'};
}
function salesFilterItemOptions(prefix){
  const cat=String($(`#${prefix}_category`).val()||''),current=String($(`#${prefix}_item`).val()||'');
  const rows=(App.options.items||[]).filter(x=>!cat||String(x.category_id||'')===cat),$s=$(`#${prefix}_item`);if(!$s.length)return;
  $s.html('<option value="">Select</option>'+rows.map(x=>`<option value="${esc(x.id)}">${esc(x.label)}</option>`).join(''));
  if(rows.some(x=>String(x.id)===current))$s.val(current);else $s.val('');bsmRefreshSearchableSelect($s[0]);
}
async function renderSalesRegister(kind){
  const m=salesKindMeta(kind);App.page=1;setTitle(m.title);await ensureOptions(['customers','sites','categories','items','units']);
  const p=`sales_${kind}`;
  const extra=m.items?`${selectHtml(`${p}_category`,'Item Category','categories','')}${selectHtml(`${p}_item`,'Item','items','')}`:'';
  const filters=`<div id="${p}_filters" class="bsm-filters" style="display:none"><label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="${p}_from"></label><label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="${p}_to"></label>${selectHtml(`${p}_customer`,'Customer','customers','')}${selectHtml(`${p}_site`,'Site','sites','')}${extra}<button class="bsm-icon-btn bsm-purchase-clear-btn sales-clear" data-kind="${kind}" title="Clear Filters">${clearIcon()}</button></div>`;
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="${p}_search" class="bsm-search sales-search" data-kind="${kind}" aria-label="Search ${esc(m.title)}" placeholder=""><button class="bsm-icon-btn sales-filter-toggle" data-kind="${kind}" title="Filters">☷</button><div class="bsm-spacer"></div>${excelButton(`${p}_export`)}</div>${filters}<div id="${p}_list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  if(m.items)salesFilterItemOptions(p);await loadSalesRegister(kind);
}
async function loadSalesRegister(kind,exportAll=false){
  const m=salesKindMeta(kind),p=`sales_${kind}`;
  const r=await ajax('bsm_sales_register',{kind,page:exportAll?1:App.page,export_all:exportAll?'1':'',search:$(`#${p}_search`).val()||'',date_from:$(`#${p}_from`).val()||'',date_to:$(`#${p}_to`).val()||'',customer_id:$(`#${p}_customer`).val()||'',site_id:$(`#${p}_site`).val()||'',category_id:m.items?($(`#${p}_category`).val()||''):'',item_id:m.items?($(`#${p}_item`).val()||''):''});
  if(!r.success){if(!exportAll)$(`#${p}_list`).html(`<div class="bsm-empty">Unable to load ${esc(m.title)}.</div>`);return null;}if(exportAll)return r.data.rows||[];
  const rows=r.data.rows||[];let h='<div class="bsm-table-wrap"><table><thead><tr>';
  if(m.items&&m.quote)h+='<th>Sl</th><th>Quotation No.</th><th>Date</th><th>Customer</th><th>Item Category</th><th>Item</th><th>Description</th><th class="num">Qty</th><th>Unit</th><th class="num">Rate</th><th class="num">GST %</th><th class="num">GST</th><th class="num">Amount</th>';
  else if(m.items)h+='<th>Sl</th><th>Document No.</th><th>Date</th><th>Customer</th><th>Site</th><th>Item Category</th><th>Item</th><th>Description</th><th class="num">Qty</th><th>Unit</th><th class="num">Rate</th><th class="num">GST %</th><th class="num">GST</th><th class="num">Amount</th>';
  else if(m.quote)h+='<th>Sl</th><th>Document No.</th><th>Date</th><th>Valid Till</th><th>Customer</th><th>Site</th><th class="num">Taxable</th><th class="num">GST</th><th class="num">Other Charge</th><th class="num">GST on Other Charge</th><th class="num">Total</th><th>PDF</th><th>Action</th>';
  else h+='<th>Sl</th><th>Document No.</th><th>Date</th><th>Customer</th><th>Site</th><th class="num">Taxable</th><th class="num">GST</th><th class="num">Other Charge</th><th class="num">GST on Other Charge</th><th class="num">Total</th><th>PDF</th><th>Action</th>';
  h+='</tr></thead><tbody>';
  rows.forEach((x,i)=>{
    if(m.items&&m.quote){
      h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.document_no||'')}</td><td>${dateFmt(x.document_date)}</td><td>${esc(x.customer_name||'')}</td><td>${esc(x.category_name||'')}</td><td>${esc(x.item_name||'')}</td><td>${esc(x.description||'')}</td><td class="num">${Number(x.qty||0).toFixed(2)}</td><td>${esc(x.unit_name||'')}</td><td class="num">${moneyFmt(x.rate)}</td><td class="num">${Number(x.gst_rate||0).toFixed(2)}%</td><td class="num">${moneyFmt(Number(x.cgst_amount||0)+Number(x.sgst_amount||0)+Number(x.igst_amount||0))}</td><td class="num">${moneyFmt(x.amount)}</td></tr>`;
    }else if(m.items){
      h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.document_no||'')}</td><td>${dateFmt(x.document_date)}</td><td>${esc(x.customer_name||'')}</td><td>${esc(x.site_name||'')}</td><td>${esc(x.category_name||'')}</td><td>${esc(x.item_name||'')}</td><td>${esc(x.description||'')}</td><td class="num">${Number(x.qty||0).toFixed(2)}</td><td>${esc(x.unit_name||'')}</td><td class="num">${moneyFmt(x.rate)}</td><td class="num">${Number(x.gst_rate||0).toFixed(2)}%</td><td class="num">${moneyFmt(Number(x.cgst_amount||0)+Number(x.sgst_amount||0)+Number(x.igst_amount||0))}</td><td class="num">${moneyFmt(x.amount)}</td></tr>`;
    }else{
      const gst=Number(x.cgst_amount||0)+Number(x.sgst_amount||0)+Number(x.igst_amount||0);
      const pdf=`<button type="button" class="bsm-pdf-link bsm-sales-pdf" data-module="${m.module}" data-id="${x.id}" title="Generate latest PDF" aria-label="Generate latest PDF"><svg class="bsm-pdf-icon" viewBox="0 0 32 36" aria-hidden="true"><path d="M6.5 1.5h12l7 7v25H6.5z" fill="#fff" stroke="#ef2020" stroke-width="2" stroke-linejoin="round"/><path d="M18.5 1.5v7h7" fill="none" stroke="#ef2020" stroke-width="2" stroke-linejoin="round"/><rect x="3" y="19" width="26" height="11" rx="2" fill="#ef2020"/><text x="16" y="27.1" text-anchor="middle" font-size="8.3" font-weight="700" font-family="Arial,Helvetica,sans-serif" fill="#fff">PDF</text></svg></button>`;
      if(m.quote){
        h+=`<tr class="bsm-sales-master-row" data-id="${x.id}" data-module="${m.module}" tabindex="0" aria-expanded="false"><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.document_no||'')}</td><td>${dateFmt(x.document_date)}</td><td>${dateFmt(x.valid_till)}</td><td>${esc(x.customer_name||'')}</td><td>${esc(x.site_name||'')}</td><td class="num">${moneyFmt(x.taxable_amount)}</td><td class="num">${moneyFmt(gst)}</td><td class="num">${moneyFmt(x.other_charges)}</td><td class="num">${moneyFmt(x.gst_on_other_charge)}</td><td class="num">${moneyFmt(x.document_total)}</td><td>${pdf}</td><td class="bsm-actions"><button class="bsm-action-edit" data-module="${m.module}" data-id="${x.id}" title="Edit">✎</button><button class="bsm-action-delete" data-module="${m.module}" data-id="${x.id}" title="Delete"><img class="bsm-delete-icon" src="${BSM.deleteIcon}" alt="Delete"></button></td></tr>`;
      }else{
        h+=`<tr class="bsm-sales-master-row" data-id="${x.id}" data-module="${m.module}" tabindex="0" aria-expanded="false"><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.document_no||'')}</td><td>${dateFmt(x.document_date)}</td><td>${esc(x.customer_name||'')}</td><td>${esc(x.site_name||'')}</td><td class="num">${moneyFmt(x.taxable_amount)}</td><td class="num">${moneyFmt(gst)}</td><td class="num">${moneyFmt(x.other_charges)}</td><td class="num">${moneyFmt(x.gst_on_other_charge)}</td><td class="num">${moneyFmt(x.document_total)}</td><td>${pdf}</td><td class="bsm-actions"><button class="bsm-action-edit" data-module="${m.module}" data-id="${x.id}" title="Edit">✎</button><button class="bsm-action-delete" data-module="${m.module}" data-id="${x.id}" title="Delete"><img class="bsm-delete-icon" src="${BSM.deleteIcon}" alt="Delete"></button></td></tr>`;
      }
    }
  });
  if(!rows.length)h+=`<tr><td colspan="${m.items?14:(m.quote?13:12)}" class="bsm-empty">No records found.</td></tr>`;h+='</tbody></table></div>';if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;$(`#${p}_list`).html(h);return rows;
}
async function exportSalesRegister(kind){const m=salesKindMeta(kind),rows=await loadSalesRegister(kind,true);if(!rows)return;if(m.items&&m.quote){const headers=['Sl','Quotation No.','Date','Customer','Item Category','Item','Description','Qty','Unit','Rate','GST %','GST','Amount'];downloadExcelFile(m.title,headers,rows.map((x,i)=>[i+1,x.document_no||'',dateFmt(x.document_date),x.customer_name||'',x.category_name||'',x.item_name||'',x.description||'',x.qty||0,x.unit_name||'',x.rate||0,x.gst_rate||0,Number(x.cgst_amount||0)+Number(x.sgst_amount||0)+Number(x.igst_amount||0),x.amount||0]));}else if(m.items){const headers=['Sl','Document No.','Date','Customer','Site','Item Category','Item','Description','Qty','Unit','Rate','GST %','GST','Amount'];downloadExcelFile(m.title,headers,rows.map((x,i)=>[i+1,x.document_no||'',dateFmt(x.document_date),x.customer_name||'',x.site_name||'',x.category_name||'',x.item_name||'',x.description||'',x.qty||0,x.unit_name||'',x.rate||0,x.gst_rate||0,Number(x.cgst_amount||0)+Number(x.sgst_amount||0)+Number(x.igst_amount||0),x.amount||0]));}else if(m.quote){downloadExcelFile(m.title,['Sl','Document No.','Date','Valid Till','Customer','Site','Taxable','GST','Other Charge','GST on Other Charge','Total'],rows.map((x,i)=>[i+1,x.document_no||'',dateFmt(x.document_date),dateFmt(x.valid_till),x.customer_name||'',x.site_name||'',x.taxable_amount||0,Number(x.cgst_amount||0)+Number(x.sgst_amount||0)+Number(x.igst_amount||0),x.other_charges||0,x.gst_on_other_charge||0,x.document_total||0]));}else{downloadExcelFile(m.title,['Sl','Document No.','Date','Customer','Site','Taxable','GST','Other Charge','GST on Other Charge','Total'],rows.map((x,i)=>[i+1,x.document_no||'',dateFmt(x.document_date),x.customer_name||'',x.site_name||'',x.taxable_amount||0,Number(x.cgst_amount||0)+Number(x.sgst_amount||0)+Number(x.igst_amount||0),x.other_charges||0,x.gst_on_other_charge||0,x.document_total||0]));}}
function salesDetailHtml(items){let h='<div class="bsm-purchase-item-details"><div class="bsm-purchase-detail-wrap"><table class="bsm-purchase-detail-table"><thead><tr><th>Item / Description</th><th>HSN / SAC</th><th class="num">Qty</th><th>Unit</th><th class="num">Rate</th><th class="num">Amount</th><th class="num">GST %</th><th class="num">GST</th><th class="num">Total</th></tr></thead><tbody>';for(const x of (items||[])){const gstTotal=Number(x.cgst_amount||0)+Number(x.sgst_amount||0)+Number(x.igst_amount||0);h+=`<tr><td>${esc(labelFor('items',x.item_id)||x.description||'')}</td><td>${esc(x.hsn_sac||'')}</td><td class="num">${Number(x.qty||0).toFixed(2)}</td><td>${esc(labelFor('units',x.unit_id)||'')}</td><td class="num">${moneyFmt(x.rate)}</td><td class="num">${moneyFmt(x.amount)}</td><td class="num">${Number(x.gst_rate||0).toFixed(2)}%</td><td class="num">${moneyFmt(gstTotal)}</td><td class="num">${moneyFmt(x.total_amount)}</td></tr>`;}if(!(items||[]).length)h+='<tr><td colspan="10" class="bsm-empty">No item data found.</td></tr>';return h+'</tbody></table></div></div>';}
async function toggleSalesDetails($row){const id=Number($row.data('id')||0),module=$row.data('module');if(!id||!module)return;const $e=$row.next('.bsm-sales-details-row');if($e.length){const open=$e.is(':visible');$e.toggle(!open);$row.attr('aria-expanded',String(!open));return;}const c=$row.children('td').length,$d=$(`<tr class="bsm-sales-details-row"><td colspan="${c}"><div class="bsm-loading">Loading items…</div></td></tr>`);$row.after($d);const r=await ajax('bsm_get',{module,id});$d.find('td').html(r.success?salesDetailHtml(r.data?._items||[]):'<div class="bsm-empty">Unable to load items.</div>');}
async function prepareSalesForm(key,row={}){
  const $form=$('#bsm-form'),$fields=$('#bsm-form-fields');$form.data('sales-company-state','');
  try{const r=await ajax('bsm_list',{module:'company_settings',page:1,search:'',export_all:'1',visible_columns:'[]'});if(r.success&&r.data?.rows?.[0]){$form.data('sales-company-state',String(r.data.rows[0].state_id||''));}}catch(e){}
  const $cust=$fields.find('select[name="customer_id"]');if($cust.length&&$cust.val())await salesFillCustomer($cust.val());
  if(['quotations','tax_invoices'].includes(key)){
    $form.addClass('bsm-quotation-style');
    $fields.addClass('bsm-quotation-head');
    const madeBy=esc(BSM.currentUser?.name||'');
    $fields.append(`<label class="bsm-quote-made-by"><span class="bsm-field-label">Made By</span><input value="${madeBy}" readonly></label>`);
    $fields.find('[name="round_off"]').closest('label').hide();
    const $terms=$fields.find('[name="terms"]').closest('label').detach();
    
    $('#bsm-line-items').append(`<div class="bsm-quote-totals"><label>Amount<input id="bsm-quote-amount" readonly value="0.00"></label>${key==='tax_invoices'?'<label>IGST<input id="bsm-quote-igst" readonly value="0.00"></label><label>CGST<input id="bsm-quote-cgst" readonly value="0.00"></label><label>SGST<input id="bsm-quote-sgst" readonly value="0.00"></label>':'<label>GST<input id="bsm-quote-gst" readonly value="0.00"></label>'}<label>Other Charge<input id="bsm-quote-other" readonly value="0.00"></label><label>GST on Other Charge<input id="bsm-quote-other-gst" readonly value="0.00"></label><label>Round Off<input id="bsm-quote-round" type="number" step="0.01" value="${esc($fields.find('[name="round_off"]').val()||'')}"></label><label>Total Amount<input id="bsm-quote-total" readonly value="0.00"></label></div><div class="bsm-quote-notes"></div>`);
    if(key==='quotations')$('.bsm-quote-notes').append($terms); else $('.bsm-quote-notes').remove();
  }
  recalcSalesDocument();
}
function bsmApplyInvoiceGstColumns(){
  const $form=$('#bsm-form[data-module="tax_invoices"]');if(!$form.length)return;
  const type=String($form.data('customer-gst-type')||'').toLowerCase();
  const known=['intra_state','inter_state','no_gst'].includes(type);
  const visibility={igst_amount:!known||type==='inter_state',cgst_amount:!known||type==='intra_state',sgst_amount:!known||type==='intra_state',gst_rate:type!=='no_gst'};
  Object.keys(visibility).forEach(name=>{
    $form.find('.bsm-quotation-lines th.bsm-col-'+name+',.bsm-quotation-lines td.bsm-col-'+name).toggle(visibility[name]);
  });
  ['igst','cgst','sgst'].forEach(name=>{
    const $el=$form.find('#bsm-quote-'+name).closest('label');
    $el.toggle(!known||(name==='igst'?type==='inter_state':type==='intra_state'));
  });
  $form.find('#bsm-quote-other-gst').closest('label').toggle(type!=='no_gst');
}
async function salesFillCustomer(id){if(!id)return;const r=await ajax('bsm_get',{module:'customers',id});if(!r.success)return;const d=r.data||{},$f=$('#bsm-form-fields');if($('#bsm-form').data('module')==='tax_invoices')$('#bsm-form').data('customer-gst-type',String(d.gst_type||''));$f.find('[name="billing_address"]').val(d.address||'');$f.find('[name="customer_gstin"]').val(d.gstin||'');$f.find('[name="billing_state_id"]').val(String(d.state_id||'')).trigger('change');if(!$f.find('[name="place_of_supply_state_id"]').val())$f.find('[name="place_of_supply_state_id"]').val(String(d.state_id||'')).trigger('change');bsmSyncAllSearchables($f);bsmApplyInvoiceGstColumns();recalcSalesDocument();}
function recalcSalesLine($tr){const key=$('#bsm-form').data('module'),qty=Number($tr.find('[name="qty"]').val()||0),rate=Number($tr.find('[name="rate"]').val()||0),gst=Number($tr.find('[name="gst_rate"]').val()||0),amount=Math.round(qty*rate*100)/100;let cgst=0,sgst=0,igst=0;if(key==='tax_invoices'){const type=String($('#bsm-form').data('customer-gst-type')||'');if(type==='intra_state'){cgst=Math.round(amount*gst/2)/100;sgst=cgst;}else if(type==='inter_state'){igst=Math.round(amount*gst)/100;}}else if(key==='quotations'){igst=Math.round(amount*gst)/100;}else{const company=String($('#bsm-form').data('sales-company-state')||''),pos=String($('#bsm-form-fields [name="place_of_supply_state_id"]').val()||''),intra=!!company&&!!pos&&company===pos;if(intra){cgst=Math.round(amount*gst/2)/100;sgst=cgst;}else{igst=Math.round(amount*gst)/100;}}const total=Math.round((amount+cgst+sgst+igst)*100)/100;$tr.find('[name="amount"]').val(amount?amount.toFixed(2):'0.00');$tr.find('[name="cgst_amount"]').val(cgst?cgst.toFixed(2):'0.00');$tr.find('[name="sgst_amount"]').val(sgst?sgst.toFixed(2):'0.00');$tr.find('[name="igst_amount"]').val(igst?igst.toFixed(2):'0.00');$tr.find('[name="total_amount"]').val(total?total.toFixed(2):'0.00');}
function recalcSalesDocument(){bsmApplyInvoiceGstColumns();const key=$('#bsm-form').data('module');if(!['quotations','tax_invoices'].includes(key))return;let amount=0,cgst=0,sgst=0,igst=0,total=0;$('#bsm-lines-body .bsm-line').each(function(){recalcSalesLine($(this));amount+=Number($(this).find('[name="amount"]').val()||0);cgst+=Number($(this).find('[name="cgst_amount"]').val()||0);sgst+=Number($(this).find('[name="sgst_amount"]').val()||0);igst+=Number($(this).find('[name="igst_amount"]').val()||0);total+=Number($(this).find('[name="total_amount"]').val()||0);});if(['quotations','tax_invoices'].includes(key)&&$('#bsm-quote-round').length){$('#bsm-form-fields [name="round_off"]').val($('#bsm-quote-round').val()||0);}const other=Number($('#bsm-form-fields [name="other_charges"]').val()||0),otherGst=(key==='tax_invoices'&&$('#bsm-form').data('customer-gst-type')==='no_gst')?0:Number($('#bsm-form-fields [name="gst_on_other_charge"]').val()||0),round=Number($('#bsm-form-fields [name="round_off"]').val()||0),grand=Math.round((total+other+otherGst+round)*100)/100;$('#bsm-form-fields [name="taxable_amount"]').val(amount.toFixed(2));$('#bsm-form-fields [name="cgst_amount"]').val(cgst.toFixed(2));$('#bsm-form-fields [name="sgst_amount"]').val(sgst.toFixed(2));$('#bsm-form-fields [name="igst_amount"]').val(igst.toFixed(2));$('#bsm-form-fields [name="'+(key==='quotations'?'grand_total':'invoice_total')+'"]').val(grand.toFixed(2));if(['quotations','tax_invoices'].includes(key)){$('#bsm-quote-amount').val(amount.toFixed(2));$('#bsm-quote-gst').val((cgst+sgst+igst).toFixed(2));$('#bsm-quote-igst').val(igst.toFixed(2));$('#bsm-quote-cgst').val(cgst.toFixed(2));$('#bsm-quote-sgst').val(sgst.toFixed(2));$('#bsm-quote-other').val(other.toFixed(2));$('#bsm-quote-other-gst').val(otherGst.toFixed(2));$('#bsm-quote-total').val(grand.toFixed(2));}}

async function renderPendingInvoiceCopy(){
  setTitle('Pending Invoice Copy');
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-pending-invoice-search" class="bsm-search" aria-label="Search Pending Invoice Copy" placeholder=""><div class="bsm-spacer"></div>${excelButton('bsm-pending-invoice-export')}</div><div id="bsm-pending-invoice-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  await loadPendingInvoiceCopy();
}
async function loadPendingInvoiceCopy(){
  const r=await ajax('bsm_pending_invoice_copy',{page:App.page,search:$('#bsm-pending-invoice-search').val()||''});
  if(!r.success){$('#bsm-pending-invoice-list').html('<div class="bsm-empty">Unable to load Pending Invoice Copy.</div>');return;}
  const rows=r.data.rows||[];
  let h='<div class="bsm-table-wrap"><table class="bsm-pending-invoice-table"><thead><tr><th>Sl</th><th>Purchase</th><th>Date</th><th>Supplier</th><th>GST Type</th><th>Supplier Invoice No.</th><th class="num">Total</th><th>Bill Type</th><th>Upload Invoice</th></tr></thead><tbody>';
  rows.forEach((row,i)=>{
    const vendor=row.vendor_name||labelFor('vendors',row.vendor_id)||'';
    h+=`<tr data-id="${row.id}"><td>${(r.data.page-1)*20+i+1}</td><td>${esc(row.inward_no||'')}</td><td>${dateFmt(row.inward_date)}</td><td>${esc(vendor)}</td><td>${esc(gstTypeLabel(row.gst_type))}</td><td>${esc(row.supplier_invoice_no||'')}</td><td class="num">${moneyFmt(row.total_amount)}</td><td><select class="bsm-pending-bill-type" aria-label="Bill Type"><option value="">Select Bill Type</option><option value="gst_bill" ${row.bill_type==='gst_bill'?'selected':''}>GST Bill</option><option value="non_gst_bill" ${row.bill_type==='non_gst_bill'?'selected':''}>Non GST Bill</option></select></td><td><div class="bsm-pending-invoice-upload"><input type="file" class="bsm-pending-invoice-file" accept="application/pdf,image/*"><button type="button" class="bsm-btn bsm-btn-primary bsm-upload-pending-invoice" data-id="${row.id}">Upload</button></div></td></tr>`;
  });
  if(!rows.length)h+='<tr><td colspan="9" class="bsm-empty">No pending invoice copies.</td></tr>';
  h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-pending-invoice-list').html(h);
}


async function exportPendingInvoiceCopy(){
  const r=await ajax('bsm_pending_invoice_copy',{page:1,search:$('#bsm-pending-invoice-search').val()||'',export_all:'1'});
  if(!r.success)return showToast(r.data?.message||'Unable to export',true);
  const headers=['Sl','Purchase','Date','Supplier','GST Type','Supplier Invoice No.','Total','Bill Type'];
  const rows=(r.data.rows||[]).map((row,i)=>[i+1,row.inward_no||'',dateFmt(row.inward_date),row.vendor_name||labelFor('vendors',row.vendor_id)||'',gstTypeLabel(row.gst_type),row.supplier_invoice_no||'',Number(row.total_amount||0).toFixed(2),billTypeLabel(row.bill_type)]);
  return downloadExcelFile('Pending Invoice Copy',headers,rows);
}
$(document).on('click','#bsm-pending-invoice-export',exportPendingInvoiceCopy);

async function renderPendingContractorBillCopy(){
  setTitle('Pending Bill Copy');
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-pending-contractor-bill-search" class="bsm-search" aria-label="Search Pending Labour Contractor Bill Copy" placeholder=""><div class="bsm-spacer"></div></div><div id="bsm-pending-contractor-bill-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  await loadPendingContractorBillCopy();
}
async function loadPendingContractorBillCopy(){
  const r=await ajax('bsm_pending_contractor_bill_copy',{page:App.page,search:$('#bsm-pending-contractor-bill-search').val()||''});
  if(!r.success){$('#bsm-pending-contractor-bill-list').html('<div class="bsm-empty">Unable to load pending Labour Contractor bill copies.</div>');return;}
  const rows=r.data.rows||[];
  let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Bill No.</th><th>Date</th><th>Labour Contractor</th><th>Site</th><th>Bill Reference</th><th class="num">Total</th><th>Upload Bill</th></tr></thead><tbody>';
  rows.forEach((row,i)=>{
    h+=`<tr data-id="${row.id}"><td>${(r.data.page-1)*20+i+1}</td><td>${esc(row.bill_no||'')}</td><td>${dateFmt(row.bill_date)}</td><td>${esc(row.contractor_name||'')}</td><td>${esc(row.site_name||'')}</td><td>${esc(row.bill_reference||'')}</td><td class="num">${moneyFmt(row.total_amount)}</td><td><div class="bsm-pending-invoice-upload"><input type="file" class="bsm-pending-contractor-bill-file" accept="application/pdf,image/*"><button type="button" class="bsm-btn bsm-btn-primary bsm-upload-pending-contractor-bill" data-id="${row.id}">Upload</button></div></td></tr>`;
  });
  if(!rows.length)h+='<tr><td colspan="9" class="bsm-empty">No pending Labour Contractor bill copies.</td></tr>';
  h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-pending-contractor-bill-list').html(h);
}

async function renderAllowanceForm(){
  setTitle('Allowance Form');
  const employeeId=String(BSM.currentUser?.plugin_id||'');
  const employeeRecord=(App.options.users||[]).find(x=>String(x.id)===employeeId);
  const employeeName=employeeRecord?.label||BSM.currentUser?.name||'';
  const employeeField=`<label>Employee<input id="allowance_employee_name" value="${esc(employeeName)}" readonly placeholder="Employee profile not linked"><input type="hidden" id="allowance_employee" value="${esc(employeeId)}"></label>`;
  $('#bsm-content').html(`<div class="bsm-panel"><form id="bsm-allowance-form" enctype="multipart/form-data"><div class="bsm-form-grid">${employeeField}<label>Date<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="allowance_date" value="${esc(BSM.today||'')}" required></label>${selectHtml('allowance_type','Allowance Type','allowance_types','',true)}${selectHtml('allowance_site','Site Name','sites','',true)}<label>Customer Name<input type="text" id="allowance_customer_name" readonly placeholder="Auto from Site"></label><input type="hidden" id="allowance_customer_id"><label>Amount<input type="number" id="allowance_amount" step="0.01" required></label><div class="bsm-allowance-limit-box wide" id="bsm-allowance-limit-box" style="display:none"></div><label class="wide">Purpose<textarea id="allowance_purpose" required></textarea></label><label class="wide"><span class="bsm-field-label">Supporting Document</span><input type="file" id="allowance_document" accept="application/pdf,image/jpeg,image/png,image/webp"><small class="bsm-small-muted">Optional: bill, receipt or screenshot (PDF/JPG/PNG/WebP, max 10 MB).</small></label></div><div class="bsm-form-actions"><button type="submit" class="bsm-btn bsm-btn-primary">Submit</button></div></form></div>`);
  if(!employeeId||!employeeRecord){$('#bsm-allowance-form button[type="submit"]').prop('disabled',true);$('#bsm-allowance-form .bsm-form-grid').prepend('<div class="wide bsm-error-list">Your login is not linked to an active Employee record. Please contact the administrator.</div>');}
  bsmSyncAllowanceCustomer();
}
function bsmSyncAllowanceCustomer(){
  const siteId=String($('#allowance_site').val()||'');
  const site=(App.options.sites||[]).find(x=>String(x.id)===siteId);
  const customerId=site?String(site.customer_id||''):'';
  const customer=(App.options.customers||[]).find(x=>String(x.id)===customerId);
  $('#allowance_customer_id').val(customerId);
  $('#allowance_customer_name').val(customer?customer.label:'');
}

async function renderEmployeeAllowanceLimits(){
  setTitle('Employee Allowance Limit');
  const filters=`<div id="bsm-allowance-limit-filters" class="bsm-filters" style="display:none">${selectHtml('allowance_limit_employee','Employee','users','')}${selectHtml('allowance_limit_type','Allowance Type','allowance_types','')}<label>Status<select id="allowance_limit_status"><option value="">Select</option><option value="active">Active</option><option value="inactive">Inactive</option></select></label><button class="bsm-icon-btn bsm-payment-clear" id="allowance_limit_clear" title="Clear">×</button></div>`;
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-allowance-limit-search" class="bsm-search" aria-label="Search" placeholder=""><button id="bsm-allowance-limit-filter-toggle" class="bsm-icon-btn" title="Filters">☷</button><div class="bsm-spacer"></div>${excelButton('bsm-allowance-limit-export')}<button id="bsm-add-allowance-limit" class="bsm-btn bsm-btn-primary">+</button></div>${filters}<div id="bsm-allowance-limit-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  await loadEmployeeAllowanceLimits();
}
async function loadEmployeeAllowanceLimits(exportAll=false){
  const r=await ajax('bsm_allowance_limits',{page:exportAll?1:App.page,export_all:exportAll?'1':'',search:$('#bsm-allowance-limit-search').val()||'',employee_id:$('#allowance_limit_employee').val()||'',allowance_type_id:$('#allowance_limit_type').val()||'',status:$('#allowance_limit_status').val()||''});
  if(!r.success){if(!exportAll)$('#bsm-allowance-limit-list').html('<div class="bsm-empty">Unable to load Employee Allowance Limits.</div>');return null;}
  if(exportAll)return r.data.rows||[];
  const rows=r.data.rows||[];let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Employee</th><th>Allowance Type</th><th class="num">Limit Amount</th><th>Status</th><th>Action</th></tr></thead><tbody>';
  rows.forEach((x,i)=>{h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.employee_name||'')}</td><td>${esc(x.allowance_type_name||'')}</td><td class="num">${moneyFmt(x.limit_amount)}</td><td><span class="bsm-pill">${esc(x.status||'')}</span></td><td class="bsm-actions"><button class="bsm-action-edit bsm-edit-allowance-limit" data-id="${x.id}" title="Edit">✎</button><button class="bsm-action-delete bsm-delete-allowance-limit" data-id="${x.id}" title="Delete"><img class="bsm-delete-icon" src="${BSM.deleteIcon}" alt="Delete"></button></td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="6" class="bsm-empty">No allowance limits found.</td></tr>';h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-allowance-limit-list').html(h);return rows;
}
async function exportEmployeeAllowanceLimits(){const rows=await loadEmployeeAllowanceLimits(true);if(!rows)return;downloadExcelFile('Employee Allowance Limit',['Sl','Employee','Allowance Type','Limit Amount','Status'],rows.map((x,i)=>[i+1,x.employee_name||'',x.allowance_type_name||'',x.limit_amount||0,x.status||'']));}

async function renderAllowanceQueue(mode){
  const isAll=mode==='all';
  setTitle(isAll?'Allowance Master':(mode==='approval'?'Pending Allowance Approval':'Pending Allowance Payment'));
  const filters=`<div id="bsm-allowance-filters" class="bsm-filters" style="display:none"><label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="allowance_date_from"></label><label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="allowance_date_to"></label>${selectHtml('allowance_filter_employee','Employee','users','')}${selectHtml('allowance_filter_type','Allowance Type','allowance_types','')}<button class="bsm-icon-btn bsm-payment-clear" id="allowance_clear" title="Clear">×</button></div>`;
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-allowance-search" class="bsm-search" aria-label="Search" placeholder=""><button id="bsm-allowance-filter-toggle" class="bsm-icon-btn">☷</button><div class="bsm-spacer"></div>${excelButton('bsm-allowance-export')}</div>${filters}<div id="bsm-allowance-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  App.allowanceMode=mode;await loadAllowanceQueue();
}
async function loadAllowanceQueue(exportAll=false){
  const mode=App.allowanceMode||'approval';
  const r=await ajax('bsm_allowance_list',{mode:mode==='payment'?'payment':(mode==='all'?'all':'approval'),page:exportAll?1:App.page,export_all:exportAll?'1':'',search:$('#bsm-allowance-search').val()||'',date_from:$('#allowance_date_from').val()||'',date_to:$('#allowance_date_to').val()||'',employee_id:$('#allowance_filter_employee').val()||'',allowance_type_id:$('#allowance_filter_type').val()||''});
  if(!r.success){if(!exportAll)$('#bsm-allowance-list').html('<div class="bsm-empty">Unable to load allowances.</div>');return null;}
  if(exportAll)return r.data.rows||[];
  const rows=r.data.rows||[];
  let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Date</th><th>Employee</th><th>Site</th><th>Customer</th><th>Allowance Type</th><th class="num">Amount</th><th>Purpose</th><th>Document</th>';
  if(mode==='payment') h+='<th>Approved By</th><th>Action</th>';
  else if(mode==='all') h+='<th>Status</th><th>Approved By</th>';
  else h+='<th>Action</th>';
  h+='</tr></thead><tbody>';
  rows.forEach((x,i)=>{
    h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${dateFmt(x.allowance_date)}</td><td>${esc(x.employee_name||'')}</td><td>${esc(x.site_name||'')}</td><td>${esc(x.customer_name||'')}</td><td>${esc(x.allowance_type_name||'')}</td><td class="num">${moneyFmt(x.amount)}</td><td>${esc(x.purpose||'')}</td><td>${x.document_url?`<a href="${esc(x.document_url)}" target="_blank" rel="noopener">View</a>`:'-'}</td>`;
    if(mode==='payment') h+=`<td>${esc(x.approved_by_name||'')}</td><td><button class="bsm-btn bsm-btn-small bsm-btn-success bsm-pay-allowance" data-id="${x.id}">Pay</button></td>`;
    else if(mode==='all') h+=`<td><span class="bsm-pill">${esc(String(x.status||'').replaceAll('_',' '))}</span></td><td>${esc(x.approved_by_name||'')}</td>`;
    else h+=`<td><button class="bsm-btn bsm-btn-small bsm-btn-success bsm-allowance-approve" data-id="${x.id}">Approve</button> <button class="bsm-btn bsm-btn-small bsm-allowance-reject" data-id="${x.id}">Reject</button></td>`;
    h+='</tr>';
  });
  if(!rows.length)h+=`<tr><td colspan="${mode==='all'?11:(mode==='payment'?11:10)}" class="bsm-empty">${mode==='all'?'No allowance records found.':'No pending allowances.'}</td></tr>`;
  h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn allowance-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn allowance-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-allowance-list').html(h);return rows;
}
async function refreshAllowanceLimitBox(){
  const employeeId=BSM.currentUser?.plugin_id||'';const typeId=$('#allowance_type').val()||'';const $box=$('#bsm-allowance-limit-box');
  if(!employeeId||!typeId){$box.hide().empty();return;}
  const r=await ajax('bsm_allowance_limits',{page:1,employee_id:employeeId,allowance_type_id:typeId,status:'active'});$('#allowance_amount').removeAttr('min max');if(!r.success||!(r.data.rows||[]).length){$box.html('<strong>Allowance Limit:</strong> Not configured').show();return;}
  const x=r.data.rows[0];$box.html(`<div><strong>Allowance Limit:</strong> ${moneyFmt(x.limit_amount)}</div>`).show();
}

async function openAllowancePayment(id){
  const rr=await ajax('bsm_allowance_list',{mode:'payment',page:1,export_all:'1'});if(!rr.success)return showToast('Unable to load allowance',true);const x=(rr.data.rows||[]).find(r=>String(r.id)===String(id));if(!x)return showToast('Allowance is no longer pending payment',true);
  await ensureOptions(optionTypesForView('vendor_payments'));await openForm('vendor_payments',0);$('#bsm-modal-title').text('Pay Employee Allowance');
  const $type=$('#bsm-form-fields select[name="payment_type"]');$type.val('employee_allowance');$type.find('option[value="employee_allowance"]').text(x.allowance_type_name||'Employee Allowance');$type.prop('disabled',true);bsmRefreshSearchableSelect($type[0]);
  $('#bsm-form-fields input[name="allowance_id"]').val(x.id);$('#bsm-form-fields input[name="allowance_type_id"]').val(x.allowance_type_id);
  const $emp=$('#bsm-form-fields select[name="employee_id"]');$emp.val(String(x.employee_id)).prop('disabled',true);bsmRefreshSearchableSelect($emp[0]);
  $('#bsm-form-fields input[name="payment_date"]').val(dateFmt(BSM.today));$('#bsm-form-fields input[name="amount"]').val(Number(x.amount||0).toFixed(2)).prop('readonly',true);$('#bsm-form-fields textarea[name="remarks"]').val(x.purpose||'');updatePaymentFormVisibility();$('#bsm-modal').show();
}


function salaryMonthDefault(){const d=new Date();return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;}
async function downloadSalaryTemplate(){
  const month=$('#bsm-salary-upload-month').val()||salaryMonthDefault();const r=await ajax('bsm_salary_template_rows');if(!r.success)return showToast(r.data?.message||'Unable to prepare template',true);
  const headers=['Month','Employee ID','Employee Name','Designation','Present Days','Basic Salary','Allowance','Overtime','Incentive','Bonus','Other Earnings','Deduction','Advance Deduction','Other Deduction','Net Salary Paid','Remarks'];
  const rows=(r.data.rows||[]).map(x=>[month,x.employee_code||'',x.employee_name||'',x.designation||'','','','','','','','','','','','','']);
  const q=v=>'"'+String(v??'').replace(/"/g,'""')+'"';const csv='\ufeff'+[headers,...rows].map(r=>r.map(q).join(',')).join('\r\n');const blob=new Blob([csv],{type:'text/csv;charset=utf-8;'});const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download=`Salary Template ${month}.csv`;document.body.appendChild(a);a.click();setTimeout(()=>{URL.revokeObjectURL(a.href);a.remove();},300);
}
async function renderMonthlySalaryUpload(){
  setTitle('Monthly Salary Upload');const month=salaryMonthDefault();App.salaryPreviewRows=[];
  $('#bsm-content').html(`<div class="bsm-panel bsm-salary-upload-panel"><div class="bsm-form-grid"><label>Salary Month<input type="month" id="bsm-salary-upload-month" value="${month}"></label><label class="wide"><span class="bsm-field-label">Salary Excel / CSV</span><input type="file" id="bsm-salary-upload-file" accept=".csv,.xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv"></label></div><div class="bsm-form-actions"><button type="button" class="bsm-btn" id="bsm-salary-template">Download Excel Template</button><button type="button" class="bsm-btn bsm-btn-primary" id="bsm-salary-preview">Upload & Preview</button></div></div><div id="bsm-salary-preview-area"></div>`);
}
function salaryPreviewTable(rows){
  const amt=v=>Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});let h='<div class="bsm-panel"><div class="bsm-panel-head"><h3>Salary Preview</h3><button type="button" id="bsm-generate-salary-slips" class="bsm-btn bsm-btn-primary">Generate Salary Slips</button></div><div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Employee</th><th>Present Days</th><th class="num">Basic</th><th class="num">Allowance</th><th class="num">OT</th><th class="num">Incentive</th><th class="num">Bonus</th><th class="num">Other Earnings</th><th class="num">Deduction</th><th class="num">Advance</th><th class="num">Other Deduction</th><th class="num">Gross</th><th class="num">Net Payable</th><th class="num">Paid</th><th class="num">Balance</th></tr></thead><tbody>';
  rows.forEach((x,i)=>h+=`<tr><td>${i+1}</td><td>${esc(x.employee_name)}<div class="bsm-small-muted">${esc(x.employee_code||'')} · ${esc(x.designation||'')}</div></td><td>${Number(x.present_days||0)}</td><td class="num">${amt(x.basic_salary)}</td><td class="num">${amt(x.allowance)}</td><td class="num">${amt(x.overtime)}</td><td class="num">${amt(x.incentive)}</td><td class="num">${amt(x.bonus)}</td><td class="num">${amt(x.other_earnings)}</td><td class="num">${amt(x.deduction)}</td><td class="num">${amt(x.advance_deduction)}</td><td class="num">${amt(x.other_deduction)}</td><td class="num">${amt(x.gross_salary)}</td><td class="num"><strong>${amt(x.net_salary_payable)}</strong></td><td class="num">${amt(x.net_salary_paid)}</td><td class="num"><strong>${amt(x.balance_payable)}</strong></td></tr>`);
  h+='</tbody></table></div></div>';return h;
}
async function previewSalaryUpload(){
  const file=$('#bsm-salary-upload-file')[0]?.files?.[0];const month=$('#bsm-salary-upload-month').val();if(!month)return showToast('Select Salary Month',true);if(!file)return showToast('Select Salary Excel / CSV file',true);const fd=new FormData();fd.append('action','bsm_salary_upload_preview');fd.append('nonce',BSM.nonce);fd.append('salary_month',month);fd.append('file',file);const r=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});if(!r.success)return showToast(r.data?.message||'Unable to read salary file',true);const errors=r.data.errors||[];if(errors.length){$('#bsm-salary-preview-area').html(`<div class="bsm-panel"><div class="bsm-error-list"><strong>Please correct these issues:</strong><ul>${errors.map(x=>`<li>${esc(x)}</li>`).join('')}</ul></div></div>`);App.salaryPreviewRows=[];return;}App.salaryPreviewRows=r.data.rows||[];if(!App.salaryPreviewRows.length){$('#bsm-salary-preview-area').html('<div class="bsm-panel bsm-empty">No salary rows found. Fill salary amounts in the template and upload again.</div>');return;}$('#bsm-salary-preview-area').html(salaryPreviewTable(App.salaryPreviewRows));
}
async function commitSalarySlips(){const rows=App.salaryPreviewRows||[];if(!rows.length)return showToast('Upload and preview salary first',true);if(!confirm(`Generate ${rows.length} Salary Slip(s)?`))return;const r=await ajax('bsm_salary_commit',{salary_month:$('#bsm-salary-upload-month').val(),rows:JSON.stringify(rows)});if(r.success){showToast(r.data.message||'Salary Slips generated');App.salaryPreviewRows=[];openView('salary_slips');}else showToast(r.data?.message||'Unable to generate Salary Slips',true);}
async function renderSalarySlips(mode='all'){
  App.salaryMode=mode;App.page=1;const title=mode==='pending'?'Pending Salary Payment':(mode==='my'?'My Salary Slips':'Salary Slips');setTitle(title);const month=salaryMonthDefault();const emp=mode==='my'?'':selectHtml('salary_filter_employee','Employee','users','');const filters=`<div class="bsm-filters report"><label>Month<input type="month" id="salary_filter_month" value="${month}"></label>${emp}<label>Search<input type="text" id="salary_filter_search" aria-label="Search" placeholder=""></label><button class="bsm-icon-btn bsm-purchase-clear-btn" id="salary_filter_clear" title="Clear">${clearIcon()}</button>${mode==='all'?excelButton('salary_slips_export'):''}</div>`;$('#bsm-content').html(`${filters}<div id="bsm-salary-slip-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);await loadSalarySlips();
}
async function loadSalarySlips(exportAll=false){
  const mode=App.salaryMode||'all';const r=await ajax('bsm_salary_slips',{mode,page:exportAll?1:App.page,export_all:exportAll?'1':'',salary_month:$('#salary_filter_month').val()||'',employee_id:$('#salary_filter_employee').val()||'',search:$('#salary_filter_search').val()||''});if(!r.success){if(!exportAll)$('#bsm-salary-slip-list').html('<div class="bsm-empty">Unable to load Salary Slips.</div>');return null;}if(exportAll)return r.data.rows||[];const rows=r.data.rows||[];let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Month</th><th>Employee</th><th>Present Days</th><th class="num">Gross Salary</th><th class="num">Deduction</th><th class="num">Net Payable</th><th class="num">Paid</th><th class="num">Balance</th><th>Status</th><th>PDF</th>'+(mode==='pending'?'<th>Action</th>':'')+'</tr></thead><tbody>';
  rows.forEach((x,i)=>{const pdf=x.pdf_url?`<a href="${esc(x.pdf_url)}" target="_blank" class="bsm-pdf-link" title="Salary Slip PDF" aria-label="Open Salary Slip PDF"><svg class="bsm-pdf-icon" viewBox="0 0 32 36" aria-hidden="true"><path d="M6.5 1.5h12l7 7v25H6.5z" fill="#fff" stroke="#ef2020" stroke-width="2" stroke-linejoin="round"/><path d="M18.5 1.5v7h7" fill="none" stroke="#ef2020" stroke-width="2" stroke-linejoin="round"/><rect x="3" y="19" width="26" height="11" rx="2" fill="#ef2020"/><text x="16" y="27.1" text-anchor="middle" font-size="8.3" font-weight="700" font-family="Arial,Helvetica,sans-serif" fill="#fff">PDF</text></svg></a>`:'-';const action=mode==='pending'?`<button type="button" class="bsm-btn bsm-btn-small bsm-btn-success bsm-pay-salary" data-id="${x.id}">Pay</button>`:'';h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.salary_month)}</td><td>${esc(x.employee_name)}<div class="bsm-small-muted">${esc(x.designation||'')}</div></td><td>${Number(x.present_days||0)}</td><td class="num">${moneyFmt(x.gross_salary)}</td><td class="num">${moneyFmt(x.total_deduction)}</td><td class="num"><strong>${moneyFmt(x.net_salary_payable)}</strong></td><td class="num">${moneyFmt(x.net_salary_paid)}</td><td class="num"><strong>${moneyFmt(x.balance_payable)}</strong></td><td><span class="bsm-pill">${esc(String(x.status||'').replaceAll('_',' '))}</span></td><td>${pdf}</td>${mode==='pending'?`<td>${action}</td>`:''}</tr>`;});if(!rows.length)h+=`<tr><td colspan="${mode==='pending'?12:11}" class="bsm-empty">No salary slips found.</td></tr>`;h+='</tbody></table></div>';if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn salary-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn salary-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;$('#bsm-salary-slip-list').html(h);App.salaryRows=rows;return rows;
}
async function exportSalarySlips(){const rows=await loadSalarySlips(true);if(!rows)return;downloadExcelFile('Salary Slips',['Sl','Month','Employee ID','Employee','Designation','Present Days','Basic Salary','Allowance','Overtime','Incentive','Bonus','Other Earnings','Gross Salary','Deduction','Advance Deduction','Other Deduction','Total Deduction','Net Payable','Paid','Balance','Status'],rows.map((x,i)=>[i+1,x.salary_month,x.employee_code,x.employee_name,x.designation,x.present_days,x.basic_salary,x.allowance,x.overtime,x.incentive,x.bonus,x.other_earnings,x.gross_salary,x.deduction,x.advance_deduction,x.other_deduction,x.total_deduction,x.net_salary_payable,x.net_salary_paid,x.balance_payable,x.status]));}
async function openSalaryPayment(id){
  const rr=await ajax('bsm_salary_slips',{mode:'pending',page:1,export_all:'1'});if(!rr.success)return showToast('Unable to load Salary Slip',true);const x=(rr.data.rows||[]).find(r=>String(r.id)===String(id));if(!x)return showToast('Salary is no longer pending payment',true);await ensureOptions(optionTypesForView('vendor_payments'));await openForm('vendor_payments',0);$('#bsm-modal-title').text('Pay Salary');const $type=$('#bsm-form-fields select[name="payment_type"]');$type.val('salary').prop('disabled',true);bsmRefreshSearchableSelect($type[0]);$('#bsm-form-fields input[name="salary_slip_id"]').val(x.id);const $emp=$('#bsm-form-fields select[name="employee_id"]');$emp.val(String(x.employee_id)).prop('disabled',true);bsmRefreshSearchableSelect($emp[0]);$('#bsm-form-fields input[name="payment_date"]').val(dateFmt(BSM.today));$('#bsm-form-fields input[name="amount"]').val(Number(x.balance_payable||0).toFixed(2)).attr('max',Number(x.balance_payable||0).toFixed(2));$('#bsm-form-fields textarea[name="remarks"]').val(`Salary for ${x.salary_month}`);updatePaymentFormVisibility();$('#bsm-modal').show();
}

async function renderDashboard(siteId=''){
  setTitle('Site Dashboard','HO control panel');
  $('#bsm-content').html('<div class="bsm-loading">Loading…</div>');
  const r=await ajax('bsm_dashboard',{site_id:siteId||''});
  if(!r.success){$('#bsm-content').html('<div class="bsm-empty">Unable to load Dashboard.</div>');return;}
  const c=r.data.cards||{}, sites=r.data.sites||[], docs=r.data.pending_documents||{}, act=r.data.today_activity||{}, oa=r.data.office_attendance||{};
  const pct=v=>`${Number(v||0).toFixed(1)}%`;
  const taskCell=(value,view,site)=>`<button type="button" class="bsm-dashboard-count ${Number(value)>0?'has-pending':'is-clear'}" data-dashboard-go="${view}" data-site="${site||''}" title="Open ${view}">${Number(value||0)}</button>`;
  const attentionClass=n=>Number(n||0)===0?'bsm-attention-clear':(Number(n)>=5?'bsm-attention-high':'bsm-attention-mid');

  let h=`<div class="bsm-dashboard-toolbar">
    <label>Site<select id="bsm-dashboard-site"><option value="">All Sites</option>${(App.options.sites||[]).map(x=>`<option value="${x.id}" ${String(x.id)===String(siteId)?'selected':''}>${esc(x.label)}</option>`).join('')}</select></label>
    <div class="bsm-dashboard-date">Today: <strong>${dateFmt(r.data.today||'')}</strong></div>
  </div>`;

  h+=`<div class="bsm-dashboard-kpis">
    ${dashboardCard('Contractor Outstanding',c.contractor_outstanding,'report_contractor_outstanding',true)}
    ${dashboardCard('Total Pending Actions',c.total_pending,'pending-actions')}
    ${dashboardCard('Pending Approval',c.pending_approval,'pending_requisition_approval')}
    ${dashboardCard('Pending Dispatch',c.pending_dispatch,'pending_material_dispatch')}
    ${dashboardCard('Pending Site Receipt',c.pending_receipt,'pending_site_receipt')}
    ${dashboardCard('Pending Challans',c.pending_challan,'pending_site_receive_challans')}
  </div>`;

  h+=`<div class="bsm-dashboard-summary-grid">
    <div class="bsm-panel bsm-dashboard-summary-card bsm-summary-fund">
      <div class="bsm-panel-head"><div class="bsm-summary-title"><span class="bsm-summary-icon">₹</span><div><h3>Site Fund</h3><small>Collection position</small></div></div><button class="bsm-link-btn" data-dashboard-go="report_customer_outstanding">View →</button></div>
      <div class="bsm-summary-lines"><div><span>Work Order Value</span><strong>${moneyFmt(c.work_order_value)}</strong></div><div><span>Received</span><strong>${moneyFmt(c.received)} <small class="bsm-positive-badge">${pct(c.received_pct)}</small></strong></div><div><span>Outstanding</span><strong>${moneyFmt(c.outstanding)} <small class="bsm-warning-badge">${pct(c.outstanding_pct)}</small></strong></div></div>
      <div class="bsm-summary-progress" title="Received ${pct(c.received_pct)}"><span style="width:${Math.max(0,Math.min(100,Number(c.received_pct||0)))}%"></span></div>
    </div>
    <div class="bsm-panel bsm-dashboard-summary-card bsm-summary-petty">
      <div class="bsm-panel-head"><div class="bsm-summary-title"><span class="bsm-summary-icon">₹</span><div><h3>Petty Cash</h3><small>Site cash position</small></div></div><button class="bsm-link-btn" data-dashboard-go="report_site_fund">View →</button></div>
      <div class="bsm-summary-lines"><div class="bsm-summary-primary"><span>Current Balance</span><strong>${moneyFmt(c.petty_cash_balance)}</strong></div><div><span>Today's Site Expense</span><strong>${moneyFmt(c.today_expense)}</strong></div><div><span>Today's Labour</span><strong>${Number(c.today_labour||0).toLocaleString('en-IN')}</strong></div></div>
    </div>
    <div class="bsm-panel bsm-dashboard-summary-card bsm-summary-docs">
      <div class="bsm-panel-head"><div class="bsm-summary-title"><span class="bsm-summary-icon">▧</span><div><h3>Pending Documents</h3><small>Copies still required</small></div></div></div>
      <div class="bsm-summary-lines"><button data-dashboard-go="pending_invoice_copy"><span>Supplier Invoice Copies</span><strong class="bsm-doc-count ${Number(docs.supplier_invoices||0)>0?'has-pending':'is-clear'}">${Number(docs.supplier_invoices||0)}</strong></button><button data-dashboard-go="pending_contractor_bill_copy"><span>Contractor Bill Copies</span><strong class="bsm-doc-count ${Number(docs.contractor_bills||0)>0?'has-pending':'is-clear'}">${Number(docs.contractor_bills||0)}</strong></button><button data-dashboard-go="pending_site_receive_challans"><span>Site Receive Challans</span><strong class="bsm-doc-count ${Number(docs.site_challans||0)>0?'has-pending':'is-clear'}">${Number(docs.site_challans||0)}</strong></button></div>
    </div>
  </div>`;

  h+=`<div class="bsm-panel bsm-dashboard-site-work"><div class="bsm-panel-head"><h3>Site-wise Pending Work</h3></div><div class="bsm-table-wrap"><table><thead><tr><th>Site</th><th class="num">Req. Approval</th><th class="num">Pending Dispatch</th><th class="num">Pending Receipt</th><th class="num">Pending Challan</th><th class="num">Contractor Bill</th><th class="num">Labour Attendance</th><th class="num">Total Pending</th></tr></thead><tbody>`;
  sites.forEach(x=>{
    h+=`<tr class="${attentionClass(x.total_pending)}"><td><strong>${esc(x.site_name||'')}</strong></td><td class="num">${taskCell(x.pending_approval,'pending_requisition_approval',x.site_id)}</td><td class="num">${taskCell(x.pending_dispatch,'pending_material_dispatch',x.site_id)}</td><td class="num">${taskCell(x.pending_receipt,'pending_site_receipt',x.site_id)}</td><td class="num">${taskCell(x.pending_challan,'pending_site_receive_challans',x.site_id)}</td><td class="num">${taskCell(x.pending_contractor_bill_copy,'pending_contractor_bill_copy',x.site_id)}</td><td class="num">${taskCell(x.attendance_pending,'labour_attendance',x.site_id)}</td><td class="num"><strong>${Number(x.total_pending||0)}</strong></td></tr>`;
  });
  if(!sites.length)h+='<tr><td colspan="9" class="bsm-empty">No active Sites found.</td></tr>';
  h+='</tbody></table></div></div>';

  h+=`<div class="bsm-dashboard-two-col">
    <div class="bsm-panel"><div class="bsm-panel-head"><h3>Site Balance</h3></div><div class="bsm-table-wrap"><table><thead><tr><th>Site</th><th class="num">Work Order</th><th class="num">Received</th><th class="num">Outstanding</th><th class="num">Received %</th><th class="num">Petty Cash</th><th class="num">Today Expense</th></tr></thead><tbody>`;
  sites.forEach(x=>h+=`<tr><td>${esc(x.site_name||'')}</td><td class="num">${moneyFmt(x.work_order_value)}</td><td class="num">${moneyFmt(x.received)}</td><td class="num">${moneyFmt(x.outstanding)}</td><td class="num">${pct(x.received_pct)}</td><td class="num">${moneyFmt(x.petty_cash_balance)}</td><td class="num">${moneyFmt(x.today_expense)}</td></tr>`);
  if(!sites.length)h+='<tr><td colspan="7" class="bsm-empty">No Site data found.</td></tr>';
  h+=`</tbody></table></div></div>
    <div class="bsm-panel"><div class="bsm-panel-head"><h3>Today's Activity</h3></div><div class="bsm-dashboard-activity">
      <button class="bsm-activity-purchases" data-dashboard-go="purchase_register"><span class="bsm-activity-symbol" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5h2l2 11h11l3-8H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg></span><span class="bsm-activity-label">Purchases</span><strong>${Number(act.purchases||0)}</strong></button>
      <button class="bsm-activity-dispatches" data-dashboard-go="material_dispatches"><span class="bsm-activity-symbol" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h11v12H3zM14 10h4l3 4v4h-7z"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg></span><span class="bsm-activity-label">Dispatches</span><strong>${Number(act.dispatches||0)}</strong></button>
      <button class="bsm-activity-receipts" data-dashboard-go="site_receipts"><span class="bsm-activity-symbol" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3h10l4 4v14H5zM15 3v5h4M8 12h8M8 16h6"/></svg></span><span class="bsm-activity-label">Site Receipts</span><strong>${Number(act.receipts||0)}</strong></button>
      <button class="bsm-activity-consumptions" data-dashboard-go="site_consumptions"><span class="bsm-activity-symbol" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 7l8 4 8-4-8-4zM4 12l8 4 8-4M4 17l8 4 8-4"/></svg></span><span class="bsm-activity-label">Consumptions</span><strong>${Number(act.consumptions||0)}</strong></button>
      <button class="bsm-activity-expense" data-dashboard-go="daily_expense_summary"><span class="bsm-activity-symbol" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18v13H3zM3 7V4h15M16 13h5v4h-5a2 2 0 0 1 0-4z"/></svg></span><span class="bsm-activity-label">Site Expense</span><strong>${moneyFmt(act.site_expenses)}</strong></button>
      <button class="bsm-activity-office" data-dashboard-go="office_attendance_master"><span class="bsm-activity-symbol" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21V4h10v17M14 9h6v12M7 8h2M7 12h2M7 16h2M16 13h2M16 17h2M2 21h20"/></svg></span><span class="bsm-activity-label">Office P / A</span><strong>${Number(oa.present||0)} / ${Number(oa.absent||0)}</strong></button>
    </div></div>
  </div>`;

  h+=`<div class="bsm-panel"><div class="bsm-panel-head"><h3>Quick Actions</h3></div><div class="bsm-dashboard-quick-actions">
    <button class="bsm-btn bsm-btn-primary" data-dashboard-go="purchase_form">+ Purchase</button>
    <button class="bsm-btn bsm-btn-primary" data-dashboard-go="requisition_form">+ Requisition</button>
    <button class="bsm-btn bsm-btn-primary" data-dashboard-go="site_expenses">+ Site Expense</button>
    <button class="bsm-btn bsm-btn-primary" data-dashboard-go="customer_receipts">+ Customer Receipt</button>
    <button class="bsm-btn bsm-btn-primary" data-dashboard-go="vendor_payments">+ HO Payment</button>
    <button class="bsm-btn bsm-btn-primary" data-dashboard-go="office_daily_attendance">+ Daily Attendance</button>
    <button class="bsm-btn bsm-btn-primary" data-dashboard-go="report_contractor_outstanding" data-dashboard-direct="1">Contractor Outstanding</button>
    <button class="bsm-btn bsm-btn-primary" data-dashboard-go="report_vendor_outstanding" data-dashboard-direct="1">Supplier Outstanding</button>
    <button class="bsm-btn bsm-btn-primary" id="bsm-dashboard-add-site">+ Add Site</button>
  </div></div>`;
  $('#bsm-content').html(h);
  bsmEnhanceSearchableSelects($('#bsm-content')[0]);
}
function dashboardCard(label,val,target='',isMoney=false){return `<button type="button" class="bsm-dashboard-kpi${isMoney?' bsm-dashboard-kpi-money':''}" ${target&&target!=='site-attention'&&target!=='pending-actions'?`data-dashboard-go="${target}"`:''}><span>${esc(label)}</span><strong>${isMoney?moneyFmt(val):Number(val||0).toLocaleString('en-IN')}</strong></button>`;}
async function openDashboardTarget(view,siteId=''){
  if(!view)return;
  await openView(view);
  if(!siteId)return;
  setTimeout(()=>{
    const selectors=['#bsm-vo-site','#filter_site','#r_site','#requisition_items_site','#dispatch_items_site','#site_receipt_items_site','#consumption_items_site','#daily_expense_site'];
    const $sel=$(selectors.join(',')).filter(function(){return $(this).is(':visible')||$(this).closest('.bsm-filters').length;}).first();
    if($sel.length && $sel.find('option').filter(function(){return this.value===String(siteId);}).length){$sel.val(String(siteId));bsmRefreshSearchableSelect($sel[0]);$sel.trigger('change');}
  },80);
}

async function renderCompanySettings(){
  const cfg=App.configs.company_settings;
  setTitle('Company Settings');
  $('#bsm-content').html('<div class="bsm-panel bsm-settings-panel"><div class="bsm-loading">Loading…</div></div>');
  await ensureOptions(['states'],true);
  const r=await ajax('bsm_list',{module:'company_settings',page:1,search:''});
  if(!r.success){$('.bsm-settings-panel').html('<div class="bsm-empty">Unable to load Company Settings.</div>');return;}
  const row=(r.data.rows&&r.data.rows[0])?r.data.rows[0]:{};
  if(!row.state_id && row.state){
    const match=(App.options.states||[]).find(x=>String(x.label||'').trim().toLowerCase()===String(row.state||'').trim().toLowerCase());
    if(match) row.state_id=match.id;
  }
  App.editId=row.id||0;
  App.companySettingsRow=row;
  const stateName=labelFor('states',row.state_id)||row.state||'-';
  const val=v=>String(v??'').trim()!==''?esc(v):'-';
  let h='<div class="bsm-settings-view-head"><div></div><button type="button" id="bsm-company-settings-edit" class="bsm-btn bsm-btn-primary">Edit</button></div>';
  h+='<div class="bsm-table-wrap bsm-company-settings-table-wrap"><table class="bsm-company-settings-table"><thead><tr><th>Particular</th><th>Details</th></tr></thead><tbody>';
  h+=`<tr><td>Company Name</td><td>${val(row.company_name)}</td></tr>`;
  h+=`<tr><td>Address</td><td class="bsm-preline">${val(row.address)}</td></tr>`;
  h+=`<tr><td>City</td><td>${val(row.city)}</td></tr>`;
  h+=`<tr><td>State</td><td>${val(stateName)}</td></tr>`;
  h+=`<tr><td>PIN Code</td><td>${val(row.pin_code)}</td></tr>`;
  h+=`<tr><td>GSTIN</td><td>${val(row.gstin)}</td></tr>`;
  h+=`<tr><td>PAN</td><td>${val(row.pan)}</td></tr>`;
  h+=`<tr><td>Phone</td><td>${val(row.phone)}</td></tr>`;
  h+=`<tr><td>Email</td><td>${val(row.email)}</td></tr>`;
  h+=`<tr><td>Company Logo</td><td>${row.logo_url?`<img class="bsm-company-settings-logo-view" src="${esc(row.logo_url)}" alt="Company Logo">`:'-'}</td></tr>`;
  h+=`<tr><td>UPI QR</td><td>${row.upi_qr_url?`<img class="bsm-company-settings-logo-view" src="${esc(row.upi_qr_url)}" alt="UPI QR">`:'-'}</td></tr>`;
  h+=`<tr><td>Bank Details</td><td>${[row.bank_account_holder&&`Account Holder: ${row.bank_account_holder}`,row.bank_name&&`Bank: ${row.bank_name}`,row.bank_branch&&`Branch: ${row.bank_branch}`,row.bank_ifsc&&`IFSC: ${row.bank_ifsc}`,row.bank_account_no&&`Account No.: ${row.bank_account_no}`].filter(Boolean).map(esc).join('<br>')||'-'}</td></tr>`;
  h+=`<tr><td>PDF Theme Color</td><td>${row.pdf_theme_color?`<span style="display:inline-block;width:18px;height:18px;border-radius:4px;vertical-align:middle;background:${esc(row.pdf_theme_color)};border:1px solid #bbb;margin-right:8px"></span>${esc(row.pdf_theme_color)}`:'Auto from Company Logo'}</td></tr>`;

  h+='</tbody></table></div>';
  $('.bsm-settings-panel').html(h);
}

function openCompanySettingsEditor(){
  const cfg=App.configs.company_settings;
  const row=App.companySettingsRow||{};
  let h='<form id="bsm-company-settings-form"><div class="bsm-form-grid">';
  h+=cfg.fields.map(f=>fieldHtml(f,row[f.name]??'')).join('');
  h+=`<label class="wide bsm-company-logo-field"><span class="bsm-field-label">Company Logo</span><div class="bsm-company-logo-upload">${row.logo_url?`<img id="bsm-company-logo-preview" src="${esc(row.logo_url)}" alt="Company Logo">`:'<div id="bsm-company-logo-preview" class="bsm-logo-placeholder">No logo uploaded</div>'}<input type="file" id="bsm-company-logo-file" accept="image/png,image/jpeg,image/webp"><button type="button" id="bsm-company-logo-upload-btn" class="bsm-btn">Upload Logo</button></div></label>`;
  h+=`<label class="wide bsm-company-logo-field"><span class="bsm-field-label">UPI QR</span><div class="bsm-company-logo-upload">${row.upi_qr_url?`<img id="bsm-company-upi-qr-preview" src="${esc(row.upi_qr_url)}" alt="UPI QR">`:'<div id="bsm-company-upi-qr-preview" class="bsm-logo-placeholder">No UPI QR uploaded</div>'}<input type="file" id="bsm-company-upi-qr-file" accept="image/png,image/jpeg,image/webp"><button type="button" id="bsm-company-upi-qr-upload-btn" class="bsm-btn">Upload UPI QR</button></div></label>`;

  h+='</div><div class="bsm-form-actions"><button type="button" id="bsm-company-settings-cancel" class="bsm-btn bsm-btn-cancel">Cancel</button><button type="submit" class="bsm-btn bsm-btn-primary">Save</button></div></form>';
  $('.bsm-settings-panel').html(h);
  bsmEnhanceSearchableSelects(document.getElementById('bsm-company-settings-form'));
  const $state=$('#bsm-company-settings-form select[name="state_id"]');
  if($state.length){
    $state.val(String(row.state_id||''));
    bsmRefreshSearchableSelect($state[0]);
    setTimeout(()=>bsmRefreshSearchableSelect($state[0]),0);
  }
}

async function uploadCompanyLogoFile(file,{reload=false,silent=false}={}){
  if(!file)return {success:true,url:''};
  const allowed=['image/png','image/jpeg','image/webp'];
  const ext=String(file.name||'').split('.').pop().toLowerCase();
  if((file.type && !allowed.includes(file.type)) || !['png','jpg','jpeg','webp'].includes(ext)){
    if(!silent)showToast('Logo must be PNG, JPG or WEBP',true);return {success:false,message:'Logo must be PNG, JPG or WEBP'};
  }
  if(Number(file.size||0)>1024*1024){
    if(!silent)showToast('Logo file must be 1 MB or smaller',true);return {success:false,message:'Logo file must be 1 MB or smaller'};
  }
  try{
    const dataUrl=await new Promise((resolve,reject)=>{const fr=new FileReader();fr.onload=()=>resolve(String(fr.result||''));fr.onerror=()=>reject(new Error('read failed'));fr.readAsDataURL(file);});
    const r=await ajax('bsm_company_logo_upload',{filename:file.name||'logo',mime:file.type||'',data:dataUrl});
    if(!r.success){const message=r.data?.message||'Logo upload failed';if(!silent)showToast(message,true);return {success:false,message};}
    $('#bsm-company-logo-preview').replaceWith(`<img id="bsm-company-logo-preview" src="${esc(dataUrl)}" alt="Company Logo">`);
    if(!silent)showToast('Logo uploaded');
    if(reload)setTimeout(()=>location.reload(),500);
    return {success:true,url:dataUrl};
  }catch(e){const message='Logo upload failed while reading or saving the image.';if(!silent)showToast(message,true);return {success:false,message};}
}

async function uploadCompanyUpiQrFile(file,{silent=false}={}){
  if(!file)return {success:true,url:''};
  const allowed=['image/png','image/jpeg','image/webp'];
  const ext=String(file.name||'').split('.').pop().toLowerCase();
  if((file.type && !allowed.includes(file.type)) || !['png','jpg','jpeg','webp'].includes(ext)){if(!silent)showToast('UPI QR must be PNG, JPG or WEBP',true);return {success:false,message:'UPI QR must be PNG, JPG or WEBP'};}
  if(Number(file.size||0)>2*1024*1024){if(!silent)showToast('UPI QR file must be 2 MB or smaller',true);return {success:false,message:'UPI QR file must be 2 MB or smaller'};}
  try{
    const dataUrl=await new Promise((resolve,reject)=>{const fr=new FileReader();fr.onload=()=>resolve(String(fr.result||''));fr.onerror=()=>reject(new Error('read failed'));fr.readAsDataURL(file);});
    const r=await ajax('bsm_company_upi_qr_upload',{filename:file.name||'upi-qr',mime:file.type||'',data:dataUrl});
    if(!r.success){const message=r.data?.message||'UPI QR upload failed';if(!silent)showToast(message,true);return {success:false,message};}
    $('#bsm-company-upi-qr-preview').replaceWith(`<img id="bsm-company-upi-qr-preview" src="${esc(dataUrl)}" alt="UPI QR">`);
    if(!silent)showToast('UPI QR uploaded');
    return {success:true,url:dataUrl};
  }catch(e){const message='UPI QR upload failed while reading or saving the image.';if(!silent)showToast(message,true);return {success:false,message};}
}

async function saveCompanySettings(){
  const data={id:App.editId};
  $('#bsm-company-settings-form').find('input,select,textarea').each(function(){if(!this.name)return;data[this.name]=isDateFieldName(this.name)?dateSql($(this).val()):$(this).val();});
  const $save=$('#bsm-company-settings-form button[type="submit"]').prop('disabled',true).text('Saving...');
  const r=await ajax('bsm_save',{module:'company_settings',data:JSON.stringify(data)});
  if(!r.success){$save.prop('disabled',false).text('Save');showToast(r.data?.message||'Save failed',true);return;}
  App.editId=r.data.id||App.editId;
  const file=$('#bsm-company-logo-file')[0]?.files?.[0]||null;
  if(file){
    $save.text('Uploading Logo...');
    const up=await uploadCompanyLogoFile(file,{silent:true});
    if(!up.success){$save.prop('disabled',false).text('Save');showToast('Company Settings saved, but '+(up.message||'Logo upload failed'),true);return;}
  }
  const qrFile=$('#bsm-company-upi-qr-file')[0]?.files?.[0]||null;
  if(qrFile){
    $save.text('Uploading UPI QR...');
    const upQr=await uploadCompanyUpiQrFile(qrFile,{silent:true});
    if(!upQr.success){$save.prop('disabled',false).text('Save');showToast('Company Settings saved, but '+(upQr.message||'UPI QR upload failed'),true);return;}
  }
  showToast('Company Settings saved');
  setTimeout(()=>location.reload(),500);
}

async function renderModule(key){
  const cfg=App.configs[key]; setTitle(cfg.title);
  const isMaster=masterModules.includes(key);
  let toolbar=`<div class="bsm-toolbar"><input id="bsm-search" class="bsm-search" aria-label="Search" placeholder="" value="${esc(App.search)}">${isMaster?'':`<button id="bsm-filter-toggle" class="bsm-icon-btn" title="Filters">☷</button>`}<div class="bsm-spacer"></div>${excelButton()}${key==='material_requisitions'?'':'<button id="bsm-add" class="bsm-btn bsm-btn-primary">+</button>'}</div>`;
  let filters=isMaster?'':`<div id="bsm-filters" class="bsm-filters ${key==='vendor_payments'?'bsm-payment-filters':''}" style="display:none">${commonFilters(key)}<button class="bsm-btn bsm-clear ${key==='vendor_payments'?'bsm-payment-clear':''}">Clear</button></div>`;
  $('#bsm-content').html(toolbar+filters+'<div id="bsm-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>'); await loadList(key);
}
function commonFilters(key){
  let h=''; if(['labour_attendance','material_requisitions','material_dispatches','site_receipts','site_consumptions','material_returns','site_opening_stock','site_fund_allocations','site_expenses','customer_receipts','vendor_payments','contractor_bills','contractor_payments','gst_invoices'].includes(key)) h+=selectHtml('filter_site','Site','sites','');
  if(key==='vendor_payments'){const allowanceOpts=(App.options.allowance_types||[]).map(x=>`<option value="allowance_${x.id}">${esc(x.label)}</option>`).join('');h+=`<label>Payment Type<select id="filter_payment_type"><option value="">Select</option><option value="head_office_vendor">Head Office Supplier Payment</option><option value="site_vendor">Supplier Payment for Site</option><option value="site_fund">Site Petty Cash Transfer</option><option value="contractor_payment">Labour Contractor Payment</option><option value="contractor_advance">Labour Contractor Advance</option><option value="salary">Salary</option>${allowanceOpts}</select></label>`;}
  if(['material_inwards'].includes(key)) h+=selectHtml('filter_vendor','Supplier','vendors','');
  if(['labour_attendance','contractor_bills','contractor_payments'].includes(key)) h+=selectHtml('filter_contractor','Contractor','contractors','');
  if(['material_inwards','material_dispatches'].includes(key)) h+=selectHtml('filter_warehouse','Warehouse','warehouses','');
  if(!['customers','sites','vendors','labour_contractors','item_categories','units','gst_rates','items','warehouses','expense_categories','company_settings'].includes(key)){
    if(key==='vendor_payments') h+=`<label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="filter_date_from"></label><label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="filter_date_to"></label>`;
    else h+=`<label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="filter_date_from"></label><label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="filter_date_to"></label>`;
  }
  return h;
}

function currentListRequest(key,exportAll=false){const cfg=App.configs[key]||{};return {module:key,page:App.page,search:App.search,visible_columns:JSON.stringify((cfg.columns||[]).map(c=>({key:c.key,type:c.type||'text'}))),site_id:$('#filter_site').val()||'',vendor_id:$('#filter_vendor').val()||'',contractor_id:$('#filter_contractor').val()||'',warehouse_id:$('#filter_warehouse').val()||'',payment_type:$('#filter_payment_type').val()||'',date_from:dateSql($('#filter_date_from').val()||''),date_to:dateSql($('#filter_date_to').val()||''),export_all:exportAll?'1':''};}
async function exportCurrentList(){
  if(App.current==='purchase_register'){
    const cfg=App.configs.material_inwards;
    const r=await ajax('bsm_list',{module:'material_inwards',page:1,search:App.search,inward_type:'purchase',vendor_id:$('#purchase_vendor').val()||'',date_from:dateSql($('#purchase_date_from').val()||''),date_to:dateSql($('#purchase_date_to').val()||''),export_all:'1',visible_columns:JSON.stringify([...(cfg.columns||[]),C('gst_type','GST Type'),C('bill_type','Bill Type'),C('supplier_invoice_no','Supplier Invoice No.'),C('supplier_invoice_date','Supplier Invoice Date','date')].map(c=>({key:c.key,type:c.type||'text'})))});
    if(!r.success)return showToast(r.data?.message||'Unable to export',true);
    const purchaseCols=[...cfg.columns];
    const vendorIndex=purchaseCols.findIndex(c=>c.key==='vendor_id');
    const extraCols=[C('gst_type','GST Type'),C('bill_type','Bill Type'),C('supplier_invoice_no','Supplier Invoice No.'),C('supplier_invoice_date','Supplier Invoice Date','date')];
    if(vendorIndex>=0) purchaseCols.splice(vendorIndex+1,0,...extraCols); else purchaseCols.push(...extraCols);
    const headers=['Sl',...purchaseCols.map(c=>c.label),'Approved By'];
    const rows=(r.data.rows||[]).map((row,i)=>[i+1,...purchaseCols.map(c=>plainCell(row,c)),purchaseApprovedByName(row)==='-'?'':purchaseApprovedByName(row)]);
    return downloadExcelFile('Purchase Master',headers,rows);
  }
  const key=App.current,cfg=App.configs[key];if(!cfg)return;
  const r=await ajax('bsm_list',currentListRequest(key,true));if(!r.success)return showToast(r.data?.message||'Unable to export',true);
  const headers=['Sl',...cfg.columns.map(c=>c.label)];
  const rows=(r.data.rows||[]).map((row,i)=>[i+1,...cfg.columns.map(c=>plainCell(row,c))]);
  downloadExcelFile(cfg.title,headers,rows);
}
async function loadList(key){
  const cfg=App.configs[key], data=currentListRequest(key,false);
  const r=await ajax('bsm_list',data); if(!r.success){$('#bsm-list').html('<div class="bsm-empty">Unable to load.</div>');return;}
  const rows=r.data.rows||[]; let h=`<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th>${cfg.columns.map(c=>`<th class="${c.type==='money'||c.type==='number'||c.type==='percent'?'num':''}">${esc(c.label)}</th>`).join('')}<th>Action</th></tr></thead><tbody>`;
  rows.forEach((row,i)=>{let actions='';if(!(key==='vendor_payments'&&row._source==='contractor')){const edit=`<button class="bsm-action-edit" data-id="${row.id}" data-allowed="${row._can_edit!==0?1:0}" data-message="${esc(row._edit_message||'This record cannot be edited.')}" title="Edit">✎</button>`;const del=`<button class="bsm-action-delete" data-id="${row.id}" data-allowed="${row._can_delete!==0?1:0}" data-message="${esc(row._delete_message||'This record cannot be deleted.')}" title="Delete"><img class="bsm-delete-icon" src="${BSM.deleteIcon}" alt="Delete"></button>`;const slip=key==='material_dispatches'?`<button type="button" class="bsm-pdf-link bsm-action-dispatch-slip" data-id="${row.id}" title="Download Dispatch Slip PDF" aria-label="Download Dispatch Slip PDF"><svg class="bsm-pdf-icon" viewBox="0 0 32 36" aria-hidden="true"><path d="M6.5 1.5h12l7 7v25H6.5z" fill="#fff" stroke="#ef2020" stroke-width="2" stroke-linejoin="round"/><path d="M18.5 1.5v7h7" fill="none" stroke="#ef2020" stroke-width="2" stroke-linejoin="round"/><rect x="3" y="19" width="26" height="11" rx="2" fill="#ef2020"/><text x="16" y="27.1" text-anchor="middle" font-size="8.3" font-weight="700" font-family="Arial,Helvetica,sans-serif" fill="#fff">PDF</text></svg></button>`:'';actions=edit+slip+del;}let rowClass='',rowAttrs='';if(key==='labour_attendance'){rowClass=' class="bsm-labour-attendance-row"';rowAttrs=` data-id="${Number(row.id)}" tabindex="0" style="cursor:pointer" title="Open attendance"`;}else if(key==='material_requisitions'){rowClass=' class="bsm-requisition-row"';rowAttrs=` data-id="${row.id}" tabindex="0" aria-expanded="false" title="Click to view requisition items"`;}else if(key==='material_dispatches'){rowClass=' class="bsm-dispatch-row"';rowAttrs=` data-id="${row.id}" tabindex="0" aria-expanded="false" title="Click to view dispatch items"`;}else if(key==='site_receipts'){rowClass=' class="bsm-site-receipt-row"';rowAttrs=` data-id="${row.id}" tabindex="0" aria-expanded="false" title="Click to view receipt items"`;}else if(key==='site_consumptions'){rowClass=' class="bsm-consumption-row"';rowAttrs=` data-id="${row.id}" tabindex="0" aria-expanded="false" title="Click to view consumption items"`;}else if(key==='material_returns'){rowClass=' class="bsm-material-return-row"';rowAttrs=` data-id="${row.id}" tabindex="0" aria-expanded="false" title="Click to view return items"`;}h+=`<tr${rowClass}${rowAttrs}><td>${(r.data.page-1)*20+i+1}</td>${cfg.columns.map(c=>`<td class="${c.type==='money'||c.type==='number'||c.type==='percent'?'num':''}">${renderCell(row,c)}</td>`).join('')}<td class="bsm-actions">${actions}</td></tr>`;});
  if(!rows.length)h+=`<tr><td colspan="${cfg.columns.length+2}" class="bsm-empty">No records found.</td></tr>`;h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-list').html(h);
}

function selectHtml(id,label,type,val,required=false){ const arr=App.options[type]||[]; return `<label>${esc(label)}<select id="${id}" ${required?'required':''}><option value="">Select</option>${arr.map(x=>`<option value="${x.id}" ${String(x.id)===String(val)?'selected':''}>${esc(x.label)}</option>`).join('')}</select></label>`; }
function fieldHtml(f,val=''){
  const req=f.required?'required':''; const ro=f.readonly?'readonly':''; const dis=f.readonly?'disabled':''; const wide=f.wide?'wide':''; const v=val==null?'':val; let input='';
  if(f.type==='site_sections') {
    const selected=new Set(String(v||'').split(',').map(x=>x.trim()).filter(Boolean));
    const opts=(App.options.sections||[]);
    const options=opts.map(o=>`<label class="bsm-site-section-option"><input type="checkbox" value="${esc(o.label)}" ${selected.has(String(o.label))?'checked':''}><span>${esc(o.label)}</span></label>`).join('');
    const old=[...selected].filter(x=>!opts.some(o=>String(o.label)===x)).map(x=>`<label class="bsm-site-section-option"><input type="checkbox" value="${esc(x)}" checked><span>${esc(x)} (Existing)</span></label>`).join('');
    return `<div class="bsm-site-section-field"><span class="bsm-field-label">Section</span><div class="bsm-site-section-multi"><button type="button" class="bsm-site-section-toggle">${selected.size?esc([...selected].join(', ')):'Select Sections'} <span>⌄</span></button><div class="bsm-site-section-menu"><input class="bsm-site-section-search" type="text" aria-label="Search Sections" placeholder="" autocomplete="off">${options}${old}${!opts.length&&!old?'<div class="bsm-site-section-empty">Add Sections in Section Master first.</div>':''}</div></div><input type="hidden" name="site_sections" value="${esc([...selected].join(', '))}"></div>`;
  }
  if(f.type==='hidden') return `<input type="hidden" name="${f.name}" value="${esc(v)}">`;
  if(f.type==='display') input=`<input type="text" class="bsm-display-field bsm-display-${esc(f.name)}" value="${esc(v)}" readonly tabindex="-1" data-display-name="${esc(f.name)}">`;
  else if(f.type==='textarea')input=`<textarea name="${f.name}" ${req} ${ro}>${esc(v)}</textarea>`;
  else if(f.type==='select_ref') {
    const nativeClass=f.name==='gst_rate'?' class="bsm-native-gst-rate"':'';
    const select=`<select${nativeClass} name="${f.name}" ${req} ${dis}><option value="">Select</option>${bsmOptionsForRef(f.ref).map(x=>`<option value="${x.id}" ${(String(x.id)===String(v)||Number(x.id)===Number(v))?'selected':''}>${esc(x.label)}</option>`).join('')}</select>`;
    input=f.addMaster?`<div class="bsm-select-add">${select}<button type="button" class="bsm-quick-add" data-master="${esc(f.addMaster)}" data-target="${esc(f.name)}" title="Add new">+</button></div>`:select;
  } else if(f.type==='select') input=`<select name="${f.name}" ${req} ${dis}>${(f.options||[]).map(x=>`<option value="${esc(x[0])}" ${String(x[0])===String(v)?'selected':''}>${esc(x[1])}</option>`).join('')}</select>`;
  else if(f.type==='date') input=`<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" name="${f.name}" value="${esc(normalizeDateInput(v))}" ${req} ${ro}>`;
  else {
    const isMobile=(f.name==='mobile'||f.name.endsWith('_mobile'));
    input=`<input type="${f.type||'text'}" name="${f.name}" value="${esc(v)}" ${req} ${ro} ${f.step?`step="${f.step}"`:''} ${f.min!=null?`min="${f.min}"`:''} ${isMobile?'inputmode="numeric" maxlength="10" pattern="[0-9]{10}" autocomplete="tel"':''}>`;
  }
  return `<label class="${wide}"><span class="bsm-field-label">${esc(f.label)}${f.required&&f.label?'<span class="bsm-required">*</span>':''}</span>${input}</label>`;
}

function updatePaymentFormVisibility(){
  if($('#bsm-form').data('module')!=='vendor_payments')return;
  const type=String($('#bsm-form-fields select[name="payment_type"]').val()||'head_office_vendor');
  const $vendor=$('#bsm-form-fields select[name="vendor_id"]');
  const $inward=$('#bsm-form-fields select[name="material_inward_id"]');
  const $site=$('#bsm-form-fields select[name="site_id"]');
  const $employee=$('#bsm-form-fields select[name="employee_id"]');
  const isAllowance=type==='employee_allowance';
  const isSalary=type==='salary';
  const showEmployee=isAllowance||isSalary;
  const showVendor=type==='head_office_vendor'||type==='site_vendor';
  const showSite=type==='site_vendor'||type==='site_fund';
  $vendor.closest('label').toggle(showVendor);$inward.closest('label').toggle(showVendor);$site.closest('label').toggle(showSite);$employee.closest('label').toggle(showEmployee);
  $vendor.prop('required',showVendor);$site.prop('required',showSite);$employee.prop('required',showEmployee);
  if(!showVendor){$vendor.val('');$inward.val('');bsmRefreshSearchableSelect($vendor[0]);bsmRefreshSearchableSelect($inward[0]);}
  if(!showSite){$site.val('');bsmRefreshSearchableSelect($site[0]);}
  if(!showEmployee){$employee.val('');bsmRefreshSearchableSelect($employee[0]);}
}



async function refreshContractorAvailableAdvance(){
  if($('#bsm-form').data('module')!=='contractor_bills')return;
  const contractorId=$('#bsm-form-fields select[name="contractor_id"]').val()||'';
  const siteId=$('#bsm-form-fields select[name="site_id"]').val()||'';
  const $available=$('#bsm-form-fields input[name="available_advance"]');
  const $adjust=$('#bsm-form-fields input[name="advance_adjustment"]');
  if(!contractorId||!siteId){$available.val('0.00');$adjust.attr('max','0.00');return;}
  const r=await ajax('bsm_contractor_advance_available',{contractor_id:contractorId,site_id:siteId,bill_id:App.editId||0});
  if(!r.success)return;
  const available=Number(r.data?.available||0);$available.val(available.toFixed(2));$adjust.attr('max',available.toFixed(2));
}

function recalcContractorBillTotal(){
  const $form=$('#bsm-form[data-module="contractor_bills"]');
  if(!$form.length)return;
  const amount=Number($form.find('[name="taxable_amount"]').val()||0);
  const gst=Number($form.find('[name="gst_amount"]').val()||0);
  const total=Math.round((amount+gst)*100)/100;
  $form.find('[name="total_amount"]').val(total ? total.toFixed(2) : '');
}

function updateBankFieldVisibility(scope){
  const $scope=scope?$(scope):$(document);
  $scope.find('select[name="bank_account_id"]').each(function(){
    const $bank=$(this);
    $bank.closest('label').show();
    const $form=$bank.closest('#bsm-form, .bsm-form-grid, form');
    const $mode=($form.length?$form:$('#bsm-form-fields')).find('select[name="payment_mode"]').first();
    const isBank=String($mode.val()||'').toLowerCase()==='bank';
    $bank.prop('required',isBank);
  });
}

function contractorEmployeeRow(employee={}){
  const fields=[['employee_name','Name'],['mobile','Mobile'],['address','Address'],['bank_name','Bank'],['ifsc','IFSC'],['account_number','AC No.']];
  return `<tr data-employee-id="${Number(employee.id||0)}">${fields.map(([field,label])=>`<td>${field==='address'?`<textarea data-employee-field="${field}" aria-label="${label}" rows="2">${esc(employee[field]||'')}</textarea>`:`<input type="text" data-employee-field="${field}" aria-label="${label}" value="${esc(employee[field]||'')}" ${field==='mobile'?'inputmode="numeric" maxlength="10" pattern="[0-9]{10}"':field==='account_number'?'inputmode="numeric" maxlength="60"':field==='ifsc'?'maxlength="11"':''}>`}</td>`).join('')}<td><button type="button" class="bsm-btn bsm-contractor-employee-remove" aria-label="Remove Employee">×</button></td></tr>`;
}
function renderContractorEmployeeTable(employees=[]){
  $('#bsm-line-items').html(`<div class="bsm-contractor-employees"><div class="bsm-lines-head"><h3>Employees</h3><button type="button" id="bsm-contractor-employee-add" class="bsm-btn">+ Add Employee</button></div><div class="bsm-table-wrap"><table class="bsm-table"><thead><tr><th>Name</th><th>Mobile</th><th>Address</th><th>Bank</th><th>IFSC</th><th>AC No.</th><th>Action</th></tr></thead><tbody id="bsm-contractor-employee-rows">${(employees.length?employees:[{}]).map(contractorEmployeeRow).join('')}</tbody></table></div></div>`);
}
$(document).on('click','#bsm-contractor-employee-add',function(){$('#bsm-contractor-employee-rows').append(contractorEmployeeRow());});
$(document).on('click','.bsm-contractor-employee-remove',function(){$(this).closest('tr').remove();});

async function openForm(key,id=0){
  const cfg=App.configs[key];App.editId=id;
  $('#bsm-modal').toggleClass('bsm-site-receipt-modal',key==='site_receipts').toggleClass('bsm-purchase-edit-modal',key==='material_inwards' && !!id);
  // Set the active form module BEFORE module-specific initializers run.
  // Site Consumption stock loading checks this value and otherwise exits early.
  $('#bsm-form').attr('data-module',key).data('module',key);
  let row={}; if(id){const r=await ajax('bsm_get',{module:key,id});if(!r.success)return showToast(r.data?.message||'Not found',true);row=r.data;}
  $('#bsm-form-fields').removeClass('bsm-approval-summary bsm-requisition-summary bsm-dispatch-summary bsm-contractor-summary'); $('#bsm-modal-title').text((id?'Edit ':'Add ')+cfg.title.replace(/s$/,'')); const formFields=(!id&&(masterModules.includes(key)||key==='material_requisitions'))?cfg.fields.filter(f=>f.name!=='status'):cfg.fields; $('#bsm-form-fields').html(formFields.map(f=>fieldHtml(f,row[f.name]??defaultValue(f))).join(''));
  if(key==='site_consumptions'){
    const entryBy=id?(row.created_by_name||BSM.currentUser?.name||''):(BSM.currentUser?.name||'');
    $('#bsm-form-fields .bsm-display-created_by_display').val(entryBy);
  }
  if(key==='vendor_payments'){
    updatePaymentFormVisibility();
    const paymentDocs=(row._documents||[]).filter(d=>String(d.document_type||'').toLowerCase()==='payment document');
    const existingPaymentDoc=paymentDocs.map(d=>`<a href="${esc(d.file_url)}" target="_blank" rel="noopener">${esc(d.original_filename||'Payment Document')} ↗</a>`).join('');
    $('#bsm-form-fields').append(`<label class="wide bsm-payment-document-field"><span class="bsm-field-label">Payment Document</span><input type="file" id="bsm-payment-document-file" accept="application/pdf,image/*">${existingPaymentDoc?`<div class="bsm-payment-existing-doc">${existingPaymentDoc}</div>`:''}</label>`);
  }
  if(key==='contractor_advances'){
    $('#bsm-form-fields').append(`<div id="bsm-contractor-payment-summary" class="wide bsm-contractor-payment-summary" style="display:none"></div>`);
    const paymentPhotos=(row._documents||[]).filter(d=>String(d.document_type||'').toLowerCase()==='payment photo');
    const existingPhoto=paymentPhotos.map(d=>`<a href="${esc(d.file_url)}" target="_blank" rel="noopener">${esc(d.original_filename||'Payment Photo')} ↗</a>`).join('');
    const photoHtml=`<label class="bsm-contractor-payment-photo-field"><span class="bsm-field-label">Upload Payment Photo</span><input type="file" id="bsm-contractor-payment-photo" accept="image/png,image/jpeg,image/webp">${existingPhoto?`<div class="bsm-payment-existing-doc">${existingPhoto}</div>`:''}</label>`;
    const $remarks=$('#bsm-form-fields textarea[name="remarks"]').closest('label');
    if($remarks.length)$remarks.before(photoHtml); else $('#bsm-form-fields').append(photoHtml);
  }
  if(key==='contractor_bills'){
    $('#bsm-form-fields').addClass('bsm-contractor-summary');
    const existingDocs=(row._documents||[]).map(d=>`<a href="${esc(d.file_url)}" target="_blank">${esc(d.original_filename||d.document_type||'Bill')} ↗</a>`).join('');
    const uploadHtml=`<label class="wide bsm-contractor-doc-field"><span class="bsm-field-label">Upload Bill</span><input type="file" id="bsm-contractor-doc-file">${existingDocs?`<div class="bsm-contractor-existing-docs">${existingDocs}</div>`:''}</label>`;
    const $remarks=$('#bsm-form-fields textarea[name="remarks"]').closest('label');
    if($remarks.length)$remarks.before(uploadHtml); else $('#bsm-form-fields').append(uploadHtml);
    recalcContractorBillTotal();
    refreshContractorAvailableAdvance();
  }
  if(key==='material_inwards'){
    const invoiceDocs=(row._documents||[]).filter(d=>String(d.document_type||'').toLowerCase()==='supplier invoice');
    const existingInvoice=invoiceDocs.map(d=>`<a href="${esc(d.file_url)}" target="_blank" rel="noopener">${esc(d.original_filename||'Supplier Invoice')} ↗</a>`).join('');
    const uploadInvoiceHtml=`<label class="bsm-purchase-invoice-field"><span class="bsm-field-label">Upload Supplier Invoice</span><input type="file" id="bsm-purchase-invoice-file" accept="application/pdf,image/*">${existingInvoice?`<div class="bsm-purchase-existing-invoice">${existingInvoice}</div>`:''}</label>`;
    $('#bsm-form-fields').append(uploadInvoiceHtml);
  }
  if(key==='material_requisitions'){
    $('#bsm-form-fields').addClass('bsm-requisition-summary');
    const requesterId=id?(row.requested_by||''):(BSM.currentUser?.plugin_id||'');
    const requesterName=id?((App.options.users||[]).find(x=>String(x.id)===String(requesterId))?.label||''):(BSM.currentUser?.name||'');
    const $req=$('#bsm-form-fields select[name="requested_by"]');
    if($req.length){
      $req.val(String(requesterId)).prop('disabled',true).trigger('change');
      $req.closest('label').append(`<input type="hidden" name="requested_by" value="${esc(requesterId)}">`);
      const $search=$req.next('.bsm-searchable-select').find('.bsm-select-search');
      if($search.length && requesterName)$search.val(requesterName);
    }
  }
  if(key==='material_dispatches') $('#bsm-form-fields').addClass('bsm-dispatch-summary');
  if(key==='material_inwards'){
    const mode=String(row.inward_type||'purchase');
    const $type=$('#bsm-form-fields select[name="inward_type"]');
    if($type.length)$type.val(mode);
    setMaterialInwardMode(mode);
    if(mode==='return'){
      const receiverId=id?(row.received_by||''):(BSM.currentUser?.plugin_id||'');
      const $rb=$('#bsm-form-fields select[name="received_by"]');
      if($rb.length){$rb.val(String(receiverId)).prop('disabled',true).trigger('change');}
      if(row.return_id) setTimeout(()=>fillMaterialInwardFromReturn(row.return_id),0);
    }else{
      const vendorId=$('#bsm-form-fields select[name="vendor_id"]').val()||'';
      if(vendorId) setTimeout(()=>loadMaterialInwardVendorGST(vendorId),0); else setTimeout(()=>applyMaterialInwardGSTType('no_gst'),0);
    }
  }

  if(key==='site_receipts'){
    const challanDocs=(row._documents||[]).filter(d=>String(d.document_type||'').toLowerCase()==='receiving challan');
    const existingChallan=challanDocs.map(d=>`<a href="${esc(d.file_url)}" target="_blank" rel="noopener">${esc(d.original_filename||'Receiving Challan')} ↗</a>`).join('');
    $('#bsm-form-fields').append(`<label class="bsm-site-receipt-challan-field"><span class="bsm-field-label">Upload Receiving Challan</span><input type="file" id="bsm-site-receipt-challan-file" accept="application/pdf,image/*">${existingChallan?`<div class="bsm-purchase-existing-invoice">${existingChallan}</div>`:''}</label>`);
    const receiverId=id?(row.received_by||''):(BSM.currentUser?.plugin_id||'');
    const receiverName=id?((App.options.users||[]).find(x=>String(x.id)===String(receiverId))?.label||''):(BSM.currentUser?.name||'');
    const $rb=$('#bsm-form-fields select[name="received_by"]');
    if($rb.length){$rb.val(String(receiverId)).prop('disabled',true).trigger('change');$rb.closest('label').append(`<input type="hidden" name="received_by" value="${esc(receiverId)}">`);const $search=$rb.next('.bsm-searchable-select').find('.bsm-select-search');if($search.length&&receiverName)$search.val(receiverName);}
    const $site=$('#bsm-form-fields select[name="site_id"]');
    if($site.length&&id)$site.prop('disabled',true).trigger('change');
  }
  if(key==='labour_attendance'){
    $('#bsm-form-fields input[name="attendance_date"]').closest('label').after(`<label><span class="bsm-field-label">Updated By</span><input type="text" id="bsm-labour-updated-by" value="${esc(BSM.currentUser?.name||'')}" readonly></label>`);
    App.attendanceWorkers=(await opsCall('meta')).workers||[];
    let savedHours=[];try{savedHours=JSON.parse(row.remarks||'{}').employee_hours||[];}catch(e){}
    App.attendanceSavedHours=savedHours;
    const attendanceRows=id?[{contractor_id:row.contractor_id||'',no_of_labour:row.no_of_labour||''}]:[];
    renderItems(cfg,attendanceRows);
    refreshLabourAttendanceRows();
  } else if(cfg.items) renderItems(cfg,row._items||[]); else $('#bsm-line-items').empty();
  if(key==='labour_contractors')renderContractorEmployeeTable(row._employees||[]);
  if(['quotations','tax_invoices'].includes(key)) setTimeout(()=>prepareSalesForm(key,row),0);
  if(key==='material_inwards'){
    const mode=String(row.inward_type||$('#bsm-form-fields select[name="inward_type"]').val()||'purchase');
    setMaterialInwardMode(mode);
    if(mode==='return'&&row.return_id)setTimeout(()=>fillMaterialInwardFromReturn(row.return_id),0);
  }
  if(key==='site_receipts'){
    $('#bsm-lines-body .bsm-add-line-inline,#bsm-lines-body .bsm-remove-line').hide();
    if(id){$('#bsm-lines-body select[name="item_id"],#bsm-lines-body select[name="unit_id"]').prop('disabled',true).each(function(){bsmRefreshSearchableSelect(this);});$('#bsm-lines-body input[name="dispatch_qty"]').prop('readonly',true);}
  }
  if(key==='material_returns'){
    const returnerId=id?(row.return_by||''):(BSM.currentUser?.plugin_id||'');
    const returnerName=id?((App.options.users||[]).find(x=>String(x.id)===String(returnerId))?.label||''):(BSM.currentUser?.name||'');
    const $rby=$('#bsm-form-fields select[name="return_by"]');
    if($rby.length){$rby.val(String(returnerId)).prop('disabled',true).trigger('change');$rby.closest('label').append(`<input type="hidden" name="return_by" value="${esc(returnerId)}">`);const $search=$rby.next('.bsm-searchable-select').find('.bsm-select-search');if($search.length&&returnerName)$search.val(returnerName);}
    await refreshMaterialReturnItemOptions();
    $('#bsm-lines-body .bsm-line').each(function(){const $tr=$(this);autoSelectUnitFromItem($tr);refreshMaterialReturnStock($tr);});
  }
  if(key==='site_consumptions'){ refreshConsumptionItemOptions(); }
  const hideDocumentsFor = new Set([
    ...masterModules,
    'material_requisitions','material_inwards','material_dispatches',
    'site_receipts','site_consumptions','material_returns','contractor_bills','contractor_advances','employee_allowance_limits','quotations','tax_invoices'
  ]);
  if(key==='material_dispatches'){
    const photos=(row._documents||[]).filter(d=>d.document_type==='Dispatch Photo');
    $('#bsm-documents').html(`<div class="bsm-docbox"><label><span class="bsm-field-label">Upload Photo</span><input type="file" id="bsm-dispatch-photo" accept="image/jpeg,image/png,image/webp"></label>${photos.length?`<div class="bsm-doc-list">${photos.map(d=>`<a href="${esc(d.file_url)}" target="_blank" rel="noopener">${esc(d.original_filename||'View Photo')}</a>`).join('')}</div>`:''}</div>`);
  }else if(hideDocumentsFor.has(key)) $('#bsm-documents').empty();
  else if(key==='labour_attendance')$('#bsm-documents').empty();
  else renderDocuments(row._documents||[],key,id);
  if(key==='material_dispatches'){positionDispatchPhoto();setupDispatchSource();}
  updateBankFieldVisibility($('#bsm-form'));
  if(key==='contractor_advances') setTimeout(loadContractorPaymentSummary,0);
  $('#bsm-modal').fadeIn(100);
}


// BOQ-restricted requisition items. BOQ quantity is informative; requisition
// quantity is the existing requested_qty persisted by the original backend.
let requisitionBoqItems = new Map();
let requisitionBoqSite = '';
let requisitionBoqRevision = 0;
function applyRequisitionBoqRow($row){
  if($('#bsm-form').data('module')!=='material_requisitions'||App.current!=='requisition_form') return;
  const $select=$row.find('select[name="item_id"]');
  if(!$select.length)return;
  const chosen=String($select.val()||'');
  $select.find('option').each(function(){
    const id=String(this.value||'');
    $(this).prop('disabled',Boolean(id)&&!requisitionBoqItems.has(id));
  });
  if(chosen&&!requisitionBoqItems.has(chosen))$select.val('');
  const selected=String($select.val()||'');
  const row=requisitionBoqItems.get(selected);
  $row.find('.bsm-display-boq_qty_display').val(row && Number.isFinite(Number(row.qty)) ? Number(row.qty).toFixed(2) : '');
  $row.find('.bsm-display-already_req_qty_display').val(row && Number.isFinite(Number(row.already)) ? Number(row.already).toFixed(2) : '');
  bsmRefreshSearchableSelect($select[0]);
  if(chosen!==selected)autoSelectUnitFromItem($row);
}
async function refreshRequisitionBoqItems(){
  if($('#bsm-form').data('module')!=='material_requisitions'||App.current!=='requisition_form')return;
  const site=String($('#bsm-form-fields select[name="site_id"]').val()||'');
  const revision=++requisitionBoqRevision;
  requisitionBoqSite=site;
  requisitionBoqItems=new Map();
  if(site){
    const response=await ajax('bsm_requisition_boq_items',{site_id:site});
    if(revision!==requisitionBoqRevision)return;
    if(response.success){
      (response.data.items||[]).forEach(row=>requisitionBoqItems.set(String(row.item_id),{qty:row.boq_qty,already:row.already_req_qty}));
    }else showToast(response.data?.message||'Could not load BOQ items',true);
  }
  if(revision!==requisitionBoqRevision)return;
  $('#bsm-lines-body .bsm-line').each(function(){applyRequisitionBoqRow($(this));});
  if(site&&!requisitionBoqItems.size)showToast('No BOQ items found for this Site. Create a BOQ first.',true);
}
$(document).on('change','#bsm-form[data-module="material_requisitions"] #bsm-form-fields select[name="site_id"]',refreshRequisitionBoqItems);
$(document).on('change','#bsm-form[data-module="material_requisitions"] #bsm-lines-body select[name="item_id"]',function(){applyRequisitionBoqRow($(this).closest('tr'));});

function defaultValue(f){if(f.name.endsWith('_date')||['attendance_date','requisition_date','inward_date','dispatch_date','receipt_date','consumption_date','return_date','fund_date','expense_date','payment_date','bill_date','invoice_date'].includes(f.name))return BSM.today; if(f.name==='status')return 'active'; if(f.name==='paid_by')return 'HEAD_OFFICE'; return '';}
function renderItems(cfg,items){
  const lineClass=(cfg===App.configs.material_requisitions)?' bsm-requisition-lines':((cfg===App.configs.material_inwards)?' bsm-purchase-lines':'');
  const hasActionColumn=(cfg!==App.configs.site_receipts);
  const consumptionDocumentHead=(cfg===App.configs.site_consumptions)?'<th class="bsm-col-document">Document</th>':'';
  const actionHead=hasActionColumn?'<th class="bsm-line-actions-head"></th>':'';
  const heading=cfg.attendanceTable?'Attendance':'Items';
  const quoteAdd=([App.configs.quotations,App.configs.tax_invoices].includes(cfg))?'<button type="button" class="bsm-btn bsm-btn-small bsm-add-line-inline bsm-quote-add-item">+ Add Item</button>':'';
  const showLinesHeading=(cfg!==App.configs.material_inwards) || cfg.attendanceTable || quoteAdd;
  const linesHead=showLinesHeading?`<div class="bsm-lines-head"><h3>${heading}</h3>${quoteAdd}</div>`:'';
  let h=`<div class="bsm-lines${lineClass}${[App.configs.quotations,App.configs.tax_invoices].includes(cfg)?' bsm-quotation-lines':''}">${linesHead}<div class="bsm-table-wrap"><table><thead><tr>${cfg.items.filter(f=>f.type!=='hidden').map(f=>`<th class="bsm-col-${esc(f.name)}">${esc(f.label)}${f.required?'<span class="bsm-required">*</span>':''}</th>`).join('')}${consumptionDocumentHead}${actionHead}</tr></thead><tbody id="bsm-lines-body"></tbody>${cfg.attendanceTable?'<tfoot><tr class="bsm-attendance-total-row"><td class="num"><strong>Total</strong></td><td class="num"><strong id="bsm-attendance-total">0</strong></td><td></td></tr></tfoot>':''}</table></div></div>`;
  $('#bsm-line-items').html(h);(items.length?items:[{}]).forEach(it=>addLine(cfg,it));
}
function bsmSetSalesGstValue($tr,raw){
  const $gst=$tr.find('select[name="gst_rate"]');
  if(!$gst.length)return;
  const wanted=String(raw??'').trim();
  if(wanted===''){ $gst.val(''); return; }
  let match='';
  $gst.find('option').each(function(){
    const v=String($(this).val()||'').trim();
    if(v!=='' && (v===wanted || Number(v)===Number(wanted))){match=v;return false;}
  });
  $gst.val(match||wanted);
}
// Let the Requisition Remarks cell grow vertically while typing; never change the column width.
function bsmResizeRequisitionRemarks(el){
  if(!el)return;
  el.style.height='36px';
  el.style.height=Math.max(36,Math.min(el.scrollHeight+2,220))+'px';
}
$(document).on('input change','#bsm-form[data-module="material_requisitions"] .bsm-requisition-lines textarea[name="remarks"]',function(){bsmResizeRequisitionRemarks(this);});
function addLine(cfg,it={}){
  const hasActionColumn=(cfg!==App.configs.site_receipts);
  const consumptionDocumentCell=(cfg===App.configs.site_consumptions)?`<td class="bsm-col-document"><div class="bsm-consumption-doc-cell"><input type="file" class="bsm-consumption-document-file" accept="image/*,.pdf">${it.document_url?`<a class="bsm-consumption-doc-link" href="${esc(it.document_url)}" target="_blank" title="${esc(it.document_name||'View Document')}">View</a>`:''}</div></td>`:'';
  let h='<tr class="bsm-line">'+cfg.items.map(f=>{
    // Return receipts must render their saved Unit as text, not a disabled/enhanced dropdown.
    // Keep its ID in a hidden input for the existing serialization process.
    if(cfg===App.configs.material_inwards && f.name==='unit_id' && it.return_qty!==undefined){
      const master=(App.options.items||[]).find(x=>String(x.id)===String(it.item_id))||{};
      const uid=String(it.unit_id||master.unit_id||'');
      const unit=(App.options.units||[]).find(x=>String(x.id)===uid)||{};
      const label=String(it.unit_name||unit.label||unit.unit_name||labelFor('units',uid)||'').trim();
      return `<td class="bsm-col-unit_id"><input type="hidden" name="unit_id" value="${esc(uid)}"><input type="text" class="bsm-return-unit-display" value="${esc(label||'Unit unavailable')}" readonly aria-label="Unit"></td>`;
    }
    return f.type==='hidden'?fieldHtml(f,it[f.name]??''):`<td class="bsm-col-${esc(f.name)}">${fieldHtml(Object.assign({},f,{label:''}),it[f.name]??'').replace('<label class="">','<label class="line-field">').replace('</label>','</label>')}</td>`;
  }).join('')+consumptionDocumentCell+(hasActionColumn?'<td class="bsm-line-actions"><button type="button" class="bsm-add-line-inline" title="Add Row">+</button><button type="button" class="bsm-remove-line" title="Remove Row">×</button></td>':'')+'</tr>';
  $('#bsm-lines-body').append(h);const $tr=$('#bsm-lines-body .bsm-line').last();
  if(cfg===App.configs.material_requisitions){ applyRequisitionBoqRow($tr); bsmResizeRequisitionRemarks($tr.find('textarea[name="remarks"]')[0]); }
  bsmEnhanceSearchableSelects($tr[0]);
  const isSalesLine=(cfg===App.configs.quotations||cfg===App.configs.tax_invoices);
  if(isSalesLine)bsmSetSalesGstValue($tr,it.gst_rate);
  const hasItem=cfg.items.some(f=>f.name==='item_id');const $unit=$tr.find('select[name="unit_id"]');
  if(hasItem&&$unit.length){
    if(isSalesLine){
      // Keep the real select enabled for saving, but show Unit as a simple read-only input.
      $unit.prop('disabled',false).addClass('bsm-sales-unit-native');
      const $label=$unit.closest('label');
      $label.find('.bsm-searchable-select').hide();
      if(!$label.find('.bsm-sales-unit-display').length)$label.append('<input type="text" class="bsm-sales-unit-display" readonly tabindex="-1" aria-label="Unit">');
      autoSelectUnitFromItem($tr);
      syncSalesUnitDisplay($tr);
    }else{
      $unit.prop('disabled',true);bsmRefreshSearchableSelect($unit[0]);autoSelectUnitFromItem($tr);
      if(cfg===App.configs.material_inwards && it.return_qty===undefined){
        const $label=$unit.closest('label');
        if(!$label.find('.bsm-purchase-unit-display').length){
          $label.append('<input type="text" class="bsm-purchase-unit-display" readonly tabindex="-1" aria-label="Unit">');
        }
        syncPurchaseUnitDisplay($tr);
      }
    }
  }
  if(cfg===App.configs.material_inwards){
    syncPurchaseItemPrice($tr);
    // Purchase GST % is driven by Item Master. Keep the real select for saving/calculation,
    // but show a simple read-only field so users cannot change it manually.
    const $gst=$tr.find('select[name="gst_rate"]');
    if($gst.length){
      $gst.prop('disabled',false).addClass('bsm-purchase-gst-native');
      const $label=$gst.closest('label');
      $label.find('.bsm-searchable-select').hide();
      if(!$label.find('.bsm-purchase-gst-display').length){
        $label.append('<input type="text" class="bsm-purchase-gst-display" readonly tabindex="-1" aria-label="GST %">');
      }
      const val=String(it.gst_rate??'').trim();
      if(val!=='') $label.find('.bsm-purchase-gst-display').val(`${Number(val)}%`);
    }
    // SGST: keep the real input for serialization, but use a dedicated readonly
    // display control. This avoids the recurring browser/UI issue where the real
    // readonly SGST input retained a blank visual value even though calculation/save was correct.
    const $sgst=$tr.find('input[name="sgst_amount"]');
    if($sgst.length){
      const $sgstLabel=$sgst.closest('label');
      $sgst.hide().addClass('bsm-purchase-sgst-native');
      if(!$sgstLabel.find('.bsm-purchase-sgst-display').length){
        $sgstLabel.append('<input type="text" class="bsm-purchase-sgst-display" readonly tabindex="-1" aria-label="SGST">');
      }
    }

    // GST % is source-controlled by Item Master. This is important for PO-prefilled
    // Purchase Forms because the Item dropdown is disabled and no change event fires.
    syncPurchaseItemGst($tr);
    applyMaterialInwardGSTType($('#bsm-form').data('vendor-gst-type')||'no_gst');
    recalcMaterialInwardRow($tr);
  }
  if(cfg===App.configs.material_returns) setTimeout(refreshMaterialReturnItemOptions,0);
  if(cfg.attendanceTable) refreshLabourAttendanceRows();
}
function renderLabourEmployeeHours($row){
  const cid=String($row.find('select[name="contractor_id"]').val()||'');
  let $grid=$row.find('.bsm-labour-hours');
  if($grid.attr('data-contractor')===cid)return;
  $grid.remove();$row.find('input[name="no_of_labour"]').prop('hidden',true);
  const workers=(App.attendanceWorkers||[]).filter(w=>String(w.contractor_id)===cid&&(w.status==='active'||(App.attendanceSavedHours||[]).some(h=>String(h.worker_id)===String(w.id))));
  const html=!cid?'Select a Labour Contractor.':!workers.length?'No employees found. Add employees in Labour Contractor Master.':`<div class="bsm-labour-hours-head"><strong>Employee Name</strong><strong>Working Hours</strong></div>`+workers.map(w=>{const saved=(App.attendanceSavedHours||[]).find(h=>String(h.worker_id)===String(w.id));return `<label class="bsm-labour-hours-row"><span>${esc(w.employee_name)}</span><input type="number" class="bsm-labour-working-hours" data-worker="${Number(w.id)}" aria-label="Working Hours for ${esc(w.employee_name)}" min="0" max="24" step="0.01" placeholder="Hours" value="${saved?esc(saved.working_hours):''}"></label>`;}).join('');
  $row.find('input[name="no_of_labour"]').after(`<div class="bsm-labour-hours" data-contractor="${esc(cid)}">${html}</div>`);
}
$(document).on('input','.bsm-labour-working-hours',refreshLabourAttendanceRows);
function refreshLabourAttendanceRows(){
  if($('#bsm-form').data('module')!=='labour_attendance') return;
  $('#bsm-lines-body .bsm-line').each(function(){const $row=$(this);renderLabourEmployeeHours($row);$row.find('input[name="no_of_labour"]').val($row.find('.bsm-labour-working-hours').filter(function(){return Number(this.value)>0;}).length);});
  const selected=[];
  $('#bsm-lines-body select[name="contractor_id"]').each(function(){const v=String($(this).val()||'');if(v)selected.push(v);});
  $('#bsm-lines-body select[name="contractor_id"]').each(function(){
    const $sel=$(this), current=String($sel.val()||'');
    (App.options.contractors||[]).forEach(x=>{
      const value=String(x.id), used=selected.includes(value)&&value!==current;
      let $o=$sel.find(`option[value="${value.replace(/"/g,'\\"')}"]`);
      if(!$o.length){$sel.append(`<option value="${esc(value)}">${esc(x.label)}</option>`);$o=$sel.find(`option[value="${value.replace(/"/g,'\\"')}"]`);}
      $o.prop('disabled',used);
    });
    bsmRefreshSearchableSelect($sel[0]);
  });
  let total=0;
  $('#bsm-lines-body input[name="no_of_labour"]').each(function(){const v=String($(this).val()||'').trim();if(v!=='')total+=Number(v||0);});
  $('#bsm-attendance-total').text(Number.isFinite(total)?Math.trunc(total).toLocaleString('en-IN'):'0');
}

async function validateExistingLabourAttendance($sel){
  if($('#bsm-form').data('module')!=='labour_attendance') return true;
  const contractorId=String($sel.val()||'');
  const siteId=String($('#bsm-form-fields select[name="site_id"]').val()||'');
  const attendanceDate=dateSql($('#bsm-form-fields input[name="attendance_date"]').val()||'');
  if(!contractorId||!siteId||!attendanceDate) return true;
  const r=await ajax('bsm_labour_attendance_exists',{site_id:siteId,contractor_id:contractorId,attendance_date:attendanceDate,exclude_id:App.editId||0});
  if(r.success && Number(r.data?.exists||0)>0){
    $sel.val('').trigger('change');
    bsmRefreshSearchableSelect($sel[0]);
    refreshLabourAttendanceRows();
    showToast('Attendance for this Labour Contractor already exists for the selected Site and Date.',true);
    return false;
  }
  return true;
}

function positionDispatchPhoto(){
  const $photo=$('#bsm-dispatch-photo').closest('label');
  const $driver=$('#bsm-form-fields [name="driver_mobile"]').closest('label');
  if($photo.length&&$driver.length){$photo.insertAfter($driver);if(!$('#bsm-documents .bsm-doc-list').length)$('#bsm-documents').empty();}
}
function renderDocuments(docs,key,id){ if(!id){$('#bsm-documents').empty();return;} let h='<div class="bsm-docbox"><h3>Documents</h3><div class="bsm-doc-upload"><input id="bsm-doc-type" placeholder="Document Type"><input type="file" id="bsm-doc-file"><button type="button" class="bsm-btn" id="bsm-upload-doc">Upload</button></div>'; if(docs.length)h+='<div class="bsm-doc-list">'+docs.map(d=>`<a href="${esc(d.file_url)}" target="_blank">${esc(d.document_type||d.original_filename)} ↗</a>`).join('')+'</div>';h+='</div>';$('#bsm-documents').html(h);}


function setMaterialInwardMode(type){
  type=(type==='return')?'return':'purchase';
  const $fields=$('#bsm-form-fields');
  $('#bsm-line-items').toggleClass('bsm-return-inward-lines',type==='return');
  // Purchase header fields use a compact four-column desktop grid.
  $fields.toggleClass('bsm-purchase-summary',type==='purchase');
  const purchase=['vendor_id','warehouse_id','inward_date','supplier_invoice_no','supplier_invoice_date','bill_type','taxable_amount','other_expenses','other_expense_gst','round_off','total_amount'];
  const returned=['return_id','site_id','received_by'];
  purchase.forEach(n=>$fields.find(`[name="${n}"]`).closest('label').toggle(type==='purchase'));
  $fields.find('.bsm-purchase-invoice-field').toggle(type==='purchase');
  returned.forEach(n=>$fields.find(`[name="${n}"]`).closest('label').toggle(type==='return'));
  // Warehouse is required for every inward. For Purchase it must be visible/selectable;
  // for Return it is populated from the selected Material Return.
  const $wh=$fields.find('select[name="warehouse_id"]');
  if($wh.length){
    $wh.closest('label').show();
    if(type==='purchase'){
      $wh.prop('disabled',false);
      const realOptions=$wh.find('option').filter(function(){return String($(this).val()||'')!=='';});
      if(!$wh.val() && realOptions.length===1) $wh.val(String(realOptions.first().val())).trigger('change');
      bsmRefreshSearchableSelect($wh[0]);
    }
  }
  if(type==='return'){
    applyMaterialInwardGSTType('no_gst');
    // Return receipt approval is intentionally minimal:
    // Item | Return Qty | Received Qty | Unit
    ['item_price','rate','remarks','taxable_amount','gst_rate','cgst_amount','sgst_amount','igst_amount','total_amount'].forEach(n=>setInwardColumnVisible(n,false));
    ['return_qty'].forEach(n=>setInwardColumnVisible(n,true));
    const qi=inwardFieldColumnIndex('qty'); if(qi>0) $('#bsm-line-items thead th').eq(qi-1).text('Received Qty');
    $('#bsm-line-items thead th').eq(inwardFieldColumnIndex('return_qty')-1).text('Return Qty');
    showReturnReceiptUnitColumns();
  }else{
    ['return_qty','remarks'].forEach(n=>setInwardColumnVisible(n,false));
    ['item_price','rate'].forEach(n=>setInwardColumnVisible(n,true));
    const qi=inwardFieldColumnIndex('qty'); if(qi>0) $('#bsm-line-items thead th').eq(qi-1).text('Qty');
    const vendorId=$fields.find('select[name="vendor_id"]').val()||'';
    if(vendorId) loadMaterialInwardVendorGST(vendorId); else applyMaterialInwardGSTType('no_gst');
  }
}
async function fillMaterialInwardFromReturn(returnId){
  if(!returnId){renderItems(App.configs.material_inwards,[]);setMaterialInwardMode('return');return;}
  // Always fetch fresh Item/Unit master data before building the Return Receipt rows.
  await ensureOptions(['items','units'],true);
  const r=await ajax('bsm_get',{module:'material_returns',id:returnId});
  if(!r.success){showToast(r.data?.message||'Unable to load Material Return',true);return;}
  const d=r.data||{};
  // Received Returns are intentionally excluded from the Add dropdown. On Edit,
  // temporarily restore the already-linked Return so the historical record displays correctly.
  const $ret=$('#bsm-form-fields select[name="return_id"]');
  if($ret.length && !$ret.find(`option[value="${String(returnId)}"]`).length){
    const siteLabel=optionLabel('sites',d.site_id)||'';
    const label=[d.return_no||('Return #'+returnId),siteLabel].filter(Boolean).join(' - ');
    $ret.append(`<option value="${esc(String(returnId))}">${esc(label)}</option>`);
  }
  if($ret.length){$ret.val(String(returnId));bsmRefreshSearchableSelect($ret[0]);}
  const $site=$('#bsm-form-fields select[name="site_id"]');
  if($site.length){$site.val(String(d.site_id||'')).prop('disabled',true).trigger('change');bsmRefreshSearchableSelect($site[0]);}
  const $wh=$('#bsm-form-fields select[name="warehouse_id"]');
  if($wh.length){$wh.val(String(d.warehouse_id||'')).prop('disabled',true).trigger('change');bsmRefreshSearchableSelect($wh[0]);}
  const uid=BSM.currentUser?.plugin_id||'';
  const $rb=$('#bsm-form-fields select[name="received_by"]');
  if($rb.length){$rb.val(String(uid)).prop('disabled',true).trigger('change');bsmRefreshSearchableSelect($rb[0]);}
  const returnLines=(d._items||[]).filter(it=>Number(it.pending_qty??it.qty??0)>0.0001).map(it=>{
    const masterItem=(App.options.items||[]).find(x=>String(x.id)===String(it.item_id))||{};
    const resolvedUnitId=String(it.unit_id||it.resolved_unit_id||masterItem.unit_id||'');
    return {
      item_id:it.item_id,
      return_qty:it.qty,
      qty:Number(it.pending_qty??it.qty??0),
      unit_id:resolvedUnitId,
      unit_name:String(it.unit_name||masterItem.unit_name||''),
      item_price:'',rate:'',remarks:'',taxable_amount:'',gst_rate:'',cgst_amount:'',sgst_amount:'',igst_amount:'',total_amount:''
    };
  });
  renderItems(App.configs.material_inwards,returnLines);
  setMaterialInwardMode('return');
  $('#bsm-lines-body .bsm-add-line-inline').hide();

  $('#bsm-lines-body .bsm-line').each(function(i){
    const $tr=$(this),it=returnLines[i]||{};
    const $item=$tr.find('select[name="item_id"]');
    const masterItem=(App.options.items||[]).find(x=>String(x.id)===String(it.item_id))||{};
    const unitId=String(it.unit_id||it.resolved_unit_id||masterItem.unit_id||'');
    const unitObj=(App.options.units||[]).find(x=>String(x.id)===unitId)||{};
    const unitLabel=String(it.unit_name||unitObj.label||unitObj.unit_name||labelFor('units',unitId)||'').trim();
    // Target the column directly; do not depend on the Unit select still existing.
    // The select enhancer can replace/remove the original control during row creation.
    const $cell=$tr.children('td.bsm-col-unit_id');
    if($cell.length){
      $cell.empty().append(
        $('<input>',{type:'hidden',name:'unit_id'}).val(unitId),
        $('<input>',{type:'text',class:'bsm-return-unit-display',readonly:true,'aria-label':'Unit'}).val(unitLabel||'Unit unavailable')
      );
    }

  });

  showReturnReceiptUnitColumns();
  $('#bsm-lines-body input[name="return_qty"],#bsm-lines-body input[name="item_price"],#bsm-lines-body input[name="rate"],#bsm-lines-body input[name="remarks"]').prop('readonly',true);
  $('#bsm-lines-body input[name="qty"]').prop('readonly',false);
  $('#bsm-lines-body .bsm-add-line-inline,#bsm-lines-body .bsm-remove-line').hide();
}

function inwardFieldColumnIndex(name){
  const cfg=App.configs.material_inwards;
  return cfg.items.filter(f=>f.type!=='hidden').findIndex(f=>f.name===name)+1;
}
function setInwardColumnVisible(name,show){
  // Select columns by their actual field class, never by position. Hidden
  // inputs in material inward rows do not occupy a table column.
  const safe=String(name||'').replace(/[^a-zA-Z0-9_]/g,'');
  if(!safe)return;
  $('#bsm-line-items table').find('th.bsm-col-'+safe+', td.bsm-col-'+safe)
    .css('display',show?'':'none');
}
function showReturnReceiptUnitColumns(){
  if($('#bsm-form').data('module')!=='material_inwards')return;
  if($('#bsm-form-fields select[name="inward_type"]').val()!=='return')return;
  $('#bsm-line-items').find('th.bsm-col-unit_id,td.bsm-col-unit_id')
    .css('display','table-cell');
  $('#bsm-lines-body tr.bsm-line').each(function(){
    const $tr=$(this),$cell=$tr.children('td.bsm-col-unit_id');
    const id=String($cell.find('[name="unit_id"]').val()||'');
    const master=(App.options.items||[]).find(x=>String(x.id)===String($tr.find('select[name="item_id"]').val()))||{};
    const actualId=id||String(master.unit_id||'');
    const unit=(App.options.units||[]).find(x=>String(x.id)===actualId)||{};
    const name=String(unit.label||unit.unit_name||labelFor('units',actualId)||master.unit_name||'').trim();
    $cell.find('input.bsm-return-unit-display').val(name||'Unit unavailable');
  });
}
function applyMaterialInwardGSTType(type){
  type=['intra_state','inter_state','no_gst'].includes(type)?type:'no_gst';
  if($('#bsm-form-fields [name="bill_type"]').val()==='non_gst_bill') type='no_gst';
  $('#bsm-form').data('vendor-gst-type',type);
  const intra=type==='intra_state', inter=type==='inter_state', taxable=type!=='no_gst';
  setInwardColumnVisible('gst_rate',taxable);
  setInwardColumnVisible('cgst_amount',intra);
  setInwardColumnVisible('sgst_amount',intra);
  setInwardColumnVisible('igst_amount',inter);
  $('#bsm-lines-body .bsm-line').each(function(){
    const $tr=$(this);
    recalcMaterialInwardRow($tr);
    // Some browsers retain the old visual value in readonly cells after columns
    // are toggled. Re-apply the calculated value after the column becomes visible.
    if(intra){
      const cg=$tr.find('[name="cgst_amount"]').val()||'';
      const sg=$tr.find('[name="sgst_amount"]').val()||'';
      $tr.find('[name="cgst_amount"]').attr('value',cg).val(cg);
      $tr.find('[name="sgst_amount"]').attr('value',sg).val(sg);
    }
  });
}
async function loadMaterialInwardVendorGST(vendorId){
  if(!vendorId){applyMaterialInwardGSTType('no_gst');return;}
  const r=await ajax('bsm_get',{module:'vendors',id:vendorId});
  applyMaterialInwardGSTType(r.success?(r.data?.gst_type||'no_gst'):'no_gst');
  $('#bsm-lines-body .bsm-line').each(function(){
    syncPurchaseItemGst($(this));
    recalcMaterialInwardRow($(this));
  });
}
function recalcMaterialInwardRow($tr){
  if($('#bsm-form').data('module')!=='material_inwards')return;
  const type=$('#bsm-form-fields [name="bill_type"]').val()==='non_gst_bill'?'no_gst':($('#bsm-form').data('vendor-gst-type')||'no_gst');
  const qtyRaw=String($tr.find('[name="qty"]').val()||'').trim();
  const rateRaw=String($tr.find('[name="rate"]').val()||'').trim();
  // Do not prefill calculated fields with 0 on a fresh form.
  if(qtyRaw==='' || rateRaw===''){
    $tr.find('[name="taxable_amount"],[name="cgst_amount"],[name="sgst_amount"],[name="igst_amount"],[name="total_amount"]').val('');
    $tr.find('.bsm-purchase-sgst-display').val('');
    if(type==='no_gst')$tr.find('[name="gst_rate"]').val('');
    recalcMaterialInwardTotals();
    return;
  }
  const qty=Number(qtyRaw), rate=Number(rateRaw);
  const taxable=Math.round(qty*rate*100)/100;
  const gstRaw=String($tr.find('[name="gst_rate"]').val()||'').trim();
  let gst=type==='no_gst'?0:Number(gstRaw||0);
  let cgst=0,sgst=0,igst=0;
  if(type==='intra_state'){cgst=Math.round(taxable*gst/2)/100;sgst=cgst;}
  if(type==='inter_state'){igst=Math.round(taxable*gst)/100;}
  const total=Math.round((taxable+cgst+sgst+igst)*100)/100;
  $tr.find('[name="taxable_amount"]').val(taxable!==0?taxable.toFixed(2):'');
  if(type!=='no_gst' && gstRaw!==''){
    // Keep the GST select on one of its real option values. Converting 18 to 18.00
    // made the native select look blank when the option value was stored as "18".
    const $gst=$tr.find('select[name="gst_rate"]');
    let matched='';
    $gst.find('option').each(function(){
      const ov=String($(this).val()||'').trim();
      if(ov!=='' && Number(ov)===gst){ matched=ov; return false; }
    });
    $gst.val(matched || gstRaw);
  }
  else if(type==='no_gst')$tr.find('[name="gst_rate"]').val('');
  const setAmountField=(name,value)=>{
    const shown=value!==0?Number(value).toFixed(2):'';
    const $f=$tr.find(`[name="${name}"]`);
    $f.val(shown).attr('value',shown).trigger('input');
  };
  setAmountField('cgst_amount',cgst);
  setAmountField('sgst_amount',sgst);
  const sgstShown=sgst!==0?Number(sgst).toFixed(2):'';
  $tr.find('.bsm-purchase-sgst-display').val(sgstShown).attr('value',sgstShown);
  setAmountField('igst_amount',igst);
  setAmountField('total_amount',total);
  recalcMaterialInwardTotals();
}
function recalcMaterialInwardTotals(){
  if($('#bsm-form').data('module')!=='material_inwards')return;
  let taxable=0,total=0,hasTaxable=false,hasTotal=false;
  $('#bsm-lines-body .bsm-line').each(function(){
    const tv=String($(this).find('[name="taxable_amount"]').val()||'').trim();
    const av=String($(this).find('[name="total_amount"]').val()||'').trim();
    if(tv!==''){taxable+=Number(tv||0);hasTaxable=true;}
    if(av!==''){total+=Number(av||0);hasTotal=true;}
  });
  $('#bsm-form-fields [name="taxable_amount"]').val(hasTaxable?(taxable!==0?taxable.toFixed(2):''):'');
  const otherExpenses=Number($('#bsm-form-fields [name="other_expenses"]').val()||0);
  const otherExpenseGst=$('#bsm-form-fields [name="bill_type"]').val()==='non_gst_bill'?0:Number($('#bsm-form-fields [name="other_expense_gst"]').val()||0);
  const roundOff=Number($('#bsm-form-fields [name="round_off"]').val()||0);
  const invoiceTotal=Math.round((total+otherExpenses+otherExpenseGst+roundOff)*100)/100;
  const anyParent=String($('#bsm-form-fields [name="other_expenses"]').val()||'').trim()!=='' || String($('#bsm-form-fields [name="other_expense_gst"]').val()||'').trim()!=='' || String($('#bsm-form-fields [name="round_off"]').val()||'').trim()!=='';
  $('#bsm-form-fields [name="total_amount"]').val((hasTotal||anyParent)?invoiceTotal.toFixed(2):'');
}

async function refreshOptionsAfterSave(key){
  const map={designations:'designations',states:'states',customers:'customers',sites:'sites',vendors:'vendors',labour_contractors:'contractors',item_categories:'categories',expense_categories:'expense_categories',units:'units',items:'items',warehouses:'warehouses',material_requisitions:'requisitions',material_dispatches:'dispatches',material_inwards:'inwards',contractor_bills:'contractor_bills',gst_invoices:'gst_invoices'};
  const t=map[key]; if(t) delete App.options[t];
  await ensureOptions(optionTypesForView(key));
}

function validateMobileFields($scope){
  let bad=null;
  $scope.find('input[name="mobile"],input[name$="_mobile"]').each(function(){
    const v=String($(this).val()||'').trim();
    if(v!==''&&!/^\d{10}$/.test(v)){bad=$(this);return false;}
  });
  if(bad){bad.addClass('bsm-invalid').trigger('focus');setTimeout(()=>bad.removeClass('bsm-invalid'),2200);showToast('Mobile Number should be a 10 digit number only',true);return false;}
  return true;
}

async function fillSiteReceiptFromDispatch(dispatchId){
  if(!dispatchId){
    const $site=$('#bsm-form-fields select[name="site_id"]');
    if($site.length){$site.val('').trigger('change');bsmRefreshSearchableSelect($site[0]);}
    renderItems(App.configs.site_receipts,[]);
    $('#bsm-lines-body .bsm-add-line-inline').hide();
    return;
  }
  const r=await ajax('bsm_site_receipt_prefill',{id:dispatchId});
  if(!r.success){showToast(r.data?.message||'Unable to load dispatch',true);return;}
  const d=r.data||{};
  // Received Returns are intentionally excluded from the Add dropdown. On Edit,
  // temporarily restore the already-linked Return so the historical record displays correctly.
  const $ret=$('#bsm-form-fields select[name="return_id"]');
  if($ret.length && !$ret.find(`option[value="${String(returnId)}"]`).length){
    const siteLabel=optionLabel('sites',d.site_id)||'';
    const label=[d.return_no||('Return #'+returnId),siteLabel].filter(Boolean).join(' - ');
    $ret.append(`<option value="${esc(String(returnId))}">${esc(label)}</option>`);
  }
  if($ret.length){$ret.val(String(returnId));bsmRefreshSearchableSelect($ret[0]);}
  const $site=$('#bsm-form-fields select[name="site_id"]');
  if($site.length){
    $site.val(String(d.site_id||'')).prop('disabled',true).trigger('change');
    bsmRefreshSearchableSelect($site[0]);
  }
  const cfg=App.configs.site_receipts;
  const rows=(d._items||[]).map(it=>({
    dispatch_item_id:it.dispatch_item_id||it.id||'',
    item_id:it.item_id,
    dispatch_qty:it.dispatch_qty,
    received_qty:it.dispatch_qty,
    damaged_qty:'',
    short_qty:'',
    unit_id:it.unit_id,
    rate:(Number(it.rate||0)!==0?Number(it.rate).toFixed(2):'')
  }));
  renderItems(cfg,rows);
  $('#bsm-lines-body .bsm-add-line-inline').hide();
  $('#bsm-lines-body .bsm-add-line-inline,#bsm-lines-body .bsm-remove-line').hide();
  $('#bsm-lines-body select[name="item_id"],#bsm-lines-body select[name="unit_id"]').prop('disabled',true).each(function(){bsmRefreshSearchableSelect(this);});
  $('#bsm-lines-body input[name="dispatch_qty"]').prop('readonly',true);
}

async function refreshConsumptionItemOptions(){
  if($('#bsm-form').data('module')!=='site_consumptions') return;
  const siteId=$('#bsm-form-fields select[name="site_id"]').val()||'';
  const $rows=$('#bsm-lines-body .bsm-line');

  // For Consumption, Item choices should come from the selected Site's actual positive stock.
  // This uses the same Material Stock calculation as Site Inventory, so the form and inventory
  // cannot disagree about which items are available. The dropdown displays Item Name only.
  let stockRows=[];
  App.consumptionStockByItem={};
  if(siteId){
    try{
      const r=await ajax('bsm_report',{report:'material_stock',site_id:siteId,stock_scope:'site_options'});
      if(r&&r.success&&Array.isArray(r.data?.rows)){
        stockRows=r.data.rows.filter(x=>String(x.location_type||'').toLowerCase()==='site' && Number(x.balance||0)>0.0001);
        stockRows.forEach(x=>{
          const id=String(x.item_id||''); if(!id)return;
          const prev=App.consumptionStockByItem[id];
          if(!prev){
            App.consumptionStockByItem[id]={available_qty:Number(x.balance||0),unit_id:String(x.unit_id||''),category_name:String(x.category_name||''),item_name:String(x.item_name||'')};
          }else{
            prev.available_qty+=Number(x.balance||0);
            if(!prev.unit_id&&x.unit_id)prev.unit_id=String(x.unit_id);
          }
        });
      }
    }catch(e){}
  }

  const selectedAll=$rows.map(function(){return String($(this).find('select[name="item_id"]').val()||'');}).get().filter(Boolean);
  $rows.each(function(){
    const $tr=$(this),$sel=$tr.find('select[name="item_id"]');
    const current=String($sel.val()||'');
    let source=[];
    if(siteId && stockRows.length){
      const allowed=new Set(stockRows.map(x=>String(x.item_id||'')));
      source=(App.options.items||[]).filter(x=>allowed.has(String(x.id)) || String(x.id)===current);
    }else if(siteId){
      // Selected Site currently has no positive inventory. Keep the current item only during Edit.
      source=(App.options.items||[]).filter(x=>String(x.id)===current);
    }else{
      source=App.options.items||[];
    }
    source=source.filter(x=>String(x.id)===current || !selectedAll.includes(String(x.id)));
    const itemOptions=source.map(x=>{
      const stock=App.consumptionStockByItem?.[String(x.id)]||{};
      return `<option value="${x.id}" data-available="${Number(stock.available_qty||0)}" data-unit="${esc(stock.unit_id||x.unit_id||'')}">${esc(x.label)}</option>`;
    }).join('');
    $sel.html('<option value="">Select</option>'+itemOptions);
    if(current)$sel.val(current);
    bsmRefreshSearchableSelect($sel[0]);
    if(!siteId || !$sel.val()){
      $tr.find('input[name="available_qty"]').val('');
      const $unit=$tr.find('select[name="unit_id"]');
      $unit.val(''); bsmRefreshSearchableSelect($unit[0]);
    }else{
      applyConsumptionStockRow($tr);
    }
  });

  if(!siteId) return;
  const jobs=[];
  $rows.each(function(){
    const $tr=$(this);
    if($tr.find('select[name="item_id"]').val()) jobs.push(refreshConsumptionAvailability($tr));
  });
  await Promise.all(jobs);
}

function applyConsumptionStockRow($tr){
  if(!$tr || !$tr.length) return false;
  const $sel=$tr.find('select[name="item_id"]');
  const itemId=String($sel.val()||'');
  if(!itemId) return false;
  let row=App.consumptionStockByItem?.[itemId]||null;
  if(!row){
    const $opt=$sel.find('option:selected');
    if($opt.length && $opt.attr('data-available')!==undefined){
      row={available_qty:Number($opt.attr('data-available')||0),unit_id:String($opt.attr('data-unit')||'')};
    }
  }
  if(!row) return false;
  $tr.find('input[name="available_qty"]').val(Number(row.available_qty||0).toFixed(2));
  if(row.unit_id){
    const $unit=$tr.find('select[name="unit_id"]');
    $unit.val(String(row.unit_id));
    bsmRefreshSearchableSelect($unit[0]);
  }
  return true;
}

function syncSalesUnitDisplay($tr){
  if(!$tr || !$tr.length) return;
  const $unit=$tr.find('select[name="unit_id"]');
  const $display=$tr.find('.bsm-sales-unit-display');
  if(!$unit.length || !$display.length) return;
  const txt=$unit.find('option:selected').text()||'';
  $display.val(txt==='Select'?'':txt);
}
function syncPurchaseItemPrice($tr,force=false){
  if($('#bsm-form').data('module')!=='material_inwards') return;
  const $price=$tr.find('input[name="item_price"]');
  if(!$price.length) return;
  // Existing saved Purchase Price is a fixed snapshot. Only refresh it when the Item itself changes.
  if(!force && String($price.val()||'').trim()!=='') return;
  const itemId=String($tr.find('select[name="item_id"]').val()||'');
  const item=(App.options.items||[]).find(x=>String(x.id)===itemId);
  const raw=item&&item.opening_rate!==undefined&&item.opening_rate!==null?String(item.opening_rate):'';
  const n=Number(raw||0);
  $price.val(raw!==''&&Number.isFinite(n)?n.toFixed(2):'');
}

function syncPurchaseItemGst($tr){
  if($('#bsm-form').data('module')!=='material_inwards') return;
  const itemId=String($tr.find('select[name="item_id"]').val()||'');
  const item=(App.options.items||[]).find(x=>String(x.id)===itemId);
  const $gst=$tr.find('select[name="gst_rate"]');
  if(!$gst.length) return;
  if(!item || item.gst_rate===undefined || item.gst_rate===null || String(item.gst_rate).trim()===''){
    $gst.val('');
    bsmRefreshSearchableSelect($gst[0]);
    const $display=$tr.find('.bsm-purchase-gst-display');
    if($display.length) $display.val('');
    return;
  }
  const target=Number(item.gst_rate);
  let matched='';
  $gst.find('option').each(function(){
    const ov=String($(this).val()||'').trim();
    if(ov!=='' && Number(ov)===target){ matched=ov; return false; }
  });
  if(matched===''){
    const raw=Number.isFinite(target)?String(target):String(item.gst_rate);
    $gst.append($('<option>').val(raw).text(`${raw}%`));
    matched=raw;
  }
  $gst.val(matched);
  bsmRefreshSearchableSelect($gst[0]);
  const $display=$tr.find('.bsm-purchase-gst-display');
  if($display.length) $display.val(`${Number(matched)}%`);
  recalcMaterialInwardRow($tr);
}

function syncPurchaseUnitDisplay($tr){
  if($('#bsm-form').data('module')!=='material_inwards')return;
  const $display=$tr.find('.bsm-purchase-unit-display');
  if(!$display.length)return;
  const itemId=String($tr.find('select[name="item_id"]').val()||'');
  const item=(App.options.items||[]).find(x=>String(x.id)===itemId)||{};
  const $unit=$tr.find('select[name="unit_id"]');
  const id=String($unit.val()||item.unit_id||'');
  if(id && String($unit.val()||'')!==id){$unit.val(id);}
  const master=(App.options.units||[]).find(x=>String(x.id)===id)||{};
  const optionText=$unit.find('option:selected').text();
  const label=String(master.label||master.unit_name||item.unit_name||((optionText && optionText!=='Select')?optionText:'')||'').trim();
  $display.val(label);
}
function autoSelectUnitFromItem($tr){
  if(!$tr || !$tr.length) return;
  const itemId=String($tr.find('select[name="item_id"]').val()||'');
  const $unit=$tr.find('select[name="unit_id"]');
  if(!$unit.length) return;
  if(!itemId){
    $unit.val('');
    if(!$unit.hasClass('bsm-sales-unit-native')) bsmRefreshSearchableSelect($unit[0]);
    syncSalesUnitDisplay($tr);
    return;
  }
  const item=(App.options.items||[]).find(x=>String(x.id)===itemId);
  if(!item || !item.unit_id){syncSalesUnitDisplay($tr);return;}
  $unit.val(String(item.unit_id));
  if(!$unit.hasClass('bsm-sales-unit-native')) bsmRefreshSearchableSelect($unit[0]);
  syncSalesUnitDisplay($tr);
  syncPurchaseUnitDisplay($tr);
}

async function refreshMaterialReturnItemOptions(){
  if($('#bsm-form').data('module')!=='material_returns') return;
  const siteId=$('#bsm-form-fields select[name="site_id"]').val()||'';
  const $rows=$('#bsm-lines-body .bsm-line');
  let stockRows=[];

  if(siteId){
    try{
      const r=await ajax('bsm_site_stock_items',{site_id:siteId});
      if(r&&r.success&&Array.isArray(r.data?.rows)){
        stockRows=r.data.rows.filter(x=>Number(x.available_qty||0)>0.0001);
      }
    }catch(e){}
  }

  const stockByItem={};
  stockRows.forEach(x=>{
    const id=String(x.id||''); if(!id)return;
    if(!stockByItem[id]) stockByItem[id]={available_qty:0,unit_id:String(x.unit_id||'')};
    stockByItem[id].available_qty+=Number(x.available_qty||0);
    if(!stockByItem[id].unit_id&&x.unit_id) stockByItem[id].unit_id=String(x.unit_id);
  });

  const selected=$rows.map(function(){return String($(this).find('select[name="item_id"]').val()||'');}).get().filter(Boolean);

  $rows.each(function(){
    const $tr=$(this),$sel=$tr.find('select[name="item_id"]');
    const current=String($sel.val()||'');
    let source=[];

    if(siteId){
      const allowed=new Set(Object.keys(stockByItem));
      source=(App.options.items||[]).filter(x=>allowed.has(String(x.id)) || String(x.id)===current);
    }else{
      source=App.options.items||[];
    }

    source=source.filter(x=>String(x.id)===current || !selected.includes(String(x.id)));
    const opts=source.map(x=>{
      const st=stockByItem[String(x.id)]||{};
      return `<option value="${esc(x.id)}" data-available="${Number(st.available_qty||0)}" data-unit="${esc(st.unit_id||x.unit_id||'')}">${esc(x.label)}</option>`;
    }).join('');

    $sel.html('<option value="">Select</option>'+opts);
    if(current)$sel.val(current);
    bsmRefreshSearchableSelect($sel[0]);

    if(!siteId || !$sel.val()){
      $tr.find('input[name="available_qty"]').val('');
      const $unit=$tr.find('select[name="unit_id"]');
      $unit.val(''); bsmRefreshSearchableSelect($unit[0]);
    }
  });
}

async function refreshMaterialReturnStock($tr){
  if(!$tr || !$tr.length) return;
  const siteId=$('#bsm-form-fields select[name="site_id"]').val()||'';
  const itemId=$tr.find('select[name="item_id"]').val()||'';
  const $available=$tr.find('input[name="available_qty"]');
  const $unit=$tr.find('select[name="unit_id"]');
  const $rate=$tr.find('input[name="rate"]');
  if(!siteId || !itemId){
    $available.val('');
    if($rate.length)$rate.val('');
    return;
  }
  try{
    const r=await ajax('bsm_site_stock_available',{site_id:siteId,item_id:itemId,return_id:App.editId||0});
    if(r && r.success && r.data){
      const av=Number(r.data.available_qty||0), rv=Number(r.data.rate||0);
      $available.val(av!==0?av.toFixed(2):'');
      if($rate.length)$rate.val(rv!==0?rv.toFixed(2):'');
      if(r.data.unit_id && $unit.length){
        $unit.val(String(r.data.unit_id));
        bsmRefreshSearchableSelect($unit[0]);
      }else{
        autoSelectUnitFromItem($tr);
      }
    }
  }catch(e){
    autoSelectUnitFromItem($tr);
  }
}

async function refreshConsumptionAvailability($tr){
  if(!$tr || !$tr.length) return;
  const siteId=$('#bsm-form-fields select[name="site_id"]').val()||'';
  const $item=$tr.find('select[name="item_id"]');
  const itemId=String($item.val()||'');
  const itemName=String($item.find('option:selected').text()||'').trim();
  const $available=$tr.find('input[name="available_qty"]');
  const $unit=$tr.find('select[name="unit_id"]');
  if(!siteId || !itemId){
    $available.val('');
    if($unit.length){$unit.val('');bsmRefreshSearchableSelect($unit[0]);}
    return;
  }

  let balance=0, unitId='';

  // Primary source: direct site-stock calculation. This endpoint calculates
  // Receipt - Consumption - Return and includes the legacy item-name fallback.
  try{
    const direct=await ajax('bsm_site_stock_available',{
      site_id:siteId,
      item_id:itemId,
      consumption_id:App.editId||0
    });
    if(direct && direct.success && direct.data){
      balance=Number(direct.data.available_qty||0);
      unitId=String(direct.data.unit_id||'');
    }
  }catch(e){}

  // Safety fallback: use the same Material Stock report shown in Materials > Stock.
  // Match by item ID first, then visible item name for legacy duplicate master IDs.
  if(!(balance>0.001)){
    try{
      const r=await ajax('bsm_report',{report:'material_stock',site_id:siteId,stock_scope:'site_options'});
      let rows=(r&&r.success&&Array.isArray(r.data?.rows))?r.data.rows:[];
      rows=rows.filter(x=>String(x.location_type||'').toLowerCase()==='site');
      let matches=rows.filter(x=>String(x.item_id||'')===itemId);
      if(!matches.length && itemName){
        const n=itemName.toLowerCase();
        matches=rows.filter(x=>String(x.item_name||'').trim().toLowerCase()===n);
      }
      if(matches.length){
        const positive=matches.filter(x=>Number(x.balance||0)>0.001);
        const use=positive.length?positive:matches;
        const first=use[0];
        unitId=String(first.unit_id||unitId||'');
        const sameUnit=unitId?use.filter(x=>String(x.unit_id||'')===unitId):use;
        balance=sameUnit.reduce((a,x)=>a+Number(x.balance||0),0);
      }
    }catch(e){}
  }

  $available.val(Number(balance||0)!==0?Number(balance).toFixed(2):'');
  if($unit.length){
    $unit.val(unitId||'');
    bsmRefreshSearchableSelect($unit[0]);
  }
}
async function refreshAllConsumptionAvailability(){
  if($('#bsm-form').data('module')!=='site_consumptions') return;
  const jobs=[]; $('#bsm-lines-body .bsm-line').each(function(){jobs.push(refreshConsumptionAvailability($(this)));});
  await Promise.all(jobs);
}

async function saveForm(){
  if(!validateMobileFields($('#bsm-form'))) return;
  const key=$('#bsm-form').data('module'),cfg=App.configs[key], data={id:App.editId}; $('#bsm-form-fields').find('input,select,textarea').each(function(){if(!this.name)return;data[this.name]=isDateFieldName(this.name)?dateSql($(this).val()):$(this).val();}); if(key==='material_inwards'){const $bottomRemarks=$('#bsm-line-items .bsm-inward-bottom-remarks textarea[name="remarks"]');if($bottomRemarks.length)data.remarks=$bottomRemarks.val();} if(!App.editId&&masterModules.includes(key))data.status='active'; if(!App.editId&&key==='material_requisitions')data.status='pending_approval'; if(key==='site_consumptions')data.status='approved';
  if(key==='labour_contractors'){
    data._employees=$('#bsm-contractor-employee-rows tr').map(function(){const employee={id:Number($(this).data('employee-id')||0)};$(this).find('[data-employee-field]').each(function(){employee[$(this).data('employee-field')]=$(this).val();});return employee;}).get();
  }
  if(key==='material_inwards' && String(data.inward_type||'purchase')==='purchase' && !Number(data.purchase_order_id||0)){
    showToast('PO No. is required. Purchase cannot be entered without a Purchase Order.',true);
    return;
  }
  if(key==='material_requisitions'){
    const site=String(data.site_id||'');
    if(!site||site!==requisitionBoqSite){showToast('Select a Site and wait for BOQ items to load.',true);return;}
    for(const item of (data._items||[])){if(!requisitionBoqItems.has(String(item.item_id||''))){showToast('Only items from this Site BOQ can be requisitioned.',true);return;}}
    const today=String(BSM.today||'');
    for(const nm of ['requisition_date','required_date']){
      const val=String(data[nm]||'');
      if(!val){const $f=$(`#bsm-form-fields [name="${nm}"]`).first();$f.addClass('bsm-invalid').trigger('focus');setTimeout(()=>$f.removeClass('bsm-invalid'),2200);showToast((nm==='requisition_date'?'Requisition Date':'Required Date')+' is required',true);return;}
      if(today && val<today){const $f=$(`#bsm-form-fields [name="${nm}"]`).first();$f.addClass('bsm-invalid').trigger('focus');setTimeout(()=>$f.removeClass('bsm-invalid'),2200);showToast((nm==='requisition_date'?'Requisition Date':'Required Date')+' cannot be before today',true);return;}
    }
  }
  if(['quotations','tax_invoices'].includes(key)){if(key==='quotations'){const $qt=$('#bsm-line-items .bsm-quote-notes textarea[name="terms"]');if($qt.length)data.terms=$qt.val();}if($('#bsm-quote-round').length){data.round_off=$('#bsm-quote-round').val()||0;}}
  if(cfg.items){data._items=[];$('#bsm-lines-body .bsm-line').each(function(){const x={};$(this).find('input,select,textarea').each(function(){if(!this.name)return;x[this.name]=isDateFieldName(this.name)?dateSql($(this).val()):$(this).val();});data._items.push(x);});}
  if(key==='site_consumptions'){
    const seenItems=new Set();
    for(const row of (data._items||[])){
      const iid=String(row.item_id||'');
      if(iid && seenItems.has(iid)){showToast('Same Item cannot be repeated in Consumption Form',true);return;}
      if(iid)seenItems.add(iid);
    }
  }
  if(key==='labour_attendance'){
    $('#bsm-lines-body .bsm-line').each(function(i){data._items[i].employee_hours=$(this).find('.bsm-labour-working-hours').map(function(){return {worker_id:Number($(this).data('worker')),working_hours:this.value};}).get().filter(h=>h.working_hours!=='');});
    const seen=new Set();
    for(const row of (data._items||[])){
      const cid=String(row.contractor_id||''); const attend=String(row.no_of_labour||'').trim();
      if(!cid){showToast('Please select a Labour Contractor.',true);return;}
      if(!(row.employee_hours||[]).some(h=>Number(h.working_hours)>0)){showToast('Enter Working Hours for at least one employee.',true);return;}
      if(row.employee_hours.some(h=>!Number.isFinite(Number(h.working_hours))||Number(h.working_hours)<0||Number(h.working_hours)>24)){showToast('Working Hours must be between 0 and 24.',true);return;}
      if(!/^\d+$/.test(attend)){showToast('Attendance must be a whole number.',true);return;}
      if(Number(attend)<=0){showToast('Attendance must be greater than 0.',true);return;}
      if(seen.has(cid)){showToast('This Labour Contractor has already been selected.',true);return;}
      seen.add(cid);
    }
  }
  if(key==='material_dispatches'){
    if(!(data.dispatch_from==='site'?data.from_site_id:data.warehouse_id)){showToast(data.dispatch_from==='site'?'Select From Site':'Select Warehouse',true);return;}
    if(data.dispatch_from==='site'&&String(data.from_site_id)===String(data.site_id)){showToast('From Site and To Site must be different',true);return;}
    const lines=data._items||[];
    for(const it of lines){const q=Number(it.qty||0);if(!Number.isFinite(q)||q<0||q>Number(it.available_qty||0)+0.0001||(it.pending_qty!==''&&q>Number(it.pending_qty)+0.0001)){showToast('Dispatch Qty must be within Pending Qty and Available Stock',true);return;}}
    data._items=lines.filter(it=>Number(it.qty)>0);
    if(!data._items.length){showToast('Enter Dispatch Qty for at least one item',true);return;}
  }
  const dispatchPhoto=key==='material_dispatches'?($('#bsm-dispatch-photo')[0]?.files?.[0]||null):null;
  if(dispatchPhoto&&(!['image/jpeg','image/png','image/webp'].includes(dispatchPhoto.type)||dispatchPhoto.size>10*1024*1024)){showToast('Choose a JPG, PNG or WebP photo up to 10 MB.',true);return;}
  const contractorDocFile=(key==='contractor_bills')?($('#bsm-contractor-doc-file')[0]?.files?.[0]||null):null;
  const contractorPaymentPhoto=(key==='contractor_advances')?($('#bsm-contractor-payment-photo')[0]?.files?.[0]||null):null;
  const paymentDocumentFile=(key==='vendor_payments')?($('#bsm-payment-document-file')[0]?.files?.[0]||null):null;
  const purchaseInvoiceFile=(key==='material_inwards' && String(data.inward_type||'purchase')==='purchase')?($('#bsm-purchase-invoice-file')[0]?.files?.[0]||null):null;
  if(purchaseInvoiceFile && !['gst_bill','non_gst_bill'].includes(String(data.bill_type||''))){showToast('Please select Bill Type before uploading the invoice.',true);return;}
  const siteReceiptChallanFile=(key==='site_receipts')?($('#bsm-site-receipt-challan-file')[0]?.files?.[0]||null):null;
  const consumptionItemDocs=[];
  if(key==='site_consumptions'){$('#bsm-lines-body .bsm-line').each(function(){const file=$(this).find('.bsm-consumption-document-file')[0]?.files?.[0]||null;const itemId=$(this).find('[name="item_id"]').val()||'';if(file&&itemId)consumptionItemDocs.push({item_id:String(itemId),file});});}
  const r=await ajax('bsm_save',{module:key,data:JSON.stringify(data)}); if(r.success){
    let uploadFailed=false;
    if(paymentDocumentFile && r.data?.id){
      try{
        const fd=new FormData();
        fd.append('action','bsm_upload');fd.append('nonce',BSM.nonce);fd.append('module','vendor_payments');fd.append('reference_id',r.data.id);fd.append('document_type','Payment Document');fd.append('file',paymentDocumentFile);
        const ur=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});
        if(!ur.success) uploadFailed=true;
      }catch(e){uploadFailed=true;}
    }
    if(contractorDocFile && r.data?.id){
      try{
        const fd=new FormData();
        fd.append('action','bsm_upload');fd.append('nonce',BSM.nonce);fd.append('module','contractor_bills');fd.append('reference_id',r.data.id);fd.append('document_type','Contractor Bill');fd.append('file',contractorDocFile);
        const ur=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});
        if(!ur.success) uploadFailed=true;
      }catch(e){uploadFailed=true;}
    }
    if(contractorPaymentPhoto && r.data?.id){
      try{
        const allowed=['image/png','image/jpeg','image/webp'];
        if(contractorPaymentPhoto.type && !allowed.includes(contractorPaymentPhoto.type)){
          uploadFailed=true;
        }else{
          const fd=new FormData();
          fd.append('action','bsm_upload');fd.append('nonce',BSM.nonce);fd.append('module','contractor_advances');fd.append('reference_id',r.data.id);fd.append('document_type','Payment Photo');fd.append('file',contractorPaymentPhoto);
          const ur=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});
          if(!ur.success) uploadFailed=true;
        }
      }catch(e){uploadFailed=true;}
    }
    if(purchaseInvoiceFile && r.data?.id){
      try{
        const fd=new FormData();
        fd.append('action','bsm_upload');fd.append('nonce',BSM.nonce);fd.append('module','material_inwards');fd.append('reference_id',r.data.id);fd.append('document_type','Supplier Invoice');fd.append('bill_type',data.bill_type||'');fd.append('file',purchaseInvoiceFile);
        const ur=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});
        if(!ur.success) uploadFailed=true;
      }catch(e){uploadFailed=true;}
    }
    if(dispatchPhoto && r.data?.id){
      try{
        const fd=new FormData();fd.append('action','bsm_upload');fd.append('nonce',BSM.nonce);fd.append('module','material_dispatches');fd.append('reference_id',r.data.id);fd.append('document_type','Dispatch Photo');fd.append('file',dispatchPhoto);
        const ur=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});if(!ur.success)uploadFailed=true;
      }catch(e){uploadFailed=true;}
    }
    if(siteReceiptChallanFile && r.data?.id){
      try{
        const fd=new FormData();
        fd.append('action','bsm_upload');fd.append('nonce',BSM.nonce);fd.append('module','site_receipts');fd.append('reference_id',r.data.id);fd.append('document_type','Receiving Challan');fd.append('file',siteReceiptChallanFile);
        const ur=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});
        if(!ur.success) uploadFailed=true;
      }catch(e){uploadFailed=true;}
    }
    if(consumptionItemDocs.length && r.data?.id){
      try{
        const gr=await ajax('bsm_get',{module:'site_consumptions',id:r.data.id});
        const savedItems=gr.success?(gr.data?._items||[]):[];
        for(const d of consumptionItemDocs){
          const saved=savedItems.find(x=>String(x.item_id)===String(d.item_id));
          if(!saved?.id){uploadFailed=true;continue;}
          const fd=new FormData();fd.append('action','bsm_consumption_item_upload');fd.append('nonce',BSM.nonce);fd.append('consumption_item_id',saved.id);fd.append('file',d.file);
          const ur=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});
          if(!ur.success)uploadFailed=true;
        }
      }catch(e){uploadFailed=true;}
    }
    showToast(uploadFailed?'Saved, but file upload failed.':(r.data.message||'Saved Successfully'),uploadFailed);
    const sourceView=App.current;
    $('#bsm-modal').hide();
    await refreshOptionsAfterSave(key);

    // Direct form workflow: Save -> related Master/Register view.
    // Do this before special legacy redirects so the user's selected form has one predictable finish view.
    if(directFormViews.has(sourceView)){
      const destination=relatedMasterView(sourceView,key);
      if(destination) return openView(destination);
    }

    if(key==='contractor_advances'){return openView('contractor_advance_register');}
    if(['quotation_master','quotation_items','invoice_master','invoice_items'].includes(App.current)&&['quotations','tax_invoices'].includes(key)){return renderSalesRegister(App.current);}
    if(key==='vendor_payments'&&String(data.payment_type||'')==='employee_allowance'&&App.current==='pending_allowance_payment'){return renderAllowanceQueue('payment');}
    if(key==='vendor_payments'&&String(data.payment_type||'')==='salary'&&App.current==='pending_salary_payment'){return renderSalarySlips('pending');}
    if(key==='material_dispatches'&&$('#bsm-form').data('requisition-id')){App.current='pending_material_dispatch';$('#bsm-form').removeData('requisition-id');return renderPendingDispatch();}
    if(key==='material_inwards'&&String(data.inward_type||'')==='return'&&sourceView==='pending_material_receipt'){
      return openView('pending_material_receipt');
    }
    if(key==='material_inwards'&&String(data.inward_type||'')==='purchase'&&App.current==='purchase_register'){return renderPurchaseRegister();}
    if(key==='employee_allowance_limits'&&App.current==='employee_allowance_limits'){return renderEmployeeAllowanceLimits();}
    renderModule(key);
  } else showToast(r.data?.message||'Save failed',true);
}


function officeAttendanceRowHtml(x){const st=String(x.attendance_status||'').toUpperCase();if(st==='H')return `<tr data-user-id="${x.user_id}"><td>${esc(x.name||'')}</td><td>${esc(x.designation||'')}</td><td><span class="bsm-pill">Holiday</span><input type="hidden" class="bsm-pa-value" value="H"></td></tr>`;return `<tr data-user-id="${x.user_id}"><td>${esc(x.name||'')}</td><td>${esc(x.designation||'')}</td><td><div class="bsm-attendance-actions"><div class="bsm-pa-toggle"><button type="button" class="bsm-pa-btn ${st==='P'?'active':''}" data-value="P">P</button><button type="button" class="bsm-pa-btn ${st==='A'?'active':''}" data-value="A">A</button><input type="hidden" class="bsm-pa-value" value="${esc(st)}"></div></div></td></tr>`;}
async function renderOfficeDailyAttendance(){setTitle('Daily Attendance');const t=new Date(),today=`${t.getFullYear()}-${String(t.getMonth()+1).padStart(2,'0')}-${String(t.getDate()).padStart(2,'0')}`;$('#bsm-content').html(`<div class="bsm-panel bsm-office-attendance-panel"><div class="bsm-toolbar bsm-office-att-toolbar"><label class="bsm-inline-date-label">Date<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="bsm-office-att-date" value="${today}"></label><button type="button" class="bsm-btn" id="bsm-office-mark-all-p">Mark All P</button><div class="bsm-spacer"></div><button type="button" class="bsm-btn bsm-btn-primary" id="bsm-office-att-submit">Submit</button></div><div id="bsm-office-att-list"><div class="bsm-loading">Loading…</div></div></div>`);await loadOfficeDailyAttendance();}
async function loadOfficeDailyAttendance(){const r=await ajax('bsm_office_attendance_day',{date:$('#bsm-office-att-date').val()||''});if(!r.success){$('#bsm-office-att-list').html('<div class="bsm-empty">Unable to load attendance.</div>');return;}const rows=r.data.rows||[];let h='<div class="bsm-table-wrap"><table class="bsm-office-att-table"><thead><tr><th>User</th><th>Designation</th><th>Attendance</th></tr></thead><tbody>';rows.forEach(x=>h+=officeAttendanceRowHtml(x));if(!rows.length)h+='<tr><td colspan="3" class="bsm-empty">No active employees found.</td></tr>';h+='</tbody></table></div>';$('#bsm-office-att-list').html(h);}
async function saveOfficeDailyAttendance(){const rows=[];let missing=false;$('#bsm-office-att-list tbody tr[data-user-id]').each(function(){const userId=Number($(this).data('user-id'));const st=$(this).find('.bsm-pa-value').val();if(!st)missing=true;rows.push({user_id:userId,status:st});});if(missing){showToast('Please mark Present or Absent for every employee.',true);return;}if(!rows.length){showToast('No attendance users found.',true);return;}const r=await ajax('bsm_office_attendance_save',{date:$('#bsm-office-att-date').val()||'',rows:JSON.stringify(rows)});if(r.success){showToast(r.data.message||'Attendance saved');await loadOfficeDailyAttendance();}else showToast(r.data?.message||'Unable to save attendance',true);}
async function renderOfficeAttendanceMaster(){setTitle('Attendance Master');const filters=`<div id="bsm-office-att-master-filters" class="bsm-filters" style="display:none"><label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="office_att_date_from"></label><label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="office_att_date_to"></label>${selectHtml('office_att_user','User','users','')}<label>Status<select id="office_att_status"><option value="">Select</option><option value="P">Present</option><option value="A">Absent</option><option value="H">Holiday</option></select></label><button class="bsm-icon-btn bsm-purchase-clear-btn" id="office_att_clear" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button></div>`;$('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-office-att-search" class="bsm-search" aria-label="Search Attendance" placeholder=""><button id="bsm-office-att-filter-toggle" class="bsm-icon-btn" title="Filters">☷</button><div class="bsm-spacer"></div>${excelButton('bsm-office-att-export')}</div>${filters}<div id="bsm-office-att-master-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);await loadOfficeAttendanceMaster();}
async function loadOfficeAttendanceMaster(exportAll=false){const r=await ajax('bsm_office_attendance_master',{page:exportAll?1:App.page,export_all:exportAll?'1':'',search:$('#bsm-office-att-search').val()||'',date_from:dateSql($('#office_att_date_from').val()||''),date_to:dateSql($('#office_att_date_to').val()||''),user_id:$('#office_att_user').val()||'',status:$('#office_att_status').val()||''});if(!r.success){if(!exportAll)$('#bsm-office-att-master-list').html('<div class="bsm-empty">Unable to load Attendance Master.</div>');return null;}if(exportAll)return r.data.rows||[];const rows=r.data.rows||[];let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Date</th><th>User</th><th>Designation</th><th>Attendance</th></tr></thead><tbody>';rows.forEach((x,i)=>{h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${dateFmt(x.attendance_date)}</td><td>${esc(x.name||'')}</td><td>${esc(x.designation||'')}</td><td><span class="bsm-pill">${x.attendance_status==='H'?'Holiday':(x.attendance_status==='P'?'Present':'Absent')}</span></td></tr>`});if(!rows.length)h+='<tr><td colspan="5" class="bsm-empty">No attendance records found.</td></tr>';h+='</tbody></table></div>';if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;$('#bsm-office-att-master-list').html(h);return rows;}
async function exportOfficeAttendanceMaster(){const rows=await loadOfficeAttendanceMaster(true);if(!rows)return;downloadExcelFile('Attendance Master',['Sl','Date','User','Designation','Attendance'],rows.map((x,i)=>[i+1,dateFmt(x.attendance_date),x.name||'',x.designation||'',x.attendance_status==='H'?'Holiday':(x.attendance_status==='P'?'Present':'Absent')]));}
async function renderOfficeMonthlySalary(){setTitle('Monthly Salary');const n=new Date(),month=`${n.getFullYear()}-${String(n.getMonth()+1).padStart(2,'0')}`;$('#bsm-content').html(`<div class="bsm-toolbar"><label class="bsm-inline-date-label">Month<input type="month" id="bsm-salary-month" value="${month}"></label><div class="bsm-spacer"></div>${excelButton('bsm-salary-export')}</div><div class="bsm-panel"><div class="bsm-salary-note">Payable Salary = applicable Monthly Salary ÷ calendar days in the selected month × payable days (Present + applicable Holidays, counted once per date). Salary effective date is applied day-wise.</div><div id="bsm-salary-list"><div class="bsm-loading">Loading…</div></div></div>`);await loadOfficeMonthlySalary();}
async function loadOfficeMonthlySalary(exportOnly=false){const r=await ajax('bsm_monthly_salary',{month:$('#bsm-salary-month').val()||''});if(!r.success){if(!exportOnly)$('#bsm-salary-list').html('<div class="bsm-empty">Unable to calculate salary.</div>');return null;}const rows=r.data.rows||[];if(exportOnly)return rows;let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>User</th><th>Designation</th><th class="num">Monthly Salary</th><th class="num">Present Days</th><th class="num">Holiday Days</th><th class="num">Absent Days</th><th class="num">Marked Days</th><th class="num">Payable Salary</th></tr></thead><tbody>';rows.forEach((x,i)=>h+=`<tr><td>${i+1}</td><td>${esc(x.name||'')}</td><td>${esc(x.designation||'')}</td><td class="num">${moneyFmt(x.monthly_salary)}</td><td class="num">${x.present_days}</td><td class="num">${x.holiday_days||0}</td><td class="num">${x.absent_days}</td><td class="num">${x.marked_days}</td><td class="num">${moneyFmt(x.payable_salary)}</td></tr>`);if(!rows.length)h+='<tr><td colspan="9" class="bsm-empty">No attendance-applicable users found.</td></tr>';h+='</tbody></table></div>';$('#bsm-salary-list').html(h);return rows;}
async function exportOfficeMonthlySalary(){const rows=await loadOfficeMonthlySalary(true);if(!rows)return;downloadExcelFile('Monthly Salary '+($('#bsm-salary-month').val()||''),['Sl','User','Designation','Monthly Salary','Present Days','Holiday Days','Absent Days','Marked Days','Payable Salary'],rows.map((x,i)=>[i+1,x.name||'',x.designation||'',x.monthly_salary||0,x.present_days||0,x.holiday_days||0,x.absent_days||0,x.marked_days||0,x.payable_salary||0]));}

function userRoleLabel(role){return role==='bsm_employee'?'Employee':'Admin';}
async function renderUsers(){
  setTitle('Users');
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-user-search" class="bsm-search" aria-label="Search Users" placeholder="" value="${esc(App.search)}"><div class="bsm-spacer"></div>${excelButton('bsm-user-export-excel')}<button id="bsm-user-add" class="bsm-btn bsm-btn-primary">+</button></div><div id="bsm-user-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  await loadUserList();
}
async function loadUserList(){
  const $list=$('#bsm-user-list');
  if(!$list.length)return;
  const r=await ajax('bsm_user_list',{search:App.search});
  if(!r.success){const msg=r.data?.message||'Unable to load users';$list.html(`<div class="bsm-empty">${esc(msg)}</div>`);showToast(msg,true);return;}
  App.userRows=r.data.rows||[];
  let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>User ID</th><th>Name</th><th>Email</th><th>Role</th><th>Designation</th><th>Mobile</th><th>Attendance</th><th class="num">Salary</th><th>Effective Date</th><th>Status</th><th>Action</th></tr></thead><tbody>';
  (r.data.rows||[]).forEach((x,i)=>h+=`<tr><td>${i+1}</td><td>${esc(x.user_id)}</td><td>${esc(x.name)}</td><td>${esc(x.email)}</td><td>${esc(userRoleLabel(x.role))}</td><td>${esc(x.designation)}</td><td>${esc(x.mobile||'')}</td><td>${Number(x.attendance_applicable||0)===1?'Yes':'No'}</td><td class="num">${Number(x.attendance_applicable||0)===1?moneyFmt(x.salary):''}</td><td>${Number(x.attendance_applicable||0)===1?dateFmt(x.salary_effective_date):''}</td><td><span class="bsm-pill">${esc(x.status)}</span></td><td class="bsm-actions"><button class="bsm-user-edit" data-row='${esc(JSON.stringify(x))}' title="Edit">✎</button><button class="bsm-user-delete bsm-action-delete" data-id="${x.id}" title="Delete"><img class="bsm-delete-icon" src="${BSM.deleteIcon}" alt="Delete"></button></td></tr>`);
  if(!(r.data.rows||[]).length)h+='<tr><td colspan="12" class="bsm-empty">No records found.</td></tr>';
  h+='</tbody></table></div>';
  $list.html(h);
}
async function openUserForm(row={}){
  await ensureOptions(['designations','sites']);
  App.editId=row.id||0;
  const roles=[['administrator','Admin'],['bsm_employee','Employee']];
  const existingRole=row.role==='administrator'?'administrator':'bsm_employee';
  let fields=[F('name','Name',{required:true}),F('email','Email',{type:'email',required:true}),F('designation_id','Designation',Object.assign({required:true,addMaster:'designations'},ref('designations'))),F('mobile','Mobile',{required:true}),F('role','Role',{type:'select',options:roles,required:true}),F('attendance_applicable','Attendance Applicable',{type:'select',options:[['0','No'],['1','Yes']]}),F('salary','Monthly Salary',Object.assign({step:'0.01'},money)),F('salary_effective_date','Salary Effective Date',date),F('user_id','User ID',{required:true}),F('password',row.id?'New Password (leave blank to keep)':'Password',{type:'password',required:!row.id}),F('status','Status',status())];
  if(!row.id)fields=fields.filter(f=>f.name!=='status');
  $('#bsm-form-fields').removeClass('bsm-approval-summary');
  $('#bsm-modal-title').text(row.id?'Edit User':'Add User');
  const values=Object.assign({},row,{role:existingRole});
  $('#bsm-form-fields').html(fields.map(f=>fieldHtml(f,values[f.name]||'')).join('')+'<div id="bsm-user-access" class="bsm-user-access wide"></div>');
  $('#bsm-line-items,#bsm-documents').empty();$('#bsm-form').attr('data-module','users').data('module','users');
  renderUserAccess(row);toggleUserSalaryFields();$('#bsm-modal').show();
}
function toggleUserSalaryFields(){
  const yes=String($('#bsm-form-fields select[name="attendance_applicable"]').val()||'0')==='1';
  ['salary','salary_effective_date'].forEach(name=>{
    const $input=$('#bsm-form-fields [name="'+name+'"]');
    $input.closest('label').toggle(yes);$input.prop('required',yes);
  });
}
function allUserViews(){
  const out=[];App.nav.forEach(g=>{if(['Masters','Settings'].includes(g.group))return;g.items.forEach(([key,label])=>out.push({key,label,group:g.group}))});return out;
}
function parseJsonArray(v){if(Array.isArray(v))return v;try{const x=JSON.parse(v||'[]');return Array.isArray(x)?x:[]}catch(e){return []}}
function renderUserAccess(row={}){
  const role=$('#bsm-form-fields select[name="role"]').val()||'bsm_employee';
  const $box=$('#bsm-user-access');
  if(role==='administrator'){$box.html('<div class="bsm-access-note">Admin has access to all Sites and all Views.</div>').hide();return;}
  const selectedSites=new Set(parseJsonArray(row.assigned_sites).map(String));
  const allowedSaved=parseJsonArray(row.allowed_views);const allowed=new Set((allowedSaved.length?allowedSaved:allUserViews().map(v=>v.key)).map(String));
  const siteRows=(App.options.sites||[]);
  const siteHtml=`<div class="bsm-access-sites"><span class="bsm-field-label">Site</span><div class="bsm-multiselect" id="bsm-user-sites"><div class="bsm-multiselect-control"><input type="text" class="bsm-multiselect-search" aria-label="Search and select Sites" placeholder="" autocomplete="off"><span>⌄</span></div><div class="bsm-multiselect-menu">${siteRows.map(s=>`<label class="bsm-multiselect-option"><input type="checkbox" value="${esc(s.id)}" ${selectedSites.has(String(s.id))?'checked':''}><span>${esc(s.label)}</span></label>`).join('')}</div><div class="bsm-multiselect-selected"></div></div></div>`;
  const viewHtml=`<div class="bsm-access-table"><table><thead><tr><th>View</th><th>Action</th></tr></thead><tbody>${allUserViews().map(v=>`<tr><td><span class="bsm-access-group">${esc(v.group)}</span> - ${esc(v.label)}</td><td><input type="checkbox" class="bsm-view-permission" value="${esc(v.key)}" ${allowed.has(v.key)?'checked':''}></td></tr>`).join('')}</tbody></table></div>`;
  $box.html(siteHtml+viewHtml).show();updateUserSiteChips();
}
function updateUserSiteChips(){const names=[];$('#bsm-user-sites input[type="checkbox"]:checked').each(function(){names.push($(this).closest('label').find('span').text())});$('#bsm-user-sites .bsm-multiselect-selected').text(names.length?names.join(', '):'');}
async function saveUser(){
  if(!validateMobileFields($('#bsm-form')))return;
  const data={id:App.editId};$('#bsm-form-fields').find('input,select,textarea').not('.bsm-multiselect-search,.bsm-view-permission,#bsm-user-sites input').each(function(){if(this.name)data[this.name]=isDateFieldName(this.name)?dateSql($(this).val()):$(this).val();});
  if(data.role==='bsm_employee'){
    data.assigned_sites=$('#bsm-user-sites input[type="checkbox"]:checked').map(function(){return Number(this.value)}).get();
    data.allowed_views=$('.bsm-view-permission:checked').map(function(){return this.value}).get();
    if(!data.allowed_views.length){showToast('Please allow at least one View for Employee.',true);return;}
  }else{data.assigned_sites=[];data.allowed_views=[];}
  if(!App.editId)data.status='active';const r=await ajax('bsm_user_save',{data:JSON.stringify(data)});if(r.success){showToast(r.data.message);$('#bsm-modal').hide();delete App.options.users;renderUsers();}else{showToast(r.data?.message||'Save failed',true);if(r.data?.field){const $f=$('#bsm-form-fields').find(`[name="${r.data.field}"]`);if($f.length){$f.addClass('bsm-invalid').trigger('focus');setTimeout(()=>$f.removeClass('bsm-invalid'),2200);}}}
}
$(document).on('change','#bsm-form[data-module="users"] select[name="role"]',function(){renderUserAccess({assigned_sites:'[]',allowed_views:'[]'});});
$(document).on('click','.bsm-multiselect-control',function(e){$(this).parent().toggleClass('open');$(this).find('input').trigger('focus');});
$(document).on('input','.bsm-multiselect-search',function(){const q=$(this).val().toLowerCase();$(this).closest('.bsm-multiselect').find('.bsm-multiselect-option').each(function(){$(this).toggle($(this).text().toLowerCase().includes(q));});});
$(document).on('change','#bsm-user-sites input[type="checkbox"]',updateUserSiteChips);
$(document).on('click',function(e){if(!$(e.target).closest('.bsm-multiselect').length)$('.bsm-multiselect').removeClass('open');});

let QuickMaster={master:'',target:''};
function quickAddMaster(master,target){
  QuickMaster={master,target};
  $('#bsm-quick-msg').text('');
  if(master==='items'){
    $('#bsm-quick-title').text('Add Item');
    const body=`<div class="bsm-form-grid bsm-quick-item-grid">
      ${fieldHtml(F('item_name','Item Name',{required:true}), '')}
      ${fieldHtml(F('category_id','Category',ref('categories')), '')}
      ${fieldHtml(F('unit_id','Unit',ref('units')), '')}
      ${fieldHtml(F('hsn_code','HSN Code'), '')}
      ${fieldHtml(F('gst_rate','GST Rate %',ref('gst_rates')), '')}
      ${fieldHtml(F('opening_qty','Opening Qty',qty), '')}
      ${fieldHtml(F('opening_rate','Price',money), '')}
      ${fieldHtml(F('minimum_stock','Minimum Stock',qty), '')}
    </div><div id="bsm-quick-msg" class="bsm-form-msg"></div>`;
    $('#bsm-quick-body').html(body);
    ensureOptions(['categories','units','gst_rates'],true).then(()=>{
      const c=App.options.categories||[],u=App.options.units||[],g=App.options.gst_rates||[];
      $('#bsm-quick-body select[name="category_id"]').html('<option value="">Select</option>'+c.map(x=>`<option value="${x.id}">${esc(x.label)}</option>`).join(''));
      $('#bsm-quick-body select[name="unit_id"]').html('<option value="">Select</option>'+u.map(x=>`<option value="${x.id}">${esc(x.label)}</option>`).join(''));
      $('#bsm-quick-body select[name="gst_rate"]').html('<option value="">Select</option>'+g.map(x=>`<option value="${x.id}">${esc(x.label)}</option>`).join(''));
    });
  }else if(master==='gst_rates'){
    $('#bsm-quick-title').text('Add GST Rate');
    $('#bsm-quick-body').html(`<label><span>GST Rate % <span class="bsm-required-star">*</span></span><input type="number" step="0.01" min="0" id="bsm-quick-gst-rate" autocomplete="off"></label><div id="bsm-quick-msg" class="bsm-form-msg"></div>`);
  }else{
    const isState=master==='states'; const label=isState?'State':'Designation';
    $('#bsm-quick-title').text(`Add ${label}`);
    $('#bsm-quick-body').html(`<label><span>${label} Name <span class="bsm-required-star">*</span></span><input type="text" id="bsm-quick-name" autocomplete="off"></label><div id="bsm-quick-msg" class="bsm-form-msg"></div>`);
  }
  $('#bsm-quick-modal').css('display','flex');
  setTimeout(()=>$('#bsm-quick-body input:first').trigger('focus'),50);
}
async function saveQuickMaster(){
  const master=QuickMaster.master,target=QuickMaster.target;if(!master||!target)return;
  let data={},type='',label='';
  if(master==='items'){
    label='Item';type='items';
    $('#bsm-quick-body').find('input,select,textarea').each(function(){data[this.name]=$(this).val();});
    if(!String(data.item_name||'').trim()){ $('#bsm-quick-msg').text('Item Name is required'); return; }
    data.status='active';
  }else if(master==='gst_rates'){
    label='GST Rate';type='gst_rates';
    const rate=String($('#bsm-quick-gst-rate').val()||'').trim();if(rate===''||Number(rate)<0){$('#bsm-quick-msg').text('GST Rate is required');return;}
    data={gst_rate:rate,status:'active'};
  }else{
    const isState=master==='states'; label=isState?'State':'Designation'; type=isState?'states':'designations';
    const name=$('#bsm-quick-name').val().trim();if(!name){$('#bsm-quick-msg').text(`${label} Name is required`);return;}
    data=isState?{state_name:name,status:'active'}:{designation_name:name,status:'active'};
  }
  $('#bsm-quick-save').prop('disabled',true);const r=await ajax('bsm_save',{module:master,data:JSON.stringify(data)});$('#bsm-quick-save').prop('disabled',false);
  if(!r.success){$('#bsm-quick-msg').text(r.data?.message||`Unable to add ${label}`);return;}
  delete App.options[type];await ensureOptions([type],true);
  const $sel=$(`#bsm-modal select[name="${target}"]`).last(); const rows=App.options[type]||[];
  $sel.html('<option value="">Select</option>'+rows.map(x=>`<option value="${x.id}">${esc(x.label)}</option>`).join(''));
  if(master==='gst_rates')$sel.val(String(data.gst_rate)).trigger('change');else if(r.data?.id)$sel.val(String(r.data.id)).trigger('change');
  $('#bsm-quick-modal').hide();showToast(`${label} added`);
}

function filterVisibleRows(rows,fields,q){q=String(q||'').trim().toLowerCase();if(!q)return rows;return rows.filter(row=>fields.map(f=>{let v=typeof f==='function'?f(row):row[f];return String(v??'').toLowerCase();}).join(' | ').includes(q));}

function requisitionItemDetailsHtml(items){
  const rows=items||[];
  const num=v=>Number(v||0).toLocaleString('en-IN',{maximumFractionDigits:2});
  let h='<div class="bsm-purchase-item-details"><div class="bsm-purchase-detail-wrap"><table class="bsm-purchase-detail-table"><thead><tr><th>Item</th><th class="num">Requested Qty</th><th class="num">Approved Qty</th><th class="num">Rejected Qty</th><th>Unit</th><th>Remarks</th></tr></thead><tbody>';
  rows.forEach(it=>{h+=`<tr><td>${esc(it.item_name||labelFor('items',it.item_id)||'')}</td><td class="num">${num(it.requested_qty)}</td><td class="num">${num(it.approved_qty)}</td><td class="num">${num(it.rejected_qty)}</td><td>${esc(it.unit_name||labelFor('units',it.unit_id)||'')}</td><td>${esc(it.remarks||'')}</td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="6" class="bsm-empty">No item data found.</td></tr>';
  h+='</tbody></table></div></div>';
  return h;
}
async function toggleRequisitionDetails($row){
  const id=Number($row.data('id')||0);if(!id)return;
  const $existing=$row.next('.bsm-requisition-details-row');
  if($existing.length){const isOpen=$existing.is(':visible');$existing.toggle(!isOpen);$row.attr('aria-expanded',String(!isOpen)).toggleClass('is-expanded',!isOpen);return;}
  const colspan=$row.children('td').length;
  const $detail=$(`<tr class="bsm-requisition-details-row"><td colspan="${colspan}"><div class="bsm-loading">Loading items…</div></td></tr>`);
  $row.after($detail).attr('aria-expanded','true').addClass('is-expanded');
  const r=await ajax('bsm_requisition_approval_get',{id});
  if(!r.success){$detail.find('td').html('<div class="bsm-empty">Unable to load requisition items.</div>');return;}
  $detail.find('td').html(requisitionItemDetailsHtml(r.data?._items||[]));
}

async function togglePendingApprovalDetails($row){
  const id=Number($row.data('id')||0);if(!id)return;
  const $existing=$row.next('.bsm-pending-approval-details-row');
  if($existing.length){const isOpen=$existing.is(':visible');$existing.toggle(!isOpen);$row.attr('aria-expanded',String(!isOpen)).toggleClass('is-expanded',!isOpen);return;}
  const colspan=$row.children('td').length;
  const $detail=$(`<tr class="bsm-pending-approval-details-row"><td colspan="${colspan}"><div class="bsm-loading">Loading items…</div></td></tr>`);
  $row.after($detail).attr('aria-expanded','true').addClass('is-expanded');
  const r=await ajax('bsm_requisition_approval_get',{id});
  if(!r.success){$detail.find('td').html('<div class="bsm-empty">Unable to load requisition items.</div>');return;}
  $detail.find('td').html(requisitionItemDetailsHtml(r.data?._items||[]));
}
function requisitionItemsFilterItemOptions(){
  const categoryId=String($('#requisition_items_category').val()||'');
  const current=String($('#requisition_items_item').val()||'');
  const items=(App.options.items||[]).filter(x=>!categoryId || String(x.category_id||'')===categoryId);
  const $sel=$('#requisition_items_item');if(!$sel.length)return;
  $sel.html('<option value="">Select</option>'+items.map(x=>`<option value="${esc(x.id)}">${esc(x.label)}</option>`).join(''));
  if(items.some(x=>String(x.id)===current))$sel.val(current);else $sel.val('');
  bsmRefreshSearchableSelect($sel[0]);
}
function requisitionItemsSyncCategoryFromItem(){
  const itemId=String($('#requisition_items_item').val()||'');if(!itemId)return;
  const item=(App.options.items||[]).find(x=>String(x.id)===itemId);
  if(item&&item.category_id){$('#requisition_items_category').val(String(item.category_id));bsmRefreshSearchableSelect($('#requisition_items_category')[0]);}
}
async function renderMaterialRequisitionItem(){
  setTitle('Requisition Item Master');
  const filters=`<div id="bsm-requisition-items-filters" class="bsm-filters" style="display:none">
    <label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="requisition_items_date_from"></label>
    <label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="requisition_items_date_to"></label>
    ${selectHtml('requisition_items_site','Site','sites','')}
    ${selectHtml('requisition_items_category','Item Category','categories','')}
    ${selectHtml('requisition_items_item','Item','items','')}
    <button class="bsm-icon-btn bsm-purchase-clear-btn" id="requisition_items_clear_filters" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button>
  </div>`;
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-requisition-items-search" class="bsm-search" aria-label="Search Requisition Item Master" placeholder=""><button id="bsm-requisition-items-filter-toggle" class="bsm-icon-btn" title="Filters">☷</button><div class="bsm-spacer"></div>${excelButton('bsm-requisition-items-export')}</div>${filters}<div id="bsm-requisition-items-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  requisitionItemsFilterItemOptions();await loadMaterialRequisitionItem();
}
async function loadMaterialRequisitionItem(exportAll=false){
  const r=await ajax('bsm_material_requisition_item',{
    page:exportAll?1:App.page,export_all:exportAll?'1':'',search:$('#bsm-requisition-items-search').val()||'',
    date_from:dateSql($('#requisition_items_date_from').val()||''),date_to:dateSql($('#requisition_items_date_to').val()||''),
    site_id:$('#requisition_items_site').val()||'',category_id:$('#requisition_items_category').val()||'',item_id:$('#requisition_items_item').val()||''
  });
  if(!r.success){if(!exportAll)$('#bsm-requisition-items-list').html('<div class="bsm-empty">Unable to load Requisition Item Master.</div>');return null;}
  if(exportAll)return r.data.rows||[];
  const rows=r.data.rows||[],num=v=>Number(v||0).toLocaleString('en-IN',{maximumFractionDigits:2});
  let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Requisition</th><th>Date</th><th>Required Date</th><th>Site</th><th>Requested By</th><th>Approved By</th><th>Item Category</th><th>Item</th><th class="num">Requested Qty</th><th class="num">Approved Qty</th><th class="num">Rejected Qty</th><th>Unit</th><th>Status</th><th>Remarks</th></tr></thead><tbody>';
  rows.forEach((x,i)=>{h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.requisition_no||'')}</td><td>${dateFmt(x.requisition_date)}</td><td>${dateFmt(x.required_date)}</td><td>${esc(x.site_name||'')}</td><td>${esc(x.requested_by_name||'')}</td><td>${esc(x.approved_by_name||'')}</td><td>${esc(x.category_name||'')}</td><td>${esc(x.item_name||'')}</td><td class="num">${num(x.requested_qty)}</td><td class="num">${num(x.approved_qty)}</td><td class="num">${num(x.rejected_qty)}</td><td>${esc(x.unit_name||'')}</td><td><span class="bsm-pill">${esc(String(x.status||'').replaceAll('_',' '))}</span></td><td>${esc(x.remarks||'')}</td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="15" class="bsm-empty">No material requisition item records found.</td></tr>';
  h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-requisition-items-list').html(h);return rows;
}
async function exportMaterialRequisitionItem(){
  const rows=await loadMaterialRequisitionItem(true);if(!rows)return;
  const headers=['Sl','Requisition','Date','Required Date','Site','Requested By','Approved By','Item Category','Item','Requested Qty','Approved Qty','Rejected Qty','Unit','Status','Remarks'];
  const data=rows.map((x,i)=>[i+1,x.requisition_no||'',dateFmt(x.requisition_date),dateFmt(x.required_date),x.site_name||'',x.requested_by_name||'',x.approved_by_name||'',x.category_name||'',x.item_name||'',x.requested_qty||0,x.approved_qty||0,x.rejected_qty||0,x.unit_name||'',String(x.status||'').replaceAll('_',' '),x.remarks||'']);
  downloadExcelFile('Requisition Item Master',headers,data);
}

function drawPendingApproval(){const rows=filterVisibleRows(App.pendingApprovalRows||[],['requisition_no',x=>dateFmt(x.requisition_date),'site_name',x=>dateFmt(x.required_date),'requested_by_name','item_count'],App.specialSearch||'');let h='<div class="bsm-table-wrap"><table id="bsm-pending-approval-table"><thead><tr><th>Sl</th><th>Req. No.</th><th>Date</th><th>Site</th><th>Required Date</th><th>Requested By</th><th>Items</th><th>Action</th></tr></thead><tbody>';rows.forEach((x,i)=>h+=`<tr class="bsm-pending-approval-row" data-id="${x.id}" tabindex="0" aria-expanded="false" title="Click to view requisition items"><td>${i+1}</td><td>${esc(x.requisition_no)}</td><td>${dateFmt(x.requisition_date)}</td><td>${esc(x.site_name)}</td><td>${dateFmt(x.required_date)}</td><td>${esc(x.requested_by_name)}</td><td>${esc(x.item_count)}</td><td><button class="bsm-btn bsm-btn-small bsm-btn-success bsm-review-req" data-id="${x.id}">Approve</button></td></tr>`);if(!rows.length)h+='<tr><td colspan="9" class="bsm-empty">No requisitions pending approval.</td></tr>';h+='</tbody></table></div>';$('#bsm-pending-approval').html(h);}
async function renderPendingApproval(){
  setTitle('Pending Approval');App.specialSearch='';$('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-special-search" class="bsm-search" aria-label="Search" placeholder=""><div class="bsm-spacer"></div>${excelButton('bsm-pending-approval-excel')}</div><div id="bsm-pending-approval" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  const r=await ajax('bsm_pending_approval');if(!r.success)return;App.pendingApprovalRows=r.data.rows||[];drawPendingApproval();
}
async function openApprovalForm(id){
  const r=await ajax('bsm_requisition_approval_get',{id});if(!r.success)return showToast(r.data?.message||'Unable to load',true);const row=r.data;
  App.editId=id;App.approvalStockItems=row._items||[];$('#bsm-modal-title').text('Approve Material Requisition');
  $('#bsm-form-fields').addClass('bsm-approval-summary').html(`<label><span class="bsm-field-label">Requisition No.</span><input value="${esc(row.requisition_no)}" readonly></label><label><span class="bsm-field-label">Site</span><input value="${esc(row.site_name)}" readonly></label><label><span class="bsm-field-label">Required Date</span><input value="${dateFmt(row.required_date)}" readonly></label><label><span class="bsm-field-label">Approved By</span><input value="${esc(BSM.currentUser?.name||'')}" readonly></label>`);
  let h='<div class="bsm-lines bsm-approval-lines"><div class="bsm-lines-head"><h3>Items</h3></div><div class="bsm-table-wrap"><table><colgroup><col class="bsm-col-item"><col class="bsm-col-stock"><col class="bsm-col-other-site"><col class="bsm-col-requested"><col class="bsm-col-approved"><col class="bsm-col-rejected"><col class="bsm-col-unit"></colgroup><thead><tr><th>Item</th><th>Current HO Stock</th><th>Site Stock</th><th>Requested Qty</th><th>Approved Qty</th><th>Rejected Qty</th><th>Unit</th></tr></thead><tbody id="bsm-approval-lines">';
  (row._items||[]).forEach(it=>{const rq=Number(it.requested_qty||0);const aq=Number(it.approved_qty||0);const initial=(aq>0||Number(it.rejected_qty||0)>0)?aq:rq;const rejected=Math.max(0,rq-initial);h+=`<tr data-id="${it.id}" data-requested="${rq}"><td>${esc(it.item_name)}</td><td class="num">${Number(it.current_ho_stock||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td class="num"><button type="button" class="bsm-site-stock-details" data-item-index="${(row._items||[]).indexOf(it)}" title="View stock at other sites" aria-label="View other site stock for ${esc(it.item_name)}">${Number(it.other_site_stock||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</button></td><td class="num">${rq}</td><td><input class="bsm-approved-qty num" type="number" min="0" max="${rq}" step="0.01" value="${initial}"></td><td class="num bsm-rejected-qty">${rejected}</td><td>${esc(it.unit_name||'')}</td></tr>`;});
  h+='</tbody></table></div></div>';$('#bsm-line-items').html(h);$('#bsm-documents').empty();$('#bsm-form').data('module','requisition_approval');$('#bsm-modal').show();
}
// Nested, read-only breakdown popup. No inventory posting or approval changes.
function showApprovalSiteStockDetails(itemIndex){
  const it=(App.approvalStockItems||[])[itemIndex];if(!it)return;
  $('#bsm-site-stock-popup').remove();
  // Show only other sites with positive available stock. The summary is based
  // on the same positive rows, so it cannot include hidden zero/negative sites.
  const sites=(it.other_site_stock_breakdown||[]).filter(s=>Number(s.available_qty)>0);
  let rows=sites.map(s=>`<tr><td>${esc(s.site_name||'')}</td><td class="num">${Number(s.available_qty).toLocaleString('en-IN',{maximumFractionDigits:2})}</td></tr>`).join('');
  if(!rows)rows='<tr><td colspan="2" class="bsm-empty">No stock available at other sites.</td></tr>';
  const total=sites.reduce((sum,s)=>sum+Number(s.available_qty),0).toLocaleString('en-IN',{maximumFractionDigits:2});
  const html=`<div id="bsm-site-stock-popup" class="bsm-site-stock-popup" role="dialog" aria-modal="true" aria-label="Site stock breakdown"><div class="bsm-site-stock-popup-panel"><div class="bsm-site-stock-popup-head"><strong>Site Stock — ${esc(it.item_name||'Item')}</strong><button class="bsm-site-stock-close" type="button" aria-label="Close">×</button></div><div class="bsm-site-stock-popup-body"><div class="bsm-table-wrap"><table><thead><tr><th>Site Name</th><th class="num">Available Stock</th></tr></thead><tbody>${rows}</tbody><tfoot><tr><th>Total (Other Sites)</th><th class="num">${total}</th></tr></tfoot></table></div></div></div></div>`;
  $('body').append(html);
  $('#bsm-site-stock-popup .bsm-site-stock-close').trigger('focus');
}
$(document).on('click','.bsm-site-stock-details',function(e){e.preventDefault();e.stopPropagation();showApprovalSiteStockDetails(Number($(this).attr('data-item-index')));});
$(document).on('click','#bsm-site-stock-popup .bsm-site-stock-close',function(){$('#bsm-site-stock-popup').remove();});
$(document).on('click','#bsm-site-stock-popup',function(e){if(e.target===this)$(this).remove();});
$(document).on('keydown',function(e){if(e.key==='Escape'&&$('#bsm-site-stock-popup').length){e.stopPropagation();$('#bsm-site-stock-popup').remove();}});

async function saveApproval(){
  const items=[];let invalid=false;$('#bsm-approval-lines tr').each(function(){const rq=Number($(this).data('requested')||0),$inp=$(this).find('.bsm-approved-qty'),aq=Number($inp.val()||0);if(aq<0||aq>rq){$inp.addClass('bsm-invalid');invalid=true;}items.push({id:$(this).data('id'),approved_qty:aq});});
  if(invalid){showToast('Approved Qty cannot exceed Requested Qty',true);return;}
  const decidedId=App.editId;
  const r=await ajax('bsm_requisition_approval_save',{id:decidedId,items:JSON.stringify(items)});
  if(r.success){
    showToast(r.data.message);
    $('#bsm-modal').hide();
    // A decided requisition must disappear from Pending Approval immediately.
    App.pendingApprovalRows=(App.pendingApprovalRows||[]).filter(x=>String(x.id)!==String(decidedId));
    const $row=$(`#bsm-pending-approval .bsm-review-req[data-id="${decidedId}"]`).closest('tr');
    $row.remove();
    if(!$('#bsm-pending-approval tbody tr').length){
      $('#bsm-pending-approval tbody').html('<tr><td colspan="9" class="bsm-empty">No requisitions pending approval.</td></tr>');
    }
    delete App.options.requisitions;
    // Continue the requisition workflow to procurement shortage review.
    await openView('pending_po_items');
  }else showToast(r.data?.message||'Unable to save approval',true);
}

function simpleItemDetailsTable(headers, rows){
  let h='<div class="bsm-purchase-item-details"><div class="bsm-purchase-detail-wrap"><table class="bsm-purchase-detail-table"><thead><tr>'+headers.map(x=>`<th class="${x.num?'num':''}">${esc(x.label)}</th>`).join('')+'</tr></thead><tbody>';
  rows.forEach(r=>{h+='<tr>'+headers.map(x=>`<td class="${x.num?'num':''}">${x.render?x.render(r):esc(r[x.key]??'')}</td>`).join('')+'</tr>';});
  if(!rows.length)h+=`<tr><td colspan="${headers.length}" class="bsm-empty">No item data found.</td></tr>`;
  return h+'</tbody></table></div></div>';
}
async function togglePendingDispatchDetails($row){
  const id=Number($row.data('id')||0);if(!id)return;
  const $existing=$row.next('.bsm-pending-dispatch-details-row');
  if($existing.length){const open=$existing.is(':visible');$existing.toggle(!open);$row.attr('aria-expanded',String(!open)).toggleClass('is-expanded',!open);return;}
  const $detail=$(`<tr class="bsm-pending-dispatch-details-row"><td colspan="${$row.children('td').length}"><div class="bsm-loading">Loading items…</div></td></tr>`);$row.after($detail).attr('aria-expanded','true').addClass('is-expanded');
  const r=await ajax('bsm_dispatch_prefill',{id});if(!r.success){$detail.find('td').html('<div class="bsm-empty">Unable to load items.</div>');return;}
  const headers=[{label:'Item',render:x=>esc(labelFor('items',x.item_id)||'')},{label:'Approved Qty',num:true,render:x=>Number(x.approved_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})},{label:'Already Dispatched',num:true,render:x=>Number(x.dispatched_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})},{label:'Pending Qty',num:true,render:x=>Number(x.pending_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})},{label:'Unit',render:x=>esc(labelFor('units',x.unit_id)||'')}];
  $detail.find('td').html(simpleItemDetailsTable(headers,r.data?._items||[]));
}
async function togglePendingSiteReceiptDetails($row){
  const id=Number($row.data('id')||0);if(!id)return;
  const $existing=$row.next('.bsm-pending-site-receipt-details-row');
  if($existing.length){const open=$existing.is(':visible');$existing.toggle(!open);$row.attr('aria-expanded',String(!open)).toggleClass('is-expanded',!open);return;}
  const $detail=$(`<tr class="bsm-pending-site-receipt-details-row"><td colspan="${$row.children('td').length}"><div class="bsm-loading">Loading items…</div></td></tr>`);$row.after($detail).attr('aria-expanded','true').addClass('is-expanded');
  const r=await ajax('bsm_site_receipt_prefill',{id});if(!r.success){$detail.find('td').html('<div class="bsm-empty">Unable to load items.</div>');return;}
  const headers=[{label:'Item',render:x=>esc(labelFor('items',x.item_id)||'')},{label:'Dispatch Qty',num:true,render:x=>Number(x.dispatch_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})},{label:'Unit',render:x=>esc(labelFor('units',x.unit_id)||'')},{label:'Remarks',render:x=>esc(x.remarks||'')}];
  $detail.find('td').html(simpleItemDetailsTable(headers,r.data?._items||[]));
}
async function toggleSiteReceiptDetails($row){
  const id=Number($row.data('id')||0);if(!id)return;
  const $existing=$row.next('.bsm-site-receipt-details-row');if($existing.length){const open=$existing.is(':visible');$existing.toggle(!open);$row.attr('aria-expanded',String(!open)).toggleClass('is-expanded',!open);return;}
  const $detail=$(`<tr class="bsm-site-receipt-details-row"><td colspan="${$row.children('td').length}"><div class="bsm-loading">Loading items…</div></td></tr>`);$row.after($detail).attr('aria-expanded','true').addClass('is-expanded');
  const r=await ajax('bsm_get',{module:'site_receipts',id});if(!r.success){$detail.find('td').html('<div class="bsm-empty">Unable to load items.</div>');return;}
  const headers=[{label:'Item',render:x=>esc(labelFor('items',x.item_id)||'')},{label:'Dispatch Qty',num:true,render:x=>Number(x.dispatch_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})},{label:'Received Qty',num:true,render:x=>Number(x.received_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})},{label:'Damaged Qty',num:true,render:x=>Number(x.damaged_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})},{label:'Short Qty',num:true,render:x=>Number(x.short_qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})},{label:'Unit',render:x=>esc(labelFor('units',x.unit_id)||'')}];
  $detail.find('td').html(simpleItemDetailsTable(headers,r.data?._items||[]));
}
async function toggleConsumptionDetails($row){
  const id=Number($row.data('id')||0);if(!id)return;
  const $existing=$row.next('.bsm-consumption-details-row');if($existing.length){const open=$existing.is(':visible');$existing.toggle(!open);$row.attr('aria-expanded',String(!open)).toggleClass('is-expanded',!open);return;}
  const $detail=$(`<tr class="bsm-consumption-details-row"><td colspan="${$row.children('td').length}"><div class="bsm-loading">Loading items…</div></td></tr>`);$row.after($detail).attr('aria-expanded','true').addClass('is-expanded');
  const r=await ajax('bsm_get',{module:'site_consumptions',id});if(!r.success){$detail.find('td').html('<div class="bsm-empty">Unable to load items.</div>');return;}
  const headers=[{label:'Item',render:x=>esc(labelFor('items',x.item_id)||'')},{label:'Qty',num:true,render:x=>Number(x.qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})},{label:'Unit',render:x=>esc(labelFor('units',x.unit_id)||'')},{label:'Remarks',render:x=>esc(x.remarks||'')}];
  $detail.find('td').html(simpleItemDetailsTable(headers,r.data?._items||[]));
}
function drawPendingDispatch(){const rows=filterVisibleRows(App.pendingDispatchRows||[],['requisition_no','site_name',x=>dateFmt(x.required_date),'approved_qty','dispatched_qty','pending_qty'],App.specialSearch||'');let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Req. No.</th><th>Site</th><th>Required Date</th><th class="num">Approved Qty</th><th class="num">Dispatched Qty</th><th class="num">Pending Qty</th><th>Action</th></tr></thead><tbody>';rows.forEach((x,i)=>h+=`<tr class="bsm-pending-dispatch-row" data-id="${x.id}" tabindex="0" aria-expanded="false" title="Click to view pending dispatch items"><td>${i+1}</td><td>${esc(x.requisition_no)}</td><td>${esc(x.site_name)}</td><td>${dateFmt(x.required_date)}</td><td class="num">${Number(x.approved_qty||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2})}</td><td class="num">${Number(x.dispatched_qty||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2})}</td><td class="num">${Number(x.pending_qty||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2})}</td><td><button class="bsm-btn bsm-btn-small bsm-btn-dispatch bsm-dispatch-req" data-id="${x.id}">Dispatch</button></td></tr>`);if(!rows.length)h+='<tr><td colspan="9" class="bsm-empty">No approved material pending dispatch.</td></tr>';h+='</tbody></table></div>';$('#bsm-pending-dispatch').html(h);}
async function renderPendingDispatch(){
  setTitle('Pending Dispatch');App.specialSearch='';$('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-special-search" class="bsm-search" aria-label="Search" placeholder=""><div class="bsm-spacer"></div>${excelButton('bsm-pending-dispatch-excel')}</div><div id="bsm-pending-dispatch" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  const r=await ajax('bsm_pending_dispatch');if(!r.success)return;App.pendingDispatchRows=r.data.rows||[];drawPendingDispatch();
}
async function openDispatchFromRequisition(id){
  await ensureOptions(['warehouses','sites','requisitions','items','units']);const r=await ajax('bsm_dispatch_prefill',{id});if(!r.success)return showToast(r.data?.message||'Unable to load',true);const x=r.data,cfg=App.configs.material_dispatches;
  App.current='material_dispatches';App.editId=0;$('#bsm-modal-title').text('Add Material Dispatch');
  const row={dispatch_from:'warehouse',warehouse_id:'',site_id:x.site_id,requisition_id:x.id,dispatch_date:BSM.today};$('#bsm-form-fields').removeClass('bsm-approval-summary bsm-requisition-summary').addClass('bsm-dispatch-summary').html(cfg.fields.map(f=>fieldHtml(f,row[f.name]??defaultValue(f))).join(''));
  $('#bsm-form-fields select[name="site_id"],#bsm-form-fields select[name="requisition_id"]').prop('disabled',true);renderItems(cfg,(x._items||[]).map(it=>({requisition_item_id:it.id,item_id:it.item_id,approved_qty:it.approved_qty,already_dispatched:it.dispatched_qty,pending_qty:it.pending_qty,qty:it.pending_qty,unit_id:it.unit_id,rate:''})));$('#bsm-lines-body select[name="item_id"],#bsm-lines-body select[name="unit_id"]').prop('disabled',true);
  // Dispatch is created only from the approved requisition items: rows cannot be added or removed here.
  $('#bsm-lines-body .bsm-add-line-inline').remove();
  $('#bsm-line-items thead th:last-child').remove();
  $('#bsm-lines-body tr').each(function(){$(this).find('td:last-child').remove();});
  $('#bsm-documents').html('<div class="bsm-docbox"><label><span class="bsm-field-label">Upload Photo</span><input type="file" id="bsm-dispatch-photo" accept="image/jpeg,image/png,image/webp"></label></div>');positionDispatchPhoto();$('#bsm-form').data('module','material_dispatches').data('requisition-id',id);setupDispatchSource();$('#bsm-modal').show();
}
let dispatchStockRequest=0;
function setupDispatchSource(){
  const $f=$('#bsm-form-fields'),source=$f.find('[name="dispatch_from"]').val()||'warehouse';
  $f.find('[name="warehouse_id"]').closest('label').toggle(source==='warehouse');
  $f.find('[name="from_site_id"]').closest('label').toggle(source==='site');
  $f.find('[name="warehouse_id"]').prop('required',source==='warehouse');
  const $from=$f.find('[name="from_site_id"]');$from.prop('required',source==='site');
  $from.find('option').prop('disabled',false).filter(function(){return this.value && this.value===$f.find('[name="site_id"]').val();}).prop('disabled',true);
  if($from.val()===$f.find('[name="site_id"]').val())$from.val('');
  if($from[0])bsmRefreshSearchableSelect($from[0]);
  refreshDispatchStock();
}
async function refreshDispatchStock(){
  const token=++dispatchStockRequest,$f=$('#bsm-form-fields'),source=$f.find('[name="dispatch_from"]').val()||'warehouse',location=$f.find(source==='site'?'[name="from_site_id"]':'[name="warehouse_id"]').val();
  const $rows=$('#bsm-lines-body .bsm-line');
  $rows.find('[name="available_qty"]').val('');$rows.find('[name="qty"]').attr('max','0');
  if(!location)return;
  try{
    const r=await ajax('bsm_dispatch_source_stock',{dispatch_from:source,location_id:location,dispatch_id:App.editId||0,item_ids:$rows.map(function(){return $(this).find('[name="item_id"]').val();}).get()});
    if(token!==dispatchStockRequest||$('#bsm-form').data('module')!=='material_dispatches')return;
    if(!r.success){showToast(r.data?.message||'Unable to load stock',true);return;}
    $rows.each(function(){const $r=$(this),stock=r.data.rows[$r.find('[name="item_id"]').val()],available=Math.max(0,Number(stock?.available_qty||0)),pending=$r.find('[name="pending_qty"]').val();$r.find('[name="available_qty"]').val(available.toFixed(2));$r.find('[name="qty"]').attr('min','0').attr('max',pending===''?available:Math.min(available,Number(pending||0)));});
  }catch(e){if(token===dispatchStockRequest)showToast('Unable to load source stock',true);}
}
$(document).on('change','#bsm-form-fields [name="dispatch_from"],#bsm-form-fields [name="from_site_id"],#bsm-form-fields [name="warehouse_id"],#bsm-form-fields [name="site_id"]',function(){if($('#bsm-form').data('module')==='material_dispatches')setupDispatchSource();});
$(document).on('change','#bsm-lines-body [name="item_id"]',function(){if($('#bsm-form').data('module')==='material_dispatches')refreshDispatchStock();});

function exportSpecialRows(title,headers,rows){downloadExcelFile(title,headers,rows);}


function optionLabel(type,id){
  const x=(App.options[type]||[]).find(o=>String(o.id)===String(id));
  return x?x.label:'';
}
function returnItemDetailsHtml(items){
  const rows=items||[];
  const num=v=>Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
  let h='<div class="bsm-purchase-item-details"><div class="bsm-purchase-detail-wrap"><table class="bsm-purchase-detail-table"><thead><tr><th>Item</th><th class="num">Qty</th><th>Unit</th><th>Remarks</th><th class="bsm-consumption-document-head">Document</th></tr></thead><tbody>';
  rows.forEach(it=>{h+=`<tr><td>${esc(labelFor('items',it.item_id)||'')}</td><td class="num">${num(it.qty)}</td><td>${esc(labelFor('units',it.unit_id)||'')}</td><td>${esc(it.remarks||'')}</td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="4" class="bsm-empty">No item data found.</td></tr>';
  h+='</tbody></table></div></div>';
  return h;
}
async function toggleMaterialReturnDetails($row,detailClass='bsm-material-return-details-row'){
  const id=Number($row.data('id')||0);if(!id)return;
  const $existing=$row.next('.'+detailClass);
  if($existing.length){const isOpen=$existing.is(':visible');$existing.toggle(!isOpen);$row.attr('aria-expanded',String(!isOpen)).toggleClass('is-expanded',!isOpen);return;}
  const colspan=$row.children('td').length;
  const $detail=$(`<tr class="${detailClass}"><td colspan="${colspan}"><div class="bsm-loading">Loading items…</div></td></tr>`);
  $row.after($detail).attr('aria-expanded','true').addClass('is-expanded');
  const r=await ajax('bsm_get',{module:'material_returns',id});
  if(!r.success){$detail.find('td').html('<div class="bsm-empty">Unable to load return items.</div>');return;}
  $detail.find('td').html(returnItemDetailsHtml(r.data?._items||[]));
}

function drawPendingMaterialReceipt(){
  const q=String(App.specialSearch||'').toLowerCase().trim();
  const rows=(App.pendingMaterialReceiptRows||[]).filter(x=>!q||[x.return_no,dateFmt(x.return_date),optionLabel('sites',x.site_id),optionLabel('warehouses',x.warehouse_id),optionLabel('users',x.return_by)].join(' ').toLowerCase().includes(q));
  let h='<div class="bsm-table-wrap"><table id="bsm-pending-material-receipt-table"><thead><tr><th>Sl</th><th>Return No.</th><th>Date</th><th>Site</th><th>Warehouse</th><th>Return By</th><th>Action</th></tr></thead><tbody>';
  rows.forEach((x,i)=>h+=`<tr class="bsm-pending-return-row" data-id="${x.id}" tabindex="0" aria-expanded="false" title="Click to view return items"><td>${i+1}</td><td>${esc(x.return_no||'')}</td><td>${dateFmt(x.return_date)}</td><td>${esc(optionLabel('sites',x.site_id))}</td><td>${esc(optionLabel('warehouses',x.warehouse_id))}</td><td>${esc(optionLabel('users',x.return_by))}</td><td><button class="bsm-btn bsm-btn-small bsm-btn-primary bsm-receive-return" data-id="${x.id}">Receive</button></td></tr>`);
  if(!rows.length)h+='<tr><td colspan="7" class="bsm-empty">No returns pending receipt.</td></tr>';
  h+='</tbody></table></div>';$('#bsm-pending-material-receipt').html(h);
}
async function renderPendingMaterialReceipt(){
  setTitle('Pending Site Return Approval');App.specialSearch='';
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-special-search" class="bsm-search" aria-label="Search" placeholder=""><div class="bsm-spacer"></div>${excelButton('bsm-pending-material-receipt-excel')}</div><div id="bsm-pending-material-receipt" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  const r=await ajax('bsm_pending_material_returns',{search:''});
  App.pendingMaterialReceiptRows=r.success?(r.data.rows||[]):[];drawPendingMaterialReceipt();
}
async function openReturnReceipt(returnId){
  await ensureOptions(optionTypesForView('material_inwards'));await openForm('material_inwards',0);
  const $type=$('#bsm-form-fields select[name="inward_type"]');$type.val('return');bsmRefreshSearchableSelect($type[0]);
  const $ret=$('#bsm-form-fields select[name="return_id"]');$ret.val(String(returnId));bsmRefreshSearchableSelect($ret[0]);
  await fillMaterialInwardFromReturn(returnId);
}
function drawPendingSiteReceipt(){
  const q=String(App.specialSearch||'').toLowerCase().trim();
  const rows=(App.pendingSiteReceiptRows||[]).filter(x=>!q||[x.dispatch_no,dateFmt(x.dispatch_date),optionLabel('sites',x.site_id),optionLabel('warehouses',x.warehouse_id)].join(' ').toLowerCase().includes(q));
  let h='<div class="bsm-table-wrap"><table id="bsm-pending-site-receipt-table"><thead><tr><th>Sl</th><th>Dispatch No.</th><th>Date</th><th>Site</th><th>Warehouse</th><th>Action</th></tr></thead><tbody>';
  rows.forEach((x,i)=>h+=`<tr class="bsm-pending-site-receipt-row" data-id="${x.id}" tabindex="0" aria-expanded="false" title="Click to view dispatch items"><td>${i+1}</td><td>${esc(x.dispatch_no||'')}</td><td>${dateFmt(x.dispatch_date)}</td><td>${esc(optionLabel('sites',x.site_id))}</td><td>${esc(optionLabel('warehouses',x.warehouse_id))}</td><td><button class="bsm-btn bsm-btn-small bsm-btn-success bsm-receive-dispatch" data-id="${x.id}">Receive</button></td></tr>`);
  if(!rows.length)h+='<tr><td colspan="6" class="bsm-empty">No dispatches pending receipt.</td></tr>';
  h+='</tbody></table></div>';$('#bsm-pending-site-receipt').html(h);
}
async function renderPendingSiteReceipt(){
  setTitle('Ordered but Not Received');App.specialSearch='';
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-special-search" class="bsm-search" aria-label="Search" placeholder=""><div class="bsm-spacer"></div>${excelButton('bsm-pending-site-receipt-excel')}</div><div id="bsm-pending-site-receipt" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  // Pending means: dispatched, but there is NO Site Receipt record against that dispatch.
  // We deliberately check the receipt table instead of trusting dispatch.status so legacy
  // dispatches received before the status-sync logic was introduced are also excluded.
  const [d,r]=await Promise.all([
    ajax('bsm_list',{module:'material_dispatches',page:1,search:'',status:'dispatched',export_all:'1',visible_columns:'[]'}),
    ajax('bsm_list',{module:'site_receipts',page:1,search:'',export_all:'1',visible_columns:'[]'})
  ]);
  const receivedDispatchIds=new Set((r.success?(r.data.rows||[]):[]).map(x=>String(x.dispatch_id||'')).filter(Boolean));
  App.pendingSiteReceiptRows=(d.success?(d.data.rows||[]):[]).filter(x=>!receivedDispatchIds.has(String(x.id)));
  drawPendingSiteReceipt();
}
async function openSiteReceiptFromDispatch(dispatchId){
  await ensureOptions(optionTypesForView('site_receipts'));await openForm('site_receipts',0);
  const $d=$('#bsm-form-fields select[name="dispatch_id"]');
  $d.val(String(dispatchId)).prop('disabled',true).trigger('change');
  bsmRefreshSearchableSelect($d[0]);
  // The Dispatch is fixed when the receipt is opened from Pending Receipt.
  // Keep the value in the form while preventing the user from changing it.
  const $wrap=$d.next('.bsm-searchable-select');
  $wrap.find('.bsm-select-search').prop('readonly',true).attr('tabindex','-1');
  $wrap.addClass('bsm-readonly-select');
  await fillSiteReceiptFromDispatch(dispatchId);
}


function siteReceiptItemsFilterItemOptions(){
  const categoryId=String($('#site_receipt_items_category').val()||'');
  const current=String($('#site_receipt_items_item').val()||'');
  const items=(App.options.items||[]).filter(x=>!categoryId || String(x.category_id||'')===categoryId);
  const $sel=$('#site_receipt_items_item');if(!$sel.length)return;
  $sel.html('<option value="">Select</option>'+items.map(x=>`<option value="${esc(x.id)}">${esc(x.label)}</option>`).join(''));
  if(items.some(x=>String(x.id)===current))$sel.val(current);else $sel.val('');
  bsmRefreshSearchableSelect($sel[0]);
}
function siteReceiptItemsSyncCategoryFromItem(){
  const itemId=String($('#site_receipt_items_item').val()||'');if(!itemId)return;
  const item=(App.options.items||[]).find(x=>String(x.id)===itemId);
  if(item&&item.category_id){$('#site_receipt_items_category').val(String(item.category_id));bsmRefreshSearchableSelect($('#site_receipt_items_category')[0]);}
}
async function renderSiteMaterialItemReceipt(){
  setTitle('Material Receipts Item Master');
  const filters=`<div id="bsm-site-receipt-items-filters" class="bsm-filters" style="display:none">
    <label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="site_receipt_items_date_from"></label>
    <label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="site_receipt_items_date_to"></label>
    ${selectHtml('site_receipt_items_site','Site','sites','')}
    ${selectHtml('site_receipt_items_category','Item Category','categories','')}
    ${selectHtml('site_receipt_items_item','Item','items','')}
    <button class="bsm-icon-btn bsm-purchase-clear-btn" id="site_receipt_items_clear_filters" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button>
  </div>`;
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-site-receipt-items-search" class="bsm-search" aria-label="Search Material Receipts Item Master" placeholder=""><button id="bsm-site-receipt-items-filter-toggle" class="bsm-icon-btn" title="Filters">☷</button><div class="bsm-spacer"></div>${excelButton('bsm-site-receipt-items-export')}</div>${filters}<div id="bsm-site-receipt-items-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  siteReceiptItemsFilterItemOptions();
  await loadSiteMaterialItemReceipt();
}
async function loadSiteMaterialItemReceipt(exportAll=false){
  const r=await ajax('bsm_site_material_item_receipt',{
    page:exportAll?1:App.page,export_all:exportAll?'1':'',search:$('#bsm-site-receipt-items-search').val()||'',
    date_from:dateSql($('#site_receipt_items_date_from').val()||''),date_to:dateSql($('#site_receipt_items_date_to').val()||''),
    site_id:$('#site_receipt_items_site').val()||'',category_id:$('#site_receipt_items_category').val()||'',item_id:$('#site_receipt_items_item').val()||''
  });
  if(!r.success){if(!exportAll)$('#bsm-site-receipt-items-list').html('<div class="bsm-empty">Unable to load Material Receipts Item Master.</div>');return null;}
  if(exportAll)return r.data.rows||[];
  const rows=r.data.rows||[];const num=v=>Number(v||0).toLocaleString('en-IN',{maximumFractionDigits:2});
  let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Receipt</th><th>Date</th><th>Dispatch</th><th>Site</th><th>Item Category</th><th>Item</th><th class="num">Dispatch Qty</th><th class="num">Received Qty</th><th class="num">Damaged Qty</th><th class="num">Short Qty</th><th>Unit</th><th>Received By</th></tr></thead><tbody>';
  rows.forEach((x,i)=>{h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.receipt_no||'')}</td><td>${dateFmt(x.receipt_date)}</td><td>${esc(x.dispatch_no||'')}</td><td>${esc(x.site_name||'')}</td><td>${esc(x.category_name||'')}</td><td>${esc(x.item_name||'')}</td><td class="num">${num(x.dispatch_qty)}</td><td class="num">${num(x.received_qty)}</td><td class="num">${num(x.damaged_qty)}</td><td class="num">${num(x.short_qty)}</td><td>${esc(x.unit_name||'')}</td><td>${esc(x.received_by_name||'')}</td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="13" class="bsm-empty">No site material receipt item records found.</td></tr>';
  h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-site-receipt-items-list').html(h);return rows;
}
async function exportSiteMaterialItemReceipt(){
  const rows=await loadSiteMaterialItemReceipt(true);if(!rows)return;
  const headers=['Sl','Receipt','Date','Dispatch','Site','Item Category','Item','Dispatch Qty','Received Qty','Damaged Qty','Short Qty','Unit','Received By'];
  const data=rows.map((x,i)=>[i+1,x.receipt_no||'',dateFmt(x.receipt_date),x.dispatch_no||'',x.site_name||'',x.category_name||'',x.item_name||'',x.dispatch_qty||0,x.received_qty||0,x.damaged_qty||0,x.short_qty||0,x.unit_name||'',x.received_by_name||'']);
  downloadExcelFile('Material Receipts Item Master',headers,data);
}


function consumptionItemsFilterItemOptions(){
  const categoryId=String($('#consumption_items_category').val()||'');
  const current=String($('#consumption_items_item').val()||'');
  const items=(App.options.items||[]).filter(x=>!categoryId || String(x.category_id||'')===categoryId);
  const $sel=$('#consumption_items_item');if(!$sel.length)return;
  $sel.html('<option value="">Select</option>'+items.map(x=>`<option value="${esc(x.id)}">${esc(x.label)}</option>`).join(''));
  if(items.some(x=>String(x.id)===current))$sel.val(current);else $sel.val('');
  bsmRefreshSearchableSelect($sel[0]);
}
function consumptionItemsSyncCategoryFromItem(){
  const itemId=String($('#consumption_items_item').val()||'');if(!itemId)return;
  const item=(App.options.items||[]).find(x=>String(x.id)===itemId);
  if(item&&item.category_id){$('#consumption_items_category').val(String(item.category_id));bsmRefreshSearchableSelect($('#consumption_items_category')[0]);}
}
async function renderConsumptionItemsRegister(){
  setTitle('Consumption Items Master');
  const filters=`<div id="bsm-consumption-items-filters" class="bsm-filters" style="display:none">
    <label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="consumption_items_date_from"></label>
    <label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="consumption_items_date_to"></label>
    ${selectHtml('consumption_items_site','Site','sites','')}
    ${selectHtml('consumption_items_category','Item Category','categories','')}
    ${selectHtml('consumption_items_item','Item','items','')}
    <button class="bsm-icon-btn bsm-purchase-clear-btn" id="consumption_items_clear_filters" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button>
  </div>`;
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-consumption-items-search" class="bsm-search" aria-label="Search Consumption Items" placeholder=""><button id="bsm-consumption-items-filter-toggle" class="bsm-icon-btn" title="Filters">☷</button><div class="bsm-spacer"></div>${excelButton('bsm-consumption-items-export')}</div>${filters}<div id="bsm-consumption-items-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  consumptionItemsFilterItemOptions();
  await loadConsumptionItemsRegister();
}
async function loadConsumptionItemsRegister(exportAll=false){
  const r=await ajax('bsm_consumption_items_register',{
    page:exportAll?1:App.page,export_all:exportAll?'1':'',search:$('#bsm-consumption-items-search').val()||'',
    date_from:dateSql($('#consumption_items_date_from').val()||''),date_to:dateSql($('#consumption_items_date_to').val()||''),
    site_id:$('#consumption_items_site').val()||'',category_id:$('#consumption_items_category').val()||'',item_id:$('#consumption_items_item').val()||''
  });
  if(!r.success){if(!exportAll)$('#bsm-consumption-items-list').html('<div class="bsm-empty">Unable to load Consumption Items Master.</div>');return null;}
  if(exportAll)return r.data.rows||[];
  const rows=r.data.rows||[];const num=v=>Number(v||0).toLocaleString('en-IN',{maximumFractionDigits:2});
  let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Consumption</th><th>Date</th><th>Site</th><th>Consumption Entry By</th><th>Item Category</th><th>Item</th><th class="num">Qty</th><th>Unit</th><th>Remarks</th><th>Document</th></tr></thead><tbody>';
  rows.forEach((x,i)=>{h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.consumption_no||'')}</td><td>${dateFmt(x.consumption_date)}</td><td>${esc(x.site_name||'')}</td><td>${esc(x.created_by_name||'-')}</td><td>${esc(x.category_name||'')}</td><td>${esc(x.item_name||'')}</td><td class="num">${num(x.qty)}</td><td>${esc(x.unit_name||'')}</td><td>${esc(x.remarks||'')}</td><td>${x.document_url?`<a class="bsm-document-link" href="${esc(x.document_url)}" target="_blank" title="${esc(x.document_name||'View Document')}">View</a>`:'-'}</td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="11" class="bsm-empty">No consumption item records found.</td></tr>';
  h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-consumption-items-list').html(h);return rows;
}
async function exportConsumptionItemsRegister(){
  const rows=await loadConsumptionItemsRegister(true);if(!rows)return;
  const headers=['Sl','Consumption','Date','Site','Consumption Entry By','Item Category','Item','Qty','Unit','Remarks','Document'];
  const data=rows.map((x,i)=>[i+1,x.consumption_no||'',dateFmt(x.consumption_date),x.site_name||'',x.created_by_name||'',x.category_name||'',x.item_name||'',x.qty||0,x.unit_name||'',x.remarks||'',x.document_url||'']);
  downloadExcelFile('Consumption Items Master',headers,data);
}


function materialReturnsItemsFilterItemOptions(){
  const categoryId=String($('#material_returns_items_category').val()||'');
  const current=String($('#material_returns_items_item').val()||'');
  const items=(App.options.items||[]).filter(x=>!categoryId || String(x.category_id||'')===categoryId);
  const $sel=$('#material_returns_items_item');if(!$sel.length)return;
  $sel.html('<option value="">Select</option>'+items.map(x=>`<option value="${esc(x.id)}">${esc(x.label)}</option>`).join(''));
  if(items.some(x=>String(x.id)===current))$sel.val(current);else $sel.val('');
  bsmRefreshSearchableSelect($sel[0]);
}
function materialReturnsItemsSyncCategoryFromItem(){
  const itemId=String($('#material_returns_items_item').val()||'');if(!itemId)return;
  const item=(App.options.items||[]).find(x=>String(x.id)===itemId);
  if(item&&item.category_id){$('#material_returns_items_category').val(String(item.category_id));bsmRefreshSearchableSelect($('#material_returns_items_category')[0]);}
}

function returnReceiptItemFilterOptions(){
  const categoryId=String($('#return_receipt_item_category').val()||'');
  const current=String($('#return_receipt_item_item').val()||'');
  const items=(App.options.items||[]).filter(x=>!categoryId || String(x.category_id||'')===categoryId);
  const $sel=$('#return_receipt_item_item');if(!$sel.length)return;
  $sel.html('<option value="">Select</option>'+items.map(x=>`<option value="${esc(x.id)}">${esc(x.label)}</option>`).join(''));
  if(items.some(x=>String(x.id)===current))$sel.val(current);else $sel.val('');
  bsmRefreshSearchableSelect($sel[0]);
}
function returnReceiptItemSyncCategory(){
  const iid=String($('#return_receipt_item_item').val()||'');if(!iid)return;
  const item=(App.options.items||[]).find(x=>String(x.id)===iid);
  if(item&&item.category_id){$('#return_receipt_item_category').val(String(item.category_id));bsmRefreshSearchableSelect($('#return_receipt_item_category')[0]);}
}

async function renderReturnReceiptMaster(){
  setTitle('Return Receipt Master');
  const filters=`<div id="bsm-return-receipt-filters" class="bsm-filters" style="display:none">
    <label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="return_receipt_date_from"></label>
    <label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="return_receipt_date_to"></label>
    ${selectHtml('return_receipt_site','Site','sites','')}
    ${selectHtml('return_receipt_warehouse','Warehouse','warehouses','')}
    <button class="bsm-icon-btn bsm-purchase-clear-btn" id="return_receipt_clear_filters" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button>
  </div>`;
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-return-receipt-search" class="bsm-search" aria-label="Search Return Receipt Master" placeholder=""><button id="bsm-return-receipt-filter-toggle" class="bsm-icon-btn" title="Filters">☷</button><div class="bsm-spacer"></div>${excelButton('bsm-return-receipt-export')}</div>${filters}<div id="bsm-return-receipt-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  await loadReturnReceiptMaster();
}
async function loadReturnReceiptMaster(exportAll=false){
  const r=await ajax('bsm_return_receipt_master',{
    page:exportAll?1:App.page,export_all:exportAll?'1':'',search:$('#bsm-return-receipt-search').val()||'',
    date_from:dateSql($('#return_receipt_date_from').val()||''),date_to:dateSql($('#return_receipt_date_to').val()||''),
    site_id:$('#return_receipt_site').val()||'',warehouse_id:$('#return_receipt_warehouse').val()||''
  });
  if(!r.success){if(!exportAll)$('#bsm-return-receipt-list').html('<div class="bsm-empty">Unable to load Return Receipt Master.</div>');return null;}
  if(exportAll)return r.data.rows||[];
  const rows=r.data.rows||[];const num=v=>Number(v||0).toLocaleString('en-IN',{maximumFractionDigits:2});
  let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Receipt No.</th><th>Date</th><th>Return No.</th><th>Site</th><th>Warehouse</th><th>Received By</th><th class="num">Received Qty</th><th>Status</th></tr></thead><tbody>';
  rows.forEach((x,i)=>{h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.inward_no||'')}</td><td>${dateFmt(x.inward_date)}</td><td>${esc(x.return_no||'')}</td><td>${esc(x.site_name||'')}</td><td>${esc(x.warehouse_name||'')}</td><td>${esc(x.received_by_name||'-')}</td><td class="num">${num(x.received_qty)}</td><td><span class="bsm-pill">${esc(String(x.status||'').replaceAll('_',' '))}</span></td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="9" class="bsm-empty">No return receipt records found.</td></tr>';
  h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-return-receipt-list').html(h);return rows;
}
async function exportReturnReceiptMaster(){
  const rows=await loadReturnReceiptMaster(true);if(!rows)return;
  const headers=['Sl','Receipt No.','Date','Return No.','Site','Warehouse','Received By','Received Qty','Status'];
  const data=rows.map((x,i)=>[i+1,x.inward_no||'',dateFmt(x.inward_date),x.return_no||'',x.site_name||'',x.warehouse_name||'',x.received_by_name||'',x.received_qty||0,String(x.status||'').replaceAll('_',' ')]);
  downloadExcelFile('Return Receipt Master',headers,data);
}

async function renderReturnReceiptItemMaster(){
  setTitle('Return Receipt Item Master');
  const filters=`<div id="bsm-return-receipt-item-filters" class="bsm-filters" style="display:none">
    <label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="return_receipt_item_date_from"></label>
    <label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="return_receipt_item_date_to"></label>
    ${selectHtml('return_receipt_item_site','Site','sites','')}
    ${selectHtml('return_receipt_item_warehouse','Warehouse','warehouses','')}
    ${selectHtml('return_receipt_item_category','Item Category','categories','')}
    ${selectHtml('return_receipt_item_item','Item','items','')}
    <button class="bsm-icon-btn bsm-purchase-clear-btn" id="return_receipt_item_clear_filters" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button>
  </div>`;
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-return-receipt-item-search" class="bsm-search" aria-label="Search Return Receipt Item Master" placeholder=""><button id="bsm-return-receipt-item-filter-toggle" class="bsm-icon-btn" title="Filters">☷</button><div class="bsm-spacer"></div>${excelButton('bsm-return-receipt-item-export')}</div>${filters}<div id="bsm-return-receipt-item-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  returnReceiptItemFilterOptions();
  await loadReturnReceiptItemMaster();
}
async function loadReturnReceiptItemMaster(exportAll=false){
  const r=await ajax('bsm_return_receipt_item_master',{
    page:exportAll?1:App.page,export_all:exportAll?'1':'',search:$('#bsm-return-receipt-item-search').val()||'',
    date_from:dateSql($('#return_receipt_item_date_from').val()||''),date_to:dateSql($('#return_receipt_item_date_to').val()||''),
    site_id:$('#return_receipt_item_site').val()||'',warehouse_id:$('#return_receipt_item_warehouse').val()||'',
    category_id:$('#return_receipt_item_category').val()||'',item_id:$('#return_receipt_item_item').val()||''
  });
  if(!r.success){if(!exportAll)$('#bsm-return-receipt-item-list').html('<div class="bsm-empty">Unable to load Return Receipt Item Master.</div>');return null;}
  if(exportAll)return r.data.rows||[];
  const rows=r.data.rows||[];const num=v=>Number(v||0).toLocaleString('en-IN',{maximumFractionDigits:2});
  let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Receipt No.</th><th>Date</th><th>Return No.</th><th>Site</th><th>Warehouse</th><th>Received By</th><th>Item Category</th><th>Item</th><th class="num">Return Qty</th><th class="num">Received Qty</th><th>Unit</th></tr></thead><tbody>';
  rows.forEach((x,i)=>{h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.inward_no||'')}</td><td>${dateFmt(x.inward_date)}</td><td>${esc(x.return_no||'')}</td><td>${esc(x.site_name||'')}</td><td>${esc(x.warehouse_name||'')}</td><td>${esc(x.received_by_name||'-')}</td><td>${esc(x.category_name||'')}</td><td>${esc(x.item_name||'')}</td><td class="num">${num(x.return_qty)}</td><td class="num">${num(x.received_qty)}</td><td>${esc(x.unit_name||'')}</td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="12" class="bsm-empty">No return receipt item records found.</td></tr>';
  h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-return-receipt-item-list').html(h);return rows;
}
async function exportReturnReceiptItemMaster(){
  const rows=await loadReturnReceiptItemMaster(true);if(!rows)return;
  const headers=['Sl','Receipt No.','Date','Return No.','Site','Warehouse','Received By','Item Category','Item','Return Qty','Received Qty','Unit'];
  const data=rows.map((x,i)=>[i+1,x.inward_no||'',dateFmt(x.inward_date),x.return_no||'',x.site_name||'',x.warehouse_name||'',x.received_by_name||'',x.category_name||'',x.item_name||'',x.return_qty||0,x.received_qty||0,x.unit_name||'']);
  downloadExcelFile('Return Receipt Item Master',headers,data);
}

async function renderMaterialReturnsItemMaster(){
  setTitle('Material Returns Item Master');
  const filters=`<div id="bsm-material-returns-items-filters" class="bsm-filters" style="display:none">
    <label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="material_returns_items_date_from"></label>
    <label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="material_returns_items_date_to"></label>
    ${selectHtml('material_returns_items_site','Site','sites','')}
    ${selectHtml('material_returns_items_category','Item Category','categories','')}
    ${selectHtml('material_returns_items_item','Item','items','')}
    <button class="bsm-icon-btn bsm-purchase-clear-btn" id="material_returns_items_clear_filters" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button>
  </div>`;
  $('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-material-returns-items-search" class="bsm-search" aria-label="Search Material Returns Items" placeholder=""><button id="bsm-material-returns-items-filter-toggle" class="bsm-icon-btn" title="Filters">☷</button><div class="bsm-spacer"></div>${excelButton('bsm-material-returns-items-export')}</div>${filters}<div id="bsm-material-returns-items-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  materialReturnsItemsFilterItemOptions();
  await loadMaterialReturnsItemMaster();
}
async function loadMaterialReturnsItemMaster(exportAll=false){
  const r=await ajax('bsm_material_returns_item_master',{
    page:exportAll?1:App.page,export_all:exportAll?'1':'',search:$('#bsm-material-returns-items-search').val()||'',
    date_from:dateSql($('#material_returns_items_date_from').val()||''),date_to:dateSql($('#material_returns_items_date_to').val()||''),
    site_id:$('#material_returns_items_site').val()||'',category_id:$('#material_returns_items_category').val()||'',item_id:$('#material_returns_items_item').val()||''
  });
  if(!r.success){if(!exportAll)$('#bsm-material-returns-items-list').html('<div class="bsm-empty">Unable to load Material Returns Item Master.</div>');return null;}
  if(exportAll)return r.data.rows||[];
  const rows=r.data.rows||[];const num=v=>Number(v||0).toLocaleString('en-IN',{maximumFractionDigits:2});
  let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Return</th><th>Date</th><th>Site</th><th>Warehouse</th><th>Return By</th><th>Received By</th><th>Item Category</th><th>Item</th><th class="num">Qty</th><th>Unit</th><th>Remarks</th><th>Status</th></tr></thead><tbody>';
  rows.forEach((x,i)=>{h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.return_no||'')}</td><td>${dateFmt(x.return_date)}</td><td>${esc(x.site_name||'')}</td><td>${esc(x.warehouse_name||'')}</td><td>${esc(x.return_by_name||'-')}</td><td>${esc(x.received_by_name||'-')}</td><td>${esc(x.category_name||'')}</td><td>${esc(x.item_name||'')}</td><td class="num">${num(x.qty)}</td><td>${esc(x.unit_name||'')}</td><td>${esc(x.remarks||'')}</td><td><span class="bsm-pill">${esc(String(x.status||'').replaceAll('_',' '))}</span></td></tr>`;});
  if(!rows.length)h+='<tr><td colspan="13" class="bsm-empty">No material return item records found.</td></tr>';
  h+='</tbody></table></div>';
  if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;
  $('#bsm-material-returns-items-list').html(h);return rows;
}
async function exportMaterialReturnsItemMaster(){
  const rows=await loadMaterialReturnsItemMaster(true);if(!rows)return;
  const headers=['Sl','Return','Date','Site','Warehouse','Return By','Received By','Item Category','Item','Qty','Unit','Remarks','Status'];
  const data=rows.map((x,i)=>[i+1,x.return_no||'',dateFmt(x.return_date),x.site_name||'',x.warehouse_name||'',x.return_by_name||'',x.received_by_name||'',x.category_name||'',x.item_name||'',x.qty||0,x.unit_name||'',x.remarks||'',String(x.status||'').replaceAll('_',' ')]);
  downloadExcelFile('Material Returns Item Master',headers,data);
}


function inventoryCols(scope){
  return scope==='ho' ? [
    {key:'category_name',label:'Item Category'},{key:'item_name',label:'Item'},{key:'unit',label:'Unit'},{key:'opening',label:'Opening',num:true},{key:'inward',label:'Inward',num:true},{key:'dispatch',label:'Outward',num:true},{key:'balance',label:'Balance',num:true}
  ] : [
    {key:'category_name',label:'Item Category'},{key:'item_name',label:'Item'},{key:'unit',label:'Unit'},{key:'opening',label:'Opening',num:true},{key:'receipt',label:'Received',num:true},{key:'consumption',label:'Consumed',num:true},{key:'return_qty',label:'Returned',num:true},{key:'site_transfer',label:'Site Transfer',num:true},{key:'balance',label:'Balance',num:true}
  ];
}
function inventoryGroupedHtml(rows,scope){
  const cols=inventoryCols(scope),groups={};
  rows.forEach(x=>{const type=String(x.location_type||'').toLowerCase();if(scope==='ho'&&type!=='warehouse')return;if(scope==='site'&&type!=='site')return;const name=x.location||'Unknown';(groups[name]||(groups[name]=[])).push(x);});
  const names=Object.keys(groups).sort((a,b)=>a.localeCompare(b));
  if(!names.length)return '<div class="bsm-empty">No inventory found.</div>';
  return names.map(name=>{let h=`<div class="bsm-inventory-group"><h3>${esc(name)}</h3><div class="bsm-table-wrap"><table><thead><tr>${cols.map(c=>`<th class="${c.num?'num':''}">${esc(c.label)}</th>`).join('')}</tr></thead><tbody>`;groups[name].forEach(x=>{h+='<tr>'+cols.map(c=>`<td class="${c.num?'num':''}">${c.num?Number(x[c.key]||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}):esc(x[c.key]||'')}</td>`).join('')+'</tr>';});return h+'</tbody></table></div></div>';}).join('');
}

function inventoryFilterItemOptions(){
  const categoryId=String($('#inv_category').val()||'');
  const current=String($('#inv_item').val()||'');
  const items=(App.options.items||[]).filter(x=>!categoryId || String(x.category_id||'')===categoryId);
  const $sel=$('#inv_item');if(!$sel.length)return;
  $sel.html('<option value="">Select</option>'+items.map(x=>`<option value="${esc(x.id)}">${esc(x.label)}</option>`).join(''));
  if(items.some(x=>String(x.id)===current))$sel.val(current);else $sel.val('');
  bsmRefreshSearchableSelect($sel[0]);
}
function inventorySyncCategoryFromItem(){
  const itemId=String($('#inv_item').val()||'');if(!itemId)return;
  const item=(App.options.items||[]).find(x=>String(x.id)===itemId);
  if(item&&item.category_id){$('#inv_category').val(String(item.category_id));bsmRefreshSearchableSelect($('#inv_category')[0]);}
}
async function renderInventoryView(scope){
  const isHo=scope==='ho';setTitle(isHo?'HO Inventory':'Site Inventory');
  const filters=`<div class="bsm-filters report bsm-stock-filters"><label>Search<input type="text" id="inv_search" aria-label="Search" placeholder=""></label>${isHo?selectHtml('inv_warehouse','Warehouse','warehouses',''):selectHtml('inv_site','Site','sites','')}${selectHtml('inv_category','Item Category','categories','')}${selectHtml('inv_item','Item','items','')}<button id="inv_clear" class="bsm-icon-btn bsm-purchase-clear-btn" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button>${excelButton('inv_export')}</div>`;
  $('#bsm-content').html(filters+'<div id="bsm-inventory-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>');
  App.inventoryScope=scope;inventoryFilterItemOptions();await loadInventoryView();
}
async function loadInventoryView(){
  const scope=App.inventoryScope||'ho';const r=await ajax('bsm_report',{report:'material_stock',site_id:$('#inv_site').val()||'',warehouse_id:$('#inv_warehouse').val()||'',category_id:$('#inv_category').val()||'',item_id:$('#inv_item').val()||''});
  if(!r.success)return showToast(r.data?.message||'Unable to load inventory',true);
  const q=String($('#inv_search').val()||'').toLowerCase().trim();let rows=r.data.rows||[];if(q)rows=rows.filter(x=>[x.location,x.category_name,x.item_name,x.unit].join(' ').toLowerCase().includes(q));App.inventoryRows=rows;$('#bsm-inventory-list').html(inventoryGroupedHtml(rows,scope));
}
function exportInventoryView(){
  const scope=App.inventoryScope||'ho',cols=inventoryCols(scope),headers=[scope==='ho'?'Warehouse':'Site',...cols.map(c=>c.label)],rows=[];
  (App.inventoryRows||[]).forEach(x=>{const type=String(x.location_type||'').toLowerCase();if(scope==='ho'&&type!=='warehouse')return;if(scope==='site'&&type!=='site')return;rows.push([x.location||'',...cols.map(c=>c.num?Number(x[c.key]||0):x[c.key]||'')]);});
  downloadExcelFile(scope==='ho'?'HO Inventory':'Site Inventory',headers,rows);
}
function outwardGroupedHtml(rows){
  const groups={};rows.forEach(x=>{const name=x.site_name||'Unknown Site';(groups[name]||(groups[name]=[])).push(x);});const names=Object.keys(groups).sort((a,b)=>a.localeCompare(b));if(!names.length)return '<div class="bsm-empty">No outward found for selected date.</div>';
  return names.map(name=>{let h=`<div class="bsm-inventory-group"><h3>${esc(name)}</h3><div class="bsm-table-wrap"><table><thead><tr><th>Dispatch</th><th>Date</th><th>Item</th><th class="num">Qty</th><th>Unit</th></tr></thead><tbody>`;groups[name].forEach(x=>h+=`<tr><td>${esc(x.dispatch_no||'')}</td><td>${dateFmt(x.dispatch_date)}</td><td>${esc(x.item_name||'')}</td><td class="num">${Number(x.qty||0).toLocaleString('en-IN',{maximumFractionDigits:2})}</td><td>${esc(x.unit_name||'')}</td></tr>`);return h+'</tbody></table></div></div>';}).join('');
}
async function renderDailyOutwardSummary(){
  setTitle('Daily Outward Summary');const today=String(BSM.today||'');
  $('#bsm-content').html(`<div class="bsm-filters report"><label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="outward_date_from" value="${esc(today)}"></label><label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="outward_date_to" value="${esc(today)}"></label>${selectHtml('outward_site','Site','sites','')}${selectHtml('outward_item','Item','items','')}<label>Search<input type="text" id="outward_search" aria-label="Search" placeholder=""></label><button id="outward_clear" class="bsm-icon-btn bsm-purchase-clear-btn" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button>${excelButton('outward_export')}</div><div id="bsm-outward-summary" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);await loadDailyOutwardSummary();
}
async function loadDailyOutwardSummary(){
  const r=await ajax('bsm_daily_outward_summary',{date_from:dateSql($('#outward_date_from').val()||''),date_to:dateSql($('#outward_date_to').val()||''),site_id:$('#outward_site').val()||'',item_id:$('#outward_item').val()||'',search:$('#outward_search').val()||''});if(!r.success)return showToast(r.data?.message||'Unable to load outward summary',true);App.outwardRows=r.data.rows||[];$('#bsm-outward-summary').html(outwardGroupedHtml(App.outwardRows));
}
function exportDailyOutwardSummary(){downloadExcelFile('Daily Outward Summary',['Site','Dispatch','Date','Item','Qty','Unit'],(App.outwardRows||[]).map(x=>[x.site_name||'',x.dispatch_no||'',dateFmt(x.dispatch_date),x.item_name||'',x.qty||0,x.unit_name||'']));}

function expenseSummaryGroupedHtml(rows){
  const groups={};
  rows.forEach(x=>{const name=x.site_name||'Unknown Site';(groups[name]||(groups[name]=[])).push(x);});
  const names=Object.keys(groups).sort((a,b)=>a.localeCompare(b));
  if(!names.length)return '<div class="bsm-empty">No site expenses found for selected date.</div>';
  return names.map(name=>{
    const total=groups[name].reduce((sum,x)=>sum+Number(x.amount||0),0);
    let h=`<div class="bsm-inventory-group"><div class="bsm-panel-head"><h3>${esc(name)}</h3><strong>Total: ${moneyFmt(total)}</strong></div><div class="bsm-table-wrap"><table><thead><tr><th>Expense</th><th>Date</th><th>Expense Category</th><th>Supplier / Paid To</th><th>Particulars</th><th>Payment Mode</th><th class="num">Amount</th></tr></thead><tbody>`;
    groups[name].forEach(x=>{const party=x.vendor_name||x.paid_to||'';h+=`<tr><td>${esc(x.expense_no||'')}</td><td>${dateFmt(x.expense_date)}</td><td>${esc(x.category_name||'')}</td><td>${esc(party)}</td><td>${esc(x.particulars||'')}</td><td>${esc(x.payment_mode||'')}</td><td class="num">${moneyFmt(x.amount)}</td></tr>`;});
    return h+'</tbody></table></div></div>';
  }).join('');
}
async function renderDailyExpenseSummary(){
  setTitle('Daily Expense Summary');const today=String(BSM.today||'');
  $('#bsm-content').html(`<div class="bsm-filters report"><label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="expense_summary_date_from" value="${esc(today)}"></label><label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="expense_summary_date_to" value="${esc(today)}"></label>${selectHtml('expense_summary_site','Site','sites','')}${selectHtml('expense_summary_category','Expense Category','expense_categories','')}${selectHtml('expense_summary_vendor','Supplier','vendors','')}<label>Search<input type="text" id="expense_summary_search" aria-label="Search" placeholder=""></label><button id="expense_summary_clear" class="bsm-icon-btn bsm-purchase-clear-btn" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button>${excelButton('expense_summary_export')}</div><div id="bsm-expense-summary" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);
  await loadDailyExpenseSummary();
}
async function loadDailyExpenseSummary(){
  const r=await ajax('bsm_daily_expense_summary',{date_from:dateSql($('#expense_summary_date_from').val()||''),date_to:dateSql($('#expense_summary_date_to').val()||''),site_id:$('#expense_summary_site').val()||'',category_id:$('#expense_summary_category').val()||'',vendor_id:$('#expense_summary_vendor').val()||'',search:$('#expense_summary_search').val()||''});
  if(!r.success)return showToast(r.data?.message||'Unable to load expense summary',true);
  App.expenseSummaryRows=r.data.rows||[];
  $('#bsm-expense-summary').html(expenseSummaryGroupedHtml(App.expenseSummaryRows));
}
function exportDailyExpenseSummary(){
  downloadExcelFile('Daily Expense Summary',['Site','Expense','Date','Expense Category','Supplier / Paid To','Particulars','Payment Mode','Amount'],(App.expenseSummaryRows||[]).map(x=>[x.site_name||'',x.expense_no||'',dateFmt(x.expense_date),x.category_name||'',x.vendor_name||x.paid_to||'',x.particulars||'',x.payment_mode||'',x.amount||0]));
}

async function renderPendingSiteReceiveChallans(){setTitle('Pending Site Receive Challans');$('#bsm-content').html(`<div class="bsm-toolbar"><input id="bsm-pending-challan-search" class="bsm-search" aria-label="Search Pending Site Receive Challans" placeholder=""><div class="bsm-spacer"></div></div><div id="bsm-pending-challan-list" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`);await loadPendingSiteReceiveChallans();}
async function loadPendingSiteReceiveChallans(){
  const r=await ajax('bsm_pending_site_receive_challans',{page:App.page,search:$('#bsm-pending-challan-search').val()||''});if(!r.success){$('#bsm-pending-challan-list').html('<div class="bsm-empty">Unable to load pending challans.</div>');return;}const rows=r.data.rows||[];let h='<div class="bsm-table-wrap"><table><thead><tr><th>Sl</th><th>Receipt</th><th>Date</th><th>Dispatch</th><th>Site</th><th>Upload Challan</th></tr></thead><tbody>';rows.forEach((x,i)=>h+=`<tr><td>${(r.data.page-1)*20+i+1}</td><td>${esc(x.receipt_no||'')}</td><td>${dateFmt(x.receipt_date)}</td><td>${esc(x.dispatch_no||'')}</td><td>${esc(x.site_name||'')}</td><td><div class="bsm-pending-invoice-upload"><input type="file" class="bsm-pending-receive-challan-file" accept="application/pdf,image/*"><button type="button" class="bsm-btn bsm-btn-primary bsm-upload-pending-receive-challan" data-id="${x.id}">Upload</button></div></td></tr>`);if(!rows.length)h+='<tr><td colspan="6" class="bsm-empty">No pending site receive challans.</td></tr>';h+='</tbody></table></div>';if(r.data.pages>1)h+=`<div class="bsm-pagination"><button class="bsm-btn page-prev" ${r.data.page<=1?'disabled':''}>Previous</button><span>Page ${r.data.page} of ${r.data.pages}</span><button class="bsm-btn page-next" ${r.data.page>=r.data.pages?'disabled':''}>Next</button></div>`;$('#bsm-pending-challan-list').html(h);
}

function materialStockColumns(){return [
  {key:'location',label:'Site / Warehouse'},
  {key:'item_name',label:'Item'},
  {key:'unit',label:'Unit'},
  {key:'opening',label:'Opening',type:'number'},
  {key:'inward',label:'Inward',type:'number'},
  {key:'receipt',label:'Receipt',type:'number'},
  {key:'dispatch',label:'Dispatch',type:'number'},
  {key:'consumption',label:'Consumption',type:'number'},
  {key:'return_qty',label:'Return',type:'number'},
  {key:'site_transfer',label:'Site Transfer',type:'number'},
  {key:'balance',label:'Balance',type:'number'}
];}
function drawMaterialStock(){
  const cols=materialStockColumns(),q=$('#ms_search').val()||'';
  const rows=filterVisibleRows(App.materialStockRows||[],cols.map(c=>x=>x[c.key]),q);
  let h='<div class="bsm-table-wrap"><table id="bsm-material-stock-table"><thead><tr>'+cols.map(c=>`<th class="${c.type==='number'?'num':''}">${esc(c.label)}</th>`).join('')+'</tr></thead><tbody>';
  rows.forEach(x=>{h+='<tr>'+cols.map(c=>`<td class="${c.type==='number'?'num':''}">${c.type==='number'?Number(x[c.key]||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}):esc(x[c.key]??'')}</td>`).join('')+'</tr>';});
  if(!rows.length)h+=`<tr><td colspan="${cols.length}" class="bsm-empty">No stock movement found.</td></tr>`;
  h+='</tbody></table></div>';$('#bsm-material-stock').html(h);
}
async function loadMaterialStock(){
  const r=await ajax('bsm_report',{report:'material_stock',site_id:$('#ms_site').val()||'',warehouse_id:$('#ms_warehouse').val()||'',item_id:$('#ms_item').val()||'',date_from:dateSql($('#ms_date_from').val()||''),date_to:dateSql($('#ms_date_to').val()||'')});
  if(!r.success)return showToast(r.data?.message||'Unable to load stock',true);
  App.materialStockRows=r.data.rows||[];drawMaterialStock();
}
async function renderMaterialStock(){
  setTitle('Stock');
  const f=`<div class="bsm-filters report bsm-stock-filters"><label>Search<input type="text" id="ms_search" aria-label="Search" placeholder=""></label>${selectHtml('ms_site','Site','sites','')}${selectHtml('ms_warehouse','Warehouse','warehouses','')}${selectHtml('ms_item','Item','items','')}<label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="ms_date_from"></label><label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="ms_date_to"></label><button id="ms_clear" class="bsm-btn bsm-clear">Clear</button>${excelButton('ms_export')}</div><div id="bsm-material-stock" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>`;
  $('#bsm-content').html(f);await loadMaterialStock();
}
function exportMaterialStock(){
  const cols=materialStockColumns(),headers=cols.map(c=>c.label),rows=[];
  $('#bsm-material-stock-table tbody tr').each(function(){if($(this).find('.bsm-empty').length)return;rows.push($(this).find('td').map(function(){return $(this).text();}).get());});
  downloadExcelFile('Stock',headers,rows);
}

function clearIcon(){return `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>`;}
async function loadContractorPaymentSummary(){
  const $box=$('#bsm-contractor-payment-summary');
  if(!$box.length)return;
  const contractorId=$('#bsm-form-fields select[name="contractor_id"]').val()||'';
  const siteId=$('#bsm-form-fields select[name="site_id"]').val()||'';
  if(!contractorId || !siteId){$box.hide().empty();return;}
  $box.show().html('<div class="bsm-loading">Loading...</div>');
  try{
    const r=await ajax('bsm_report',{report:'contractor_outstanding',site_id:siteId,contractor_id:contractorId});
    if(!r.success){$box.hide().empty();return;}
    const row=(r.data?.rows||[]).find(x=>String(x.contractor_id)===String(contractorId)&&String(x.site_id)===String(siteId)) || {bills:0,payments:0,outstanding:0};
    $box.html(`<div class="bsm-table-wrap"><table class="bsm-contractor-payment-summary-table"><thead><tr><th class="num">Total Bills</th><th class="num">Payments</th><th class="num">Outstanding</th></tr></thead><tbody><tr><td class="num">${moneyFmt(row.bills)}</td><td class="num">${moneyFmt(row.payments)}</td><td class="num">${moneyFmt(row.outstanding)}</td></tr></tbody></table></div>`);
  }catch(e){$box.hide().empty();}
}

async function renderReport(key){
  const cfg=reportConfigs[key];
  if(key==='contractor_advance_register') await ensureOptions(['sites','contractors','banks']);
  if(key==='report_contractor_outstanding') await ensureOptions(['sites','contractors']);
  setTitle(cfg.title);let f=`<div class="bsm-filters report${key==='report_contractor_outstanding'?' bsm-contractor-filters':''}"><label>Search<input type="text" id="r_search" aria-label="Search" placeholder=""></label>`;if(cfg.filters.includes('date'))f+=`<label>Date From<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="r_date_from"></label><label>Date To<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="r_date_to"></label>`;if(cfg.filters.includes('site'))f+=selectHtml('r_site','Site','sites','');if(cfg.filters.includes('contractor'))f+=selectHtml('r_contractor','Contractor','contractors','');
  if(key==='report_labour'||key==='report_contractor_outstanding') f+=`<button id="bsm-clear-report" class="bsm-icon-btn bsm-clear-report-btn" title="Clear Filters" aria-label="Clear Filters">${clearIcon()}</button>${excelButton('bsm-report-export-excel')}`;
  else f+=excelButton('bsm-report-export-excel');
  if(key==='report_contractor_outstanding')f+='<div id="bsm-contractor-filter-totals" class="bsm-contractor-outstanding-totals" aria-live="polite" aria-label="Filtered Contractor Outstanding Totals"></div>';
  f+='</div><div id="bsm-report" class="bsm-panel"><div class="bsm-loading">Loading…</div></div>';$('#bsm-content').html(f);
  if(key==='report_labour'){const today=String(BSM.today||'');$('#r_date_from,#r_date_to').val(today);}
  await loadReport(key);
}
function drawReportRows(key){
  const cfg=reportConfigs[key],q=$('#r_search').val()||'';
  const rows=filterVisibleRows(App.reportRows||[],cfg.columns.map(c=>x=>plainCell(x,c)),q);
  if(key==='report_contractor_outstanding'){
    let h='<div class="bsm-table-wrap"><table id="bsm-report-table"><thead><tr>'+cfg.columns.map(c=>`<th class="${c.type==='money'||c.type==='number'||c.type==='percent'?'num':''}">${esc(c.label)}</th>`).join('')+'<th>Action</th></tr></thead><tbody>';
    rows.forEach(x=>{h+='<tr>'+cfg.columns.map(c=>`<td class="${c.type==='money'||c.type==='number'||c.type==='percent'?'num':''}">${renderCell(x,c)}</td>`).join('')+`${Number(x.outstanding||0)>0?`<td><button type="button" class="bsm-btn bsm-btn-primary bsm-btn-small bsm-contractor-outstanding-payment" data-contractor-id="${esc(x.contractor_id||'')}" data-site-id="${esc(x.site_id||'')}" data-outstanding="${Number(x.outstanding||0).toFixed(2)}">Pay</button></td>`:`<td>-</td>`}</tr>`;});
    if(!rows.length)h+=`<tr><td colspan="${cfg.columns.length+1}" class="bsm-empty">No data.</td></tr>`;
    // Summarize all report rows matching the active Site/Contractor/Search filters.
    // The source array is already limited by server-side Site and Contractor filtering.
    const totals=rows.reduce((sum,row)=>{
      sum.bills+=Number(row.bills)||0;
      sum.payments+=Number(row.payments)||0;
      sum.outstanding+=Number(row.outstanding)||0;
      return sum;
    },{bills:0,payments:0,outstanding:0});
    const summary=`<div class="bsm-contractor-outstanding-totals" aria-label="Filtered Contractor Outstanding Totals">
      <div class="bsm-outstanding-total"><span>Total Bills</span><strong>${moneyFmt(totals.bills)}</strong></div>
      <div class="bsm-outstanding-total"><span>Total Payments</span><strong>${moneyFmt(totals.payments)}</strong></div>
      <div class="bsm-outstanding-total"><span>Total Outstanding</span><strong>${moneyFmt(totals.outstanding)}</strong></div>
    </div>`;
    h+='</tbody></table></div>';$('#bsm-contractor-filter-totals').html(summary);$('#bsm-report').html(h);return;
  }
  if(key==='contractor_advance_register'){
    let h='<div class="bsm-table-wrap"><table id="bsm-report-table"><thead><tr>'+cfg.columns.map(c=>`<th class="${c.type==='money'||c.type==='number'||c.type==='percent'?'num':''}">${esc(c.label)}</th>`).join('')+'<th>Action</th></tr></thead><tbody>';
    rows.forEach(x=>{h+='<tr>'+cfg.columns.map(c=>`<td class="${c.type==='money'||c.type==='number'||c.type==='percent'?'num':''}">${renderCell(x,c)}</td>`).join('')+`<td class="bsm-actions"><button type="button" class="bsm-action-edit bsm-contractor-payment-edit" data-id="${esc(x.id||'')}" title="Edit">✎</button><button type="button" class="bsm-action-delete bsm-contractor-payment-delete" data-id="${esc(x.id||'')}" title="Delete"><img class="bsm-delete-icon" src="${BSM.deleteIcon}" alt="Delete"></button></td></tr>`;});
    if(!rows.length)h+=`<tr><td colspan="${cfg.columns.length+1}" class="bsm-empty">No data.</td></tr>`;
    h+='</tbody></table></div>';$('#bsm-report').html(h);return;
  }
  if(key==='pending_contractor_payment'){
    let h='<div class="bsm-table-wrap"><table id="bsm-report-table" class="bsm-contractor-payment-table"><thead><tr>'+cfg.columns.map(c=>`<th class="${c.type==='money'||c.type==='number'||c.type==='percent'?'num':''}">${esc(c.label)}</th>`).join('')+'<th>Advance Adjustment</th><th>Paid Amount</th><th>Update</th></tr></thead><tbody>';
    rows.forEach(x=>{
      const balance=Number(x.pending_amount||0),availableAdvance=Number(x.available_advance||0);
      const maxAdvance=Math.max(0,Math.min(balance,availableAdvance));
      h+='<tr data-id="'+esc(x.id)+'">'+cfg.columns.map(c=>`<td class="${c.type==='money'||c.type==='number'||c.type==='percent'?'num':''}">${renderCell(x,c)}</td>`).join('')+
        `<td class="num"><input type="number" step="0.01" min="0" max="${maxAdvance.toFixed(2)}" class="bsm-inline-money-input bsm-new-advance-adjustment-input" data-id="${esc(x.id)}" placeholder="0.00" title="Available Advance: ${availableAdvance.toFixed(2)}"></td>`+
        `<td class="num"><input type="number" step="0.01" min="0" max="${balance.toFixed(2)}" class="bsm-inline-money-input bsm-new-paid-amount-input" data-id="${esc(x.id)}" placeholder="0.00"></td>`+
        `<td><button type="button" class="bsm-btn bsm-btn-primary bsm-btn-small bsm-update-paid-amount" data-id="${esc(x.id)}">Update</button></td></tr>`;
    });
    if(!rows.length)h+=`<tr><td colspan="${cfg.columns.length+3}" class="bsm-empty">No data.</td></tr>`;
    h+='</tbody></table></div>';
    $('#bsm-report').html(h);
    return;
  }
  let h='<div class="bsm-table-wrap"><table id="bsm-report-table"><thead><tr>'+cfg.columns.map(c=>`<th class="${c.type==='money'||c.type==='number'||c.type==='percent'?'num':''}">${esc(c.label)}</th>`).join('')+'</tr></thead><tbody>';
  rows.forEach(x=>h+='<tr>'+cfg.columns.map(c=>`<td class="${c.type==='money'||c.type==='number'||c.type==='percent'?'num':''}">${renderCell(x,c)}</td>`).join('')+'</tr>');
  if(!rows.length)h+=`<tr><td colspan="${cfg.columns.length}" class="bsm-empty">No data.</td></tr>`;
  h+='</tbody></table></div>';
  $('#bsm-report').html(h);
}
async function loadReport(key){const cfg=reportConfigs[key];const r=await ajax('bsm_report',{report:cfg.report,site_id:$('#r_site').val()||'',contractor_id:$('#r_contractor').val()||'',date_from:dateSql($('#r_date_from').val()||''),date_to:dateSql($('#r_date_to').val()||'')});if(!r.success)return;App.reportRows=r.data.rows||[];drawReportRows(key);}
function exportReportExcel(){const headers=$('#bsm-report-table thead th').map(function(){return $(this).text();}).get();const rows=[];$('#bsm-report-table tbody tr').each(function(){if($(this).find('.bsm-empty').length)return;rows.push($(this).find('td').map(function(){return $(this).text();}).get());});downloadExcelFile(reportConfigs[App.current]?.title||'Report',headers,rows);}


window.openContractorOutstandingPayment = async function(contractorId,siteId,outstanding){
  App.current='contractor_advance';App.page=1;App.search='';
  $('.bsm-nav-item').removeClass('active');$('.bsm-nav-item[data-view="contractor_advance"]').addClass('active');
  await openDirectForm('contractor_advance','contractor_advances','Contractor Payment');
  const $contractor=$('#bsm-form-fields select[name="contractor_id"]');
  if($contractor.length){
    $contractor.val(String(contractorId||'')).trigger('change');
    bsmRefreshSearchableSelect($contractor[0]);
  }
  const $site=$('#bsm-form-fields select[name="site_id"]');
  if($site.length){
    $site.val(String(siteId||'')).trigger('change');
    bsmRefreshSearchableSelect($site[0]);
  }
  const $amount=$('#bsm-form-fields input[name="amount"]');
  if($amount.length&&Number(outstanding||0)>0)$amount.val(Number(outstanding).toFixed(2));
  const $date=$('#bsm-form-fields input[name="payment_date"]');
  if($date.length&&!$date.val())$date.val(BSM.today||'');
  window.scrollTo({top:0,behavior:'smooth'});
};


// v0.1.31 - searchable dropdowns (no search icon)
function bsmSelectOptions($select){
  return $select.find('option').map(function(){
    return {value:String($(this).attr('value') ?? ''), label:$(this).text(), disabled:$(this).prop('disabled')};
  }).get();
}
function bsmRefreshSearchableSelect(select){
  const $select=$(select);
  const $wrap=$select.next('.bsm-searchable-select');
  if(!$wrap.length) return;
  const opts=bsmSelectOptions($select);
  const selected=opts.find(o=>o.value===String($select.val() ?? ''));
  const $input=$wrap.find('.bsm-select-search');
  if(!$wrap.hasClass('open')) $input.val(selected && selected.value!=='' ? selected.label : '');
  const isSalesUnit=$select.attr('name')==='unit_id' && $select.closest('#bsm-form.bsm-quotation-style').length>0;
  $input.attr('placeholder',isSalesUnit?'':'Select');
  $input.prop('disabled',$select.prop('disabled'));
  $wrap.toggleClass('is-disabled',$select.prop('disabled'));
  bsmRenderSearchableOptions($select,$wrap,$input.val(),false);
}
function bsmRenderSearchableOptions($select,$wrap,query='',filter=true){
  const q=String(query||'').toLowerCase().trim();
  const opts=bsmSelectOptions($select).filter(o=>o.value!=='' && !o.disabled);
  const rows=filter && q ? opts.filter(o=>o.label.toLowerCase().includes(q)) : opts;
  const current=String($select.val() ?? '');
  const $menu=$wrap.find('.bsm-select-menu');
  if(!rows.length){$menu.html('<div class="bsm-select-empty">No matching records found</div>');return;}
  $menu.html(rows.map(o=>`<button type="button" class="bsm-select-option ${o.value===current?'selected':''}" data-value="${esc(o.value)}">${esc(o.label)}</button>`).join(''));
}
function bsmCloseSearchable($wrap,restore=true){
  if(!$wrap || !$wrap.length) return;
  $wrap.removeClass('open drop-up invoice-item-open bsm-item-dropdown-open bsm-po-vendor-dropdown-open');
  $wrap.find('.bsm-select-menu').css({position:'',top:'',bottom:'',left:'',right:'',width:'',minWidth:'',maxWidth:'',maxHeight:''});
  $wrap.find('.bsm-select-arrow').text('⌄');
  if(restore){
    const $select=$wrap.prev('select');
    const selected=$select.find('option:selected');
    $wrap.find('.bsm-select-search').val($select.val() ? selected.text() : '');
  }
}
function bsmOpenSearchable($wrap){
  const $select=$wrap.prev('select');
  if($wrap.hasClass('is-disabled') || $select.prop('disabled')) return;
  $('.bsm-searchable-select.open').not($wrap).each(function(){bsmCloseSearchable($(this));});
  $wrap.removeClass('drop-up').addClass('open');
  $wrap.find('.bsm-select-arrow').text('⌃');
  const $input=$wrap.find('.bsm-select-search');
  $input.val('');
  bsmRenderSearchableOptions($select,$wrap,'',false);
  const rect=$wrap[0]?.getBoundingClientRect();
  const $menu=$wrap.find('.bsm-select-menu');
  // v450: Shared item selectors use the same fully visible, searchable dropdown.
  // Position relative to the viewport to avoid clipping in horizontally scrolling tables.
  const itemSelect=$select.is('select[name="item_id"],select.po-direct-item,select[name="item"]') ||
    ($select.is('select') && /(?:^|[_-])item(?:[_-]id)?$/.test(String($select.attr('id')||'')));
  // v456: PO Supplier menus must escape the horizontally scrolling PO table.
  // Keep the original select in place so PO creation still receives its Supplier ID.
  const poVendorSelect=$select.is('select.po-row-vendor,select.po-quote-supplier,select#po_direct_vendor');
  // BOQ selectors have class-based names and must escape the form/table scroll area.
  if($select.closest('#bsm-boq-form').length && rect && $menu.length){
    const vw=document.documentElement.clientWidth||window.innerWidth;
    const vh=document.documentElement.clientHeight||window.innerHeight;
    const width=Math.min(Math.max(rect.width,220),Math.max(0,vw-16));
    const left=Math.max(8,Math.min(rect.left,vw-width-8));
    const below=Math.max(0,vh-rect.bottom-12);
    const above=Math.max(0,rect.top-12);
    const dropUp=below<150 && above>below;
    const height=Math.min(260,dropUp?above:below);
    const top=dropUp?Math.max(8,rect.top-height-4):rect.bottom+4;
    $wrap.addClass('bsm-item-dropdown-open').removeClass('drop-up');
    $menu.css({position:'fixed',left:left+'px',right:'auto',top:top+'px',bottom:'auto',width:width+'px',maxHeight:height+'px'});
    return;
  }
  if((poVendorSelect || $select.closest('#ops-time-form,.bsm-time-filters').length) && rect && $menu.length){
    const vw=document.documentElement.clientWidth||window.innerWidth;
    const vh=document.documentElement.clientHeight||window.innerHeight;
    const width=Math.min(Math.max(300,rect.width),Math.max(160,vw-16));
    const left=Math.max(8,Math.min(rect.left,vw-width-8));
    const below=vh-rect.bottom-8;
    const above=rect.top-8;
    const dropUp=below<150 && above>below;
    const height=Math.max(80,Math.min(250,(dropUp?above:below)-8));
    const top=dropUp?Math.max(8,rect.top-height-4):rect.bottom+4;
    $wrap.addClass('bsm-po-vendor-dropdown-open').toggleClass('drop-up',dropUp);
    // Override legacy Pending PO !important widths, which otherwise push the menu off-screen.
    const menuStyle=$menu[0].style;
    Object.entries({position:'fixed',top:top+'px',bottom:'auto',left:left+'px',right:'auto',width:width+'px','min-width':'0','max-width':width+'px','max-height':height+'px'}).forEach(([key,value])=>menuStyle.setProperty(key,value,'important'));
    return;
  }
  if(itemSelect && rect && $menu.length){
    const viewportWidth=document.documentElement.clientWidth||window.innerWidth;
    const viewportHeight=document.documentElement.clientHeight||window.innerHeight;
    const width=Math.min(Math.max(rect.width,260),Math.max(180,viewportWidth-16));
    const left=Math.max(8,Math.min(rect.left,viewportWidth-width-8));
    const below=viewportHeight-rect.bottom-10;
    const top=rect.bottom+4;
    $wrap.addClass('bsm-item-dropdown-open').removeClass('drop-up');
    $menu.css({left:left+'px',right:'auto',top:top+'px',bottom:'auto',width:width+'px',maxHeight:Math.max(64,Math.min(250,below-4))+'px'});
    return;
  }
  if(rect && $menu.length){
    const needed=Math.min(220,Math.max(120,$menu[0].scrollHeight||0));
    const below=window.innerHeight-rect.bottom-12;
    const above=rect.top-12;
    const purchaseLine=$wrap.closest('.bsm-purchase-lines').length>0;
    const quotationLine=$wrap.closest('.bsm-quotation-lines').length>0;
    const purchaseItem=purchaseLine && $select.attr('name')==='item_id';
    // Purchase Item dropdown must open downward so it is not hidden behind the
    // Supplier Invoice area above the item table. Keep the menu compact enough
    // to remain usable near the bottom of the form.
    if(!purchaseItem && (((purchaseLine || quotationLine) && above>120) || (below<needed && above>below))) $wrap.addClass('drop-up');
  }
}
function bsmEnhanceSelect(select){
  const $select=$(select);
  if($select.data('bsmSearchable') || $select.closest('.bsm-searchable-select').length) return;
  $select.data('bsmSearchable',true).addClass('bsm-native-select');
  const $wrap=$('<div class="bsm-searchable-select"><div class="bsm-select-control"><input type="text" class="bsm-select-search" autocomplete="off" placeholder="Select"><span class="bsm-select-arrow" aria-hidden="true">⌄</span></div><div class="bsm-select-menu"></div></div>');
  $select.after($wrap);
  bsmRefreshSearchableSelect(select);
}
function bsmEnhanceSearchableSelects(scope=document){
  $(scope).find('select').addBack('select').each(function(){
    if($(this).hasClass('bsm-payment-status')||$(this).hasClass('bsm-native-gst-rate')) return;
    bsmEnhanceSelect(this);
  });
}
function bsmSyncAllSearchables(scope=document){
  $(scope).find('select.bsm-native-select').each(function(){bsmRefreshSearchableSelect(this);});
}

$(document).on('focus click','.bsm-select-search',function(e){
  const $wrap=$(this).closest('.bsm-searchable-select');
  if(!$wrap.hasClass('open')) bsmOpenSearchable($wrap);
  e.stopPropagation();
});
// Close viewport-positioned Time Sheet menus when their scroll container moves.
// Scrolling the option list itself must keep the menu open.
document.addEventListener('scroll',function(e){
  $('#ops-time-form .bsm-po-vendor-dropdown-open,.bsm-time-filters .bsm-po-vendor-dropdown-open').each(function(){
    if(e.target instanceof Element && e.target.contains(this))bsmCloseSearchable($(this));
  });
},true);
$(document).on('scroll', '#po_pending_list .po-pending-table-wrap', function(){
  $('.bsm-po-vendor-dropdown-open').each(function(){bsmCloseSearchable($(this));});
});
$(window).on('resize scroll', function(){
  $('.bsm-po-vendor-dropdown-open').each(function(){bsmCloseSearchable($(this));});
});
$(document).on('click','.bsm-select-arrow',function(e){
  const $wrap=$(this).closest('.bsm-searchable-select');
  if($wrap.hasClass('open')) bsmCloseSearchable($wrap); else {bsmOpenSearchable($wrap);$wrap.find('.bsm-select-search').trigger('focus');}
  e.stopPropagation();
});
$(document).on('input','.bsm-select-search',function(){
  const $wrap=$(this).closest('.bsm-searchable-select');
  const $select=$wrap.prev('select');
  if(!$wrap.hasClass('open')) bsmOpenSearchable($wrap);
  bsmRenderSearchableOptions($select,$wrap,$(this).val(),true);
});
$(document).on('click','.bsm-select-option',function(e){
  const $wrap=$(this).closest('.bsm-searchable-select');
  const $select=$wrap.prev('select');
  $select.val(String($(this).data('value'))).trigger('change');
  if($select.attr('name')==='dispatch_id' && $select.closest('#bsm-form').data('module')==='site_receipts') fillSiteReceiptFromDispatch($select.val());
  // Site Consumption uses the custom searchable dropdown. Trigger the stock
  // refresh explicitly from the visible option click so Available Qty + Unit
  // always update even if another delegated change handler is skipped/delayed.
  if($select.attr('name')==='item_id'){
    const $tr=$select.closest('tr');
    autoSelectUnitFromItem($tr);
    const module=$select.closest('#bsm-form').data('module');
    if(module==='site_consumptions') refreshConsumptionAvailability($tr);
    if(module==='material_returns') refreshMaterialReturnStock($tr);
  }
  bsmCloseSearchable($wrap,true);
  e.stopPropagation();
});
$(document).on('keydown','.bsm-select-search',function(e){
  const $wrap=$(this).closest('.bsm-searchable-select');
  const $items=$wrap.find('.bsm-select-option:visible');
  let i=$items.index($items.filter('.keyboard-active'));
  if(e.key==='ArrowDown'){e.preventDefault();i=Math.min(i+1,$items.length-1);$items.removeClass('keyboard-active').eq(i).addClass('keyboard-active')[0]?.scrollIntoView({block:'nearest'});}
  else if(e.key==='ArrowUp'){e.preventDefault();i=Math.max(i-1,0);$items.removeClass('keyboard-active').eq(i).addClass('keyboard-active')[0]?.scrollIntoView({block:'nearest'});}
  else if(e.key==='Enter' && i>=0){e.preventDefault();$items.eq(i).trigger('click');}
  else if(e.key==='Escape'){bsmCloseSearchable($wrap,true);$(this).trigger('blur');}
});
$(document).on('change','select.bsm-native-select',function(){bsmRefreshSearchableSelect(this);});
$(document).on('invalid','select.bsm-native-select',function(e){
  e.preventDefault();
  const $wrap=$(this).next('.bsm-searchable-select');
  $wrap.addClass('bsm-invalid-wrap');
  $wrap.find('.bsm-select-search').trigger('focus');
  setTimeout(()=>$wrap.removeClass('bsm-invalid-wrap'),2200);
});
$(window).on('resize scroll',function(){
  $('.bsm-searchable-select.bsm-item-dropdown-open').each(function(){bsmCloseSearchable($(this),true);});
});
$(document).on('scroll','.bsm-lines .bsm-table-wrap, .bsm-quotation-lines .bsm-table-wrap, .bsm-purchase-lines .bsm-table-wrap, .bsm-boq-table-wrap',function(){
  $('.bsm-searchable-select.bsm-item-dropdown-open').each(function(){bsmCloseSearchable($(this),true);});
});
$(document).on('click',function(e){
  if(!$(e.target).closest('.bsm-searchable-select').length){$('.bsm-searchable-select.open').each(function(){bsmCloseSearchable($(this),true);});}
});


// v0.1.174 - quantities and monetary number inputs use a maximum of 2 decimal places.
function bsmLimitTwoDecimals(value){
  const raw=String(value??'');
  if(!raw.includes('.')) return raw;
  const parts=raw.split('.');
  return parts[0]+'.'+parts.slice(1).join('').slice(0,2);
}
$(document).on('input','input[type="number"][step="0.01"]',function(){
  const limited=bsmLimitTwoDecimals(this.value);
  if(this.value!==limited)this.value=limited;
});
$(document).on('blur','input[type="number"][step="0.01"]',function(){
  if(this.value==='')return;
  const n=Number(this.value);
  if(Number.isFinite(n))this.value=n.toFixed(2);
});

const bsmSelectObserver=new MutationObserver(function(mutations){
  mutations.forEach(m=>{
    if(m.type==='childList'){
      if(m.target && m.target.tagName==='SELECT') bsmRefreshSearchableSelect(m.target);
      m.addedNodes.forEach(n=>{if(n.nodeType===1)bsmEnhanceSearchableSelects(n);});
    }else if(m.type==='attributes' && m.target.tagName==='SELECT'){
      bsmRefreshSearchableSelect(m.target);
    }
  });
});
function exportUsersExcel(){const rows=(App.userRows||[]).map((x,i)=>[i+1,x.user_id||'',x.name||'',x.email||'',userRoleLabel(x.role),x.designation||'',x.mobile||'',Number(x.attendance_applicable||0)===1?'Yes':'No',Number(x.attendance_applicable||0)===1?(x.salary||0):'',Number(x.attendance_applicable||0)===1?dateFmt(x.salary_effective_date):'',String(x.status||'').replace(/\b\w/g,c=>c.toUpperCase())]);downloadExcelFile('Users',['Sl','User ID','Name','Email','Role','Designation','Mobile','Attendance Applicable','Monthly Salary','Salary Effective Date','Status'],rows);}

$(document).on('input','input[name="mobile"],input[name$="_mobile"]',function(){
  const digits=String($(this).val()||'').replace(/\D/g,'').slice(0,10);
  if($(this).val()!==digits)$(this).val(digits);
});

$(document).on('blur','.bsm-date-input',function(){
  if(String($(this).attr('type')||'').toLowerCase()==='date') return;
  const raw=$(this).val().trim(); if(!raw) return;
  const sql=dateSql(raw);
  if(/^\d{4}-\d{2}-\d{2}$/.test(sql)) $(this).val(dateFmt(sql));
});

$(document).on('click','.bsm-nav-label',function(){
  const current=$(this).closest('.bsm-nav-group')[0];
  // Let the native <details> control toggle first, then close every other section.
  setTimeout(function(){
    if(current && current.open){
      document.querySelectorAll('.bsm-nav-group').forEach(function(group){
        if(group!==current) group.open=false;
      });
    }
  },0);
});
$(document).on('click','.bsm-nav-item',function(e){saveNavScroll();const k=$(this).data('view');if(!(BSM.pages&&BSM.pages[k])){e.preventDefault();openView(k);}});
$(document).on('scroll','#bsm-nav',function(){saveNavScroll();});
$(document).on('click','[data-go]',function(){const k=$(this).data('go');if(BSM.pages&&BSM.pages[k])location.href=BSM.pages[k];else openView(k);});
$(document).on('change','#bsm-dashboard-site',function(){renderDashboard($(this).val()||'');});
$(document).on('click','[data-dashboard-go]',function(){openDashboardTarget($(this).data('dashboard-go'),$(this).data('site')||$('#bsm-dashboard-site').val()||'');});
$(document).on('click','#bsm-dashboard-add-site',async function(){
  await openView('sites');
  // openView checks the user's permission; only open Add if the Site Master loaded.
  if(App.current==='sites' && $('#bsm-content #bsm-add').length){await openForm('sites',0);}
});
$(document).on('change','#bsm-form-fields select[name="vendor_id"]',function(){if($('#bsm-form').data('module')==='material_inwards')loadMaterialInwardVendorGST($(this).val());});
$(document).on('change','#bsm-form[data-module="site_opening_stock"] select[name="item_id"]',function(){const item=(App.options.items||[]).find(x=>String(x.id)===String($(this).val()||''));const $u=$('#bsm-form-fields select[name="unit_id"]');if($u.length){$u.val(item&&item.unit_id?String(item.unit_id):'');bsmRefreshSearchableSelect($u[0]);}});
$(document).on('change','#allowance_employee,#allowance_type,#allowance_date',refreshAllowanceLimitBox);
$(document).on('click','#bsm-allowance-limit-filter-toggle',()=>$('#bsm-allowance-limit-filters').slideToggle(100));
$(document).on('change','#allowance_limit_employee,#allowance_limit_type,#allowance_limit_status',function(){App.page=1;loadEmployeeAllowanceLimits();});
$(document).on('input','#bsm-allowance-limit-search',function(){clearTimeout(App.alis);App.alis=setTimeout(()=>{App.page=1;loadEmployeeAllowanceLimits();},500);});
$(document).on('click','#allowance_limit_clear',function(){$('#bsm-allowance-limit-filters select').val('');bsmSyncAllSearchables($('#bsm-allowance-limit-filters'));App.page=1;loadEmployeeAllowanceLimits();});
$(document).on('click','#bsm-allowance-limit-export',exportEmployeeAllowanceLimits);
$(document).on('click','#bsm-add-allowance-limit',()=>openForm('employee_allowance_limits',0));
$(document).on('click','.bsm-edit-allowance-limit',function(e){e.stopPropagation();openForm('employee_allowance_limits',$(this).data('id'));});
$(document).on('click','.bsm-delete-allowance-limit',async function(e){e.stopPropagation();if(!confirm('Delete this allowance limit?'))return;const r=await ajax('bsm_delete',{module:'employee_allowance_limits',id:$(this).data('id')});if(r.success){showToast('Deleted');loadEmployeeAllowanceLimits();}else showToast(r.data?.message||'Unable to delete',true);});
$(document).on('change','#allowance_site',bsmSyncAllowanceCustomer);
$(document).on('submit','#bsm-allowance-form',async function(e){e.preventDefault();const file=$('#allowance_document')[0]?.files?.[0];if(!$('#allowance_site').val())return showToast('Site Name is required',true);if(file&&file.size>10*1024*1024)return showToast('Supporting Document must be 10 MB or less',true);const fd=new FormData();fd.append('action','bsm_allowance_submit');fd.append('nonce',BSM.nonce);fd.append('employee_id',BSM.currentUser?.plugin_id||'');fd.append('site_id',$('#allowance_site').val()||'');fd.append('allowance_date',$('#allowance_date').val());fd.append('allowance_type_id',$('#allowance_type').val());fd.append('amount',$('#allowance_amount').val());fd.append('purpose',$('#allowance_purpose').val());if(file)fd.append('supporting_document',file);const r=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});if(r.success){showToast(r.data.message||'Submitted');renderAllowanceForm();}else showToast(r.data?.message||'Unable to submit',true);});
$(document).on('click','#bsm-allowance-filter-toggle',()=>$('#bsm-allowance-filters').slideToggle(100));
$(document).on('change','#allowance_date_from,#allowance_date_to,#allowance_filter_employee,#allowance_filter_type',function(){App.page=1;loadAllowanceQueue();});
$(document).on('click','#allowance_clear',function(){$('#bsm-allowance-filters input,#bsm-allowance-filters select').val('');bsmSyncAllSearchables($('#bsm-allowance-filters'));App.page=1;loadAllowanceQueue();});
$(document).on('input','#bsm-allowance-search',function(){clearTimeout(App.alws);App.alws=setTimeout(()=>{App.page=1;loadAllowanceQueue();},500);});
$(document).on('click','.allowance-prev',()=>{App.page--;loadAllowanceQueue();});$(document).on('click','.allowance-next',()=>{App.page++;loadAllowanceQueue();});
$(document).on('click','.bsm-allowance-approve,.bsm-allowance-reject',async function(){const decision=$(this).hasClass('bsm-allowance-approve')?'approve':'reject';const r=await ajax('bsm_allowance_decide',{id:$(this).data('id'),decision});if(r.success){showToast(r.data.message);if(decision==='approve'){App.current='pending_allowance_payment';App.page=1;App.search='';$('.bsm-nav-item').removeClass('active');$('.bsm-nav-item[data-view="pending_allowance_payment"]').addClass('active');return openView('pending_allowance_payment');}loadAllowanceQueue();}else showToast(r.data?.message||'Unable to update',true);});
$(document).on('click','.bsm-pay-allowance',function(){openAllowancePayment($(this).data('id'));});
$(document).on('click','#bsm-salary-template',downloadSalaryTemplate);
$(document).on('click','#bsm-salary-preview',previewSalaryUpload);
$(document).on('click','#bsm-generate-salary-slips',commitSalarySlips);
$(document).on('change','#salary_filter_month,#salary_filter_employee',function(){App.page=1;loadSalarySlips();});
$(document).on('input','#salary_filter_search',function(){clearTimeout(App.salarySearchTimer);App.salarySearchTimer=setTimeout(()=>{App.page=1;loadSalarySlips();},500);});
$(document).on('click','#salary_filter_clear',function(){$('#salary_filter_month').val('');$('#salary_filter_employee,#salary_filter_search').val('');bsmSyncAllSearchables($(this).closest('.bsm-filters'));App.page=1;loadSalarySlips();});
$(document).on('click','#salary_slips_export',exportSalarySlips);
$(document).on('click','.salary-prev',function(){if(App.page>1){App.page--;loadSalarySlips();}});$(document).on('click','.salary-next',function(){App.page++;loadSalarySlips();});
$(document).on('click','.bsm-pay-salary',function(){openSalaryPayment($(this).data('id'));});
$(document).on('click','#bsm-allowance-export',async function(){const rows=await loadAllowanceQueue(true);if(!rows)return;const headers=['Sl','Date','Employee','Site','Customer','Allowance Type','Amount','Purpose','Document','Status'];const data=rows.map((x,i)=>[i+1,dateFmt(x.allowance_date),x.employee_name||'',x.site_name||'',x.customer_name||'',x.allowance_type_name||'',x.amount||0,x.purpose||'',x.document_url||'',x.status||'']);downloadExcelFile(App.allowanceMode==='all'?'Allowance Master':(App.allowanceMode==='payment'?'Pending Allowance Payment':'Pending Allowance Approval'),headers,data);});

$(document).on('change','#bsm-form[data-module="contractor_advances"] select[name="contractor_id"], #bsm-form[data-module="contractor_advances"] select[name="site_id"]',loadContractorPaymentSummary);
$(document).on('change','#bsm-form select[name="payment_mode"]',function(){updateBankFieldVisibility($(this).closest('#bsm-form'));});
$(document).on('change','#bsm-pay-mode',function(){const isBank=$(this).val()==='Bank';$('#bsm-pay-bank-wrap').show();$('#bsm-pay-bank').prop('required',isBank);});
$(document).on('change','#bsm-form[data-module="vendor_payments"] select[name="payment_type"]',updatePaymentFormVisibility);
$(document).on('change','#bsm-form[data-module="material_inwards"] select[name="inward_type"]',function(){const t=$(this).val();if(t==='return'){setMaterialInwardMode(t);fillMaterialInwardFromReturn($('#bsm-form-fields select[name="return_id"]').val()||'');}else{const $wh=$('#bsm-form-fields select[name="warehouse_id"]');$wh.prop('disabled',false);bsmRefreshSearchableSelect($wh[0]);renderItems(App.configs.material_inwards,[]);setMaterialInwardMode(t);}});
$(document).on('change','#bsm-form[data-module="material_inwards"] select[name="return_id"]',function(){fillMaterialInwardFromReturn($(this).val());});
$(document).on('input change','#bsm-lines-body [name="qty"],#bsm-lines-body [name="rate"],#bsm-lines-body [name="gst_rate"]',function(){if($('#bsm-form').data('module')==='material_inwards')recalcMaterialInwardRow($(this).closest('.bsm-line'));});
$(document).on('input change','#bsm-form[data-module="material_inwards"] #bsm-form-fields [name="other_expenses"], #bsm-form[data-module="material_inwards"] #bsm-form-fields [name="other_expense_gst"], #bsm-form[data-module="material_inwards"] #bsm-form-fields [name="round_off"]',function(){recalcMaterialInwardTotals();});
$(document).on('click keydown','.po-pending-summary-row',function(e){if(e.type==='keydown'&&e.key!=='Enter'&&e.key!==' ')return;if($(e.target).closest('select,input,button,.bsm-searchable-select').length)return;e.preventDefault();const $row=$(this),item=$row.data('item'),$detail=$(`.po-pending-detail-row[data-detail-for="${item}"]`);const open=$row.attr('aria-expanded')==='true';$row.attr('aria-expanded',open?'false':'true');$detail.toggle(!open);});
function poWrapSupplierFields(){
  $('#po_pending_list .po-three-quotes .bsm-select-search').each(function(){
    if(this.tagName==='INPUT'){const $old=$(this),$area=$('<textarea rows="1"></textarea>');for(const attr of this.attributes){if(!['type','value'].includes(attr.name))$area.attr(attr.name,attr.value);}$area.val($old.val());$old.replaceWith($area);}
  });
  $('#po_pending_list .po-three-quotes textarea.bsm-select-search').each(function(){this.style.height='auto';this.style.height=Math.max(30,this.scrollHeight+2)+'px';});
}
$(document).on('input','#po_pending_list textarea.bsm-select-search',poWrapSupplierFields);
$(document).on('change','#po_pending_list .po-quote-supplier,#po_pending_list .po-row-vendor',function(){setTimeout(poWrapSupplierFields,0);});
$(window).on('resize',function(){if($('#po_pending_list .po-three-quotes').length)poWrapSupplierFields();});
function poRowQuotations($row){return [1,2,3].map(n=>({vendor_id:String($row.find(`.po-quote-supplier[data-slot="${n}"]`).val()||''),rate:$row.find(`.po-quote-rate[data-slot="${n}"]`).val()}));}
$(document).on('change input','#po_pending_list .po-quote-supplier,#po_pending_list .po-quote-rate',function(){
  const $row=$(this).closest('tr'),$chosen=$row.find('.po-row-vendor'),selected=$chosen.val(),quotes=poRowQuotations($row),seen=new Set();
  $chosen.html('<option value="">Select quoted supplier</option>'+quotes.filter(q=>{if(!q.vendor_id||seen.has(q.vendor_id))return false;seen.add(q.vendor_id);return true;}).map(q=>`<option value="${esc(q.vendor_id)}">${esc(labelFor('vendors',q.vendor_id))}</option>`).join('')).val(selected||'');
  $row.find('.po-row-rate').val(quotes.find(q=>q.vendor_id===$chosen.val())?.rate??'');bsmRefreshSearchableSelect($chosen[0]);
});
$(document).on('change','#po_pending_list .po-row-vendor',function(){const $row=$(this).closest('tr');$row.find('.po-row-rate').val(poRowQuotations($row).find(q=>q.vendor_id===String($(this).val()))?.rate??'');});
$(document).on('click','#po_create_selected',async function(){
  const items=[];let error='';
  $('#po_pending_list tbody tr[data-item]').each(function(){const $row=$(this),vendor=String($row.find('.po-row-vendor').val()||''),qty=Number($row.find('.po-row-qty').val()||0);if(!vendor||qty<=0)return;const quotations=poRowQuotations($row);if(new Set(quotations.map(q=>q.vendor_id)).size!==3||quotations.some(q=>!q.vendor_id||q.rate===''||!Number.isFinite(Number(q.rate))||Number(q.rate)<0)){error='Enter three different suppliers and valid rates for each selected item.';return;}const chosen=quotations.find(q=>q.vendor_id===vendor);if(!chosen){error='Choose one of the three quoted suppliers.';return;}items.push({item_id:$row.data('item'),vendor_id:vendor,po_qty:qty,rate:Number(chosen.rate),quotations});});
  if(error)return showToast(error,true);if(!items.length)return showToast('Choose Supplier for at least one item.',true);
  const $button=$(this);$button.prop('disabled',true);
  try{const r=await ajax('bsm_purchase_order_create',{warehouse_id:App.pendingPoWarehouse||'',items:JSON.stringify(items)});if(r.success){showToast(r.data.message||'PO created');await loadPendingPoItems();}else showToast(r.data?.message||'Unable to create PO',true);}finally{$button.prop('disabled',false);}
});

function syncDirectPoChosen($row){const vendor=String($('#po_direct_vendor').val()||'');const quotes=[1,2,3].map(n=>({vendor_id:String($('.po-direct-quote-supplier[data-slot="'+n+'"]').val()||''),rate:$('.po-direct-quote-rate[data-slot="'+n+'"]').val()}));const match=quotes.find(q=>q.vendor_id===vendor&&vendor);$('.po-direct-chosen-rate').val(match?match.rate:'');$row.find('.po-direct-rate').val(match?match.rate:'');updateDirectPoAmount($row);}
function refreshDirectPoChosenOptions(){const $chosen=$('#po_direct_vendor'),value=String($chosen.val()||''),seen=new Set();const opts=[1,2,3].map(n=>String($('.po-direct-quote-supplier[data-slot="'+n+'"]').val()||'')).filter(id=>{if(!id||seen.has(id))return false;seen.add(id);return true;});$chosen.html('<option value="">Select quoted supplier</option>'+opts.map(id=>`<option value="${esc(id)}">${esc(labelFor('vendors',id)||'')}</option>`).join('')).val(opts.includes(value)?value:'');bsmRefreshSearchableSelect($chosen[0]);}
$(document).on('change','.po-direct-quote-supplier',function(){refreshDirectPoChosenOptions();$('#po_direct_items .po-direct-row').each(function(){syncDirectPoChosen($(this));});});
$(document).on('input change','.po-direct-quote-rate',function(){$('#po_direct_items .po-direct-row').each(function(){syncDirectPoChosen($(this));});});
$(document).on('change','#po_direct_vendor',function(){$('#po_direct_items .po-direct-row').each(function(){syncDirectPoChosen($(this));});});
$(document).on('click','.po-direct-add',function(){
  const $row=$(this).closest('tr');
  $row.after(directPoItemRow());
  bsmEnhanceSearchableSelects($('#po_direct_items'));
});
$(document).on('click','.po-direct-remove',function(){
  const $rows=$('#po_direct_items .po-direct-row');
  if($rows.length<=1){
    const $r=$(this).closest('tr');
    $r.find('.po-direct-item').val('');
    $r.find('.po-direct-qty,.po-direct-fixed-price,.po-direct-rate').val('');
    $r.find('.po-direct-unit').text('-');
    $r.find('.po-direct-amount').text('0.00');
    return;
  }
  $(this).closest('tr').remove();
});
$(document).on('change','.po-direct-item',function(){refreshDirectPoItemRow($(this).closest('tr'));});
$(document).on('input','.po-direct-qty,.po-direct-rate',function(){updateDirectPoAmount($(this).closest('tr'));});
$(document).on('click','#po_direct_clear',async function(){await renderPurchaseOrderForm();});
$(document).on('click','#po_direct_save',async function(){
  const vendor_id=$('#po_direct_vendor').val();
  const warehouse_id=$('#po_direct_warehouse').val();
  const po_date=$('#po_direct_date').val();
  const required_date=$('#po_direct_required').val();
  const remarks='';
  const created_by=$('#po_direct_created_by').val()||'';
  if(!po_date)return showToast('PO Date is required',true);
  if(!vendor_id)return showToast('Select a Chosen Supplier from the quotations',true);
  if(!warehouse_id)return showToast('Warehouse is required',true);
  const items=[];
  let invalid=false;
  $('#po_direct_items .po-direct-row').each(function(){
    const item_id=$(this).find('.po-direct-item').val();
    const po_qty=Number($(this).find('.po-direct-qty').val()||0);
    const fixed_price=Number($(this).find('.po-direct-fixed-price').val()||0);
    const rate=Number($(this).find('.po-direct-rate').val()||0);
    if(!item_id && po_qty<=0)return;
    if(!item_id || po_qty<=0){invalid=true;return;}
    const quotations=[1,2,3].map(n=>({vendor_id:String($('.po-direct-quote-supplier[data-slot="'+n+'"]').val()||''),rate:$('.po-direct-quote-rate[data-slot="'+n+'"]').val()}));
    if(quotations.some(q=>!q.vendor_id||q.rate===''||!Number.isFinite(Number(q.rate))||Number(q.rate)<0)||new Set(quotations.map(q=>q.vendor_id)).size!==3||!quotations.some(q=>q.vendor_id===String(vendor_id))){invalid=true;return;}
    items.push({item_id,po_qty,fixed_price,rate,quotations});
  });
  if(invalid)return showToast('Select Item, enter PO Qty and three different supplier rates, and choose a quoted Supplier above',true);
  if(!items.length)return showToast('Add at least one PO item',true);
  const $btn=$('#po_direct_save').prop('disabled',true).text('Saving...');
  const r=await ajax('bsm_purchase_order_manual_save',{vendor_id,warehouse_id,po_date,required_date,remarks,created_by,items:JSON.stringify(items)});
  $btn.prop('disabled',false).text('Create PO');
  if(r.success){
    showToast(`${r.data.po_no||'PO'} created successfully`);
    await openView('purchase_order_master');
  }else showToast(r.data?.message||'Unable to create PO',true);
});

$(document).on('input','#po_receipt_search',function(){clearTimeout(App.pors);App.pors=setTimeout(()=>loadPurchaseOrderPendingReceipt(),300);});
$(document).on('click','.po-receipt-summary-row',function(e){if($(e.target).closest('button,input,select,a,.bsm-searchable-select').length)return;const id=String($(this).data('po')||'');const $d=$(`.po-receipt-detail-row[data-detail-for="${id}"]`);const open=$d.is(':visible');$('.po-receipt-detail-row').hide();$('.po-receipt-summary-row').attr('aria-expanded','false');if(!open){$d.show();$(this).attr('aria-expanded','true');}});
$(document).on('change','#bsm-purchase-required-po',async function(){
  const poId=$(this).val();
  if(!poId){$('#bsm-form-save').prop('disabled',true);return;}
  const g=(App.pendingReceiptGroups||[]).find(x=>String(x.purchase_order_id)===String(poId));
  if(!g){$('#bsm-form-save').prop('disabled',true);return showToast('Purchase Order not found',true);}
  App.poReceiptPrefill=g;
  await openView('purchase_form');
});
$(document).on('click','.po-receive-po',async function(e){e.stopPropagation();await openPurchaseFormFromPO($(this).data('po'));});
$(document).on('input','#po_master_search',function(){clearTimeout(App.poms);App.poms=setTimeout(()=>loadPurchaseOrderMaster(),350);});
$(document).on('change','#po_master_vendor,#po_master_status',()=>loadPurchaseOrderMaster());
$(document).on('click','.po-pdf',async function(e){e.stopPropagation();const r=await ajax('bsm_purchase_order_pdf',{id:$(this).data('id')});if(r.success&&r.data?.url)window.open(r.data.url,'_blank');else showToast(r.data?.message||'Unable to generate PO PDF',true);});
$(document).on('click','.po-edit',async function(e){
  e.stopPropagation();const id=$(this).data('id');await ensureOptions(['vendors','warehouses']);const r=await ajax('bsm_purchase_order_get',{id});if(!r.success)return showToast(r.data?.message||'Unable to load PO',true);const h=r.data.head||{},items=r.data.items||[],locked=Number(h.received_qty||0)>0.009;
  App.editId=id;$('#bsm-modal').removeClass('bsm-purchase-edit-modal bsm-site-receipt-modal');$('#bsm-modal-title').text('Edit Purchase Order');$('#bsm-form').data('module','purchase_order_edit');
  const vendorOpts=(App.options.vendors||[]).map(x=>`<option value="${x.id}" ${String(x.id)===String(h.vendor_id)?'selected':''}>${esc(x.label||x.vendor_name||x.name||'')}</option>`).join('');const whOpts=(App.options.warehouses||[]).map(x=>`<option value="${x.id}" ${String(x.id)===String(h.warehouse_id)?'selected':''}>${esc(x.label||x.warehouse_name||x.name||'')}</option>`).join('');
  $('#bsm-form-fields').html(`<label><span class="bsm-field-label">PO No.</span><input value="${esc(h.po_no||'')}" readonly></label><label><span class="bsm-field-label">PO Date</span><input type="date" lang="en-GB" name="po_date" value="${esc(h.po_date||'')}"></label><label><span class="bsm-field-label">Supplier</span><select name="vendor_id" ${locked?'disabled':''}>${vendorOpts}</select></label><label><span class="bsm-field-label">Warehouse</span><select name="warehouse_id" ${locked?'disabled':''}>${whOpts}</select></label><label><span class="bsm-field-label">Required Date</span><input type="date" lang="en-GB" name="required_date" value="${esc(h.required_date||'')}"></label><label class="wide"><span class="bsm-field-label">Remarks</span><textarea name="remarks">${esc(h.remarks||'')}</textarea></label>`);
  let ih='<div class="bsm-table-wrap"><table><thead><tr><th>Item</th><th>Unit</th><th class="num">Received Qty</th><th class="num">PO Qty</th><th class="num">Fixed Price</th><th class="num">Rate</th></tr></thead><tbody>';items.forEach(it=>{ih+=`<tr data-id="${it.id}"><td>${esc(it.item_name||'')}</td><td>${esc(it.unit_name||'')}</td><td class="num">${Number(it.received_qty||0).toFixed(2)}</td><td><input class="po-edit-qty num" type="number" min="${Number(it.received_qty||0)}" step="0.01" value="${Number(it.po_qty||0).toFixed(2)}"></td><td class="num">${Number(it.fixed_price||0).toFixed(2)}</td><td><input class="po-edit-rate num" type="number" min="0" step="0.01" value="${Number(it.rate||0).toFixed(2)}"></td></tr>`;});ih+='</tbody></table></div>';$('#bsm-line-items').html(ih);$('#bsm-documents').empty();$('#bsm-modal').show();
});
$(document).on('submit','#bsm-form',async function(e){if($(this).data('module')!=='purchase_order_edit')return;e.preventDefault();const data={po_date:$('#bsm-form-fields [name="po_date"]').val(),vendor_id:$('#bsm-form-fields [name="vendor_id"]').val(),warehouse_id:$('#bsm-form-fields [name="warehouse_id"]').val(),required_date:$('#bsm-form-fields [name="required_date"]').val(),remarks:$('#bsm-form-fields [name="remarks"]').val()};const items=[];$('#bsm-line-items tbody tr').each(function(){items.push({id:$(this).data('id'),po_qty:$(this).find('.po-edit-qty').val(),rate:$(this).find('.po-edit-rate').val()});});const r=await ajax('bsm_purchase_order_update',{id:App.editId,data:JSON.stringify(data),items:JSON.stringify(items)});if(r.success){showToast(r.data.message||'PO updated');$('#bsm-modal').hide();loadPurchaseOrderMaster();}else showToast(r.data?.message||'Unable to update PO',true);});
$(document).on('click','.po-cancel',async function(){if(!confirm('Cancel this PO?'))return;const r=await ajax('bsm_purchase_order_cancel',{id:$(this).data('id')});if(r.success){showToast('PO cancelled');loadPurchaseOrderMaster();}else showToast(r.data?.message||'Unable to cancel PO',true);});
$(document).on('input','#po_items_search',function(){clearTimeout(App.poims);App.poims=setTimeout(()=>loadPurchaseOrderItemsMaster(),350);});
$(document).on('change','#po_items_vendor',()=>loadPurchaseOrderItemsMaster());
$(document).on('click','#po_pending_export',async function(){const rows=await loadPendingPoItems(true);if(!rows)return;downloadExcelFile('Pending PO Items',['Sl','Site','Item','Unit','Approved Qty','Current Stock','Pending PO Qty'],rows.map((x,i)=>[i+1,x.site_name||'',x.item_name||'',x.unit_name||'',x.approved_qty||0,x.current_stock||0,x.pending_po_qty||0]));});
$(document).on('click','#po_master_export',async function(){const rows=await loadPurchaseOrderMaster(true);if(!rows)return;downloadExcelFile('PO Master',['Sl','PO No.','PO Date','Supplier','Warehouse','Required Date','Items','PO Qty','Received','Pending','Total','Status','Created By'],rows.map((x,i)=>[i+1,x.po_no||'',dateFmt(x.po_date),x.vendor_name||'',x.warehouse_name||'',dateFmt(x.required_date),x.item_count||0,x.po_qty||0,x.received_qty||0,x.pending_qty||0,x.total_amount||0,x.status||'',x.created_by_name||'']));});
$(document).on('click','#po_items_export',async function(){const rows=await loadPurchaseOrderItemsMaster(true);if(!rows)return;downloadExcelFile('PO Items Master',['Sl','PO No.','PO Date','Supplier','Item','Unit','Supplier 1','Rate 1','Supplier 2','Rate 2','Supplier 3','Rate 3','Chosen Supplier','Chosen Rate','Fixed Price','Rate','PO Qty','Received Qty','Pending Qty','Amount','Status'],rows.map((x,i)=>[i+1,x.po_no||'',dateFmt(x.po_date),x.vendor_name||'',x.item_name||'',x.unit_name||'',...poItemQuotes(x),x.fixed_price||0,x.rate||0,x.po_qty||0,x.received_qty||0,x.pending_qty||0,Number(x.po_qty||0)*Number(x.rate||0),x.status||'']));});

$(document).on('click keydown','.bsm-labour-attendance-row',function(e){
  if($(e.target).closest('button,a,input,select,textarea').length)return;
  if(e.type==='keydown'&&e.key!=='Enter'&&e.key!==' ')return;
  e.preventDefault();$(this).find('.bsm-action-edit').trigger('click');
});
$(document).on('click','#bsm-add',()=>openForm(App.current));
$(document).on('click','.bsm-contractor-payment-edit',function(e){
  e.preventDefault();e.stopPropagation();
  openForm('contractor_advances',$(this).data('id'));
});
$(document).on('click','.bsm-contractor-payment-delete',async function(e){
  e.preventDefault();e.stopPropagation();
  if(!confirm('Delete this Contractor Payment?'))return;
  const r=await ajax('bsm_delete',{module:'contractor_advances',id:$(this).data('id')});
  if(r.success){showToast('Deleted');await loadReport('contractor_advance_register');}
  else showToast(r.data?.message||'Unable to delete Contractor Payment',true);
});

$(document).on('click','.bsm-action-edit',function(){
  if($(this).hasClass('bsm-contractor-payment-edit')) return;
  const allowed=String($(this).attr('data-allowed')??'1')!=='0';
  if(!allowed) return showToast($(this).attr('data-message')||'This record cannot be edited.',true);
  const module=$(this).attr('data-module')||App.current;
  openForm(module,$(this).data('id'));
});
$(document).on('click','.bsm-user-delete',async function(e){
  e.stopPropagation();
  if(!confirm('Delete this user? This will also remove the linked login access.'))return;
  const r=await ajax('bsm_user_delete',{id:$(this).data('id')});
  if(r.success){showToast(r.data.message||'User deleted');delete App.options.users;renderUsers();}
  else showToast(r.data?.message||'Unable to delete user',true);
});

$(document).on('click','.bsm-action-delete',async function(){
  if($(this).hasClass('bsm-user-delete')||$(this).hasClass('bsm-contractor-payment-delete')) return;
  const allowed=String($(this).attr('data-allowed')??'1')!=='0';
  if(!allowed) return showToast($(this).attr('data-message')||'This record cannot be deleted.',true);
  if(!confirm('Delete this record?'))return;
  const module=$(this).attr('data-module')||App.current;
  const r=await ajax('bsm_delete',{module,id:$(this).data('id')});
  if(r.success){showToast('Deleted');if(App.current==='purchase_register')renderPurchaseRegister();else if(['quotation_master','quotation_items','invoice_master','invoice_items'].includes(App.current))renderSalesRegister(App.current);else loadList(module);}else showToast(r.data?.message||'Unable to delete',true);
});
$(document).on('input','#bsm-search',function(){
  if(App.current==='purchase_register') return;
  const el=this;
  clearTimeout(App.st);
  App.st=setTimeout(()=>{App.search=$(el).val();App.page=1;loadList(App.current);},600);
});
$(document).on('input','#bsm-user-search',function(){
  const el=this;
  clearTimeout(App.ust);
  App.ust=setTimeout(()=>{App.search=$(el).val();loadUserList();},600);
});
$(document).on('input','#bsm-special-search',function(){
  const el=this;
  clearTimeout(App.sst);
  App.sst=setTimeout(()=>{App.specialSearch=$(el).val();if(App.current==='purchase_pending_approval')drawPurchasePendingApproval();if(App.current==='pending_requisition_approval')drawPendingApproval();if(App.current==='pending_material_dispatch')drawPendingDispatch();if(App.current==='pending_material_receipt')drawPendingMaterialReceipt();if(App.current==='pending_site_receipt')drawPendingSiteReceipt();},600);
});
$(document).on('input','#r_search',function(){
  clearTimeout(App.rst);
  App.rst=setTimeout(()=>{if(reportConfigs[App.current])drawReportRows(App.current);},600);
});
$(document).on('click','#bsm-filter-toggle',()=>$('#bsm-filters').slideToggle(100));
$(document).on('click','#bsm-purchase-filter-toggle',()=>$('#bsm-purchase-filters').slideToggle(100));
$(document).on('change','#bsm-purchase-filters input,#bsm-purchase-filters select',()=>{App.page=1;loadPurchaseRegister();});
$(document).on('click','#purchase_clear_filters',function(){$('#bsm-purchase-filters input,#bsm-purchase-filters select').val('');bsmSyncAllSearchables($('#bsm-purchase-filters'));App.page=1;loadPurchaseRegister();});
$(document).on('click','#bsm-purchase-items-filter-toggle',()=>$('#bsm-purchase-items-filters').slideToggle(100));
$(document).on('change','#purchase_items_date_from,#purchase_items_date_to,#purchase_items_vendor',function(){App.page=1;loadPurchaseItemsRegister();});
$(document).on('change','#purchase_items_category',function(){purchaseItemsFilterItemOptions();App.page=1;loadPurchaseItemsRegister();});
$(document).on('change','#purchase_items_item',function(){purchaseItemsSyncCategoryFromItem();App.page=1;loadPurchaseItemsRegister();});
$(document).on('click','#purchase_items_clear_filters',function(){$('#bsm-purchase-items-filters input,#bsm-purchase-items-filters select').val('');bsmSyncAllSearchables($('#bsm-purchase-items-filters'));purchaseItemsFilterItemOptions();App.page=1;loadPurchaseItemsRegister();});
$(document).on('input','#bsm-purchase-items-search',function(){const el=this;clearTimeout(App.pirs);App.pirs=setTimeout(()=>{App.page=1;loadPurchaseItemsRegister();},600);});
$(document).on('click','#bsm-purchase-items-export',exportPurchaseItemsRegister);
$(document).on('click','#bsm-return-receipt-filter-toggle',()=>$('#bsm-return-receipt-filters').slideToggle(100));
$(document).on('change','#return_receipt_date_from,#return_receipt_date_to,#return_receipt_site,#return_receipt_warehouse',function(){App.page=1;loadReturnReceiptMaster();});
$(document).on('click','#return_receipt_clear_filters',function(){$('#bsm-return-receipt-filters input,#bsm-return-receipt-filters select').val('');bsmSyncAllSearchables($('#bsm-return-receipt-filters'));App.page=1;loadReturnReceiptMaster();});
$(document).on('input','#bsm-return-receipt-search',function(){clearTimeout(App.rrms);App.rrms=setTimeout(()=>{App.page=1;loadReturnReceiptMaster();},600);});
$(document).on('click','#bsm-return-receipt-export',exportReturnReceiptMaster);

$(document).on('click','#bsm-return-receipt-item-filter-toggle',()=>$('#bsm-return-receipt-item-filters').slideToggle(100));
$(document).on('change','#return_receipt_item_date_from,#return_receipt_item_date_to,#return_receipt_item_site,#return_receipt_item_warehouse',function(){App.page=1;loadReturnReceiptItemMaster();});
$(document).on('change','#return_receipt_item_category',function(){returnReceiptItemFilterOptions();App.page=1;loadReturnReceiptItemMaster();});
$(document).on('change','#return_receipt_item_item',function(){returnReceiptItemSyncCategory();App.page=1;loadReturnReceiptItemMaster();});
$(document).on('click','#return_receipt_item_clear_filters',function(){$('#bsm-return-receipt-item-filters input,#bsm-return-receipt-item-filters select').val('');bsmSyncAllSearchables($('#bsm-return-receipt-item-filters'));returnReceiptItemFilterOptions();App.page=1;loadReturnReceiptItemMaster();});
$(document).on('input','#bsm-return-receipt-item-search',function(){clearTimeout(App.rrims);App.rrims=setTimeout(()=>{App.page=1;loadReturnReceiptItemMaster();},600);});
$(document).on('click','#bsm-return-receipt-item-export',exportReturnReceiptItemMaster);

$(document).on('click','#bsm-material-returns-items-filter-toggle',()=>$('#bsm-material-returns-items-filters').slideToggle(100));
$(document).on('change','#material_returns_items_date_from,#material_returns_items_date_to,#material_returns_items_site',function(){App.page=1;loadMaterialReturnsItemMaster();});
$(document).on('change','#material_returns_items_category',function(){materialReturnsItemsFilterItemOptions();App.page=1;loadMaterialReturnsItemMaster();});
$(document).on('change','#material_returns_items_item',function(){materialReturnsItemsSyncCategoryFromItem();App.page=1;loadMaterialReturnsItemMaster();});
$(document).on('click','#material_returns_items_clear_filters',function(){$('#bsm-material-returns-items-filters input,#bsm-material-returns-items-filters select').val('');bsmSyncAllSearchables($('#bsm-material-returns-items-filters'));materialReturnsItemsFilterItemOptions();App.page=1;loadMaterialReturnsItemMaster();});
$(document).on('input','#bsm-material-returns-items-search',function(){const el=this;clearTimeout(App.mris);App.mris=setTimeout(()=>{App.page=1;loadMaterialReturnsItemMaster();},600);});
$(document).on('click','#bsm-material-returns-items-export',exportMaterialReturnsItemMaster);
$(document).on('change','#bsm-filters input,#bsm-filters select',()=>{App.page=1;loadList(App.current);});
$(document).on('click','.bsm-clear',function(){$('#bsm-filters input,#bsm-filters select').val('');bsmSyncAllSearchables($('#bsm-filters'));loadList(App.current);});
$(document).on('click','.bsm-action-dispatch-slip',async function(e){e.stopPropagation();const id=$(this).data('id');const r=await ajax('bsm_dispatch_pdf',{id});if(r.success&&r.data?.url){window.open(r.data.url,'_blank','noopener');}else showToast(r.data?.message||'Unable to generate Dispatch Slip',true);});
$(document).on('click','.page-prev',()=>{App.page--;if(App.current==='purchase_register')return loadPurchaseRegister();if(App.current==='purchase_items_register')return loadPurchaseItemsRegister();if(App.current==='dispatch_items_register')return loadDispatchItemsRegister();if(App.current==='office_attendance_master')return loadOfficeAttendanceMaster();if(App.current==='site_material_item_receipt')return loadSiteMaterialItemReceipt();if(App.current==='consumption_items_register')return loadConsumptionItemsRegister();if(App.current==='return_receipt_master')return loadReturnReceiptMaster();if(App.current==='return_receipt_item_master')return loadReturnReceiptItemMaster();if(App.current==='material_returns_item_master')return loadMaterialReturnsItemMaster();if(App.current==='pending_invoice_copy')return loadPendingInvoiceCopy();if(App.current==='pending_site_receive_challans')return loadPendingSiteReceiveChallans();if(App.current==='pending_contractor_bill_copy')return loadPendingContractorBillCopy();if(App.current==='material_requisition_item')return loadMaterialRequisitionItem();if(App.current==='employee_allowance_limits')return loadEmployeeAllowanceLimits();if(['quotation_master','quotation_items','invoice_master','invoice_items'].includes(App.current))return loadSalesRegister(App.current);loadList(App.current);});$(document).on('click','.page-next',()=>{App.page++;if(App.current==='purchase_register')return loadPurchaseRegister();if(App.current==='purchase_items_register')return loadPurchaseItemsRegister();if(App.current==='dispatch_items_register')return loadDispatchItemsRegister();if(App.current==='office_attendance_master')return loadOfficeAttendanceMaster();if(App.current==='site_material_item_receipt')return loadSiteMaterialItemReceipt();if(App.current==='consumption_items_register')return loadConsumptionItemsRegister();if(App.current==='return_receipt_master')return loadReturnReceiptMaster();if(App.current==='return_receipt_item_master')return loadReturnReceiptItemMaster();if(App.current==='material_returns_item_master')return loadMaterialReturnsItemMaster();if(App.current==='pending_invoice_copy')return loadPendingInvoiceCopy();if(App.current==='pending_site_receive_challans')return loadPendingSiteReceiveChallans();if(App.current==='pending_contractor_bill_copy')return loadPendingContractorBillCopy();if(App.current==='material_requisition_item')return loadMaterialRequisitionItem();if(App.current==='employee_allowance_limits')return loadEmployeeAllowanceLimits();if(['quotation_master','quotation_items','invoice_master','invoice_items'].includes(App.current))return loadSalesRegister(App.current);loadList(App.current);});
$(document).on('click','#bsm-modal-close,#bsm-form-cancel',function(){if(directFormViews.has(App.current)&&$('.bsm-modal-panel').hasClass('bsm-inline-panel')){const destination=relatedMasterView(App.current,$('#bsm-form').data('module')||'');if(destination){openView(destination);return;}openView(App.current);return;}$('#bsm-modal').hide();});
$(document).on('submit','#bsm-company-settings-form',function(e){e.preventDefault();saveCompanySettings();});
$(document).on('click','#bsm-company-settings-edit',function(){openCompanySettingsEditor();});
$(document).on('click','#bsm-company-settings-cancel',function(){renderCompanySettings();});
$(document).on('click','#bsm-company-logo-upload-btn',async function(){
  const file=$('#bsm-company-logo-file')[0]?.files?.[0];
  if(!file){showToast('Please select a logo file',true);return;}
  const $btn=$(this).prop('disabled',true).text('Uploading...');
  const r=await uploadCompanyLogoFile(file,{reload:false,silent:false});
  $btn.prop('disabled',false).text('Upload Logo');
  if(r.success)$('#bsm-company-logo-file').val('');
});

$(document).on('click','#bsm-company-upi-qr-upload-btn',async function(){
  const file=$('#bsm-company-upi-qr-file')[0]?.files?.[0];
  if(!file){showToast('Please select a UPI QR image',true);return;}
  const $btn=$(this).prop('disabled',true).text('Uploading...');
  const r=await uploadCompanyUpiQrFile(file,{silent:false});
  $btn.prop('disabled',false).text('Upload UPI QR');
  if(r.success)$('#bsm-company-upi-qr-file').val('');
});

$(document).on('input','#bsm-purchase-approval-search',function(){const el=this;clearTimeout(App.pas);App.pas=setTimeout(()=>{App.specialSearch=$(el).val();drawPurchasePendingApproval();},350);});
$(document).on('click','.bsm-site-section-toggle',function(e){e.preventDefault();e.stopPropagation();$(this).closest('.bsm-site-section-multi').toggleClass('open');});
$(document).on('input','.bsm-site-section-search',function(){const q=String($(this).val()).toLowerCase();$(this).closest('.bsm-site-section-menu').find('.bsm-site-section-option').each(function(){$(this).toggle($(this).text().toLowerCase().includes(q));});});
$(document).on('change','.bsm-site-section-option input',function(){const $f=$(this).closest('.bsm-site-section-field'),names=$f.find('.bsm-site-section-option input:checked').map(function(){return $(this).val();}).get();$f.find('input[name="site_sections"]').val(names.join(', '));$f.find('.bsm-site-section-toggle').html(`${esc(names.length?names.join(', '):'Select Sections')} <span>⌄</span>`);});
$(document).on('click',function(e){if(!$(e.target).closest('.bsm-site-section-multi').length)$('.bsm-site-section-multi').removeClass('open');});
$(document).on('submit','#bsm-form',function(e){e.preventDefault();if($(this).data('module')==='users')saveUser();else if($(this).data('module')==='requisition_approval')saveApproval();else if($(this).data('module')==='purchase_approval')savePurchaseApproval();else saveForm();});
$(document).on('change','#bsm-form[data-module="site_receipts"] select[name="dispatch_id"]',function(){fillSiteReceiptFromDispatch($(this).val());});
$(document).on('change','#bsm-form[data-module="site_consumptions"] select[name="site_id"]',function(){refreshConsumptionItemOptions();});
$(document).on('change','#bsm-lines-body select[name="item_id"]',function(){const $tr=$(this).closest('tr');autoSelectUnitFromItem($tr);syncPurchaseItemPrice($tr,true);syncPurchaseItemGst($tr);});
$(document).on('change','#bsm-form[data-module="site_consumptions"] #bsm-lines-body select[name="item_id"]',function(){
  const $sel=$(this),val=String($sel.val()||'');
  if(val){let count=0;$('#bsm-lines-body select[name="item_id"]').each(function(){if(String($(this).val()||'')===val)count++;});if(count>1){$sel.val('');bsmRefreshSearchableSelect($sel[0]);showToast('Same Item cannot be repeated in Consumption Form',true);}}
  refreshConsumptionItemOptions();
  refreshConsumptionAvailability($(this).closest('tr'));
});
$(document).on('change','#bsm-form[data-module="material_returns"] #bsm-lines-body select[name="item_id"]',function(){
  refreshMaterialReturnStock($(this).closest('tr'));
  refreshMaterialReturnItemOptions();
});
$(document).on('change','#bsm-form[data-module="material_returns"] select[name="site_id"]',async function(){
  $('#bsm-lines-body select[name="item_id"]').val('').each(function(){bsmRefreshSearchableSelect(this);});
  await refreshMaterialReturnItemOptions();
  $('#bsm-lines-body .bsm-line').each(function(){refreshMaterialReturnStock($(this));});
});
$(document).on('input','#bsm-form[data-module="site_consumptions"] #bsm-lines-body input[name="qty"]',function(){const $tr=$(this).closest('tr');const q=Number($(this).val()||0),a=Number($tr.find('input[name="available_qty"]').val()||0);if(q>a+0.001)$(this).addClass('bsm-invalid');else $(this).removeClass('bsm-invalid');});
$(document).on('click','.bsm-open-purchase-form',async function(){await ensureOptions(optionTypesForView('material_inwards'));await openForm('material_inwards',0);$('#bsm-modal-title').text('Add Purchase');const $t=$('#bsm-form-fields select[name="inward_type"]');if($t.length){$t.val('purchase').prop('disabled',true);$t.closest('label').hide();setMaterialInwardMode('purchase');}});
$(document).on('input','#bsm-search',function(){if(App.current!=='purchase_register')return;const el=this;clearTimeout(App.sst);App.sst=setTimeout(()=>{App.search=$(el).val();App.page=1;loadPurchaseRegister();},600);});
$(document).on('click','.bsm-requisition-row',function(e){
  if($(e.target).closest('button,a,input,select,textarea').length)return;
  toggleRequisitionDetails($(this));
});
$(document).on('keydown','.bsm-requisition-row',function(e){
  if((e.key==='Enter'||e.key===' ')&&!$(e.target).is('button,a,input,select,textarea')){e.preventDefault();toggleRequisitionDetails($(this));}
});
$(document).on('click','#bsm-requisition-items-filter-toggle',()=>$('#bsm-requisition-items-filters').slideToggle(100));
$(document).on('change','#requisition_items_date_from,#requisition_items_date_to,#requisition_items_site',function(){App.page=1;loadMaterialRequisitionItem();});
$(document).on('change','#requisition_items_category',function(){requisitionItemsFilterItemOptions();App.page=1;loadMaterialRequisitionItem();});
$(document).on('change','#requisition_items_item',function(){requisitionItemsSyncCategoryFromItem();App.page=1;loadMaterialRequisitionItem();});
$(document).on('click','#requisition_items_clear_filters',function(){$('#bsm-requisition-items-filters input,#bsm-requisition-items-filters select').val('');bsmSyncAllSearchables($('#bsm-requisition-items-filters'));requisitionItemsFilterItemOptions();App.page=1;loadMaterialRequisitionItem();});
$(document).on('input','#bsm-requisition-items-search',function(){clearTimeout(App.mris);App.mris=setTimeout(()=>{App.page=1;loadMaterialRequisitionItem();},600);});
$(document).on('click','#bsm-requisition-items-export',exportMaterialRequisitionItem);
$(document).on('click','#bsm-dispatch-items-filter-toggle',()=>$('#bsm-dispatch-items-filters').slideToggle(100));
$(document).on('change','#dispatch_items_date_from,#dispatch_items_date_to,#dispatch_items_site',function(){App.page=1;loadDispatchItemsRegister();});
$(document).on('change','#dispatch_items_category',function(){dispatchItemsFilterItemOptions();App.page=1;loadDispatchItemsRegister();});
$(document).on('change','#dispatch_items_item',function(){dispatchItemsSyncCategoryFromItem();App.page=1;loadDispatchItemsRegister();});
$(document).on('click','#dispatch_items_clear_filters',function(){$('#bsm-dispatch-items-filters input,#bsm-dispatch-items-filters select').val('');bsmSyncAllSearchables($('#bsm-dispatch-items-filters'));dispatchItemsFilterItemOptions();App.page=1;loadDispatchItemsRegister();});
$(document).on('input','#bsm-dispatch-items-search',function(){clearTimeout(App.disis);App.disis=setTimeout(()=>{App.page=1;loadDispatchItemsRegister();},600);});
$(document).on('click','#bsm-dispatch-items-export',exportDispatchItemsRegister);
$(document).on('click','#bsm-site-receipt-items-filter-toggle',()=>$('#bsm-site-receipt-items-filters').slideToggle(100));
$(document).on('change','#site_receipt_items_date_from,#site_receipt_items_date_to,#site_receipt_items_site',function(){App.page=1;loadSiteMaterialItemReceipt();});
$(document).on('change','#site_receipt_items_category',function(){siteReceiptItemsFilterItemOptions();App.page=1;loadSiteMaterialItemReceipt();});
$(document).on('change','#site_receipt_items_item',function(){siteReceiptItemsSyncCategoryFromItem();App.page=1;loadSiteMaterialItemReceipt();});
$(document).on('click','#site_receipt_items_clear_filters',function(){$('#bsm-site-receipt-items-filters input,#bsm-site-receipt-items-filters select').val('');bsmSyncAllSearchables($('#bsm-site-receipt-items-filters'));siteReceiptItemsFilterItemOptions();App.page=1;loadSiteMaterialItemReceipt();});
$(document).on('input','#bsm-site-receipt-items-search',function(){clearTimeout(App.sriis);App.sriis=setTimeout(()=>{App.page=1;loadSiteMaterialItemReceipt();},600);});
$(document).on('click','#bsm-site-receipt-items-export',exportSiteMaterialItemReceipt);
$(document).on('click','#bsm-consumption-items-filter-toggle',()=>$('#bsm-consumption-items-filters').slideToggle(100));
$(document).on('change','#consumption_items_date_from,#consumption_items_date_to,#consumption_items_site',function(){App.page=1;loadConsumptionItemsRegister();});
$(document).on('change','#consumption_items_category',function(){consumptionItemsFilterItemOptions();App.page=1;loadConsumptionItemsRegister();});
$(document).on('change','#consumption_items_item',function(){consumptionItemsSyncCategoryFromItem();App.page=1;loadConsumptionItemsRegister();});
$(document).on('click','#consumption_items_clear_filters',function(){$('#bsm-consumption-items-filters input,#bsm-consumption-items-filters select').val('');bsmSyncAllSearchables($('#bsm-consumption-items-filters'));consumptionItemsFilterItemOptions();App.page=1;loadConsumptionItemsRegister();});
$(document).on('input','#bsm-consumption-items-search',function(){clearTimeout(App.cims);App.cims=setTimeout(()=>{App.page=1;loadConsumptionItemsRegister();},600);});
$(document).on('click','#bsm-consumption-items-export',exportConsumptionItemsRegister);
$(document).on('click','.bsm-purchase-row',function(e){
  if($(e.target).closest('button,a,input,select,textarea').length)return;
  togglePurchaseDetails($(this));
});
$(document).on('keydown','.bsm-purchase-row',function(e){
  if((e.key==='Enter'||e.key===' ')&&!$(e.target).is('button,a,input,select,textarea')){e.preventDefault();togglePurchaseDetails($(this));}
});
$(document).on('click','.bsm-dispatch-row',function(e){
  if($(e.target).closest('button,a,input,select,textarea').length)return;
  toggleDispatchDetails($(this));
});
$(document).on('keydown','.bsm-dispatch-row',function(e){
  if((e.key==='Enter'||e.key===' ')&&!$(e.target).is('button,a,input,select,textarea')){e.preventDefault();toggleDispatchDetails($(this));}
});
$(document).on('click','.sales-filter-toggle',function(){const k=$(this).data('kind');$(`#sales_${k}_filters`).slideToggle(100);});

$(document).on('click','.bsm-sales-pdf',async function(e){
  e.preventDefault();e.stopPropagation();
  const $b=$(this),id=Number($b.data('id')||0),module=String($b.data('module')||'');
  if(!id||!['quotations','tax_invoices'].includes(module))return;
  const oldText=$b.text();$b.prop('disabled',true).text('...');
  try{
    const r=await ajax('bsm_sales_pdf',{module,id});
    if(!r.success||!r.data?.url){showToast(r.data?.message||'Unable to generate PDF',true);return;}
    window.open(r.data.url,'_blank','noopener');
  }finally{$b.prop('disabled',false).text(oldText);}
});
$(document).on('change','[id^="sales_"][id$="_from"],[id^="sales_"][id$="_to"],[id^="sales_"][id$="_customer"],[id^="sales_"][id$="_site"]',function(){if(['quotation_master','quotation_items','invoice_master','invoice_items'].includes(App.current)){App.page=1;loadSalesRegister(App.current);}});
$(document).on('change','[id^="sales_"][id$="_category"]',function(){const kind=App.current,p=`sales_${kind}`;salesFilterItemOptions(p);App.page=1;loadSalesRegister(kind);});
$(document).on('change','[id^="sales_"][id$="_item"]',function(){if(['quotation_items','invoice_items'].includes(App.current)){App.page=1;loadSalesRegister(App.current);}});
$(document).on('click','.sales-clear',function(){const kind=$(this).data('kind'),p=`sales_${kind}`;$(`#${p}_filters input,#${p}_filters select`).val('');bsmSyncAllSearchables($(`#${p}_filters`));if(kind.endsWith('items'))salesFilterItemOptions(p);App.page=1;loadSalesRegister(kind);});
$(document).on('input','.sales-search',function(){const kind=$(this).data('kind');clearTimeout(App.salesTimer);App.salesTimer=setTimeout(()=>{App.page=1;loadSalesRegister(kind);},500);});
$(document).on('click','[id^="sales_"][id$="_export"]',function(){const id=this.id,kind=id.replace(/^sales_/,'').replace(/_export$/,'');exportSalesRegister(kind);});
$(document).on('click','.bsm-sales-master-row',function(e){if($(e.target).closest('button,a,input,select,textarea').length)return;toggleSalesDetails($(this));});
$(document).on('keydown','.bsm-sales-master-row',function(e){if((e.key==='Enter'||e.key===' ')&&!$(e.target).is('button,a,input,select,textarea')){e.preventDefault();toggleSalesDetails($(this));}});
$(document).on('change','#bsm-form[data-module="quotations"] select[name="customer_id"],#bsm-form[data-module="tax_invoices"] select[name="customer_id"]',function(){salesFillCustomer($(this).val());});
$(document).on('change','#bsm-form[data-module="quotations"] [name="place_of_supply_state_id"],#bsm-form[data-module="tax_invoices"] [name="place_of_supply_state_id"]',recalcSalesDocument);
$(document).on('input change','#bsm-form[data-module="quotations"] #bsm-lines-body [name="qty"],#bsm-form[data-module="quotations"] #bsm-lines-body [name="rate"],#bsm-form[data-module="quotations"] #bsm-lines-body [name="gst_rate"],#bsm-form[data-module="tax_invoices"] #bsm-lines-body [name="qty"],#bsm-form[data-module="tax_invoices"] #bsm-lines-body [name="rate"],#bsm-form[data-module="tax_invoices"] #bsm-lines-body [name="gst_rate"],#bsm-form[data-module="quotations"] #bsm-form-fields [name="other_charges"],#bsm-form[data-module="quotations"] #bsm-form-fields [name="gst_on_other_charge"],#bsm-form[data-module="quotations"] #bsm-form-fields [name="round_off"],#bsm-form[data-module="tax_invoices"] #bsm-form-fields [name="other_charges"],#bsm-form[data-module="tax_invoices"] #bsm-form-fields [name="gst_on_other_charge"],#bsm-form[data-module="tax_invoices"] #bsm-form-fields [name="round_off"],#bsm-quote-round',recalcSalesDocument);
$(document).on('change','#bsm-form[data-module="quotations"] #bsm-lines-body select[name="item_id"],#bsm-form[data-module="tax_invoices"] #bsm-lines-body select[name="item_id"]',function(){const $tr=$(this).closest('tr'),x=(App.options.items||[]).find(a=>String(a.id)===String($(this).val()));if(x){$tr.find('[name="hsn_sac"]').val(x.hsn_code||'');bsmSetSalesGstValue($tr,x.gst_rate);$tr.find('[name="gst_rate"]').trigger('change');if(x.unit_id){$tr.find('[name="unit_id"]').val(String(x.unit_id));bsmRefreshSearchableSelect($tr.find('[name="unit_id"]')[0]);}}recalcSalesDocument();});

$(document).on('input','#bsm-pending-invoice-search',function(){const el=this;clearTimeout(App.pist);App.pist=setTimeout(()=>{App.page=1;loadPendingInvoiceCopy();},600);});

$(document).on('click','.bsm-add-line-inline',function(){const module=$('#bsm-form').data('module')||App.current,cfg=App.configs[module];if(!cfg)return;addLine(cfg,{});if(module==='material_inwards')setMaterialInwardMode($('#bsm-form-fields select[name="inward_type"]').val()||'purchase');if(module==='site_consumptions')refreshConsumptionItemOptions();if(module==='material_returns'){const $tr=$('#bsm-lines-body .bsm-line').last();autoSelectUnitFromItem($tr);}if(module==='labour_attendance')refreshLabourAttendanceRows();});
$(document).on('click','.bsm-remove-line',function(){if($('#bsm-lines-body .bsm-line').length>1)$(this).closest('tr').remove();if(App.current==='labour_attendance')refreshLabourAttendanceRows();});
$(document).on('click','.bsm-quick-add',function(){quickAddMaster($(this).data('master'),$(this).data('target'));});
$(document).on('click','#bsm-quick-close,#bsm-quick-cancel',function(){$('#bsm-quick-modal').hide();});
$(document).on('submit','#bsm-quick-form',function(e){e.preventDefault();saveQuickMaster();});

$(document).on('input','.bsm-approved-qty',function(){
  const $tr=$(this).closest('tr'),rq=Number($tr.data('requested')||0);let aq=Number($(this).val()||0);
  if(aq<0)aq=0;if(aq>rq)aq=rq;
  $tr.find('.bsm-rejected-qty').text((rq-aq).toFixed(2).replace(/\.000$/,''));
});
$(document).on('click','.bsm-pending-approval-row',function(e){
  if($(e.target).closest('button,a,input,select,textarea').length)return;
  togglePendingApprovalDetails($(this));
});
$(document).on('keydown','.bsm-pending-approval-row',function(e){
  if((e.key==='Enter'||e.key===' ')&&!$(e.target).is('button,a,input,select,textarea')){e.preventDefault();togglePendingApprovalDetails($(this));}
});
$(document).on('click','.bsm-pending-dispatch-row',function(e){if($(e.target).closest('button,a,input,select,textarea').length)return;togglePendingDispatchDetails($(this));});
$(document).on('keydown','.bsm-pending-dispatch-row',function(e){if((e.key==='Enter'||e.key===' ')&&!$(e.target).is('button,a,input,select,textarea')){e.preventDefault();togglePendingDispatchDetails($(this));}});
$(document).on('click','.bsm-material-return-row',function(e){if($(e.target).closest('button,a,input,select,textarea').length)return;toggleMaterialReturnDetails($(this));});
$(document).on('keydown','.bsm-material-return-row',function(e){if((e.key==='Enter'||e.key===' ')&&!$(e.target).is('button,a,input,select,textarea')){e.preventDefault();toggleMaterialReturnDetails($(this));}});
$(document).on('click','.bsm-pending-return-row',function(e){if($(e.target).closest('button,a,input,select,textarea').length)return;toggleMaterialReturnDetails($(this),'bsm-pending-return-details-row');});
$(document).on('keydown','.bsm-pending-return-row',function(e){if((e.key==='Enter'||e.key===' ')&&!$(e.target).is('button,a,input,select,textarea')){e.preventDefault();toggleMaterialReturnDetails($(this),'bsm-pending-return-details-row');}});
$(document).on('click','.bsm-pending-site-receipt-row',function(e){if($(e.target).closest('button,a,input,select,textarea').length)return;togglePendingSiteReceiptDetails($(this));});
$(document).on('keydown','.bsm-pending-site-receipt-row',function(e){if((e.key==='Enter'||e.key===' ')&&!$(e.target).is('button,a,input,select,textarea')){e.preventDefault();togglePendingSiteReceiptDetails($(this));}});
$(document).on('click','.bsm-site-receipt-row',function(e){if($(e.target).closest('button,a,input,select,textarea').length)return;toggleSiteReceiptDetails($(this));});
$(document).on('keydown','.bsm-site-receipt-row',function(e){if((e.key==='Enter'||e.key===' ')&&!$(e.target).is('button,a,input,select,textarea')){e.preventDefault();toggleSiteReceiptDetails($(this));}});
$(document).on('click','.bsm-consumption-row',function(e){if($(e.target).closest('button,a,input,select,textarea').length)return;toggleConsumptionDetails($(this));});
$(document).on('keydown','.bsm-consumption-row',function(e){if((e.key==='Enter'||e.key===' ')&&!$(e.target).is('button,a,input,select,textarea')){e.preventDefault();toggleConsumptionDetails($(this));}});
$(document).on('click','.bsm-review-purchase',function(){openPurchaseApprovalForm($(this).data('id'));});
$(document).on('click','.bsm-review-req',function(){openApprovalForm($(this).data('id'));});
$(document).on('click','#bsm-reject-req',function(){saveApproval('reject');});
$(document).on('click','.bsm-dispatch-req',function(){openDispatchFromRequisition($(this).data('id'));});
$(document).on('click','#bsm-pending-approval-excel',function(){exportSpecialRows('Pending Approval',['Sl','Req. No.','Date','Site','Required Date','Requested By','Items'],(App.pendingApprovalRows||[]).map((x,i)=>[i+1,x.requisition_no,dateFmt(x.requisition_date),x.site_name,dateFmt(x.required_date),x.requested_by_name||'',x.item_count]));});
$(document).on('click','#bsm-pending-dispatch-excel',function(){exportSpecialRows('Pending Dispatch',['Sl','Req. No.','Site','Required Date','Approved Qty','Dispatched Qty','Pending Qty'],(App.pendingDispatchRows||[]).map((x,i)=>[i+1,x.requisition_no,x.site_name,dateFmt(x.required_date),x.approved_qty,x.dispatched_qty,x.pending_qty]));});
$(document).on('click','#bsm-user-add',()=>openUserForm());$(document).on('click','#bsm-user-export-excel',exportUsersExcel);$(document).on('click','.bsm-user-edit',function(){openUserForm(JSON.parse($(this).attr('data-row')));});
$(document).on('change','#bsm-form[data-module="users"] select[name="attendance_applicable"]',toggleUserSalaryFields);
$(document).on('click','.bsm-pa-btn',function(){const $w=$(this).closest('.bsm-pa-toggle');$w.find('.bsm-pa-btn').removeClass('active');$(this).addClass('active');$w.find('.bsm-pa-value').val($(this).data('value'));$(this).closest('tr[data-user-id]').addClass('bsm-att-row-updated');});
$(document).on('change','#bsm-office-att-date',loadOfficeDailyAttendance);
$(document).on('click','#bsm-office-mark-all-p',function(){$('#bsm-office-att-list .bsm-pa-toggle').each(function(){$(this).find('.bsm-pa-btn').removeClass('active').filter('[data-value="P"]').addClass('active');$(this).find('.bsm-pa-value').val('P');$(this).closest('tr[data-user-id]').addClass('bsm-att-row-updated');});});
$(document).on('click','#bsm-office-att-submit',saveOfficeDailyAttendance);

$(document).on('click','#bsm-office-att-filter-toggle',()=>$('#bsm-office-att-master-filters').slideToggle(100));
$(document).on('change','#office_att_date_from,#office_att_date_to,#office_att_user,#office_att_status',function(){App.page=1;loadOfficeAttendanceMaster();});
$(document).on('click','#office_att_clear',function(){$('#bsm-office-att-master-filters input,#bsm-office-att-master-filters select').val('');bsmSyncAllSearchables($('#bsm-office-att-master-filters'));App.page=1;loadOfficeAttendanceMaster();});
$(document).on('input','#bsm-office-att-search',function(){clearTimeout(App.oats);App.oats=setTimeout(()=>{App.page=1;loadOfficeAttendanceMaster();},500);});
$(document).on('click','#bsm-office-att-export',exportOfficeAttendanceMaster);
$(document).on('change','#bsm-salary-month',loadOfficeMonthlySalary);
$(document).on('click','#bsm-salary-export',exportOfficeMonthlySalary);

$(document).on('input','#ms_search',function(){clearTimeout(App.msst);App.msst=setTimeout(drawMaterialStock,600);});
$(document).on('change','#ms_site,#ms_warehouse,#ms_item,#ms_date_from,#ms_date_to',()=>loadMaterialStock());
$(document).on('click','#ms_clear',function(){$('#ms_site,#ms_warehouse,#ms_item,#ms_date_from,#ms_date_to,#ms_search').val('');bsmSyncAllSearchables($('.bsm-stock-filters'));loadMaterialStock();});
$(document).on('click','#ms_export',exportMaterialStock);
// Reports refresh automatically when any report filter changes; no Run button is required.
$(document).on('change','#r_site,#r_contractor,#r_date_from,#r_date_to',function(){
  if(App.current && reportConfigs[App.current]) loadReport(App.current);
});
$(document).on('click','.bsm-update-paid-amount',async function(){
  const $tr=$(this).closest('tr'),id=$(this).data('id');
  const advance_adjustment=$tr.find('.bsm-new-advance-adjustment-input').val()||0;
  const paid_amount=$tr.find('.bsm-new-paid-amount-input').val()||0;
  if(Number(advance_adjustment)<=0 && Number(paid_amount)<=0)return showToast('Please enter Advance Adjustment or Paid Amount',true);
  const r=await ajax('bsm_update_contractor_paid_amount',{id,advance_adjustment,paid_amount});
  if(!r.success)return showToast(r.data?.message||'Unable to update payment',true);
  showToast(r.data?.message||'Updated successfully');
  await loadReport('pending_contractor_payment');
});
$(document).on('click','#bsm-clear-report',async function(){
  $('#r_search').val('');
  if(App.current==='report_labour'){const today=String(BSM.today||'');$('#r_date_from,#r_date_to').val(today);}else{$('#r_date_from,#r_date_to').val('');}
  $('#r_site,#r_contractor').val('').each(function(){bsmRefreshSearchableSelect(this);});
  if(App.current==='report_labour') await loadReport(App.current);
  drawReportRows(App.current);
});
$(document).on('input','#bsm-pending-contractor-bill-search',function(){const el=this;clearTimeout(App.pcbt);App.pcbt=setTimeout(()=>{App.page=1;loadPendingContractorBillCopy();},600);});
$(document).on('click','.bsm-upload-pending-contractor-bill',async function(){
  const $tr=$(this).closest('tr'),file=$tr.find('.bsm-pending-contractor-bill-file')[0]?.files?.[0],id=$(this).data('id');
  if(!file)return showToast('Please select the Labour Contractor Bill file.',true);
  const fd=new FormData();fd.append('action','bsm_upload');fd.append('nonce',BSM.nonce);fd.append('module','contractor_bills');fd.append('reference_id',id);fd.append('document_type','Contractor Bill');fd.append('file',file);
  const r=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});
  if(!r.success)return showToast(r.data?.message||'Upload failed',true);
  showToast('Labour Contractor Bill uploaded successfully.');
  await loadPendingContractorBillCopy();
});
$(document).on('change','#inv_warehouse,#inv_site',loadInventoryView);
$(document).on('change','#inv_category',function(){inventoryFilterItemOptions();loadInventoryView();});
$(document).on('change','#inv_item',function(){inventorySyncCategoryFromItem();loadInventoryView();});
$(document).on('input','#inv_search',function(){clearTimeout(App.invs);App.invs=setTimeout(loadInventoryView,300);});
$(document).on('click','#inv_clear',function(){$('#inv_search,#inv_warehouse,#inv_site,#inv_category,#inv_item').val('');bsmSyncAllSearchables($('#bsm-content'));inventoryFilterItemOptions();loadInventoryView();});
$(document).on('click','#inv_export',exportInventoryView);
$(document).on('change','#outward_date_from,#outward_date_to,#outward_site,#outward_item',loadDailyOutwardSummary);
$(document).on('input','#outward_search',function(){clearTimeout(App.outws);App.outws=setTimeout(loadDailyOutwardSummary,300);});
$(document).on('click','#outward_clear',function(){const t=String(BSM.today||'');$('#outward_date_from,#outward_date_to').val(t);$('#outward_site,#outward_item,#outward_search').val('');bsmSyncAllSearchables($('#bsm-content'));loadDailyOutwardSummary();});
$(document).on('click','#outward_export',exportDailyOutwardSummary);
$(document).on('change','#expense_summary_date_from,#expense_summary_date_to,#expense_summary_site,#expense_summary_category,#expense_summary_vendor',loadDailyExpenseSummary);
$(document).on('input','#expense_summary_search',function(){clearTimeout(App.expss);App.expss=setTimeout(loadDailyExpenseSummary,300);});
$(document).on('click','#expense_summary_clear',function(){const t=String(BSM.today||'');$('#expense_summary_date_from,#expense_summary_date_to').val(t);$('#expense_summary_site,#expense_summary_category,#expense_summary_vendor,#expense_summary_search').val('');bsmSyncAllSearchables($('#bsm-content'));loadDailyExpenseSummary();});
$(document).on('click','#expense_summary_export',exportDailyExpenseSummary);
$(document).on('input','#bsm-pending-challan-search',function(){clearTimeout(App.pchs);App.pchs=setTimeout(()=>{App.page=1;loadPendingSiteReceiveChallans();},500);});
$(document).on('click','.bsm-upload-pending-receive-challan',async function(){const $tr=$(this).closest('tr'),file=$tr.find('.bsm-pending-receive-challan-file')[0]?.files?.[0],id=$(this).data('id');if(!file)return showToast('Please select the Receiving Challan file.',true);const fd=new FormData();fd.append('action','bsm_upload');fd.append('nonce',BSM.nonce);fd.append('module','site_receipts');fd.append('reference_id',id);fd.append('document_type','Receiving Challan');fd.append('file',file);const r=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});if(!r.success)return showToast(r.data?.message||'Upload failed',true);showToast('Receiving Challan uploaded successfully.');await loadPendingSiteReceiveChallans();});
$(document).on('change','#bsm-form[data-module="material_inwards"] [name="bill_type"]',function(){
  const vendor=$('#bsm-form-fields [name="vendor_id"]').val();
  if(vendor)loadMaterialInwardVendorGST(vendor);else applyMaterialInwardGSTType('no_gst');
  const noGst=$(this).val()==='non_gst_bill';
  $('#bsm-form-fields [name="other_expense_gst"]').prop('readonly',noGst).val(noGst?'0.00':'');
  recalcMaterialInwardTotals();
});
$(document).on('click','.bsm-upload-pending-invoice',async function(){
  const $tr=$(this).closest('tr'),file=$tr.find('.bsm-pending-invoice-file')[0]?.files?.[0],id=$(this).data('id');
  if(!file)return showToast('Please select the Supplier Invoice file.',true);
  const billType=$tr.find('.bsm-pending-bill-type').val();
  if(!billType)return showToast('Please select Bill Type before uploading the invoice.',true);
  const fd=new FormData();fd.append('action','bsm_upload');fd.append('nonce',BSM.nonce);fd.append('module','material_inwards');fd.append('reference_id',id);fd.append('document_type','Supplier Invoice');fd.append('bill_type',billType);fd.append('file',file);
  const r=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});
  if(!r.success)return showToast(r.data?.message||'Upload failed',true);
  showToast('Supplier Invoice uploaded successfully.');
  await loadPendingInvoiceCopy();
});
$(document).on('click','#bsm-clear-report',function(){$('#r_search,#r_date_from,#r_date_to,#r_site,#r_contractor').val('');loadReport(App.current);});
$(document).on('click','#bsm-report-export-excel',exportReportExcel);$(document).on('click','#bsm-export-excel',exportCurrentList);
$(document).on('click','#bsm-upload-doc',async function(){const file=$('#bsm-doc-file')[0]?.files?.[0];if(!file)return showToast('Select a file',true);const fd=new FormData();fd.append('action','bsm_upload');fd.append('nonce',BSM.nonce);fd.append('module',App.current);fd.append('reference_id',App.editId);fd.append('document_type',$('#bsm-doc-type').val()||'Document');fd.append('file',file);const r=await $.ajax({url:BSM.ajax,method:'POST',data:fd,processData:false,contentType:false});if(r.success){showToast('Document uploaded');openForm(App.current,App.editId);}else showToast(r.data?.message||'Upload failed',true);});
$(document).on('click','#bsm-menu-open',()=>$('.bsm-sidebar').addClass('open'));$(document).on('click','#bsm-menu-close',()=>$('.bsm-sidebar').removeClass('open'));
$(document).on('click','#bsm-sidebar-toggle',function(){
  const collapsed=$('#bsm-shell').toggleClass('bsm-sidebar-collapsed').hasClass('bsm-sidebar-collapsed');
  try{localStorage.setItem('bsmSidebarCollapsed',collapsed?'1':'0');}catch(e){}
  $(this).attr('title',collapsed?'Show Menu':'Collapse Menu').attr('aria-label',collapsed?'Show Menu':'Collapse Menu');
});
$(document).on('click','#bsm-logout',async function(){await ajax('bsm_logout');location.reload();});

$(document).on('click','#bsm-toggle-password',function(){
  const $input=$('#bsm-login-password');
  const showing=$input.attr('type')==='text';
  $input.attr('type',showing?'password':'text');
  $(this).attr('aria-label',showing?'Show password':'Hide password').attr('title',showing?'Show password':'Hide password');
  $(this).toggleClass('is-visible',!showing);
});
$(document).on('submit','#bsm-login-form',async function(e){
  e.preventDefault();
  const $form=$(this),$btn=$form.find('button[type="submit"]');
  $form.find('.bsm-form-msg').text('');
  $btn.prop('disabled',true);
  try{
    // Login intentionally does not send the normal app nonce; the user has no authenticated
    // session yet and cached login pages can otherwise trigger WordPress HTTP 403 responses.
    const r=await $.post(BSM.ajax,{action:'bsm_login',user_id:$form.find('[name=user_id]').val(),password:$form.find('[name=password]').val()});
    if(r&&r.success) window.location.href=(r.data&&r.data.redirect)?r.data.redirect:window.location.href;
    else $form.find('.bsm-form-msg').text(r?.data?.message||'Invalid User ID or Password');
  }catch(xhr){
    const msg=xhr?.responseJSON?.data?.message||'Unable to login. Please try again.';
    $form.find('.bsm-form-msg').text(msg);
  }finally{$btn.prop('disabled',false);}
});

async function init(){
  bsmSelectObserver.observe(document.body,{childList:true,subtree:true,attributes:true,attributeFilter:['disabled']});
  bsmEnhanceSearchableSelects(document);
  App.current=BSM.initialView||'dashboard';
  if(!BSM.loggedIn){$('#bsm-login').show();return;}
  $('#bsm-shell').show();
  try{if(window.innerWidth>760 && localStorage.getItem('bsmSidebarCollapsed')==='1') $('#bsm-shell').addClass('bsm-sidebar-collapsed');}catch(e){}
  $('#bsm-sidebar-toggle').attr('title',$('#bsm-shell').hasClass('bsm-sidebar-collapsed')?'Show Menu':'Collapse Menu').attr('aria-label',$('#bsm-shell').hasClass('bsm-sidebar-collapsed')?'Show Menu':'Collapse Menu');
  buildNav();$('#bsm-sidebar-user').text(BSM.currentUser?.name||'');
  openView(App.current);
}
$(init);


$(document).on('click','.bsm-receive-return',function(){openReturnReceipt($(this).data('id'));});
$(document).on('click','.bsm-receive-dispatch',function(){openSiteReceiptFromDispatch($(this).data('id'));});
$(document).on('click','#bsm-pending-material-receipt-excel',function(){exportSpecialRows('Pending Material Receipt',['Sl','Return No.','Date','Site','Warehouse','Return By'],(App.pendingMaterialReceiptRows||[]).map((x,i)=>[i+1,x.return_no,dateFmt(x.return_date),optionLabel('sites',x.site_id),optionLabel('warehouses',x.warehouse_id),optionLabel('users',x.return_by)]));});
$(document).on('click','#bsm-pending-site-receipt-excel',function(){exportSpecialRows('Pending Site Receipt',['Sl','Dispatch No.','Date','Site','Warehouse'],(App.pendingSiteReceiptRows||[]).map((x,i)=>[i+1,x.dispatch_no,dateFmt(x.dispatch_date),optionLabel('sites',x.site_id),optionLabel('warehouses',x.warehouse_id)]));});

$(document).on('change','#bsm-form[data-module="labour_attendance"] #bsm-lines-body select[name="contractor_id"]',async function(){refreshLabourAttendanceRows();await validateExistingLabourAttendance($(this));});
$(document).on('input','#bsm-form[data-module="labour_attendance"] #bsm-lines-body input[name="no_of_labour"]',refreshLabourAttendanceRows);
$(document).on('change','#bsm-form[data-module="labour_attendance"] #bsm-form-fields select[name="site_id"], #bsm-form[data-module="labour_attendance"] #bsm-form-fields input[name="attendance_date"]',async function(){
  for(const el of $('#bsm-lines-body select[name="contractor_id"]').toArray()){
    if($(el).val()) await validateExistingLabourAttendance($(el));
  }
});

// Keep native status dropdown clear if this control exists in older cached screens.
$(document).on('mousedown focus', '.bsm-payment-status', function(){
  $(this).closest('.bsm-table-wrap').addClass('bsm-status-dropdown-open');
});
$(document).on('change blur', '.bsm-payment-status', function(){
  const $wrap=$(this).closest('.bsm-table-wrap');
  setTimeout(()=>{$wrap.removeClass('bsm-status-dropdown-open');},120);
});

$(document).on('change','#bsm-form[data-module="contractor_bills"] select[name="contractor_id"],#bsm-form[data-module="contractor_bills"] select[name="site_id"]',refreshContractorAvailableAdvance);
$(document).on('input','#bsm-form[data-module="contractor_bills"] input[name="advance_adjustment"]',function(){const a=Number($('#bsm-form-fields input[name="available_advance"]').val()||0),v=Number($(this).val()||0);$(this).toggleClass('bsm-invalid',v<0||v>a+0.009);});
$(document).on('input change','#bsm-form[data-module="contractor_bills"] [name="taxable_amount"], #bsm-form[data-module="contractor_bills"] [name="gst_amount"]',recalcContractorBillTotal);

$(document).on('change','#bsm-vo-site,#bsm-vo-vendor,#bsm-vo-date-from,#bsm-vo-date-to',loadVendorOutstanding);
$(document).on('input','#bsm-vo-search',function(){clearTimeout(App.vos);App.vos=setTimeout(loadVendorOutstanding,400);});
$(document).on('click','#bsm-vo-clear',function(){$('#bsm-vo-site,#bsm-vo-vendor,#bsm-vo-date-from,#bsm-vo-date-to,#bsm-vo-search').val('');loadVendorOutstanding();});
$(document).on('click','#bsm-vo-export',async function(){const rows=await loadVendorOutstanding(true);if(!rows)return;downloadExcelFile('Supplier Outstanding',['Sl','Supplier','Pending POs','Purchase Amount','Paid Amount','Outstanding'],rows.map((x,i)=>[i+1,x.vendor_name||'',x.purchase_count||0,x.purchase_amount||0,x.paid_amount||0,x.outstanding||0]));});
$(document).on('click','.bsm-vendor-pay',function(){return renderVendorPaymentForm({vendor_id:$(this).data('vendor')});});
$(document).on('change','.bsm-pay-select',function(){const $tr=$(this).closest('tr'),$amt=$tr.find('.bsm-paying-now');$amt.prop('disabled',!this.checked);if(this.checked&&Number($amt.val()||0)<=0)$amt.val(Number($tr.data('outstanding')||0).toFixed(2));if(!this.checked)$amt.val('0.00');recalcVendorPaymentTotal();});
$(document).on('click','#bsm-save-vendor-payment',saveVendorPayment);
$(document).on('click','#bsm-cancel-vendor-payment',function(){openView('report_vendor_outstanding');});

// Workforce and inter-site operations. Uses existing application navigation and AJAX nonce.
let opsMeta={},opsRows=[],opsTransfer=null,opsStock=[];
const opsToday=()=>BSM.today||new Date().toISOString().slice(0,10);
const opsOpts=(rows,value='',empty='Select')=>`<option value="">${esc(empty)}</option>`+(rows||[]).map(r=>`<option value="${esc(r.id)}" ${String(r.id)===String(value)?'selected':''}>${esc(r.label||r.name||r.employee_name)}</option>`).join('');
const opsSelect=(id,label,rows,value='',required=false)=>`<label>${esc(label)}<select id="${id}" ${required?'required':''}>${opsOpts(rows,value)}</select></label>`;
const opsDate=(id,label,value=opsToday())=>`<label>${label}<input type="date" lang="en-GB" class="bsm-date-input bsm-native-date" id="${id}" value="${esc(value)}" required></label>`;
const opsText=(id,label,value='',type='text')=>`<label>${label}<input type="${type}" id="${id}" value="${esc(value)}" ${type==='number'?'min="0" step="0.01"':''}></label>`;
async function opsCall(op,data={}){const r=await ajax('bsm_operations',{op,data:JSON.stringify(data)});if(!r.success)throw new Error(r.data?.message||'Unable to complete the operation.');return r.data||{};}
function opsEnhance(){bsmEnhanceSearchableSelects($('#bsm-content'));}
function opsTable(headers,body){return `<div class="bsm-table-wrap"><table class="bsm-table"><thead><tr>${headers.map(h=>`<th>${h}</th>`).join('')}</tr></thead><tbody>${body||`<tr><td colspan="${headers.length}">No records found.</td></tr>`}</tbody></table></div>`;}
function opsButton(action,label,id='',extra=''){return `<button type="button" class="bsm-btn" data-ops="${action}" data-id="${esc(id)}" ${extra}>${label}</button>`;}
async function renderOperations(key){
  opsMeta=await opsCall('meta');
  if(key==='holiday_list')return opsHolidays();
  if(key==='time_sheet')return opsTimeForm();
  if(key==='time_sheet_master')return opsTimeMaster();
  if(key==='contractor_employees')return opsWorkers();
  if(key==='contractor_calculated_bill')return opsBill();
  if(key==='site_transfer_form')return opsTransferForm();
  return opsTransfers();
}
async function opsHolidays(){
  setTitle('Holiday List');opsRows=(await opsCall('holiday_list')).rows;
  $('#bsm-content').html(`${opsMeta.admin?`<div class="bsm-toolbar"><div class="bsm-spacer"></div>${opsButton('holiday-add','+ Add')}</div>`:''}<div class="bsm-panel">${opsTable(['Date','Holiday','Applies To',...(opsMeta.admin?['Action']:[])],opsRows.map(h=>{const ids=JSON.parse(h.employee_ids||'[]');return `<tr><td>${dateFmt(h.holiday_date)}</td><td>${esc(h.holiday_name)}</td><td>${ids.length?esc(ids.map(id=>(opsMeta.employees.find(e=>+e.id===+id)||{}).label||'Employee #'+id).join(', ')):'All Employees'}</td>${opsMeta.admin?`<td>${opsButton('holiday-edit','Edit',h.id)} ${opsButton('holiday-delete','Delete',h.id)}</td>`:''}</tr>`;}).join(''))}</div>`);
}
function opsHolidayForm(holiday={}){
  if(!opsMeta.admin)return;
  setTitle(holiday.id?'Edit Holiday':'Add Holiday');
  const ids=JSON.parse(holiday.employee_ids||'[]').map(String);
  $('#bsm-content').html(`<div class="bsm-panel"><form id="ops-holiday-form"><input type="hidden" id="ops-holiday-id" value="${Number(holiday.id||0)}"><div class="bsm-form-grid">${opsDate('ops-holiday-date','Date',holiday.holiday_date||opsToday())}${opsText('ops-holiday-name','Holiday Name',holiday.holiday_name||'')}<label>Applicability<select id="ops-holiday-scope"><option value="all" ${ids.length?'':'selected'}>All Employees</option><option value="selected" ${ids.length?'selected':''}>Selected Employees</option></select></label><label id="ops-holiday-employees-wrap" ${ids.length?'':'hidden'}>Employees<select id="ops-holiday-employees" multiple size="6" class="bsm-native-gst-rate">${opsMeta.employees.map(e=>`<option value="${e.id}" ${ids.includes(String(e.id))?'selected':''}>${esc(e.label)}</option>`).join('')}</select></label></div><div class="bsm-form-actions">${opsButton('holiday-clear','Cancel')}<button class="bsm-btn bsm-btn-primary">Save Holiday</button></div></form></div>`);opsEnhance();
}
async function opsWorkers(){
  setTitle('Contractor Employees');
  $('#bsm-content').html(`${opsMeta.admin?`<div class="bsm-panel"><form id="ops-worker-form"><input type="hidden" id="ops-worker-id"><div class="bsm-form-grid">${opsSelect('ops-worker-contractor','Labour Contractor',App.options.contractors)}${opsText('ops-worker-name','Name')}${opsText('ops-worker-mobile','Mobile')}${opsText('ops-worker-address','Address')}${opsText('ops-worker-bank','Bank')}${opsText('ops-worker-ifsc','IFSC')}${opsText('ops-worker-account','AC No.')}${opsText('ops-worker-daily','Daily Rate','','number')}${opsText('ops-worker-hourly','Hourly Rate','','number')}<label>Status<select id="ops-worker-status"><option value="active">Active</option><option value="inactive">Inactive</option></select></label></div><div class="bsm-form-actions"><button class="bsm-btn bsm-btn-primary">Save Employee</button>${opsButton('worker-clear','Clear')}</div></form></div>`:''}<div class="bsm-panel">${opsTable(['Contractor','Name','Mobile','Address','Bank','IFSC','AC No.','Daily Rate','Hourly Rate','Status',...(opsMeta.admin?['Action']:[])],opsMeta.workers.map(w=>`<tr><td>${esc(w.contractor_name)}</td><td>${esc(w.employee_name)}</td><td>${esc(w.mobile||'')}</td><td>${esc(w.address||'')}</td><td>${esc(w.bank_name||'')}</td><td>${esc(w.ifsc||'')}</td><td>${esc(w.account_number||'')}</td><td>${esc(w.daily_rate)}</td><td>${esc(w.hourly_rate)}</td><td>${esc(w.status)}</td>${opsMeta.admin?`<td>${opsButton('worker-edit','Edit',w.id)}</td>`:''}</tr>`).join(''))}</div>`);opsEnhance();
}
async function opsTimeForm(){
  setTitle('Time Sheet');
  $('#bsm-content').html(`<div class="bsm-panel"><form id="ops-time-form"><div class="bsm-form-grid">${opsDate('ops-time-date','Date')}<label>Employee Type<select id="ops-time-type"><option value="office">Office Employee</option>${opsMeta.can_punch_contractor?'<option value="contractor">Contractor Employee</option>':''}</select></label><label id="ops-time-contractor-wrap" hidden>Contractor Name<select id="ops-time-contractor" disabled>${opsOpts(App.options.contractors)}</select></label>${opsSelect('ops-time-employee','Employee Name',opsMeta.employees,'',true)}<label><span id="ops-time-site-label">Site</span><select id="ops-time-site">${opsOpts(App.options.sites)}</select></label></div><div id="ops-time-grid" hidden><div class="bsm-toolbar"><span id="ops-time-total"></span><div class="bsm-spacer"></div>${opsButton('time-add','+ Add Row')}</div>${opsTable(['From Time','To Time','Activity'],'<tr></tr>')}</div><div class="bsm-form-actions">${opsButton('time-cancel','Cancel')}<button class="bsm-btn bsm-btn-primary">Save Time Sheet</button></div></form></div>`);opsEnhance();await opsLoadTimeDay();
}
function opsTimeRow(r={}){const locked=r.status==='approved'||+r.bill_id;return `<tr data-id="${r.id||0}" data-locked="${locked?1:0}"><td><input type="time" class="ops-time-from" required value="${esc((r.from_time||'').slice(0,5))}" ${locked?'disabled':''}></td><td><input type="time" class="ops-time-to" required value="${esc((r.to_time||'').slice(0,5))}" ${locked?'disabled':''}></td><td><div class="ops-activity"><textarea class="ops-time-activity" rows="1" required aria-label="Activity" ${locked?'disabled':''}>${esc(r.activity||'')}</textarea>${locked?`<span>Approved${+r.bill_id?' / Billed':''}</span>`:opsButton('time-remove','×','','aria-label="Delete activity row"')}</div></td></tr>`;}
async function opsLoadTimeDay(){
  const employee=$('#ops-time-employee').val(),date=$('#ops-time-date').val();$('#ops-time-grid').prop('hidden',!employee);if(!employee)return;
  const contractor=$('#ops-time-type').val()==='contractor';const data={from:date,to:date,[contractor?'worker_id':'employee_id']:employee};const rows=(await opsCall('time_list',data)).rows;
  $('#ops-time-grid tbody').html((rows.length?rows:[{}]).map(opsTimeRow).join(''));if(rows.length)$('#ops-time-site').val(rows[0].site_id||'');bsmSyncAllSearchables($('#bsm-content'));opsTimeTotal();opsResizeActivities();
}
function opsResizeActivity(el){
  el.style.height='auto';
  el.style.height=Math.max(36,el.scrollHeight+2)+'px';
}
function opsResizeActivities(){ $('#ops-time-grid .ops-time-activity').each(function(){opsResizeActivity(this);}); }
$(document).on('input','.ops-time-activity',function(){opsResizeActivity(this);});
$(window).on('resize',opsResizeActivities);
function opsTimeTotal(){let minutes=0;$('#ops-time-grid tbody tr').each(function(){const a=$(this).find('.ops-time-from').val(),b=$(this).find('.ops-time-to').val();if(a&&b&&b!==a){const n=x=>+x.slice(0,2)*60+(+x.slice(3,5));minutes+=(n(b)-n(a)+1440)%1440;}});$('#ops-time-total').text(`Total: ${Math.floor(minutes/60)}h ${minutes%60}m`);}
let opsTimeRequest=0,opsTimeLoading=false,opsTimeGroups=[];
const opsTimeHeaders=['Date','Employee','Contractor','Site','From','To','Hours','Activity'];
const opsTimeCells=r=>[dateFmt(r.work_date),r.employee_name,r.contractor_name||'',r.site_name||'',r.from_time.slice(0,5),r.to_time.slice(0,5),(+r.minutes/60).toFixed(2),r.activity];
function opsTimeIcon(type){
  return `<svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M14 2H5v20h14V7z M14 2v5h5"/>${type==='pdf'?'<path d="M8 16h8 M12 10v7 m-3-3 3 3 3-3"/>':'<path d="m8 11 7 7 m0-7-7 7"/>'}</svg>`;
}
async function opsTimeMaster(){
  setTitle('Time Sheet Master');
  const employees=[...opsMeta.employees.map(e=>({...e,id:'e:'+e.id})),...opsMeta.workers.map(w=>({id:'w:'+w.id,label:w.employee_name+' — '+w.contractor_name}))];
  $('#bsm-content').html(`<div class="bsm-time-filters">${opsDate('ops-time-filter-from','From Date',opsToday().slice(0,7)+'-01')}${opsDate('ops-time-filter-to','To Date')}${opsSelect('ops-time-filter-employee','Employee',employees)}${opsSelect('ops-time-filter-contractor','Contractor',App.options.contractors)}${opsSelect('ops-time-filter-site','Site',App.options.sites)}<label>Group By<select id="ops-time-group"><option value="">None</option><option value="employee">Employee</option><option value="site">Site</option><option value="contractor">Contractor</option><option value="date">Date</option></select></label><div class="ops-time-downloads">${opsButton('time-pdf',opsTimeIcon('pdf'),'','title="Download PDF" aria-label="Download PDF"')}${opsButton('time-export',opsTimeIcon('excel'),'','title="Download Excel" aria-label="Download Excel"')}${opsMeta.admin||(BSM.currentUser?.allowed_views||[]).includes('time_sheet')?opsButton('time-new','+ Add'):''}</div></div><div class="bsm-panel" id="ops-time-list"></div>`);opsEnhance();await opsLoadTimeMaster();
}
async function opsLoadTimeMaster(){
  const request=++opsTimeRequest,from=$('#ops-time-filter-from').val(),to=$('#ops-time-filter-to').val();
  if(!from||!to||from>to){opsTimeLoading=false;$('.ops-time-downloads [data-ops="time-pdf"],.ops-time-downloads [data-ops="time-export"]').prop('disabled',true);opsRows=[];opsTimeGroups=[];$('#ops-time-list').html('<p class="ops-time-note">Select a valid date range.</p>');return;}
  opsTimeLoading=true;$('.ops-time-downloads [data-ops="time-pdf"],.ops-time-downloads [data-ops="time-export"]').prop('disabled',true);
  try{
    const employee=String($('#ops-time-filter-employee').val()||'').split(':');
    const result=await opsCall('time_list',{from,to,employee_id:employee[0]==='e'?employee[1]:'',worker_id:employee[0]==='w'?employee[1]:'',contractor_id:$('#ops-time-filter-contractor').val(),site_id:$('#ops-time-filter-site').val()});
    if(request!==opsTimeRequest||App.current!=='time_sheet_master')return;
    opsRows=result.rows;opsRenderTimeMaster();
  }catch(error){if(request===opsTimeRequest){opsRows=[];opsTimeGroups=[];$('#ops-time-list').html('<p class="ops-time-note">Unable to load Time Sheets. Change a filter to retry.</p>');}throw error;
  }finally{if(request===opsTimeRequest){opsTimeLoading=false;$('.ops-time-downloads [data-ops="time-pdf"],.ops-time-downloads [data-ops="time-export"]').prop('disabled',false);}}
}
function opsRenderTimeMaster(){
  const group=$('#ops-time-group').val(),groups=new Map();
  for(const r of opsRows){const key=group==='employee'?(+r.worker_id?'w'+r.worker_id:'e'+r.employee_id):group==='site'?r.site_id:group==='contractor'?r.contractor_id:group==='date'?r.work_date:'';const label=group==='employee'?r.employee_name:group==='site'?(r.site_name||'No Site'):group==='contractor'?(r.contractor_name||'Office Employees'):group==='date'?dateFmt(r.work_date):'';if(!groups.has(String(key)))groups.set(String(key),{label,rows:[]});groups.get(String(key)).rows.push(r);}
  opsTimeGroups=[...groups.values()];
  let body='';
  for(const g of opsTimeGroups){
    const hours=(g.rows.reduce((n,r)=>n+(+r.minutes),0)/60).toFixed(2);
    if(group)body+=`<tr class="ops-time-group-row"><td colspan="9">${esc(g.label)} <span>${g.rows.length} entries · ${hours} hours</span></td></tr>`;
    body+=g.rows.map(r=>{const canChange=!Number(r.bill_id)&&(opsMeta.admin||r.status==='submitted')&&(!Number(r.worker_id)||opsMeta.can_punch_contractor);const disabled=canChange?'':'disabled title="This entry is locked"';return `<tr>${opsTimeCells(r).map(v=>`<td>${esc(v)}</td>`).join('')}<td><div class="ops-time-actions">${opsButton('time-edit','Edit',r.id,disabled)}${opsButton('time-delete','Delete',r.id,disabled)}</div></td></tr>`;}).join('');
  }
  $('#ops-time-list').html(opsTable([...opsTimeHeaders,'Action'],body)+`<div class="ops-time-summary">${opsRows.length} entries <strong>Total: ${(opsRows.reduce((n,r)=>n+(+r.minutes),0)/60).toFixed(2)} hours</strong></div>`);
}
function opsTimeExportRows(){
  const grouped=!!$('#ops-time-group').val(),rows=[];
  for(const g of opsTimeGroups){if(grouped)rows.push([g.label,'','','','','',(g.rows.reduce((n,r)=>n+(+r.minutes),0)/60).toFixed(2)+' hours','']);rows.push(...g.rows.map(opsTimeCells));}
  rows.push(['Total','','','','','',(opsRows.reduce((n,r)=>n+(+r.minutes),0)/60).toFixed(2),'']);return rows;
}
// Small standalone PDF writer: landscape A4, wrapped cells and repeated page headers.
function opsTimePdf(){
  if(opsTimeLoading)return;
  const widths=[66,100,100,80,42,42,45,215],left=26,top=554,line=11,pages=[];
  const pdfText=v=>String(v??'').normalize('NFKD').replace(/[^\x20-\x7e]/g,' ').replace(/([\\()])/g,'\\$1');
  const wrap=(value,width)=>{const size=Math.max(4,Math.floor(width/4.5)),words=String(value??'').split(/\s+/),lines=[];let current='';for(let word of words){while(word.length>size){if(current){lines.push(current);current='';}lines.push(word.slice(0,size));word=word.slice(size);}if(current.length+word.length+1>size){lines.push(current);current='';}current+=(current?' ':'')+word;}lines.push(current);return lines;};
  let commands='',y=top;
  const text=(x,y,value,bold=false)=>`BT /${bold?'F2':'F1'} 8 Tf ${x} ${y} Td (${pdfText(value)}) Tj ET\n`;
  function grid(rowTop,bottom,merged=false){
    commands+=`0.55 0.64 0.76 RG 0.5 w ${left} ${bottom} 790 ${rowTop-bottom} re S\n`;
    let x=left;for(let i=0;i<widths.length-1;i++){x+=widths[i];if(!merged)commands+=`${x} ${bottom} m ${x} ${rowTop} l S\n`;}
    if(merged)commands+=`690 ${bottom} m 690 ${rowTop} l S\n`;
    commands+='0 G\n';
  }
  function header(){commands=text(left,574,'Time Sheet Master',true)+text(left,558,dateFmt($('#ops-time-filter-from').val())+' to '+dateFmt($('#ops-time-filter-to').val()));commands+='0.07 0.20 0.36 rg 26 525 790 23 re f 1 1 1 rg\n';y=533;let x=left;opsTimeHeaders.forEach((v,i)=>{commands+=text(x+5,y,v,true);x+=widths[i];});commands+='0 0 0 rg\n';grid(548,525);y=510;}
  function finish(){commands+=text(left,20,'Page '+(pages.length+1));pages.push(commands);}
  header();
  for(const row of opsTimeExportRows()){
    const summary=row[1]===''&&row[2]===''&&row[3]===''&&row[4]===''&&row[5]==='';
    if(summary){
      if(y<76){finish();header();}
      commands+=`0.91 0.95 1 rg ${left} ${y-15} 790 30 re f 0 0 0 rg\n`;
      commands+=text(left+6,y,String(row[0]).slice(0,130),true)+text(702,y,String(row[6]).replace(/ hours$/,'')+' hours',true);grid(y+15,y-15,true);y-=30;continue;
    }
    const cells=row.map((v,i)=>wrap(v,widths[i]-10));let offset=0,count=Math.max(...cells.map(c=>c.length));
    while(offset<count){
      if(y<60){finish();header();}
      const fit=Math.max(1,Math.min(count-offset,Math.floor((y-54)/line)+1));let x=left;
      cells.forEach((lines,i)=>{for(let j=0;j<fit;j++)if(lines[offset+j]!==undefined)commands+=text(x+5,y-j*line,lines[offset+j]);x+=widths[i];});
      const bottom=y-(fit-1)*line-7;
      grid(y+15,bottom);
      y=bottom-15;offset+=fit;
    }
  }finish();
  const objects=['<< /Type /Catalog /Pages 2 0 R >>','', '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>','<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>'],kids=[];
  pages.forEach(stream=>{const pageId=objects.length+1,streamId=pageId+1;kids.push(pageId+' 0 R');objects.push(`<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ${streamId} 0 R >>`,`<< /Length ${stream.length} >>\nstream\n${stream}endstream`);});objects[1]=`<< /Type /Pages /Count ${kids.length} /Kids [${kids.join(' ')}] >>`;
  let pdf='%PDF-1.4\n',offsets=[0];objects.forEach((o,i)=>{offsets.push(pdf.length);pdf+=(i+1)+' 0 obj\n'+o+'\nendobj\n';});const xref=pdf.length;pdf+=`xref\n0 ${objects.length+1}\n0000000000 65535 f \n`+offsets.slice(1).map(n=>String(n).padStart(10,'0')+' 00000 n \n').join('')+`trailer\n<< /Size ${objects.length+1} /Root 1 0 R >>\nstartxref\n${xref}\n%%EOF`;
  const url=URL.createObjectURL(new Blob([pdf],{type:'application/pdf'})),a=document.createElement('a');a.href=url;a.download='Time-Sheet.pdf';a.click();setTimeout(()=>URL.revokeObjectURL(url),10000);
}
$(document).on('change','.bsm-time-filters input,.bsm-time-filters select',async function(){try{if(this.id==='ops-time-group')opsRenderTimeMaster();else await opsLoadTimeMaster();}catch(e){showToast(e.message,true);}});
function opsTimeEdit(id){
  const entry=opsRows.find(r=>Number(r.id)===Number(id));if(!entry)return;
  $('#ops-time-editor').remove();$('#bsm-content').append(`<div id="ops-time-editor" class="bsm-quick-modal"><div class="bsm-quick-panel"><div class="bsm-quick-head"><h3>Edit Time Sheet</h3>${opsButton('time-edit-close','×')}</div><form id="ops-time-edit-form" data-id="${Number(entry.id)}"><div class="bsm-quick-body"><p>${esc(entry.employee_name)} — ${dateFmt(entry.work_date)}</p>${Number(entry.worker_id)>0?opsSelect('ops-time-edit-site','Site *',App.options.sites,entry.site_id,true):''}${opsText('ops-time-edit-from','From Time',entry.from_time.slice(0,5),'time')}${opsText('ops-time-edit-to','To Time',entry.to_time.slice(0,5),'time')}${opsText('ops-time-edit-activity','Activity',entry.activity)}${opsMeta.admin&&Number(entry.worker_id)>0?`<label><input type="checkbox" id="ops-time-edit-approved" ${entry.status==='approved'?'checked':''}> Approved for contractor billing</label>`:''}</div><div class="bsm-form-actions">${opsButton('time-edit-close','Cancel')}<button class="bsm-btn bsm-btn-primary">Save</button></div></form></div></div>`);opsEnhance();
}
async function opsBill(){
  setTitle('Employee / Time Based Contractor Bill');
  $('#bsm-content').html(`<div class="bsm-panel"><form id="ops-bill-form"><div class="bsm-form-grid">${opsSelect('ops-bill-contractor','Labour Contractor',App.options.contractors,'',true)}${opsSelect('ops-bill-site','Site',App.options.sites,'',true)}${opsDate('ops-bill-from','From Date',opsToday().slice(0,7)+'-01')}${opsDate('ops-bill-to','To Date')}${opsText('ops-bill-no','Bill No.')}${opsDate('ops-bill-date','Bill Date')}${opsText('ops-bill-gst','GST Amount','0','number')}</div><p>Only approved, unbilled entries are included. Contractor + Employee uses one Daily Rate per employee per date; According to Time uses approved hours × Hourly Rate. Rates are retained from the submitted entries.</p><div class="bsm-form-actions">${opsButton('bill-preview','Preview Calculation')}${opsMeta.admin?'<button class="bsm-btn bsm-btn-primary">Create Contractor Bill</button>':''}</div></form><div id="ops-bill-preview"></div></div>`);opsEnhance();
}
const opsBillData=()=>({contractor_id:$('#ops-bill-contractor').val(),site_id:$('#ops-bill-site').val(),from:$('#ops-bill-from').val(),to:$('#ops-bill-to').val(),bill_no:$('#ops-bill-no').val(),bill_date:$('#ops-bill-date').val(),gst_amount:$('#ops-bill-gst').val()});
function opsBillPreview(r){$('#ops-bill-preview').html(opsTable(['Employee','Date',r.basis==='employee'?'Days':'Hours','Rate','Amount'],r.rows.map(x=>`<tr><td>${esc(x.employee_name)}</td><td>${dateFmt(x.work_date)}</td><td>${x.units}</td><td>${x.rate.toFixed(2)}</td><td>${x.amount.toFixed(2)}</td></tr>`).join(''))+`<p><strong>Amount: ${r.total.toFixed(2)}</strong></p>`);}
async function opsTransferForm(id=0){
  setTitle(id?'Edit Site-to-Site Transfer':'Site-to-Site Transfer Form');let t=id?await opsCall('transfer_get',{id}):{};opsTransfer=t;
  $('#bsm-content').html(`<div class="bsm-panel"><form id="ops-transfer-form" data-id="${id}"><div class="bsm-form-grid">${opsDate('ops-transfer-date','Transfer Date',t.transfer_date||opsToday())}${opsSelect('ops-transfer-from','From Site',App.options.sites,t.from_site_id,true)}${opsSelect('ops-transfer-to','To Site',opsMeta.destinations,t.to_site_id,true)}${opsText('ops-transfer-vehicle','Vehicle / Transport Details',t.vehicle_no||'')}${opsText('ops-transfer-remarks','Remarks',t.remarks||'')}</div><div class="bsm-toolbar">${opsButton('transfer-add','+ Add Item')}</div>${opsTable(['Item','Unit','Available Stock','Transfer Qty','Remarks','Action'],'<tr></tr>')}<div class="bsm-form-actions"><button class="bsm-btn bsm-btn-primary">Save Draft</button>${opsButton('transfer-master','View Transfers')}</div></form></div>`);opsEnhance();await opsLoadTransferStock();$('#ops-transfer-form tbody').html((t.items?.length?t.items:[{}]).map(opsTransferItem).join(''));opsEnhance();opsTransferValues();
}
function opsTransferItem(it={}){return `<tr><td><select class="ops-transfer-item" required>${opsOpts(opsStock,it.item_id)}</select></td><td class="ops-transfer-unit"></td><td class="ops-transfer-available"></td><td><input type="number" class="ops-transfer-qty" min="0.01" step="0.01" required value="${esc(it.qty||'')}"></td><td><input class="ops-transfer-line-remarks" value="${esc(it.remarks||'')}"></td><td>${opsButton('transfer-remove','Delete')}</td></tr>`;}
async function opsLoadTransferStock(){const site=$('#ops-transfer-from').val();opsStock=site?(await opsCall('transfer_stock',{site_id:site})).rows:[];}
function opsTransferValues(){$('#ops-transfer-form tbody tr').each(function(){const stock=opsStock.find(s=>String(s.id)===String($(this).find('.ops-transfer-item').val()));$(this).find('.ops-transfer-unit').text(stock?labelFor('units',stock.unit_id):'');$(this).find('.ops-transfer-available').text(stock?.available_qty??'');$(this).find('.ops-transfer-qty').attr('max',stock?.available_qty??'');});}
async function opsTransfers(){
  setTitle('Site-to-Site Transfer Master');
  $('#bsm-content').html(`<div class="bsm-toolbar">${opsSelect('ops-transfer-filter-site','Site',App.options.sites)}${opsDate('ops-transfer-filter-from','From Date',opsToday().slice(0,7)+'-01')}${opsDate('ops-transfer-filter-to','To Date')}<label>Status<select id="ops-transfer-filter-status"><option value="">All</option>${['draft','dispatched','partially_received','received','cancelled'].map(s=>`<option value="${s}">${s.replaceAll('_',' ')}</option>`).join('')}</select></label>${opsButton('transfer-filter','Apply')}${opsButton('transfer-new','+ Add Transfer')}${opsButton('transfer-export','Excel Download')}</div><div class="bsm-panel" id="ops-transfer-list"></div>`);opsEnhance();await opsLoadTransfers();
}
async function opsLoadTransfers(){opsRows=(await opsCall('transfer_list',{site_id:$('#ops-transfer-filter-site').val(),from:$('#ops-transfer-filter-from').val(),to:$('#ops-transfer-filter-to').val(),status:$('#ops-transfer-filter-status').val()})).rows;$('#ops-transfer-list').html(opsTable(['Transfer No.','Date','From Site','To Site','Status','Action'],opsRows.map(t=>`<tr><td>${esc(t.transfer_no)}</td><td>${dateFmt(t.transfer_date)}</td><td>${esc(t.from_site)}</td><td>${esc(t.to_site)}</td><td>${esc(t.status.replaceAll('_',' '))}</td><td>${t.record_type==='material_dispatches'?`<button class="bsm-btn bsm-btn-small bsm-action-dispatch-slip" data-id="${t.id}">Dispatch Slip PDF</button>`:opsButton('transfer-view','View / Challan',t.id)} ${t.status==='draft'?opsButton('transfer-edit','Edit',t.id)+' '+opsButton('transfer-dispatch','Dispatch',t.id)+(opsMeta.admin?' '+opsButton('transfer-cancel','Cancel',t.id):''):''}${t.record_type!=='material_dispatches'&&['dispatched','partially_received'].includes(t.status)?' '+opsButton('transfer-view','Receive',t.id):''}</td></tr>`).join('')));}
async function opsTransferView(id){
  const t=await opsCall('transfer_get',{id});opsTransfer=t;setTitle('Site Transfer — '+t.transfer_no);
  const receivable=['dispatched','partially_received'].includes(t.status);
  $('#bsm-content').html(`<div class="bsm-panel"><div class="bsm-toolbar">${opsButton('transfer-master','Back')}${opsButton('transfer-print','Print Challan')}</div><div id="ops-transfer-challan"><h3>Site-to-Site Transfer — ${esc(t.transfer_no)}</h3><p>Date: ${dateFmt(t.transfer_date)} | From: ${esc(t.from_site)} | To: ${esc(t.to_site)}</p><p>Transport: ${esc(t.vehicle_no)} | Status: ${esc(t.status.replaceAll('_',' '))}</p><p>${esc(t.remarks||'')}</p>${opsTable(['Item','Unit','Dispatched','Received','Damaged','Pending / Short'],t.items.map(x=>`<tr><td>${esc(x.item_name)}</td><td>${esc(x.unit_name||'')}</td><td>${x.qty}</td><td>${x.received_qty}</td><td>${x.damaged_qty}</td><td>${(+x.qty-+x.received_qty-+x.damaged_qty).toFixed(2)}</td></tr>`).join(''))}</div>${receivable?`<form id="ops-receive-form" data-id="${t.id}">${opsDate('ops-receive-date','Receipt Date')}${opsTable(['Item','Remaining','Receive Now (Good)','Damaged Now'],t.items.filter(x=>+x.qty-+x.received_qty-+x.damaged_qty>0).map(x=>`<tr data-id="${x.id}"><td>${esc(x.item_name)}</td><td>${(+x.qty-+x.received_qty-+x.damaged_qty).toFixed(2)}</td><td><input type="number" min="0" step="0.01" class="ops-receive-good" value="0"></td><td><input type="number" min="0" step="0.01" class="ops-receive-bad" value="0"></td></tr>`).join(''))}<div class="bsm-form-actions"><button class="bsm-btn bsm-btn-primary">Record Receipt</button></div></form>`:''}<h4>Supporting Documents</h4><div>${(t.documents||[]).map(d=>`<p><a href="${esc(d.file_url)}" target="_blank" rel="noopener">${esc(d.original_filename)}</a></p>`).join('')}</div><div class="bsm-toolbar"><input type="file" id="ops-transfer-document" accept=".pdf,.jpg,.jpeg,.png,.webp">${opsButton('transfer-upload','Upload Document',t.id)}</div><h4>Receipt History</h4>${opsTable(['Date','Good Qty','Damaged Qty'],t.receipts.map(r=>`<tr><td>${dateFmt(r.receipt_date)}</td><td>${r.received_qty}</td><td>${r.damaged_qty}</td></tr>`).join(''))}</div>`);
}
$(document).on('change','#ops-holiday-scope',function(){$('#ops-holiday-employees-wrap').prop('hidden',this.value!=='selected');});
function opsTimeEmployees(){
  const contractor=$('#ops-time-type').val()==='contractor',cid=$('#ops-time-contractor').val();
  const employees=contractor?opsMeta.workers.filter(w=>w.status==='active'&&String(w.contractor_id)===String(cid)):opsMeta.employees;
  $('#ops-time-employee').html(opsOpts(employees)).prop('disabled',contractor&&!cid);
  $('#ops-time-grid').prop('hidden',true).find('tbody').empty();
  $('#ops-time-total').text('');
  bsmSyncAllSearchables($('#bsm-content'));
}
$(document).on('change','#ops-time-type',function(){
  const contractor=this.value==='contractor';
  $('#ops-time-contractor-wrap').prop('hidden',!contractor);
  $('#ops-time-contractor').val('').prop('disabled',!contractor).prop('required',contractor);
  $('#ops-time-site').prop('required',contractor).attr('aria-required',String(contractor));
  $('#ops-time-site-label').text(contractor?'Site *':'Site');
  opsTimeEmployees();
});
$(document).on('change','#ops-time-contractor',opsTimeEmployees);
$(document).on('change','#ops-time-employee,#ops-time-date',async function(){try{await opsLoadTimeDay();}catch(e){showToast(e.message,true);}});
$(document).on('input','.ops-time-from,.ops-time-to',opsTimeTotal);
$(document).on('change','.ops-transfer-item',opsTransferValues);
$(document).on('change','#ops-transfer-from',async function(){try{await opsLoadTransferStock();$('#ops-transfer-form tbody').html(opsTransferItem());opsEnhance();}catch(e){showToast(e.message,true);}});
$(document).on('submit','#ops-holiday-form,#ops-worker-form,#ops-time-form,#ops-bill-form,#ops-transfer-form,#ops-receive-form,#ops-time-edit-form',async function(e){
  e.preventDefault();const $form=$(this),$save=$form.find('button[type="submit"],button:not([type])');$save.prop('disabled',true);
  try{
    switch(this.id){
      case 'ops-holiday-form':{const ids=$('#ops-holiday-scope').val()==='selected'?($('#ops-holiday-employees').val()||[]):[];if($('#ops-holiday-scope').val()==='selected'&&!ids.length)throw new Error('Select at least one Employee.');await opsCall('holiday_save',{id:$('#ops-holiday-id').val(),holiday_date:$('#ops-holiday-date').val(),holiday_name:$('#ops-holiday-name').val(),employee_ids:ids});await opsHolidays();break;}
      case 'ops-worker-form':await opsCall('worker_save',{id:$('#ops-worker-id').val(),contractor_id:$('#ops-worker-contractor').val(),employee_name:$('#ops-worker-name').val(),mobile:$('#ops-worker-mobile').val(),address:$('#ops-worker-address').val(),bank_name:$('#ops-worker-bank').val(),ifsc:$('#ops-worker-ifsc').val(),account_number:$('#ops-worker-account').val(),daily_rate:$('#ops-worker-daily').val(),hourly_rate:$('#ops-worker-hourly').val(),status:$('#ops-worker-status').val()});opsMeta=await opsCall('meta');await opsWorkers();break;
      case 'ops-time-form':{const rows=$('#ops-time-grid tbody tr').map(function(){return {id:$(this).data('id'),from_time:$(this).find('.ops-time-from').val(),to_time:$(this).find('.ops-time-to').val(),activity:$(this).find('.ops-time-activity').val()};}).get();const contractor=$('#ops-time-type').val()==='contractor';if(contractor&&!opsMeta.workers.some(w=>w.status==='active'&&String(w.id)===String($('#ops-time-employee').val())&&String(w.contractor_id)===String($('#ops-time-contractor').val())))throw new Error('Select an employee belonging to the selected Contractor Name.');if(contractor&&!$('#ops-time-site').val())throw new Error('Site is required for Contractor Employee.');await opsCall('time_save',{work_date:$('#ops-time-date').val(),[contractor?'worker_id':'employee_id']:$('#ops-time-employee').val(),site_id:$('#ops-time-site').val(),rows});await openView('time_sheet_master');break;}
      case 'ops-time-edit-form':await opsCall('time_update',{id:$form.data('id'),...($('#ops-time-edit-site').length?{site_id:$('#ops-time-edit-site').val()}:{}),from_time:$('#ops-time-edit-from').val(),to_time:$('#ops-time-edit-to').val(),activity:$('#ops-time-edit-activity').val(),approved:$('#ops-time-edit-approved').prop('checked')||false});$('#ops-time-editor').remove();await opsLoadTimeMaster();break;
      case 'ops-bill-form':{const r=await opsCall('bill_create',opsBillData());opsBillPreview(r);$save.prop('disabled',true);$('#ops-bill-no').val('');showToast('Contractor Bill created. It is available in Contractor Bills and Outstanding.');return;}
      case 'ops-transfer-form':{const items=$('#ops-transfer-form tbody tr').map(function(){return {item_id:$(this).find('.ops-transfer-item').val(),qty:$(this).find('.ops-transfer-qty').val(),remarks:$(this).find('.ops-transfer-line-remarks').val()};}).get();await opsCall('transfer_save',{id:$form.data('id'),transfer_date:$('#ops-transfer-date').val(),from_site_id:$('#ops-transfer-from').val(),to_site_id:$('#ops-transfer-to').val(),vehicle_no:$('#ops-transfer-vehicle').val(),remarks:$('#ops-transfer-remarks').val(),items});await opsTransfers();break;}
      case 'ops-receive-form':await opsCall('transfer_action',{id:$form.data('id'),action:'receive',receipt_date:$('#ops-receive-date').val(),items:$form.find('tbody tr').map(function(){return {id:$(this).data('id'),received_qty:$(this).find('.ops-receive-good').val(),damaged_qty:$(this).find('.ops-receive-bad').val()};}).get()});await opsTransferView($form.data('id'));break;
    }showToast('Saved successfully.');
  }catch(err){showToast(err.message,true);}finally{$save.prop('disabled',false);}
});
$(document).on('click','[data-ops]',async function(){
  const $button=$(this),action=$button.data('ops'),id=+$button.data('id');$button.prop('disabled',true);
  try{switch(action){
    case 'holiday-add':opsHolidayForm();break;
    case 'time-new':await openView('time_sheet');break;
    case 'time-cancel':await openView('time_sheet_master');break;
    case 'holiday-clear':await opsHolidays();break;
    case 'holiday-edit':opsHolidayForm(opsRows.find(x=>+x.id===id)||{});break;
    case 'holiday-delete':if(confirm('Delete this Holiday?')){await opsCall('holiday_delete',{id});await opsHolidays();}break;
    case 'worker-clear':await opsWorkers();break;
    case 'worker-edit':{const w=opsMeta.workers.find(x=>+x.id===id);$('#ops-worker-id').val(w.id);$('#ops-worker-contractor').val(w.contractor_id);$('#ops-worker-name').val(w.employee_name);$('#ops-worker-mobile').val(w.mobile||'');$('#ops-worker-address').val(w.address||'');$('#ops-worker-bank').val(w.bank_name||'');$('#ops-worker-ifsc').val(w.ifsc||'');$('#ops-worker-account').val(w.account_number||'');$('#ops-worker-daily').val(w.daily_rate);$('#ops-worker-hourly').val(w.hourly_rate);$('#ops-worker-status').val(w.status);bsmSyncAllSearchables($('#bsm-content'));break;}
    case 'time-add':$('#ops-time-grid tbody').append(opsTimeRow());opsResizeActivities();break;
    case 'time-remove':$button.closest('tr').remove();opsTimeTotal();break;
    case 'time-filter':await opsLoadTimeMaster();break;
    case 'time-edit':opsTimeEdit(id);break;
    case 'time-edit-close':$('#ops-time-editor').remove();break;
    case 'time-delete':if(confirm('Delete this Time Sheet entry?')){await opsCall('time_delete',{id});await opsLoadTimeMaster();}break;
    case 'time-status':await opsCall('time_status',{id,status:$button.data('status')});await opsLoadTimeMaster();break;
    case 'time-pdf':opsTimePdf();break;
    case 'time-export':if(!opsTimeLoading)downloadExcelFile('Time Sheet',opsTimeHeaders,opsTimeExportRows());break;
    case 'bill-preview':opsBillPreview(await opsCall('bill_preview',opsBillData()));break;
    case 'transfer-add':$('#ops-transfer-form tbody').append(opsTransferItem());opsEnhance();break;
    case 'transfer-remove':$button.closest('tr').remove();break;
    case 'transfer-master':await opsTransfers();break;
    case 'transfer-new':await opsTransferForm();break;
    case 'transfer-edit':await opsTransferForm(id);break;
    case 'transfer-filter':await opsLoadTransfers();break;
    case 'transfer-view':await opsTransferView(id);break;
    case 'transfer-dispatch':if(confirm('Dispatch this transfer and deduct source-site stock?')){await opsCall('transfer_action',{id,action:'dispatch'});await opsLoadTransfers();}break;
    case 'transfer-cancel':if(confirm('Cancel this Draft transfer?')){await opsCall('transfer_action',{id,action:'cancel'});await opsLoadTransfers();}break;
    case 'transfer-export':downloadExcelFile('Site Transfers',['Transfer No.','Date','From Site','To Site','Status'],opsRows.map(t=>[t.transfer_no,t.transfer_date,t.from_site,t.to_site,t.status]));break;
    case 'transfer-upload':{const file=$('#ops-transfer-document')[0]?.files?.[0];if(!file)throw new Error('Select a supporting document.');const fd=new FormData();fd.append('action','bsm_operations');fd.append('nonce',BSM.nonce);fd.append('op','transfer_document');fd.append('data',JSON.stringify({id}));fd.append('file',file);const response=await fetch(BSM.ajax,{method:'POST',body:fd,credentials:'same-origin'});const r=await response.json();if(!r.success)throw new Error(r.data?.message||'Upload failed.');await opsTransferView(id);break;}
    case 'transfer-print':{const win=window.open('','_blank');if(!win)throw new Error('Allow pop-ups to print the Challan.');win.document.write('<!doctype html><html><head><title>Site Transfer Challan</title><style>body{font:14px Arial;padding:24px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #aaa;padding:8px;text-align:left}</style></head><body>'+$('#ops-transfer-challan').html()+'</body></html>');win.document.close();win.focus();win.print();break;}
  }}catch(err){showToast(err.message,true);}finally{$button.prop('disabled',false);}
});

})(jQuery);

jQuery(document).on('click','.bsm-contractor-outstanding-payment',function(){window.openContractorOutstandingPayment(jQuery(this).data('contractor-id'),jQuery(this).data('site-id'),jQuery(this).data('outstanding'));});


/* PO comparison is displayed above the item grid. */
