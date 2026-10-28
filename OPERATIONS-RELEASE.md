# Site Management 0.0.063

## Upgrade

Deploy the complete plugin folder, including `includes/workforce-transfers.php`. On the next WordPress request, the plugin creates the additional tables, adds Payment Basis columns and creates the new application pages. Existing bills and payments retain the Direct Payment basis. Existing transactions are preserved.

Administrators see the new views automatically. Grant relevant view permissions to employees in User Master. Site actions use the existing assigned-site restrictions. New holiday and contractor-worker masters and approvals require HO/Admin.

## Office Attendance

- **Holiday List**: Date, Holiday Name, All Employees or Selected Employees. Add, edit and delete are available to HO/Admin.
- **Time Sheet**: choose Date and Employee Name, then enter repeatable From Time, To Time and Activity rows. Duration and total hours are calculated automatically. A To Time earlier than From Time is treated as the next day. Overnight durations are calculated automatically and cannot overlap entries on adjacent dates. Office employees can enter/view their own sheets; HO/Admin can select other employees.
- **Time Sheet Master**: filter by dates, employee, contractor, site or approval status; approve/reopen and export entries. Approved rows are read-only until reopened. Billed rows cannot be reopened or deleted.
- Applicable holidays display as Holiday in daily attendance and are excluded from absences in the monthly salary calculation. Applicable holidays are payable even when unmarked, using the salary effective on each date. A holiday that is also marked Present is paid once. The calendar-day divisor and uploaded salary-slip workflow are unchanged.

## Labour Contractors

1. Select **Payment Basis** in Labour Contractor Master:
   - Direct Payment to Contractor: use the existing Contractor Bill Form and payment flow.
   - Contractor + Employee: use approved contractor-employee work dates at the snapshotted Daily Rate, once per employee per date.
   - According to Time: use approved minutes / 60 multiplied by the snapshotted Hourly Rate.
2. Add employees, daily/hourly rates and status in **Contractor Employees**.
3. In the separate Time Sheet sidebar section, open Time Sheet and select Contractor Employee, Contractor Name, Employee Name, Site and the work date. Employee Name lists only active employees of the selected contractor; changing contractor clears the previous employee and activity rows. Enter activities and submit.
4. HO/Admin approves entries in Time Sheet Master.
5. Open **Employee / Time Based Bill**, choose contractor, site and date range, and preview the calculation. Supply Bill No., Bill Date and GST Amount to create the bill.
6. The resulting bill appears in existing Contractor Bills, Outstanding and Site Cost reports and uses existing payment/advance allocation flows.

Rates are captured when unapproved time entries are saved. Update and resave entries before approval if rates need correction. Daily-rate work split between multiple Sites on one date requires time-based billing instead of charging the Daily Rate twice. Already billed entries are excluded from later calculations. Calculated bills cannot be edited/deleted independently of their approved source entries.

## Dispatch: Site-to-Site Transfers

- **Site-to-Site Transfer Form**: choose From Site, different To Site, date, transport details and item rows. Available Stock and Unit are displayed. Save creates a Draft with a generated transfer number; Drafts do not move stock.
- **Site-to-Site Transfer Master**: filter, edit Drafts, dispatch, receive and print a challan. HO/Admin can cancel Drafts. Dispatched transfers cannot be silently cancelled or edited.
- Dispatch rechecks source stock, deducts it and records the material in transit. Destination stock changes only for good quantities received.
- Record one or more partial receipts. Damaged quantities are separate; pending quantities remain visible as Pending / Short. Good + damaged quantities cannot exceed the remaining quantity. Receipt dates cannot precede dispatch or be future-dated.
- Supporting PDF/JPG/PNG/WebP documents can be uploaded from the transfer detail view (10 MB limit).
- Source dispatches and destination receipts feed the stock ledger, Site Inventory and stock availability used in Consumption/Returns. Transfers are not purchases or consumption. In the existing inventory summary, transfer-ins contribute to Receipts and transfer-outs to Returns/outflows; the transfer detail view identifies both explicitly.

## Validation

- PHP syntax checks: main plugin and workforce/transfer trait.
- JavaScript syntax check: `assets/app.js`.
- `php tests/operations.php`: 54 standalone regression checks using PDO SQLite. Covers date applicability, effective holiday salary, duration/overlap checks, ownership, approvals, billing, duplicate prevention, draft/dispatch/partial receipt, damage, stock reconciliation and site permissions.
- Browser smoke checks with mocked AJAX: all seven views render, time entry duration/submission, contractor bill preview and partial/damaged receipt submission. Desktop and mobile Time Sheet layouts inspected.

Live WordPress/MySQL migration and hosting deployment have not been exercised in this workspace. After deployment, verify the new pages, grant employee permissions, and run one contractor bill and one transfer through the site's normal workflow.

## Contractor employee table (0.0.063)

The Add/Edit Labour Contractor form includes an Employees grid: Name, Mobile, Address, Bank, IFSC, AC No. Add/remove rows and save the contractor once. Existing linked employees load on edit. Child updates retain their IDs and daily/hourly rates; removal deactivates employees already referenced by time entries, preserving their history. Employee account numbers are stored as text. HO/Admin access is required to save the employee grid. The standalone Contractor Employees view also displays and edits these contact/bank details alongside rates.

## Time Sheet Master actions

Status and Bill columns are removed from the table and Excel export. Each row provides Edit and Delete. Employees can change their own unapproved entries; HO/Admin can change unbilled entries. Billed entries remain locked. Edit recalculates duration and checks overlaps, including overnight intervals. For contractor entries, HO/Admin can approve billing in the Edit dialog.
