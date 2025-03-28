function deleteEvent(eventId, csrfToken) {
  if (confirm('Are you sure you want to delete this event?')) {
      fetch(`../server/event_handlers.php?id=${eventId}`, {
          method: 'DELETE',
          headers: {
              'X-CSRF-Token': csrfToken,
              'Content-Type': 'application/json'
          }
      })
      .then(response => response.json())
      .then(data => {
          if (data.status === 'success') {
              alert('Event deleted successfully');
              location.reload();
          } else {
              alert('Error: ' + data.message);
          }
      })
      .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while deleting the event');
      });
  }
}