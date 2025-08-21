<?php
$config = require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/middleware.php';

start_session($config['SESSION_NAME']);
$db = (new Database($config))->pdo();
require_auth();

// Top stats (case-insensitive status checks)
$totalBookings  = ($db->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()['c'] ?? 0);
$totalRevenue   = ($db->query("SELECT COALESCE(SUM(TotalPrice),0) s FROM bookings WHERE LOWER(BookingStatus) IN ('paid','completed','confirmed')")->fetch_assoc()['s'] ?? 0);
$roomsTotal     = ($db->query("SELECT COUNT(*) c FROM rooms")->fetch_assoc()['c'] ?? 0);
$roomsBooked    = ($db->query("SELECT COUNT(DISTINCT RoomID) c FROM bookings WHERE LOWER(BookingStatus) IN ('paid','completed','confirmed')")->fetch_assoc()['c'] ?? 0);
$roomsAvailable = max(0, $roomsTotal - $roomsBooked);
$totalPickups   = ($db->query("SELECT COUNT(*) c FROM pickup_requests")->fetch_assoc()['c'] ?? 0);

// Monthly bookings & revenue (last 12 months)
$sql = "
  SELECT DATE_FORMAT(CheckInDate, '%Y-%m') ym,
         COUNT(*) AS cnt,
         COALESCE(SUM(TotalPrice),0) AS revenue
  FROM bookings
  WHERE CheckInDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    AND LOWER(BookingStatus) IN ('paid','completed','confirmed')
  GROUP BY ym
  ORDER BY ym
";
$res = $db->query($sql);
$labels = []; $counts = []; $revenues = [];
if ($res) {
  while ($row = $res->fetch_assoc()) {
    $labels[]   = $row['ym'];
    $counts[]   = (int)$row['cnt'];
    $revenues[] = (float)$row['revenue'];
  }
}
if (!$labels) { $labels=[date('Y-m')]; $counts=[0]; $revenues=[0]; }
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<main class="content">
  <h1>Dashboard</h1>

  <div class="widgets">
    <div class="widget">
      <h3>🔢 Total Bookings</h3>
      <div class="value"><?= (int)$totalBookings ?></div>
    </div>
    <div class="widget">
      <h3>💰 Total Revenue</h3>
      <div class="value">₵<?= number_format((float)$totalRevenue, 2) ?></div>
    </div>
    <div class="widget">
      <h3>🛏️ Available vs Booked Rooms</h3>
      <div class="value"><?= (int)$roomsAvailable ?> / <?= (int)$roomsTotal ?></div>
    </div>
    <div class="widget">
      <h3>🚕 Total Pickup Requests</h3>
      <div class="value"><?= (int)$totalPickups ?></div>
    </div>
  </div>

  <div class="card">
    <h3>📈 Bookings Over Time</h3>
    <div class="chartwrap">
      <canvas id="bookingsChart"></canvas>
    </div>
  </div>

  <div class="card">
    <h3>📅 Pickup Calendar (Next 7 days)</h3>
    <div class="calendarwrap">
      <div id="dashboardPickupCalendar"></div>
    </div>
  </div>

  <div class="card mt-4">
    <h3>🏨 Occupancy & Booking Trends</h3>
    <p><strong>Current Occupancy:</strong> <span id="occupancyRate">Loading...</span></p>
    <div class="chartwrap" style="height:220px"><canvas id="topRoomsChart"></canvas></div>
    <div class="chartwrap" style="height:220px;margin-top:14px"><canvas id="monthlyTrendChart"></canvas></div>
  </div>

  <!-- FullCalendar (for the mini list-week block) -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

  <script>
    // Mini calendar on dashboard
    (() => {
      const el = document.getElementById('dashboardPickupCalendar');
      if (!el || typeof FullCalendar === 'undefined') return;

      const cal = new FullCalendar.Calendar(el, {
        initialView: 'listWeek',
        height: '100%',
        headerToolbar: { left: '', center: 'title', right: 'prev,next' },
        events: 'pickups_events.php',
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        displayEventTime: true,
        noEventsContent: 'No pickups scheduled.',
      });
      cal.render();
    })();
  </script>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- Bookings chart -->
<script>
  (() => {
    const el = document.getElementById('bookingsChart');
    if (!el || typeof Chart === 'undefined') return;

    const labels   = <?= json_encode($labels) ?>;
    const counts   = <?= json_encode($counts) ?>;
    const revenues = <?= json_encode($revenues) ?>;

    new Chart(el, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          { type: 'line', label: 'Bookings',    data: counts,   tension: 0.25, yAxisID: 'y'  },
          { type: 'bar',  label: 'Revenue (₵)', data: revenues,                 yAxisID: 'y1' }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false, // use .chartwrap height
        scales: {
          y:  { beginAtZero: true, title: { display: true, text: 'Bookings' } },
          y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Revenue (₵)' } }
        }
      }
    });
  })();
</script>

<!-- Occupancy & trends logic (reads admin/occupancy_data.php) -->
<script src="assets/js/occupancy.js"></script>
