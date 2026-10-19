window.KPR = window.KPR || {};
(() => {
  const base = "../../backend/api/";
  const normalize = (row) => ({
    ...row,
    id: String(row.id),
    start: row.contract_start,
    end: row.contract_end,
    monthlyFee: row.monthly_fee,
    discountType: row.discount_type,
    discountPercent: row.discount_percent,
    itemReceived: row.item_received,
    unitId: row.unit_id,
    serialNumber: row.serial_number,
    status: row.contract_status,
    paymentStatus: row.payment_status,
    finalObligationOverride: row.final_obligation_override,
    overrideReason: row.override_reason,
  });
  let tokenPromise;
  async function csrf() {
    if (!tokenPromise)
      tokenPromise = fetch(base + "csrf.php", { credentials: "same-origin" })
        .then(async (r) => {
          const data = await r.json();
          if (!r.ok) throw new Error(data.message || "Session expired.");
          return data.token;
        })
        .catch((error) => {
          tokenPromise = null;
          throw error;
        });
    return tokenPromise;
  }
  async function request(url, options = {}) {
    const headers = {
      ...(options.body ? { "Content-Type": "application/json" } : {}),
      ...(options.headers || {}),
    };
    if (options.method && options.method !== "GET")
      headers["X-CSRF-Token"] = await csrf();
    const response = await fetch(base + url, {
      credentials: "same-origin",
      ...options,
      headers,
    });
    const result = await response.json().catch(() => ({ success: false, message: "The server returned an invalid response." }));
    if (response.status === 401) {
      location.href = "login.html";
      throw new Error("Your session expired. Please sign in again.");
    }
    if (!response.ok || !result.success)
      throw new Error(result.message || "Request failed.");
    return result;
  }
  const payload = (client) => ({
    name: client.name,
    address: client.address,
    job: client.job,
    contract_start: client.start,
    contract_end: client.end,
    deposit: String(client.deposit ?? ""),
    monthly_fee: String(client.monthlyFee ?? ""),
    discount_type: client.discountType || "",
    discount_percent: String(client.discountPercent ?? "0"),
    program: client.program || "",
    item_received: client.itemReceived || "",
    unit_id: client.unitId || "",
    serial_number: client.serialNumber || "",
    contract_status: client.status,
  });
  window.KPR.api = {
    request,
    csrf,
    async payments(clientId) {
      return (
        await request(`payments.php?client_id=${encodeURIComponent(clientId)}`)
      ).payments;
    },
    async addPayment(clientId, value) {
      return request("payments.php", {
        method: "POST",
        body: JSON.stringify({
          client_id: Number(clientId),
          amount: value.amount,
          payment_date: value.payment_date,
        }),
      });
    },
    async editPayment(id, value) {
      return request(`payments.php?id=${encodeURIComponent(id)}`, {
        method: "PUT",
        body: JSON.stringify(value),
      });
    },
    async deletePayment(id, reason) {
      return request(`payments.php?id=${encodeURIComponent(id)}`, {
        method: "DELETE",
        body: JSON.stringify({ reason }),
      });
    },
    async override(clientId, amount, reason) {
      return request(
        `obligation-override.php?client_id=${encodeURIComponent(clientId)}`,
        {
          method: "POST",
          body: JSON.stringify({ final_obligation_override: amount, reason }),
        },
      );
    },
  };
  window.KPR.storage = {
    async all() {
      return (await request("clients.php")).clients.map(normalize);
    },
    async one(id) {
      return normalize(
        (await request(`client.php?id=${encodeURIComponent(id)}`)).client,
      );
    },
    async create(client) {
      return request("clients.php", {
        method: "POST",
        body: JSON.stringify(payload(client)),
      });
    },
    async update(id, client) {
      return request(`client.php?id=${encodeURIComponent(id)}`, {
        method: "PUT",
        body: JSON.stringify(payload(client)),
      });
    },
  };
})();
