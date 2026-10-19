window.KPR = window.KPR || {};
(() => {
  const safe = (value) => String(value ?? "").replace(/[&<>"']/g, (char) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[char]);
  const money = (value) => "₱" + Number(value || 0).toLocaleString("en-PH");
  const statusClass = (status) => status === "Ongoing" ? "ongoing" : status === "Fully Paid" ? "paid" : status === "Pulled Out" ? "pulled" : "pending";
  const badge = (status) => `<span class="badge ${statusClass(status)}">${safe(status)}</span>`;
  const clientRow = (client) => `<tr><td><div class="client-name">${safe(client.name)}</div><div class="subcell">${safe(client.id)}</div></td><td>${safe(client.program)}</td><td>${safe(client.start)}</td><td>${safe(client.end)}</td><td>${badge(client.status)}</td><td><div class="row-actions"><a class="small-button" href="client-details.html?id=${encodeURIComponent(client.id)}">View</a><a class="small-button" href="client-form.html?id=${encodeURIComponent(client.id)}">Edit</a></div></td></tr>`;
  const clientTable = (records) => `<div class="table-card"><div class="table-scroll"><table><thead><tr><th>Client</th><th>Program</th><th>Contract start</th><th>Contract end</th><th>Status</th><th>Actions</th></tr></thead><tbody>${records.map(clientRow).join("")}</tbody></table>${records.length ? "" : '<div class="empty">No client records match your search.</div>'}</div></div>`;
  window.KPR.view = { safe, money, badge, clientTable };
})();
