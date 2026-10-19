window.KPR = window.KPR || {};
window.KPR.Pages = window.KPR.Pages || {};
window.KPR.Pages.contracts = async () => {
  const { storage, view, renderShell } = window.KPR;
  renderShell("Contract monitoring", "contracts", `<div class="page-head"><div><h1>Contract monitoring</h1><p class="muted">Review recorded contract terms and separate status values.</p></div></div><section class="panel"><div class="toolbar"><input id="search" type="search" placeholder="Search client, unit, or contract…" aria-label="Search contracts"><select id="contract-filter" aria-label="Filter by contract status"><option value="">All contract statuses</option><option>Ongoing</option><option>Pulled Out</option></select><select id="payment-filter" aria-label="Filter by payment status"><option value="">All payment statuses</option><option>Unpaid</option><option>Partially Paid</option><option>Fully Paid</option></select></div><div id="table" style="margin-top:16px"><div class="empty">Loading contracts…</div></div></section><p class="footer-note">Contract and payment statuses are independent. No automatic overdue or renewal rule is applied.</p>`);
  const draw = async () => {
    const query = document.querySelector("#search").value.toLowerCase();
    const contract = document.querySelector("#contract-filter").value;
    const payment = document.querySelector("#payment-filter").value;
    const target = document.querySelector("#table");
    try {
      const records = await storage.all();
      const matching = records.filter((c) => (!contract || c.status === contract) && (!payment || c.paymentStatus === payment) && `${c.name} ${c.id} ${c.unitId} ${c.serialNumber} ${c.program}`.toLowerCase().includes(query));
      target.innerHTML = `<div class="table-card"><div class="table-scroll"><table><thead><tr><th>Client</th><th>Contract period</th><th>Deposit</th><th>Monthly fee</th><th>Payment status</th><th>Contract status</th><th></th></tr></thead><tbody>${matching.map((c) => `<tr><td><b>${view.safe(c.name)}</b><div class="subcell">${view.safe(c.unitId)} · ${view.safe(c.serialNumber)}</div></td><td class="contract-period">${view.safe(c.start)} — ${view.safe(c.end)}</td><td>${view.money(c.deposit)}</td><td>${view.money(c.monthlyFee)}</td><td>${view.badge(c.paymentStatus)}</td><td>${view.badge(c.status)}</td><td><a class="small-button" href="client-details.php?id=${encodeURIComponent(c.id)}">Details</a></td></tr>`).join("")}</tbody></table>${matching.length ? "" : '<div class="empty">No contracts match your search.</div>'}</div></div>`;
    } catch (error) { target.innerHTML = `<div class="empty">${view.safe(error.message)}</div>`; }
  };
  document.querySelector("#search").oninput = draw;
  document.querySelector("#contract-filter").onchange = draw;
  document.querySelector("#payment-filter").onchange = draw;
  await draw();
};
