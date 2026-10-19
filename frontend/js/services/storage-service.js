window.KPR = window.KPR || {};
(() => {
  const storageKey = "kpr-demo-clients-v1";
  const clone = (records) => structuredClone(records);

  window.KPR.storage = {
    all() {
      try {
        const saved = localStorage.getItem(storageKey);
        if (saved) return JSON.parse(saved);
      } catch (error) {
        console.warn("Demo storage is unavailable; showing sample records.", error);
      }
      return clone(window.KPR.sampleClients);
    },
    save(records) {
      localStorage.setItem(storageKey, JSON.stringify(records));
    },
    reset() {
      localStorage.removeItem(storageKey);
    },
  };
})();
