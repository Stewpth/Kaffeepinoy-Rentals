# Implementation notes

## Frontend behavior
- `js/data/sample-clients.js` is retained as unused legacy demonstration data.
- `js/services/storage-service.js` adapts the existing controllers to the authenticated PHP JSON API; it does not use browser storage for client/payment records.
- `js/components/` contains shared shell, rendering helpers, form validation, and notifications.
- `js/pages/` contains page-specific controllers. `js/main.js` starts the controller named on the body element.
- `clients-backend.js`, `contracts-backend.js`, and `dashboard-backend.js` load database-backed client, payment, and dashboard data while preserving the existing CSS and layout.
- Contract and payment statuses are displayed separately. No automatic overdue, renewal, or contract-status logic is applied.
- Email, staff registration, password recovery, and user management require approved workflows and backend integrations.

## PHP and MySQL integration points
- `backend/api/` contains authenticated client, payment, dashboard, CSRF-token, and obligation-override endpoints.
- Shared auth, validation, transaction, calculation, and JSON helpers are under `backend/lib/`.
- Login uses username and password verification, rotates the session ID, checks active admins, and rate-limits failed attempts by remote-IP bucket. Logout destroys the server session.
- The attempt limit is five failures per 15-minute window per remote IP; shared NAT users may share a bucket, and proxied deployments must configure trusted client-IP handling before relying on this limit.
- Payment status recalculates transactionally after client obligation changes, overrides, and payment create/edit/delete operations. Edits and deletes require a reason; each payment action is audited.
- Three additive migrations are in `backend/migrations/`. They were applied to the local development database after a private pre-migration dump was verified.
- Migrations are one-time, ordered SQL files (001, 002, then 003); do not rerun them on this already-updated database. For a fresh/pre-migration copy, take and verify a backup, select `kaffeepinoy_dev` in phpMyAdmin, and import each SQL file in order exactly once. Do not put database passwords into project files or chat.
- Reconcile existing payment statuses with `C:\xampp\php\php.exe ..\backend\migrations\recalculate-payment-status.php`; incomplete or invalid obligation records are skipped and counted.
- Configure the email provider and send messages from PHP. Do not expose provider credentials in browser JavaScript.

## Limitations
- The database configuration remains local-development oriented; production deployment requires protected configuration, a restricted database account, HTTPS, and a reviewed backup/restore procedure.
- Email delivery, refunds, and undefined renewal/equipment dashboard metrics remain unimplemented.
- No standalone logo asset was found; the login uses the supplied reference image. Replace it with an approved standalone logo when available.
