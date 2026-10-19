window.KPR = window.KPR || {};
(() => {
  const pageName = document.body.dataset.page;
  const initializePage = window.KPR.Pages?.[pageName];
  if (initializePage) initializePage();
})();
