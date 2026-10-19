window.KPR = window.KPR || {};
window.KPR.Pages = window.KPR.Pages || {};
window.KPR.Pages.dashboard = () => {
  const records = window.KPR.storage.all();
  const count = (label, value, hint) => `<article class="stat"><div class="stat-label">${label}</div><div class="stat-value">${value}</div><div class="stat-hint">${hint}</div></article>`;
  const { clientTable } = window.KPR.view;
  window.KPR.renderShell("Dashboard", "dashboard", `
    <div class="page-head"><div><h1>Dashboard</h1><p class="muted">A summary of demonstration client records.</p></div><a class="button primary" href="client-form.html">＋ Add client</a></div>
    <div class="alert">Sample data only. Changes are saved in this browser and are not connected to business records.</div>
    <div class="stats">${count("Total clients", records.length, "All saved client records")}${count("Ongoing contracts", records.filter((client) => client.status === "Ongoing").length, "Status recorded as Ongoing")}${count("Fully paid accounts", records.filter((client) => client.status === "Fully Paid").length, "Status recorded as Fully Paid")}${count("Pulled-out accounts", records.filter((client) => client.status === "Pulled Out").length, "Status recorded as Pulled Out")}</div>
    <section class="panel"><div class="panel-head"><div><h3>Recent client records</h3><span class="muted">Latest entries in this prototype</span></div><a class="button subtle" href="client-list.html">View all clients</a></div>${clientTable(records.slice(-5).reverse())}</section>
    <p class="footer-note">Counts are calculated from sample records. The proposal's pending renewal and deployed equipment metrics need definitions before inclusion.</p>`);
};
