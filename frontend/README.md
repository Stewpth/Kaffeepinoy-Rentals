# KaffeePinoy Rentals — Client Management Prototype

PHP-authenticated admin portal with the existing HTML, CSS, and vanilla JavaScript interface. Client and payment workflows use the PHP JSON API and MySQL database.

## Run
Run through XAMPP Apache at `/Kaffepinoy-project/frontend/`. Do not open pages as local files; PHP sessions and API requests require Apache.

## Pages
- `pages/login.html` — reference-inspired username/password login
- `pages/dashboard.php` — database-backed summary counts
- `pages/client-list.php` — searchable client list
- `pages/client-form.php` — add or edit a client
- `pages/client-details.php` — client, contract, obligation, and payment details
- `pages/contract-monitoring.php` — contract view and separate status filters

`index.html` forwards to the login page. `clients.php` and `contracts.html` are compatibility redirects. There is no public registration or password-recovery workflow.

## Structure
- The login references the supplied image in the workspace `references` folder; no standalone logo file was provided, so no duplicate asset was added.
- `css/base/` — reset, design tokens, and typography.
- `css/components/` — layout, buttons, forms, tables, badges, and notifications.
- `css/pages/` — page-specific layout adjustments. `css/main.css` imports the stylesheets in order.
- `js/data/` — legacy fictional sample records; authenticated PHP pages do not load them.
- `js/services/storage-service.js` — API adapter; authenticated workflows use MySQL rather than browser storage.
- `js/components/` — shared navigation, rendering helpers, validation, and toast feedback.
- `js/pages/` — login, dashboard, client, and contract page controllers.
- `js/main.js` — initializes the controller selected by the page's `data-page` attribute.
- `docs/` — proposal checklist and implementation notes.

## Backend notes
Apply reviewed SQL migrations in `../backend/migrations/` only after a verified backup. Run payment-status reconciliation with `C:\xampp\php\php.exe ..\backend\migrations\recalculate-payment-status.php`. Email delivery, refunds, and undefined renewal metrics remain out of scope. See `docs/implementation-notes.md` for API details.
