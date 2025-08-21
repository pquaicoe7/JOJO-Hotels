document.addEventListener('DOMContentLoaded', () => {
  // Elements (safe-guard in case the card isn't on this page)
  const occEl   = document.getElementById('occupancyRate');
  const topEl   = document.getElementById('topRoomsChart');
  const monthEl = document.getElementById('monthlyTrendChart');
  if (!occEl || !topEl || !monthEl || typeof Chart === 'undefined') return;

  fetch('occupancy_data.php', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(data => {
      // Occupancy %
      const total = Number(data.totalRooms || 0);
      const booked = Number(data.bookedRooms || 0);
      const pct = total > 0 ? ((booked / total) * 100).toFixed(1) + '%' : '0%';
      occEl.textContent = `${pct} (${booked}/${total})`;

      // Top rooms chart
      const topLabels = (data.topRooms || []).map(r => r.RoomNumber);
      const topData   = (data.topRooms || []).map(r => Number(r.bookings || 0));
      new Chart(topEl, {
        type: 'bar',
        data: {
          labels: topLabels,
          datasets: [{ label: 'Most Booked Rooms', data: topData }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: { y: { beginAtZero: true, title: { display: true, text: 'Bookings' } } }
        }
      });

      // Monthly trend chart
      const mLabels = (data.monthlyBookings || []).map(m => m.month);
      const mData   = (data.monthlyBookings || []).map(m => Number(m.bookings || 0));
      new Chart(monthEl, {
        type: 'line',
        data: {
          labels: mLabels,
          datasets: [{ label: 'Bookings per Month', data: mData, tension: 0.25, fill: true }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: { y: { beginAtZero: true } }
        }
      });
    })
    .catch(() => {
      occEl.textContent = '—';
    });
});

