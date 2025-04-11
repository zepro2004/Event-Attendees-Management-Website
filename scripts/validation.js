/**
 * Form Validation Script
 *
 * This script provides client-side validation for login and registration forms.
 * Features include:
 * - Real-time validation feedback
 * - Username availability checking
 * - Password strength requirements
 * - Error message display and clearing
 */

// Initialize validation when the DOM is fully loaded
document.addEventListener("DOMContentLoaded", () => {
  const regForm = document.getElementById("registrationForm");
  const loginForm = document.getElementById("loginForm");

  // Set up form listeners
  if (regForm) {
    // Prevent form submission and validate registration form
    regForm.addEventListener("submit", (e) => validateRegistration(e));

    // Clear error messages when user starts typing
    document.querySelectorAll("#registrationForm input").forEach((input) => {
      input.addEventListener("input", clearErrorOnInput);
    });

    // Check username availability when user moves away from username field
    const usernameInput = document.getElementById("username");
    if (usernameInput) {
      usernameInput.addEventListener("blur", async function () {
        const username = this.value.trim();
        if (inputFormat("username", username)) {
          if (!(await checkUsernameAvailability(username))) {
            displayError("username", "Username is already taken!");
          }
        }
      });
    }
  }

  // Set up login form validation if present on the page
  if (loginForm) {
    loginForm.addEventListener("submit", validateLogin);
    document.querySelectorAll("#loginForm input").forEach((input) => {
      input.addEventListener("input", clearErrorOnInput);
    });
  }
});

/**
 * Validates the registration form
 * Checks all required fields, formats, and username availability
 *
 * @param {Event} event - The form submission event
 */
const validateRegistration = async (event) => {
  event.preventDefault();
  document.querySelectorAll("#registrationForm input").forEach(clearAddedError);

  let isValid = true;
  // Define fields to validate with their error messages
  const fields = [
    { id: "username", msg: "Username should be 3-20 characters" },
    { id: "first_name", msg: "First name required" },
    { id: "last_name", msg: "Last name required" },
    { id: "email", msg: "Valid email required (format: user@example.com)" },
    {
      id: "password",
      msg: "Password needs 8+ chars with 1 uppercase & 1 number",
    },
    { id: "confirmPassword", msg: "Passwords must match" },
  ];

  // Check each field against its validation rule
  fields.forEach((field) => {
    const value = document.getElementById(field.id).value;
    if (!inputFormat(field.id, value)) {
      isValid = false;
      displayError(field.id, field.msg);
    }
  });

  // Check username availability via AJAX
  if (isValid) {
    const username = document.getElementById("username").value;
    if (!(await checkUsernameAvailability(username))) {
      isValid = false;
      displayError("username", "Username is already taken!");
    }
  }

  // Submit the form if all validations pass
  if (isValid) document.getElementById("registrationForm").submit();
};

/**
 * Validates the login form
 * Checks for required username and password
 *
 * @param {Event} event - The form submission event
 */
const validateLogin = (event) => {
  event.preventDefault();
  document.querySelectorAll("#loginForm input").forEach(clearAddedError);

  let isValid = true;
  const fields = [
    { id: "username", msg: "Username required" },
    { id: "password", msg: "Password required" },
  ];

  fields.forEach((field) => {
    const value = document.getElementById(field.id).value;
    if (!inputFormat(field.id, value)) {
      isValid = false;
      displayError(field.id, field.msg);
    }
  });

  // Submit the form if all validations pass
  if (isValid) document.getElementById("loginForm").submit();
};

/**
 * Validation rules for each field type
 * Contains specific format requirements for different input fields
 *
 * @param {string} id - The input field ID
 * @param {string} value - The input field value
 * @return {boolean} True if the input is valid, false otherwise
 */
const inputFormat = (id, value) => {
  switch (id) {
    case "username":
      // Username must be 3-20 characters
      return value.trim() !== "" && value.length >= 3 && value.length <= 20;
    case "first_name":
    case "last_name":
      // Names must contain only letters, spaces, and hyphens
      return value.trim() !== "" && /^[A-Za-z\s-]+$/.test(value);
    case "email":
      // Email must match standard email format
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    case "password":
      // Password must be at least 8 characters with 1 uppercase and 1 number
      return /^(?=.*[A-Z])(?=.*\d).{8,}$/.test(value);
    case "confirmPassword":
      // Password confirmation must match the password field
      return (
        value === document.getElementById("password").value && value !== ""
      );
    default:
      return true;
  }
};

/**
 * Displays an error message for an input field
 * Creates or updates an error message span after the input
 *
 * @param {string} id - The input field ID
 * @param {string} msg - The error message to display
 */
const displayError = (id, msg) => {
  const input = document.getElementById(id);
  input.classList.add("error");

  // Find existing error span or create a new one
  const errorSpan =
    document.getElementById(`${id}Error`) ||
    (() => {
      const span = document.createElement("span");
      span.classList.add("error-message");
      span.id = `${id}Error`;
      input.insertAdjacentElement("afterend", span);
      return span;
    })();

  errorSpan.textContent = msg;
};

/**
 * Clears error styling and message when user types valid input
 *
 * @param {Event} event - The input event
 */
const clearErrorOnInput = (event) => {
  const input = event.target;
  if (input.classList.contains("error") && inputFormat(input.id, input.value)) {
    clearAddedError(input);
  }
};

/**
 * Removes error styling and message from an input
 *
 * @param {HTMLElement} input - The input element
 */
const clearAddedError = (input) => {
  if (!input || !input.id) return;
  input.classList.remove("error");
  const errorSpan = document.getElementById(`${input.id}Error`);
  if (errorSpan) errorSpan.remove();
};

/**
 * Checks username availability via AJAX request
 *
 * @param {string} username - The username to check
 * @return {Promise<boolean>} True if username is available, false if taken
 */
const checkUsernameAvailability = async (username) => {
  try {
    const response = await fetch(
      `../server/form_handlers.php?check_username=1&username=${encodeURIComponent(
        username
      )}`
    );
    const data = await response.json();
    return !data.taken;
  } catch (error) {
    console.error("Error checking username:", error);
    return true; // Assume available on error to prevent blocking registration
  }
};
