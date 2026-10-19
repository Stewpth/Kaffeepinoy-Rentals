window.KPR = window.KPR || {};
window.KPR.Pages = window.KPR.Pages || {};
window.KPR.Pages.dashboard = async () => {
  const count = (label, value, hint) =>
    `<article class="stat">
      <div class="stat-label">${label}</div>
      <div class="stat-value">${value}</div>
      <div class="stat-hint">${hint}</div>
    </article>`;
  const { clientTable } = window.KPR.view;
  window.KPR.renderShell(
    "Dashboard",
    "dashboard",
    `<div class="page-head">
      <div>
        <h1>Dashboard</h1>
        <p class="muted">A summary of client and account records.</p>
      </div>
        <a class="button primary" href="client-form.php">＋ Add client</a>
    </div>
    <div id="dashboard-content">
      <div class="empty">Loading dashboard…</div>
    </div>`,
  );
  try {
    const result = await window.KPR.api.request("dashboard.php");
    const c = result.counts;
    const rows = result.recent.map((row) => ({
      ...row,
      start: row.contract_start,
      end: row.contract_end,
      status: row.contract_status,
    }));
    document.querySelector("#dashboard-content").innerHTML =
      `<div class="alert">Counts are calculated from records stored in the KaffeePinoy database.</div><div class="stats">${count("Total clients", c.total_clients, "All saved client records")}${count("Ongoing contracts", c.ongoing_contracts, "Contract status: Ongoing")}${count("Fully paid accounts", c.fully_paid_accounts, "Payment status: Fully Paid")}${count("Pulled-out accounts", c.pulled_out_accounts, "Contract status: Pulled Out")}</div><section class="panel"><div class="panel-head"><div><h3>Recent client records</h3><span class="muted">Latest saved entries</span></div><a class="button subtle" href="client-list.php">View all clients</a></div>${clientTable(rows)}</section><p class="footer-note">Pending renewal and deployed equipment metrics remain undefined and are not included.</p>`;
  } catch (error) {
    document.querySelector("#dashboard-content").innerHTML =
      `<div class="empty">${window.KPR.view.safe(error.message)}</div>`;
  }
};
