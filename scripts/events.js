/**
 * Event Management Script
 *
 * This script manages client-side functionality for events including:
 * - Event listing and filtering
 * - Event creation form submission
 * - RSVP functionality
 * - Event deletion
 */

/**
 * Initialize all event-related functionality when the DOM is loaded
 */
document.addEventListener("DOMContentLoaded", () => {
  // Set up search form event handlers
  const searchForm = document.getElementById("searchForm");
  if (searchForm) {
    // Handle form submission - prevent default and trigger filtered event fetch
    searchForm.addEventListener("submit", (e) => {
      e.preventDefault();
      fetchEvents(new FormData(searchForm));
    });

    // Handle form reset - wait a small delay to ensure fields are cleared
    searchForm.addEventListener("reset", () => setTimeout(fetchEvents, 10));
  }

  // Set up add event form submission handler
  const addEventForm = document.getElementById("addEventForm");
  if (addEventForm) {
    addEventForm.addEventListener("submit", (e) => {
      e.preventDefault();

      // Update UI to show submission is in progress
      const submitBtn = addEventForm.querySelector('[type="submit"]');
      const originalBtnText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = "Uploading...";

      // Send form data to server using fetch API
      fetch("../server/event_handlers.php", {
        method: "POST",
        body: new FormData(addEventForm), // Handles file uploads and form fields
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.status === "success") {
            // Redirect to dashboard with success message
            window.location.href = "dashboard.php?success=event_created";
          } else {
            // Show error and restore button state
            alert("Error: " + (data.message || "Could not create event"));
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
          }
        })
        .catch((error) => {
          // Handle network or other errors
          console.error("Error:", error);
          alert("Error creating event. Please try again.");
          submitBtn.disabled = false;
          submitBtn.textContent = originalBtnText;
        });
    });
  }

  // Load initial events list when page loads
  fetchEvents();
});

/**
 * Fetch events from server with optional filtering
 *
 * @param {FormData} formData - Optional form data for filtering events
 */
function fetchEvents(formData) {
  const eventsList = document.getElementById("eventsList");

  // Show loading indicator
  eventsList.innerHTML = '<div class="loading">Loading events...</div>';

  // Build request URL with any filter parameters
  let url = "../server/event_handlers.php";
  if (formData) {
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
      if (value.trim()) params.append(key, value); // Only add non-empty values
    }
    if (params.toString()) url += "?" + params.toString();
  }

  // Fetch events from server
  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      // Handle error response from server
      if (data && data.status === "error") {
        eventsList.innerHTML = `<div class="error">Error: ${data.message}</div>`;
        return;
      }

      // Process event data
      const events = Array.isArray(data) ? data : [];
      if (events.length === 0) {
        eventsList.innerHTML = '<div class="no-events">No events found.</div>';
        return;
      }

      // Clear container and add event cards
      eventsList.innerHTML = "";
      events.forEach((event) => eventsList.appendChild(createEventCard(event)));
    })
    .catch((error) => {
      // Handle fetch errors
      console.error("Error:", error);
      eventsList.innerHTML = '<div class="error">Error loading events.</div>';
    });
}

/**
 * Create an event card DOM element from event data
 *
 * @param {Object} event - Event data object with properties like id, title, date, etc.
 * @return {HTMLElement} - DOM element representing the event card
 */
function createEventCard(event) {
  const card = document.createElement("div");
  card.className = "event-card";
  card.dataset.id = event.id;

  // Format date string for display
  const eventDate = new Date(event.date);
  const formattedDate =
    eventDate.toLocaleDateString() +
    " " +
    eventDate.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });

  // Format location string from component parts
  let location = event.city || "";
  if (event.state) location += `, ${event.state}`;
  if (event.country && event.country !== "Canada")
    location += `, ${event.country}`;

  // Check if user is logged in to conditionally show RSVP button
  const currentUserId =
    document.getElementById("eventsList").dataset.userId || "0";

  // Build the card HTML structure
  card.innerHTML = `
    <div class="event-header">
      <h3>${event.title}</h3>
      ${
        event.image_url
          ? `<img src="${event.image_url}" alt="${event.title}" class="event-image">`
          : ""
      }
    </div>
    <div class="event-details">
      <p><strong>Date:</strong> ${formattedDate}</p>
      <p><strong>Location:</strong> ${location}</p>
      <p><strong>Organizer:</strong> ${event.organizer_name || "Unknown"}</p>
      ${
        event.max_attendees
          ? `<p><strong>Max Attendees:</strong> ${event.max_attendees}</p>`
          : ""
      }
    </div>
    <div class="event-description">
      <p>${event.description || "No description provided."}</p>
    </div>
    <div class="event-actions">
      <button class="btn view-details" onclick="viewEventDetails(${
        event.id
      })">View Details</button>
      ${
        currentUserId !== "0"
          ? `<button class="btn rsvp" onclick="rsvpToEvent(${event.id})">RSVP</button>`
          : ""
      }
    </div>`;

  return card;
}

/**
 * Navigate to the event details page for a specific event
 *
 * @param {number} eventId - ID of the event to view
 */
function viewEventDetails(eventId) {
  window.location.href = `event-details.php?id=${eventId}`;
}

/**
 * Delete an event with confirmation prompt
 * Used on dashboard and event details pages
 *
 * @param {number} eventId - ID of the event to delete
 */
function deleteEvent(eventId) {
  // Show confirmation dialog before proceeding
  if (!confirm("Are you sure you want to delete this event?")) return;

  // Send delete request to server
  fetch(`../server/event_handlers.php?id=${eventId}`, {
    method: "DELETE",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        alert("Event deleted successfully!");
        location.reload(); // Refresh the page to show updated event list
      } else {
        alert("Error: " + (data.message || "Could not delete event"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error deleting event");
    });
}

/**
 * Submit an RSVP for an event
 * Creates and submits a form to handle the RSVP action
 *
 * @param {number} eventId - ID of the event to RSVP to
 */
function rsvpToEvent(eventId) {
  // Create a dynamic form for submission
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "../server/event_handlers.php";
  form.style.display = "none";

  // Set form fields for RSVP action
  const fields = { action: "rsvp", event_id: eventId, status: "attending" };

  // Add fields to the form
  for (const [key, value] of Object.entries(fields)) {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = key;
    input.value = value;
    form.appendChild(input);
  }

  // Add form to document and submit it
  document.body.appendChild(form);
  form.submit();
}
