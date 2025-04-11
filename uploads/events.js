// Events management script

document.addEventListener("DOMContentLoaded", function () {
  // Set up event listeners for the search form
  const searchForm = document.getElementById("searchForm");
  if (searchForm) {
    searchForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetchEvents(formData);
    });

    // Reset button handler
    const resetButton = searchForm.querySelector('button[type="reset"]');
    if (resetButton) {
      resetButton.addEventListener("click", function (e) {
        setTimeout(() => fetchEvents(), 10); // Small delay to ensure form is reset
      });
    }
  }
  const eventsList = document.getElementById("eventsList");
  if (eventsList) {
    console.log("Events list found, initializing...");
    // Load events on page load
    fetchEvents();

    // Set up delete functionality if user is logged in
    window.deleteEvent = function (eventId) {
      if (confirm("Are you sure you want to delete this event?")) {
        fetch(`../server/event_handlers.php?id=${eventId}`, {
          method: "DELETE",
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status === "success") {
              alert("Event deleted successfully");
              // Reload events or remove from DOM
              fetchEvents();
            } else {
              alert("Error: " + data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("Error deleting event");
          });
      }
    };
    // Initial fetch of events
    fetchEvents();
  }
});

// Add error logging to debug the fetch events function
function fetchEvents(formData) {
  const eventsList = document.getElementById("eventsList");
  if (!eventsList) {
    console.error("Events list element not found!");
    return;
  }

  eventsList.innerHTML = '<div class="loading">Loading events...</div>';

  let url = "../server/event_handlers.php";
  if (formData) {
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
      if (value.trim()) params.append(key, value);
    }
    if (params.toString()) url += "?" + params.toString();
  }

  console.log("Fetching events from:", url);

  fetch(url)
    .then((response) => {
      console.log("Response status:", response.status);
      return response.json();
    })
    .then((data) => {
      console.log("Data received:", data);

      if (data && data.status === "error") {
        eventsList.innerHTML = `<div class="error">Error: ${data.message}</div>`;
        return;
      }

      const events = Array.isArray(data) ? data : [];
      if (events.length === 0) {
        eventsList.innerHTML = '<div class="no-events">No events found.</div>';
        return;
      }

      eventsList.innerHTML = "";
      events.forEach((event) => eventsList.appendChild(createEventCard(event)));
    })
    .catch((error) => {
      console.error("Fetch error:", error);
      eventsList.innerHTML = '<div class="error">Error loading events.</div>';
    });
}

// Fetch events from server
// function fetchEvents(formData) {
//   const eventsList = document.getElementById("eventsList");
//   eventsList.innerHTML = '<div class="loading">Loading events...</div>';

//   let url = "../server/event_handlers.php";
//   if (formData) {
//     const params = new URLSearchParams();
//     for (const [key, value] of formData.entries()) {
//       if (value.trim()) params.append(key, value);
//     }
//     if (params.toString()) url += "?" + params.toString();
//   }

//   fetch(url)
//     .then((response) => response.json())
//     .then((data) => {
//       if (data && data.status === "error") {
//         eventsList.innerHTML = `<div class="error">Error: ${data.message}</div>`;
//         return;
//       }

//       const events = Array.isArray(data) ? data : [];
//       if (events.length === 0) {
//         eventsList.innerHTML = '<div class="no-events">No events found.</div>';
//         return;
//       }

//       eventsList.innerHTML = "";
//       events.forEach((event) => eventsList.appendChild(createEventCard(event)));
//     })
//     .catch((error) => {
//       console.error("Error:", error);
//       eventsList.innerHTML = '<div class="error">Error loading events.</div>';
//     });
// }

// Create HTML for an event card
function createEventCard(event) {
  const card = document.createElement("div");
  card.className = "event-card";
  card.dataset.id = event.id;

  // Format date and location
  const eventDate = new Date(event.date);
  const formattedDate =
    eventDate.toLocaleDateString() +
    " " +
    eventDate.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });

  let location = event.city || "";
  if (event.state) location += `, ${event.state}`;
  if (event.country && event.country !== "Canada")
    location += `, ${event.country}`;

  // Get user ID
  const currentUserId =
    document.getElementById("eventsList").dataset.userId || "0";

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

// View event details
function viewEventDetails(eventId) {
  window.location.href = `event-details.php?id=${eventId}`;
}

// Delete an event (used by dashboard.php)
function deleteEvent(eventId) {
  if (!confirm("Are you sure you want to delete this event?")) return;

  fetch(`../server/event_handlers.php?id=${eventId}`, {
    method: "DELETE",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        alert("Event deleted successfully!");
        location.reload();
      } else {
        alert("Error: " + (data.message || "Could not delete event"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error deleting event");
    });
}

// RSVP to an event
function rsvpToEvent(eventId) {
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "../server/event_handlers.php";
  form.style.display = "none";

  const fields = { action: "rsvp", event_id: eventId, status: "attending" };

  for (const [key, value] of Object.entries(fields)) {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = key;
    input.value = value;
    form.appendChild(input);
  }

  document.body.appendChild(form);
  form.submit();
}
