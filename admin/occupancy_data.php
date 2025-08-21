<?php
// admin/occupancy_data.php
header('Content-Type: application/json');

$config = require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/db.php';

start_session($config['SESSION_NAME']);
$db = (new Database($config))->pdo();

// Only allow logged-in admin
if (empty($_SESSION['admin'])) {
  http_response_code(403);
  echo json_encode(['error' => 'Forbidden']); exit;
}

$out = [
  'totalRooms'       => 0,
  'bookedRooms'      => 0,
  'topRooms'         => [],
  'monthlyBookings'  => [],
];

// Total rooms
if ($r = $db->query("SELECT COUNT(*) AS c FROM rooms")) {
  $out['totalRooms'] = (int)($r->fetch_assoc()['c'] ?? 0);
}

// Currently booked rooms (treat paid/completed/confirmed as occupying) — case-insensitive
if ($r = $db->query("SELECT COUNT(DISTINCT RoomID) AS c FROM bookings WHERE LOWER(BookingStatus) IN ('paid','completed','confirmed')")) {
  $out['bookedRooms'] = (int)($r->fetch_assoc()['c'] ?? 0);
}

// Top 5 most-booked rooms (lifetime)
$sqlTop = "
  SELECT r.RoomNumber, COUNT(b.BookingID) AS bookings
  FROM bookings b
  JOIN rooms r ON r.RoomID = b.RoomID
  GROUP BY b.RoomID, r.RoomNumber
  ORDER BY bookings DESC
  LIMIT 5
";
if ($rt = $db->query($sqlTop)) {
  while ($row = $rt->fetch_assoc()) $out['topRooms'][] = $row;
}

// Monthly bookings for last 12 months by CheckInDate
$sqlMonth = "
  SELECT DATE_FORMAT(CheckInDate, '%Y-%m') AS month, COUNT(*) AS bookings
  FROM bookings
  WHERE CheckInDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
  GROUP BY month
  ORDER BY month
";
if ($rm = $db->query($sqlMonth)) {
  while ($row = $rm->fetch_assoc()) $out['monthlyBookings'][] = $row;
}

echo json_encode($out);
