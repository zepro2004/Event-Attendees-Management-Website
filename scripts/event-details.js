document.addEventListener("DOMContentLoaded", function () {
  const eventDetailsElement = document.querySelector(".event-details");
  if (!eventDetailsElement) {
    console.error("Event details element not found");
    return;
  }
  // Get the event ID from the data attribute
  const eventId = document.querySelector(".event-details").dataset.eventId;

  const deleteButton = document.getElementById("delete-event");
  if (deleteButton) {
    deleteButton.addEventListener("click", function () {
      if (confirm("Are you sure you want to delete this event?")) {
        fetch(`../server/event_handlers.php?id=${eventId}`, {
          method: "DELETE",
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status === "success") {
              alert("Event deleted successfully");
              window.location.href = "events.php";
            } else {
              alert("Error: " + data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("An error occurred while deleting the event");
          });
      }
    });
  }

  const rsvpForm = document.getElementById("rsvp-form");
  if (rsvpForm) {
    rsvpForm.addEventListener("submit", function (e) {
      const statusSelect = document.getElementById("status");
      if (!statusSelect.value) {
        e.preventDefault();
        alert("Please select an RSVP status");
      }
    });
  }
});
