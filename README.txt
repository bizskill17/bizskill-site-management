Site Management
Version 0.0.054

INSTALLATION
1. Upload the ZIP from WordPress > Plugins > Add New > Upload Plugin.
2. Activate "Site Management".
3. Activation creates a front-end page named "Site Management" at /site-management/.
4. Open the page and log in with a WordPress user.
5. Administrators/Owner users can create application users from Users.

PHASE 1 INCLUDED
- Users: User ID, native WordPress password, email, role, designation, mobile, status
- Customers and Sites
- Vendors
- Labour Contractors
- Daily site-wise labour attendance: Date + Site + Labour Contractor + No. of Labour
- Items, Categories, Units, Warehouse
- Material Requisition
- Material Inward
- Warehouse to Site Dispatch
- Site Receipt with short/damaged quantity
- Site Consumption
- Site to Warehouse Return
- Stock Ledger and Stock Report
- Site Fund Allocation
- Site Expenses with Paid By = Site / Head Office
- Site Fund Ledger
- Customer Receipts
- Vendor Payments
- Labour Contractor Bills and Payments
- GST Invoices
- Supporting document uploads
- Dashboard and reports
- Customer, Vendor and Contractor outstanding reports
- Daily labour and labour summary reports

NOT INCLUDED IN PHASE 1
- Employee payroll/salary
- Employee attendance
- Individual labour names
- Inter-site material transfer
- EOD module
- Detailed audit-log module

IMPORTANT DESIGN RULE
Head Office payments on behalf of a site increase Site Cost but do not reduce Site Fund Balance. Site-paid expenses/payments reduce the Site Fund Balance.


Version 0.0.054
- Creates a separate published WordPress Page for every application view/report.
- All generated pages use the plugin Blank Template - Site Management.
- Site Management is the parent/dashboard page; all other views are child pages.
- Sidebar navigation now opens the corresponding WordPress page.
- Existing installations are upgraded automatically; no deactivate/reactivate step is required.


v0.1.32
- Material Requisition numbers now use REQ-YY/YY-00001.
- Material Dispatch numbers now use DIS-YY/YY-00001.
- Both sequences restart by financial year and remain unchanged on edit.


v0.1.34
- Hidden auto-generated Requisition No. and Dispatch No. from Add/Edit forms. Numbers continue to generate automatically and remain visible in list views/reports.

v0.1.35
- Fixed searchable dropdown menus being clipped inside Material Requisition line-item tables.
- Item/Unit option lists now open clearly above the table with proper z-index and width.

0.1.435: Load Bank Master options before Vendor Outstanding payment form; clear empty-bank notice; suppress Payment Form menu link.

0.1.438: Dashboard quick action Site filter carries to Contractor and Vendor Outstanding; Vendor Outstanding gains Site filter and backend site filtering.

0.1.439: Dashboard Site Management Snapshot heading renamed Site Balance; six KPI cards receive distinct soft background/border colors.

Version 0.1.440: Contractor Outstanding report displays filtered Total Bills, Total Payments and Total Outstanding summary above the table.

Version 0.1.441: Contractor Outstanding totals moved into the filter panel with compact responsive cards.

Version 0.1.442: Contractor Outstanding totals use full filter width; prevent currency amount wrapping with responsive mobile rows.

Version 0.1.443: Customer Master GST Type (Intra-State, Inter-State, No GST) in form, list, Excel; nullable schema migration preserves existing values.

0.1.458: Dashboard Quick Actions adds + Add Site, opening the existing Site Master Add form with standard permission checks.
0.1.460: Move Site Master navigation from Sites to Masters (alphabetically placed), retaining existing view ID, URL, permissions, and Dashboard Add Site Quick Action.

0.1.464: Global black text contrast for labels, values, placeholders, read-only inputs and muted dashboard/table text; preserve dark headers, sidebar, actions, statuses.

0.1.465: Bill Type on purchase and pending invoice upload; validation, table display, exports, and conditional non-GST calculations.

Version 0.0.043: Cache-busted frontend assets to display Project Tracker actions, and linked Sub-task Edit/Delete to the correct record.

Version 0.0.064: Pending Dispatch supports Warehouse or Site sources. Site dispatches validate source stock and approved pending quantities, post source-site stock outward, and retain the existing Site Receipt workflow. Source details are included in dispatch records, item exports, PDFs and Site Transfer Master. Adds atomic dispatch saves and regression coverage.
