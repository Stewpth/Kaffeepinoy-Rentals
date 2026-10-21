window.KPR = window.KPR || {};
window.KPR.Pages = window.KPR.Pages || {};

window.KPR.Pages.login = () => {
  const { toast, validateForm } = window.KPR;
  const form = document.querySelector("#login-form");
  const message = form.querySelector(".form-message");

  form.onsubmit = async (event) => {
    event.preventDefault();

    if (!validateForm(form)) {
      return;
    }

    const submitButton = form.querySelector('button[type="submit"]');

    const identity = form.elements.identity.value.trim();
    const password = form.elements.password.value;

    message.textContent = "";
    submitButton.disabled = true;

    try {
      const response = await fetch("../../backend/login.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "same-origin",
        body: JSON.stringify({
          identity: identity,
          password: password,
        }),
      });

      const responseText = await response.text();
      let result;
      try {
        result = JSON.parse(responseText);
      } catch {
        throw new Error('The server returned an invalid response.');
      }

      if (!response.ok || !result.success) {
        message.textContent =
          result.message || "Hindi nagtagumpay ang pag-login.";
        return;
      }

      location.href = "dashboard.php";
    } catch (error) {
      message.textContent =
        "Hindi makakonekta sa server. Tiyaking tumatakbo ang Apache at MySQL.";
      console.error("Login error:", error);
    } finally {
      submitButton.disabled = false;
    }
  };

  document.querySelector("[data-toggle-password]").onclick = (event) => {
    const button = event.currentTarget;
    const passwordInput = document.querySelector("#password");

    passwordInput.type =
      passwordInput.type === "password" ? "text" : "password";

    button.setAttribute(
      "aria-label",
      passwordInput.type === "password" ? "Show password" : "Hide password",
    );
  };

  document.querySelectorAll("[data-auth-tab]").forEach((button) => {
    button.onclick = () => {
      if (button.dataset.authTab === "register") {
        toast("Staff registration is not included in the proposal scope.");
      }
    };
  });

  document.querySelectorAll("[data-placeholder]").forEach((button) => {
    button.onclick = () => toast(button.dataset.placeholder);
  });
};
