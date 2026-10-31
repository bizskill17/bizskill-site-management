# WhatsApp notifications

Settings → Notification is available to WordPress Administrators (`manage_options`). Sending is disabled until configured. No credentials or group IDs are bundled with this release.

## Setup

1. Save the MessageAutoSender Username, Password and Office WhatsApp Group ID. Passwords are encrypted with AES-256-GCM using the site's WordPress authentication salt; leaving Password blank retains it. If authentication salts change, enter the password again.
2. Add **Site WhatsApp Group ID** in each Site Master record. Its **Test WhatsApp** action queues a connection test for that site. The notification screen also supports destination-specific tests and actual-record previews.
3. Configure the 14 individual checkboxes, comma-separated 24-hour schedule times, working days and log retention. All display and scheduling times use **Asia/Kolkata (IST)**. Instant notifications ignore scheduled working days.
4. Configure a real server cron every minute. For a WordPress CLI installation:

   ```cron
   * * * * * /absolute/path/to/wp cron event run --due-now --path=/absolute/path/to/wordpress --quiet
   ```

   If WP CLI is unavailable, a hosting cron can request the site's own WordPress cron URL, for example:

   ```cron
   * * * * * curl -fsS 'https://YOUR-DOMAIN/wp-cron.php?doing_wp_cron' >/dev/null 2>&1
   ```

   Replace the domain/paths for the installation. `DISABLE_WP_CRON` may be set only after the server cron is working. The Notification screen shows the last background run. The registered hook is `bsm_notifications_tick` at a 60-second interval.
5. Queue one test and inspect Notification Log. **Accepted** is provider API acceptance, not delivery. An unfamiliar API success response is held as **Review Required** until provider history is checked. No live message was sent during development tests.
6. Enable the master switch and required message types. Existing records are baselined; enabling does not send historical instant notifications or earlier schedule slots that day.

## Behavior

- Instant events are detected from committed record status transitions after application AJAX requests; cron also reconciles missed transitions. The observer never sends an HTTP request during record saving.
- Each event, destination and message part has a unique database key. Ordinary edits do not resend original events. Both source/destination groups receive site-transfer dispatch and receipt updates, but equal group IDs receive only one copy.
- Scheduled reminders use current outstanding quantities. Empty summaries are skipped. After downtime only today's latest due slot is processed; previous days are not replayed.
- Site Labour Attendance reports saved employee Working Hours, grouped by contractor. Legacy count-only rows are identified without invented hours. Site Timesheets include workers assigned to that site; Office Timesheets exclude contractor workers.
- Missing groups are logged as Skipped and never redirected. Turning off the master/type prevents its queued sends. Test messages require an explicit Admin action and can run while master notifications are disabled.
- Temporary HTTP 429/5xx failures retry up to three times per retry cycle. Authentication failures pause the connection until credentials are changed. Timeouts and interrupted sends are held for review to prevent uncertain automatic duplicates. Admin can retry after checking provider history; total attempt history and original creation dates are retained.
- Log retention defaults to 90 days (7–365 configurable). Cleanup does not delete unsent or uncertain messages.
- All messages use the supplied endpoint, Basic authentication, `recipientIds: [groupId]`, and `message: [messageText]`. Credentials remain server-side and are redacted from API response logs.

## Purchase Order approval

New POs start in **Pending Approval**. Administrators can Approve or Reject them from **PO Master** or **Pending PO Approval**. A rejection reason is required. Pending/rejected POs cannot be received; rejected POs release their pending PO allocation. Existing POs retain their old state, and legacy Created POs remain usable. They can also be explicitly approved from PO Master. Merely creating a PO does not generate a PO Approved notification. Editing an approved/rejected PO returns it to Pending Approval; ordinary edits do not repeat its original approval message.

## Storage and upgrades

- `bsm_notification_settings` option: connection and schedule settings (not autoloaded).
- `bsm_notification_queue`: outbox, unique event key, attempts, outcome history and UTC timestamps.
- `bsm_notification_state`: record transition baselines and completed IST schedule slots.
- `bsm_sites.whatsapp_group_id`: optional site destination mapping.
- PO approval audit columns: `approved_by`, `approved_at`, `rejection_reason`.

Tables use the installation's actual WordPress prefix. Activation/update creates these additions automatically without renaming or replacing existing transaction tables. Deactivating the plugin clears its recurring notification cron event; saved logs/settings remain.

## Verification

`tests/notifications.php` mocks WordPress, the database adapter and HTTP; it never calls the provider. Run it with PHP CLI and PDO SQLite. `tests/operations.php` contains the existing workforce/stock regression checks.
