# Requirements checklist

Source: `proposal/Web-Based Client Management System for KaffeePinoy Rentals.pdf` (5 pages). This records stated scope and keeps ambiguities visible.

## Purpose and objectives
- [x] Replace the Excel based client-record and monitoring workflow with a web-based admin portal.
- [x] Centralize client data, make records searchable, and let staff update payment/contract status.
- [x] Proposal objectives include a searchable Master List, a one-click email API for contract receipts/payment alerts, and a beginner-friendly MySQL table matching the existing record structure.

## Scope and limits
- [x] Admin login for authorized KPR personnel, accessed through a web browser.
- [x] Client encoding form and searchable Master List with a status filter.
- [x] Status update capability and email notices (provider/API details unspecified).
- [x] Dashboard summary counts.
- [x] POS hardware integration, direct Facebook Messenger API integration, and native mobile app are explicitly out of scope.

## Client fields
- [x] Name; address; job; contract start and end; deposit; monthly fee; discount type; program; item received; unit ID; serial number; status.
- [x] Contract statuses: Ongoing, Pulled Out. Payment statuses are separate: Unpaid, Partially Paid, Fully Paid.
- [x] Monthly anniversary duration; a partial contract month counts as a month, with end-of-month anniversaries clamped to the target month's last day.
- [x] Monthly percentage discount uses numeric discount_percent (0–100%) and applies to monthly fee, not deposit. Deposit is included in the obligation.
- [x] Payment status is calculated from valid payment rows and final obligation; overpayment remains Fully Paid without warning.
- [x] Early pull-out can use a reasoned absolute final-obligation override, preserving original terms.
- [x] Admins may edit/delete payments with a reason; payment changes are audited and status is recalculated. Refund workflow is deferred.

## Roles
- [x] KPR Admin Staff: encode client details, search client files, update payment/contract status, dispatch email alerts.
- [ ] Separate permissions or additional roles are not defined; none are inferred.

## Pages and components
- [x] Reference-inspired login interface connected to username/password authentication.
- [x] Dashboard layout retained; counts now use separate contract and payment status values. Pending renewal and equipment metrics remain undefined.
- [x] Client Master List with text search and status filter.
- [x] Add/edit form, client details, and status entry.
- [x] Contract monitoring view showing terms, dates, fees, payment status, and contract status separately.
- [x] Responsive navigation, shared top bar, tables, badges, and form feedback.
- [ ] Screenshot shows Register and Forgot Password controls, but proposal provides no such workflows. Register explains the boundary; password recovery is an explanatory placeholder.
- [ ] Email API is a proposal feature but needs backend, provider choice, templates, and trigger definitions; no email is sent in this frontend.
- [ ] User/staff management and client deletion are not specified and are not added.

## Unclear or inconsistent proposal details
- Dashboard scope lists total active clients, ongoing contracts, and fully paid accounts. A feature description also mentions pending renewals and deployed equipment units. Definitions and supporting data are unclear, so only unambiguous counts are displayed.
- “Active client” is not explicitly mapped to a status; the prototype displays total records and the Ongoing count separately.
- No overdue, renewal warning, contract-end transition, payment schedule, or automatic contract status rules are supplied. Contract status is never derived from dates.
- Email API provider, templates, triggers, and credentials are unspecified.
- “Real-time” search operates only on local demonstration data here; there is no shared database.
