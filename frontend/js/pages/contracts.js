window.KPR = window.KPR || {};
window.KPR.Pages = window.KPR.Pages || {};
window.KPR.Pages.contracts = () => {
  const { storage, view, renderShell } = window.KPR;
  renderShell("Contract monitoring", "contracts", `<div class="page-head"><div><h1>Contract monitoring</h1><p class="muted">Review recorded contract terms and staff-set statuses.</p></div></div><section class="panel"><div class="toolbar"><input id="search" type="search" placeholder="Search client, unit, or contract…" aria-label="Search contracts"><select id="filter" aria-label="Filter by status"><option value="">All statuses</option><option>Ongoing</option><option>Fully Paid</option><option>Pulled Out</option></select></div><div id="table" style="margin-top:16px"></div></section><p class="footer-note">Dates, deposits, monthly fees and statuses are displayed as entered. The proposal does not define overdue or upcoming-event rules.</p>`);
  const draw = () => {
    const query = document.querySelector("#search").value.toLowerCase();
    const status = document.querySelector("#filter").value;
    const matching = storage.all().filter((client) => (!status || client.status === status) && `${client.name} ${client.id} ${client.unitId} ${client.serialNumber} ${client.program}`.toLowerCase().includes(query));
    document.querySelector("#table").innerHTML = `<div class="table-card"><div class="table-scroll"><table><thead><tr><th>Client</th><th>Contract period</th><th>Deposit</th><th>Monthly fee</th><th>Payment</th><th>Status</th><th></th></tr></thead><tbody>${matching.map((client) => `<tr><td><b>${view.safe(client.name)}</b><div class="subcell">${view.safe(client.unitId)} · ${view.safe(client.serialNumber)}</div></td><td class="contract-period">${view.safe(client.start)} — ${view.safe(client.end)}</td><td>${view.money(client.deposit)}</td><td>${view.money(client.monthlyFee)}</td><td>${client.paid ? view.badge("Fully Paid") : view.badge("Not marked paid")}</td><td>${view.badge(client.status)}</td><td><a class="small-button" href="client-details.html?id=${encodeURIComponent(client.id)}">Details</a></td></tr>`).join("")}</tbody></table>${matching.length ? "" : '<div class="empty">No contracts match your search.</div>'}</div></div>`;
  };
  document.querySelector("#search").oninput = draw;
  document.querySelector("#filter").onchange = draw;
  draw();
};
