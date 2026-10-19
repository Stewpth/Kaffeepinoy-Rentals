window.KPR = window.KPR || {};
window.KPR.Pages = window.KPR.Pages || {};
window.KPR.Pages.login = () => {
  const { toast, validateForm } = window.KPR;
  document.querySelector("#login-form").onsubmit = (event) => {
    event.preventDefault();
    if (!validateForm(event.currentTarget)) return;
    sessionStorage.setItem("kpr-demo-session", "1");
    location.href = "dashboard.html";
  };
  document.querySelector("[data-toggle-password]").onclick = (event) => {
    const button = event.currentTarget;
    const password = document.querySelector("#password");
    password.type = password.type === "password" ? "text" : "password";
    button.setAttribute("aria-label", password.type === "password" ? "Show password" : "Hide password");
  };
  document.querySelectorAll("[data-auth-tab]").forEach((button) => {
    button.onclick = () => button.dataset.authTab === "register"
      ? toast("Staff registration is not included in the proposal scope.")
      : toast("Demo sign-in only; no account is authenticated.");
  });
  document.querySelectorAll("[data-placeholder]").forEach((button) => {
    button.onclick = () => toast(button.dataset.placeholder);
  });
};
