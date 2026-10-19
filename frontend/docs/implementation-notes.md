# Implementation notes

## Frontend behavior
- `js/data/sample-clients.js` holds fictional records.
- `js/services/storage-service.js` provides the demonstration storage interface using browser `localStorage`.
- `js/components/` contains shared shell, rendering helpers, form validation, and notifications.
- `js/pages/` contains page-specific controllers. `js/main.js` starts the controller named on the body element.
- Client create/edit, search/filter, details, status entry, and dashboard aggregation work locally.
- Contract dates and statuses are displayed as entered; no automatic overdue or renewal logic is applied.
- Email, staff registration, password recovery, and user management require approved workflows and backend integrations.

## PHP and MySQL integration points
- Replace `KPR.storage` with API requests while preserving the current record fields.
- Add server-side validation, authenticated sessions and authorization, CSRF protection, and access controls before deployment.
- Persist client records and status changes in MySQL. Confirm status, payment, and renewal rules before implementing automation.
- Configure the email provider and send messages from PHP. Do not expose provider credentials in browser JavaScript.

## Limitations
- Records are fictional and stored per browser. Browser storage can be edited or cleared and is not a secure database.
- No PHP, database, email, or production authentication is included.
- No standalone logo asset was found; the login uses the supplied reference image. Replace it with an approved standalone logo when available.
