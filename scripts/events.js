// This file handles dynamic behavior related to events, such as adding, displaying, and removing events.

document.addEventListener("DOMContentLoaded", function () {
  // Set up event listeners
  const searchForm = document.getElementById("searchForm");
  if (searchForm) {
    searchForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(searchForm);
      fetchEvents(formData);
    });

    searchForm.addEventListener("reset", function () {
      setTimeout(fetchEvents, 10);
    });
  }
  
  const addEventForm = document.getElementById("addEventForm");
  
  if (addEventForm) {
      addEventForm.addEventListener("submit", function(e) {
          e.preventDefault();
          
          // Create form data object
          const formData = new FormData(this);
          
          // Add the event
          fetch("../server/event_handlers.php", {
              method: "POST",
              body: formData
          })
          .then(response => {
              if (!response.ok) {
                  throw new Error("Network response was not ok");
              }
              return response.json();
          })
          .then(data => {
              if (data.status === "success") {
                  // Redirect to dashboard or event page
                  window.location.href = "dashboard.php?success=event_created";
              } else {
                  // Show error message
                  alert("Error: " + (data.message || "Could not create event"));
              }
          })
          .catch(error => {
              console.error("Error:", error);
              alert("Error creating event. Please try again.");
          });
      });
  }
  // Initial fetch of events
  fetchEvents();
});

// Function to fetch events
function fetchEvents(formData) {
  const eventsList = document.getElementById("eventsList");
  eventsList.innerHTML = '<div class="loading">Loading events...</div>';

  let url = "../server/event_handlers.php";
  if (formData) {
    const params = new URLSearchParams();
    for (const pair of formData.entries()) {
      if (pair[1].trim() !== "") {
        params.append(pair[0], pair[1]);
      }
    }
    if (params.toString()) {
      url += "?" + params.toString();
    }
  }

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
       // Check if response is an error object
      if (data && data.status === "error") {
        console.error("Server error:", data.message);
        eventsList.innerHTML = `<div class="error">Error: ${data.message}</div>`;
        return;
      }

      // Handle both array and object with events property
      const events = Array.isArray(data) ? data : [];
      if (events.length === 0) {
        eventsList.innerHTML = '<div class="no-events">No events found.</div>';
        return;
      }

      eventsList.innerHTML = "";
      events.forEach((event) => {
        const eventCard = createEventCard(event);
        eventsList.appendChild(eventCard);
      });
    })
    .catch((error) => {
      console.error("Error fetching events:", error);
      eventsList.innerHTML =
        '<div class="error">Error loading events. Please try again later.</div>';
    });
}

// Function to create an event card element
function createEventCard(event) {
  const card = document.createElement("div");
  card.className = "event-card";
  card.dataset.id = event.id;

  const eventDate = new Date(event.date);
  const formattedDate =
    eventDate.toLocaleDateString() +
    " " +
    eventDate.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });

  let location = `${event.city}`;
  if (event.state) location += `, ${event.state}`;
  if (event.country && event.country !== "Canada")
    location += `, ${event.country}`;

  // Get the current user ID from the data attribute we'll add to the eventsList div
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
            <p><strong>Organizer:</strong> ${
              event.organizer_name || "Unknown"
            }</p>
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
              event.user_id == currentUserId && currentUserId !== "0"
                ? `<button class="btn delete" onclick="deleteEvent(${event.id})">Delete</button>`
                : ""
            }
            ${
              currentUserId !== "0"
                ? `<button class="btn rsvp" onclick="rsvpToEvent(${event.id})">RSVP</button>`
                : ""
            }
        </div>
    `;

  return card;
}

// Function to add a new event
function addEvent(formData) {
  fetch("../server/event_handlers.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        alert("Event added successfully!");
        document.getElementById("addEventForm").reset();
        fetchEvents(); // Refresh the events list
      } else {
        alert("Error: " + (data.message || "Could not add event"));
      }
    })
    .catch((error) => {
      console.error("Error adding event:", error);
      alert("An error occurred while adding the event");
    });
}

// Function to delete an event
function deleteEvent(eventId) {
  if (!confirm("Are you sure you want to delete this event?")) {
    return;
  }

  fetch(`../server/event_handlers.php?id=${eventId}`, {
    method: "DELETE",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        alert("Event deleted successfully!");
        fetchEvents(); // Refresh the events list
      } else {
        alert("Error: " + (data.message || "Could not delete event"));
      }
    })
    .catch((error) => {
      console.error("Error deleting event:", error);
      alert("An error occurred while deleting the event");
    });
}

// Function to view event details
function viewEventDetails(eventId) {
  window.location.href = `event-details.php?id=${eventId}`;
}

// Function to RSVP to an event
function rsvpToEvent(eventId) {
  const formData = new FormData();
  formData.append("action", "rsvp");
  formData.append("event_id", eventId);
  formData.append("status", "attending");

  const form = document.createElement("form");
  form.method = "POST";
  form.action = "../server/event_handlers.php";
  form.style.display = "none";

  // Add form fields
  for (const [key, value] of formData.entries()) {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = key;
    input.value = value;
    form.appendChild(input);
  }

  // Add to document, submit, then remove
  document.body.appendChild(form);
  form.submit();
}
