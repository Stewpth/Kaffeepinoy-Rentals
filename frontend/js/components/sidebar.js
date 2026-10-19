window.KPR = window.KPR || {};
window.KPR.renderShell = (title, activePage, content) => {
  const escape = window.KPR.view.safe;
  document.body.innerHTML = `
    <div class="shell">
      <aside class="sidebar" id="sidebar">
        <div class="side-brand"><div class="side-mark" aria-hidden="true">☕</div><div><b>KaffeePinoy</b><small>Rentals admin</small></div></div>
        <div class="nav-label">Workspace</div>
        <a class="nav-link ${activePage === "dashboard" ? "active" : ""}" href="dashboard.php">▦ Dashboard</a>
        <a class="nav-link ${activePage === "clients" ? "active" : ""}" href="client-list.php">♙ Clients</a>
        <a class="nav-link ${activePage === "contracts" ? "active" : ""}" href="contract-monitoring.php">▤ Contract monitoring</a>
        <div class="side-foot">Prototype data is stored in this browser only.</div>
        <button class="nav-link" id="signout" type="button">↪ Sign out</button>
      </aside>
      <main class="main">
        <header class="topbar"><div><button class="mobile-menu" id="menu" type="button" aria-label="Open navigation">☰</button><span class="topbar-title">${escape(title)}</span></div><div class="top-actions"><span class="muted">KPR Admin Staff · Demo</span><div class="avatar" aria-hidden="true">A</div></div></header>
        <section class="content">${content}</section>
      </main>
    </div>
    <div id="toast" class="toast" role="status" aria-live="polite"></div>`;

  document.querySelector("#menu").onclick = () =>
    document.querySelector("#sidebar").classList.toggle("open");
  document.querySelector("#signout").onclick = () => {
    sessionStorage.removeItem("kpr-demo-session");
    location.href = "login.html";
  };
  document.querySelectorAll("[data-email-placeholder]").forEach((button) => {
    button.onclick = () =>
      window.KPR.toast(
        "Email is not sent: connect the approved PHP email API first.",
      );
  });
};
