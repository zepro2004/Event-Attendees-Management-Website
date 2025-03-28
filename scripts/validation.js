// validation.js

const validateRegistration = async (event) => {
  event.preventDefault();

  const inputs = document.querySelectorAll("#registrationForm input");
  inputs.forEach(clearAddedError);

  let isValid = true;

  const inputData = [
    {
      id: "username",
      value: document.getElementById("username").value,
      message: "Username should not be empty and between 3-20 characters",
    },
    {
      id: "first_name",
      value: document.getElementById("first_name").value,
      message: "First name should not be empty",
    },
    {
      id: "last_name",
      value: document.getElementById("last_name").value,
      message: "Last name should not be empty",
    },
    {
      id: "email",
      value: document.getElementById("email").value,
      message: "Email should not be empty and be with the format xyz@xyz.xyz",
    },
    {
      id: "password",
      value: document.getElementById("password").value,
      message:
        "Password must be at least 8 characters long with at least 1 uppercase letter and 1 number",
    },
    {
      id: "confirmPassword",
      value: document.getElementById("confirmPassword").value,
      message: "Passwords must match",
    },
  ];

  for (const input of inputData) {
    if (!inputFormat(input.id, input.value)) {
      isValid = false;
      displayError(input.id, input.message);
    }
  }

  if (isValid) {
    const username = document.getElementById("username").value;
    const available = await checkUsernameAvailability(username);

    if (!available) {
      isValid = false;
      displayError("username", "Username is already taken!");
    }
  }

  if (isValid) {
    document.getElementById("registrationForm").submit();
  }
};

const validateLogin = (event) => {
  event.preventDefault();

  const inputs = document.querySelectorAll("#loginForm input");
  inputs.forEach(clearAddedError);

  let isValid = true;

  const inputData = [
    {
      id: "username",
      value: document.getElementById("username").value,
      message: "Username should not be empty",
    },
    {
      id: "password",
      value: document.getElementById("password").value,
      message: "Password should not be empty",
    },
  ];

  for (const input of inputData) {
    if (!inputFormat(input.id, input.value)) {
      isValid = false;
      displayError(input.id, input.message);
    }
  }

  if (isValid) {
    document.getElementById("loginForm").submit();
  }
};

const inputFormat = (inputId, value) => {
  switch (inputId) {
    case "username":
      return value.trim() !== "" && value.length >= 3 && value.length <= 20;

    case "first_name":
    case "last_name":
      // Just ensure not empty and only letters, spaces, or hyphens
      return value.trim() !== "" && /^[A-Za-z\s-]+$/.test(value);

    case "email":
      let emailFormat = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailFormat.test(value);

    case "password":
      // At least 8 chars, with 1 uppercase and 1 number
      let passwordFormat = /^(?=.*[A-Z])(?=.*\d).{8,}$/;
      return passwordFormat.test(value);

    case "confirmPassword":
      const password = document.getElementById("password").value;
      return value === password && value !== "";

    default:
      return true;
  }
};

const displayError = (inputId, message) => {
  const input = document.getElementById(inputId);
  input.classList.add("error");

  // Find the error span element
  const errorSpan = document.getElementById(`${inputId}Error`);

  if (errorSpan) {
    // Update existing error span
    errorSpan.textContent = message;
  } else {
    // Create new error span if not found
    let errorMessage = document.createElement("span");
    errorMessage.classList.add("error-message");
    errorMessage.id = `${inputId}Error`;
    errorMessage.textContent = message;
    input.insertAdjacentElement("afterend", errorMessage);
  }
};

const clearErrorOnInput = (event) => {
  const input = event.target;
  const id = input.id;
  const value = input.value;

  if (input.classList.contains("error") && inputFormat(id, value)) {
    clearAddedError(input);
  }
};

const clearAddedError = (input) => {
  if (!input || !input.id) return; // Ensure input exists

  input.classList.remove("error");

  // Find the error span for this input
  const errorSpan = document.getElementById(`${input.id}Error`);
  if (errorSpan) {
    errorSpan.remove(); // Properly remove error messages
  }
};

const checkUsernameAvailability = async (username) => {
  try {
    const response = await fetch(
      `../server/form_handlers.php?check_username=1&username=${encodeURIComponent(
        username
      )}`
    );
    const data = await response.json();
    return !data.taken; // Return true if username is available
  } catch (error) {
    console.error("Error checking username:", error);
    return true; // On error, allow submission to continue to server
  }
};

document.addEventListener("DOMContentLoaded", function () {
  const registrationForm = document.getElementById("registrationForm");
  const loginForm = document.getElementById("loginForm");
  const usernameInput = document.getElementById("username");

  if (registrationForm) {
    registrationForm.addEventListener("submit", (e) => {
      e.preventDefault();
      validateRegistration(e);
    });

    const regInputs = document.querySelectorAll("#registrationForm input");
    regInputs.forEach((input) => {
      input.addEventListener("input", clearErrorOnInput);
    });
  }

  if (loginForm) {
    loginForm.addEventListener("submit", validateLogin);

    const loginInputs = document.querySelectorAll("#loginForm input");
    loginInputs.forEach((input) => {
      input.addEventListener("input", clearErrorOnInput);
    });
  }

  if (usernameInput && registrationForm) {
    usernameInput.addEventListener("blur", async function () {
      const username = this.value.trim();

      if (inputFormat("username", username)) {
        const available = await checkUsernameAvailability(username);

        if (!available) {
          displayError("username", "Username is already taken!");
        }
      }
    });
  }
});
