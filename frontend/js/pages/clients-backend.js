window.KPR = window.KPR || {};
window.KPR.Pages = window.KPR.Pages || {};
(() => {
  const { storage, api, view, renderShell, toast, validateForm } = window.KPR;
  const fields = [
    ["name", "Client name", "text"],
    ["address", "Address", "text"],
    ["job", "Job or business", "text"],
    ["start", "Contract start date", "date"],
    ["end", "Contract end date", "date"],
    ["deposit", "Deposit (₱)", "number"],
    ["monthlyFee", "Monthly fee (₱)", "number"],
    ["discountType", "Discount type", "text"],
    ["discountPercent", "Monthly discount (%)", "number"],
    ["program", "Program", "text"],
    ["itemReceived", "Item received", "text"],
    ["unitId", "Unit ID", "text"],
    ["serialNumber", "Serial number", "text"],
  ];
  const safeError = (error) => view.safe(error.message || "Request failed.");

  async function clientList() {
    renderShell(
      "Clients",
      "clients",
      `<div class="page-head"><div><h1>Client master list</h1><p class="muted">Search and filter records by contract status.</p></div><a class="button primary" href="client-form.php">＋ Add client</a></div><section class="panel client-list-panel"><div class="toolbar"><input id="search" type="search" placeholder="Search name, ID, address, unit…" aria-label="Search clients"><select id="filter" aria-label="Filter by contract status"><option value="">All contract statuses</option><option>Ongoing</option><option>Pulled Out</option></select></div><div id="table"><div class="empty">Loading clients…</div></div></section>`,
    );
    const draw = async () => {
      const query = document
        .querySelector("#search")
        .value.trim()
        .toLowerCase();
      const status = document.querySelector("#filter").value;
      try {
        const records = await storage.all();
        const rows = records.filter(
          (client) =>
            (!status || client.status === status) &&
            Object.values(client).join(" ").toLowerCase().includes(query),
        );
        document.querySelector("#table").innerHTML = view.clientTable(rows);
      } catch (error) {
        document.querySelector("#table").innerHTML =
          `<div class="empty">${safeError(error)}</div>`;
      }
    };
    document.querySelector("#search").oninput = draw;
    document.querySelector("#filter").onchange = draw;
    await draw();
  }

  async function clientForm() {
    const id = new URLSearchParams(location.search).get("id");
    let client = null;
    if (id) {
      try {
        client = await storage.one(id);
      } catch (error) {
        renderShell(
          "Client details",
          "clients",
          `<div class="empty">${safeError(error)} <a href="client-list.php">Return to client list</a></div>`,
        );
        return;
      }
    }
    const title = client ? "Edit client" : "Add client";
    renderShell(
      title,
      "clients",
      `<div class="page-head"><div><h1>${title}</h1><p class="muted">Enter the client and contract fields specified in the proposal.</p></div></div><form class="panel" id="client-form"><div class="form-grid">${fields
        .map(([key, label, type]) => {
          const required = [
            "name",
            "start",
            "end",
            "deposit",
            "monthlyFee",
          ].includes(key);
          const max =
            key === "discountPercent"
              ? ' min="0" max="100" step="0.01"'
              : ["deposit", "monthlyFee"].includes(key)
                ? ' min="0" step="0.01"'
                : "";
          return `<label class="field">${label}<input name="${key}" type="${type}"${required ? " required" : ""}${max} value="${view.safe(client?.[key] ?? (key === "discountPercent" ? "0.00" : ""))}"></label>`;
        })
        .join(
          "",
        )}<label class="field">Contract status<select name="status">${["Ongoing", "Pulled Out"].map((status) => `<option${client?.status === status ? " selected" : ""}>${status}</option>`).join("")}</select><small>Contract status is independent of payment status.</small></label></div><p class="form-message" aria-live="polite"></p><div class="actions"><a class="button subtle" href="client-list.php">Cancel</a><button class="button primary">Save client</button></div></form>`,
    );
    document.querySelector("#client-form").onsubmit = async (event) => {
      event.preventDefault();
      const form = event.currentTarget;
      if (!validateForm(form)) return;
      const data = Object.fromEntries(new FormData(form));
      try {
        if (client) await storage.update(id, data);
        else await storage.create(data);
        toast("Client record saved.");
        location.href = "client-list.php";
      } catch (error) {
        form.querySelector(".form-message").textContent =
          error.message || "Could not save client.";
      }
    };
  }

  async function clientDetails() {
    const id = new URLSearchParams(location.search).get("id");
    let client;
    try {
      client = await storage.one(id);
    } catch (error) {
      renderShell(
        "Client details",
        "clients",
        `<div class="empty">${safeError(error)} <a href="client-list.php">Return to client list</a></div>`,
      );
      return;
    }
    const detail = (label, value) =>
      `<div><div class="detail-label">${view.safe(label)}</div><div class="detail-value">${view.safe(value ?? "—")}</div></div>`;
    const amount = (value) =>
      `₱${Number(value || 0).toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    renderShell(
      "Client details",
      "clients",
      `<div class="page-head"><div><h1>${view.safe(client.name)}</h1><p class="muted">ID ${view.safe(client.id)} · Contract ${view.badge(client.status)} · Payment ${view.badge(client.paymentStatus)}</p></div><div class="row-actions"><a class="button primary" href="client-form.php?id=${encodeURIComponent(client.id)}">Edit client</a><button class="button subtle" data-email-placeholder>Send contract/payment alert</button></div></div><div class="grid-2"><section class="panel"><h3>Client information</h3><div class="detail-grid">${[
        ["Address", client.address],
        ["Job or business", client.job],
        ["Program", client.program],
        ["Discount type", client.discountType],
        ["Discount percent", `${client.discountPercent}%`],
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
        ["Deposit", amount(client.deposit)],
        ["Monthly fee", amount(client.monthlyFee)],
        ["Payment status", client.paymentStatus],
        [
          "Calculated obligation",
          client.calculated_obligation == null
            ? "Incomplete contract data"
            : amount(client.calculated_obligation),
        ],
        [
          "Final obligation used",
          client.final_obligation == null
            ? "Not available"
            : amount(client.final_obligation),
        ],
        ["Valid payments recorded", amount(client.total_paid)],
        ["Override reason", client.overrideReason],
      ]
        .map(([label, value]) => detail(label, value))
        .join(
          "",
        )}</div>${client.status === "Pulled Out" ? `<form id="override-form" class="form-stack"><h3>Set final obligation</h3><label>Final obligation amount<input name="amount" type="number" min="0" step="0.01" value="${view.safe(client.finalObligationOverride ?? "")}" required></label><label>Reason<input name="reason" maxlength="500" value="${view.safe(client.overrideReason ?? "")}" required></label><p class="form-message" aria-live="polite"></p><button class="button subtle">Save override</button></form>` : ""}<p class="footer-note">No automatic overdue or renewal rule is applied.</p></section></div><section class="panel"><h3>Payment history</h3><form id="payment-form" class="toolbar"><label>Amount<input name="amount" type="number" min="0.01" step="0.01" required></label><label>Payment date<input name="payment_date" type="date" required></label><button class="button primary">Record payment</button></form><p class="form-message" id="payment-message" aria-live="polite"></p><div id="payments"><div class="empty">Loading payments…</div></div></section>`,
    );
    const drawPayments = async () => {
      const target = document.querySelector("#payments");
      try {
        const rows = await api.payments(client.id);
        target.innerHTML = rows.length
          ? `<div class="table-card"><div class="table-scroll"><table><thead><tr><th>Amount</th><th>Payment date</th><th>Recorded</th><th></th></tr></thead><tbody>${rows.map((p) => `<tr><td>${amount(p.amount)}</td><td>${view.safe(p.payment_date)}</td><td>${view.safe(p.created_at)}</td><td><button class="small-button" data-edit-payment="${p.id}">Edit</button> <button class="small-button" data-delete-payment="${p.id}">Delete</button></td></tr>`).join("")}</tbody></table></div></div>`
          : '<div class="empty">No payment records.</div>';
        target.querySelectorAll("[data-edit-payment]").forEach(
          (button) =>
            (button.onclick = async () => {
              const row = rows.find(
                (p) => String(p.id) === button.dataset.editPayment,
              );
              const value = prompt("Payment amount", row.amount);
              if (value === null) return;
              const date = prompt(
                "Payment date (YYYY-MM-DD)",
                row.payment_date,
              );
              if (date === null) return;
              const reason = prompt("Reason for editing this payment");
              if (!reason?.trim()) return;
              try {
                await api.editPayment(row.id, {
                  amount: value,
                  payment_date: date,
                  reason,
                });
                location.reload();
              } catch (error) {
                document.querySelector("#payment-message").textContent =
                  error.message;
              }
            }),
        );
        target.querySelectorAll("[data-delete-payment]").forEach(
          (button) =>
            (button.onclick = async () => {
              const reason = prompt("Reason for deleting this payment");
              if (!reason?.trim()) return;
              if (!confirm("Delete this payment record?")) return;
              try {
                await api.deletePayment(button.dataset.deletePayment, reason);
                location.reload();
              } catch (error) {
                document.querySelector("#payment-message").textContent =
                  error.message;
              }
            }),
        );
      } catch (error) {
        target.innerHTML = `<div class="empty">${safeError(error)}</div>`;
      }
    };
    await drawPayments();
    document.querySelector("#payment-form").onsubmit = async (event) => {
      event.preventDefault();
      const form = event.currentTarget;
      try {
        await api.addPayment(client.id, Object.fromEntries(new FormData(form)));
        location.reload();
      } catch (error) {
        document.querySelector("#payment-message").textContent = error.message;
      }
    };
    const override = document.querySelector("#override-form");
    if (override)
      override.onsubmit = async (event) => {
        event.preventDefault();
        const values = Object.fromEntries(new FormData(override));
        try {
          await api.override(client.id, values.amount, values.reason);
          location.reload();
        } catch (error) {
          override.querySelector(".form-message").textContent = error.message;
        }
      };
  }

  window.KPR.Pages.clients = clientList;
  window.KPR.Pages.clientForm = clientForm;
  window.KPR.Pages.clientDetails = clientDetails;
})();
