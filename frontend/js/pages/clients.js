window.KPR = window.KPR || {};
window.KPR.Pages = window.KPR.Pages || {};
(() => {
  const { storage, view, renderShell, toast, validateForm } = window.KPR;
  const fields = [
    ["name", "Client name", "text"],
    ["address", "Address", "text"],
    ["job", "Job or business", "text"],
    ["start", "Contract start date", "date"],
    ["end", "Contract end date", "date"],
    ["deposit", "Deposit (₱)", "number"],
    ["monthlyFee", "Monthly fee (₱)", "number"],
    ["discountType", "Discount type", "text"],
    ["program", "Program", "text"],
    ["itemReceived", "Item received", "text"],
    ["unitId", "Unit ID", "text"],
    ["serialNumber", "Serial number", "text"],
  ];

  function clientList() {
    renderShell(
      "Clients",
      "clients",
      `<div class="page-head"><div><h1>Client master list</h1><p class="muted">Search and filter records by documented status.</p></div><a class="button primary" href="client-form.php">＋ Add client</a></div><section class="panel client-list-panel"><div class="toolbar"><input id="search" type="search" placeholder="Search name, ID, address, unit…" aria-label="Search clients"><select id="filter" aria-label="Filter by status"><option value="">All statuses</option><option>Ongoing</option><option>Fully Paid</option><option>Pulled Out</option></select></div><div id="table"></div></section>`,
    );
    const draw = () => {
      const query = document.querySelector("#search").value.toLowerCase();
      const status = document.querySelector("#filter").value;
      const matching = storage
        .all()
        .filter(
          (client) =>
            (!status || client.status === status) &&
            Object.values(client).join(" ").toLowerCase().includes(query),
        );
      document.querySelector("#table").innerHTML = view.clientTable(matching);
    };
    document.querySelector("#search").oninput = draw;
    document.querySelector("#filter").onchange = draw;
    draw();
  }

  function clientForm() {
    const id = new URLSearchParams(location.search).get("id");
    const records = storage.all();
    const existing = records.find((client) => client.id === id);
    const title = existing ? "Edit client" : "Add client";
    renderShell(
      title,
      "clients",
      `<div class="page-head"><div><h1>${title}</h1><p class="muted">Enter the client and contract fields specified in the proposal.</p></div></div><form class="panel" id="client-form"><div class="form-grid">${fields.map(([key, label, type]) => `<label class="field">${label}<input name="${key}" type="${type}" ${["name", "address", "start", "end", "monthlyFee", "program"].includes(key) ? "required" : ""} ${["deposit", "monthlyFee"].includes(key) ? 'min="0"' : ""} value="${view.safe(existing?.[key] ?? "")}"></label>`).join("")}<label class="field">Status<select name="status">${["Ongoing", "Fully Paid", "Pulled Out"].map((status) => `<option ${existing?.status === status ? "selected" : ""}>${status}</option>`).join("")}</select><small>Staff-set; no automatic status rule is applied.</small></label><label class="field">Payment marker<select name="paid"><option value="false" ${existing?.paid ? "" : "selected"}>Not marked fully paid</option><option value="true" ${existing?.paid ? "selected" : ""}>Fully paid</option></select></label></div><div class="actions"><a class="button subtle" href="client-list.php">Cancel</a><button class="button primary">Save client</button></div></form>`,
    );
    document.querySelector("#client-form").onsubmit = (event) => {
      event.preventDefault();
      const form = event.currentTarget;
      if (!validateForm(form)) return;
      const data = Object.fromEntries(new FormData(form));
      data.deposit = Number(data.deposit || 0);
      data.monthlyFee = Number(data.monthlyFee || 0);
      data.paid = data.paid === "true";
      if (existing) Object.assign(existing, data);
      else {
        data.id = `KPR-${String(Date.now()).slice(-6)}`;
        records.push(data);
      }
      storage.save(records);
      toast("Client record saved in tzhis browser.");
      location.href = "client-list.php";
    };
  }

  function clientDetails() {
    const id = new URLSearchParams(location.search).get("id");
    const client = storage.all().find((record) => record.id === id);
    if (!client) {
      renderShell(
        "Client details",
        "clients",
        '<div class="empty">Client record was not found. <a href="client-list.php">Return to client list</a></div>',
      );
      return;
    }
    const detail = (label, value) =>
      `<div><div class="detail-label">${label}</div><div class="detail-value">${view.safe(value || "—")}</div></div>`;
    renderShell(
      "Client details",
      "clients",
      `<div class="page-head"><div><h1>${view.safe(client.name)}</h1><p class="muted">${view.safe(client.id)} · ${view.badge(client.status)}</p></div><div class="row-actions"><a class="button primary" href="client-form.html?id=${encodeURIComponent(client.id)}">Edit client</a><button class="button subtle" data-email-placeholder>Send contract/payment alert</button></div></div><div class="grid-2"><section class="panel"><h3>Client information</h3><div class="detail-grid">${[
        ["Address", client.address],
        ["Job or business", client.job],
        ["Program", client.program],
        ["Discount type", client.discountType],
        ["Item received", client.itemReceived],
        ["Unit ID", client.unitId],
        ["Serial number", client.serialNumber],
      ]
        .map(([label, value]) => detail(label, value))
        .join(
          "",
        )}</div></section><section class="panel"><h3>Contract information</h3><div class="detail-grid">${[
        ["Start date", client.start],
        ["End date", client.end],
        ["Deposit", view.money(client.deposit)],
        ["Monthly fee", view.money(client.monthlyFee)],
        [
          "Payment status",
          client.status === "Fully Paid"
            ? "Fully Paid"
            : "Not marked fully paid",
        ],
      ]
        .map(([label, value]) => detail(label, value))
        .join(
          "",
        )}</div><p class="footer-note">No automatic overdue or renewal status rule is specified. Dates are displayed as entered.</p></section></div>`,
    );
  }

  window.KPR.Pages.clients = clientList;
  window.KPR.Pages.clientForm = clientForm;
  window.KPR.Pages.clientDetails = clientDetails;
})();
