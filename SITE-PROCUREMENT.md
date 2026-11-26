# Site procurement (0.0.105)

## Working flow

Site -> Approved Requisition -> Purchase Order -> Direct Site Purchase Receipt -> Site Inventory.

- PO creation requires one Site and one approved Requisition. Only that requisition's remaining approved item quantities may be ordered. One to three complete supplier quotations are allowed; different chosen suppliers create separate POs.
- Pending PO quantity is approved quantity less quantities on non-cancelled, non-rejected POs, including POs awaiting approval. Stock and receipts do not reduce or reopen this ordering balance.
- New purchase receipts require an approved PO, retain its Site and supplier, and post received quantities directly to that Site. Partial receipts are supported. No warehouse dispatch or second Site Receipt is created.
- New Supplier Returns reference an original Site Purchase Receipt, cannot exceed its unreturned quantity or the Site's available stock, and do not reopen the Requisition.
- Posted Site Purchase Receipts and Supplier Returns are immutable. Invoice files can be uploaded separately. Existing historical Purchase Approval remains available for older receipts.
- PO edits validate the remaining requisition balance and received quantity, then require approval again. Supplier/Rate amendments need a reason; those fields cannot change after receipt. Received POs cannot be cancelled.
- Site transfers retain the existing draft -> dispatched -> partially received/received flow. Source stock decreases at dispatch; destination stock increases only for quantities actually received.
- Availability, inventory reports and BOQ balances use the same Site movement sources. Creating a PO never changes physical stock. Item Master opening quantity is retired; new openings are entered against Sites.

## Upgrade and reconciliation

Legacy warehouse transactions and legacy Site Receipts are retained. They are not converted into new direct purchase receipts, avoiding double-counting. Historical screens remain in a separately labelled group.

The upgrade fills PO Site/Requisition references only when every PO item has a reference and all links resolve to exactly one Site and Requisition. Ambiguous or partially linked POs remain unassigned.

Settings -> Historical Stock / PO Review lists historical POs and unassigned warehouse balances. Administrators can:

1. Explicitly allocate a reviewed positive historical stock quantity to a Site. This writes an audit allocation plus matching warehouse-out and Site-in ledger entries; repeated allocations cannot exceed the remaining source balance.
2. Assign an entirely unlinked, unreceived historical PO to an approved Site Requisition when each PO item matches exactly one requisition item and its remaining approved quantity. The PO is sent for approval again and its review is recorded.

POs with historical receipts or ambiguous item matches require manual reconciliation. No automatic stock reassignment occurs. Site access restrictions apply to new procurement endpoints, stock lookups and reports.

## Validation

- `php tests/site-procurement.php`: runs the production procurement trait and SQL with SQLite fixtures. Covers partial POs, one quotation, multiple supplier POs, over-ordering, Site isolation, unapproved requisitions, direct partial receipts, over-receipts, duplicate receipt lines, Supplier Returns, consumption, partial transfers, inventory reconciliation, PO edits/reapproval/cancellation, rollback on item/ledger failures and ambiguous legacy linkage.
- `php tests/notifications.php`: mocked notification transport; verifies updated Site PO reminders as well as existing queue/routing/deduplication controls. No messages are sent by the test.
- `node tests/site-procurement-dom.cjs`: requires Playwright, Chromium and jQuery. Optional `BSM_PLAYWRIGHT`, `BSM_CHROMIUM_PATH`, `BSM_SLIM_CHROMIUM` and `BSM_JQUERY` paths support local test installations. Exercises production browser code at 1366, 1024, 760 and 390 pixels, quotation selection, partial receipt controls and table alignment.
- PHP lint and class/trait load checks pass. The older `tests/operations.php` suite still expects employee-specific holidays and fails its pre-existing Holiday assertion; suites importing it inherit that blocker. This release does not change Holiday or payroll behavior.

Live WordPress/MySQL deployment and historical reconciliation must be verified on the installation. SQLite tests do not substitute for a live database migration check.

## 0.0.106 follow-up

Pending PO now lists each site's requisitions separately with dates and a Create PO action. Repeated supplier quotations are allowed and their lowest rate is used. New PO forms send a submission identifier; the server serializes creation and returns the original supplier-split orders on an unchanged retry, including after their pending balance has been consumed. Reusing an identifier with changed details is rejected. Existing API clients without an identifier retain quantity validation but must supply request_id for retry protection.

Lifecycle tests additionally cover repeated supplier rates, unchanged retries and changed-payload rejection. Browser checks cover the requisition list and its Create PO action. Historical allocations remain explicit reviewed transactions; live stock reconciliation is not performed by this change.
