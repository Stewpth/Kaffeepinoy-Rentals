# KaffeePinoy Rentals — Client Management Prototype

Responsive HTML, CSS, and vanilla JavaScript frontend based on the project proposal and login reference. It is a browser-only demonstration; it does not authenticate users or connect to PHP/MySQL.

## Run
Open `index.html` in a browser. For predictable asset loading, serve the workspace with a static server and open `/frontend/index.html`.

## Pages
- `pages/login.html` — reference-inspired demonstration login
- `pages/dashboard.html` — computed summary counts
- `pages/client-list.html` — searchable, filterable Master List
- `pages/client-form.html` — add or edit a client
- `pages/client-details.html` — client and contract information
- `pages/contract-monitoring.html` — contract view and filtering

`index.html` forwards to the login page. The old `clients.html` and `contracts.html` paths remain as redirects for compatibility. User management and password-change pages are omitted because the proposal does not define them.

## Structure
- The login references the supplied image in the workspace `references` folder; no standalone logo file was provided, so no duplicate asset was added.
- `css/base/` — reset, design tokens, and typography.
- `css/components/` — layout, buttons, forms, tables, badges, and notifications.
- `css/pages/` — page-specific layout adjustments. `css/main.css` imports the stylesheets in order.
- `js/data/` — fictional sample client records.
- `js/services/` — browser storage adapter, ready to replace with PHP API calls.
- `js/components/` — shared navigation, rendering helpers, validation, and toast feedback.
- `js/pages/` — login, dashboard, client, and contract page controllers.
- `js/main.js` — initializes the controller selected by the page's `data-page` attribute.
- `docs/` — proposal checklist and implementation notes.

## Limitations
Demo sign-in is navigation only, not authentication. Browser storage is editable, local to one browser, and not secure. Email notices, user management, registration/recovery workflows, PHP endpoints, and MySQL remain future work. See `docs/requirements-checklist.md` for proposal ambiguities and `docs/implementation-notes.md` for integration points.
