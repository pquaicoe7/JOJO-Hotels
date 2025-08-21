<?php
// admin/pickups.php
$config = require __DIR__.'/includes/config.php';
require __DIR__.'/includes/helpers.php';
require __DIR__.'/includes/db.php';
require __DIR__.'/includes/middleware.php';

start_session($config['SESSION_NAME']);
$db = (new Database($config))->pdo();
require_auth();
?>
<?php include __DIR__.'/includes/header.php'; ?>
<?php include __DIR__.'/includes/sidebar.php'; ?>

<main class="content">
  <h1>Pickups</h1>

  <div class="card" style="margin-bottom:16px">
    <p>This calendar shows pickup requests by date/time. Click an event for details.</p>
  </div>

  <div class="card">
    <h3>📅 Calendar</h3>
    <div class="calendarwrap">
      <div id="pickupCalendar"></div>
    </div>
  </div>
</main>

<?php include __DIR__.'/includes/footer.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const el = document.getElementById('pickupCalendar');
  if (!el) return;

  const cal = new FullCalendar.Calendar(el, {
    initialView: 'dayGridMonth',
    height: '100%',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
    },
    navLinks: true,
    nowIndicator: true,
    eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
    events: 'pickups_events.php',
    eventClick(info) {
      info.jsEvent.preventDefault();
      const e = info.event;
      const x = e.extendedProps || {};
      const html = `
        <b>${e.title}</b><br>
        Time: ${e.start?.toLocaleString()}<br>
        Phone: ${x.phone || '-'}<br>
        Guests: ${x.guests || '-'}<br>
        Car: ${x.car_type || '-'}<br>
        Luggage: ${x.luggage || '-'}<br>
        Total: ₵${x.total_price || '0.00'}
      `;
      const w = window.open('', '_blank', 'width=420,height=420');
      if (w) { w.document.write('<!doctype html><title>Pickup</title><body style="font-family:system-ui;padding:12px">'+html+'</body>'); w.document.close(); }
    }
  });
  cal.render();
});
</script>
