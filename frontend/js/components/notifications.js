window.KPR = window.KPR || {};
window.KPR.toast = (message) => {
  const notice = document.querySelector("#toast");
  if (!notice) return;
  notice.textContent = message;
  notice.classList.add("show");
  window.setTimeout(() => notice.classList.remove("show"), 2600);
};
