<?php
// admin/pickups_events.php
header('Content-Type: application/json');

$config = require __DIR__.'/includes/config.php';
require __DIR__.'/includes/helpers.php';
require __DIR__.'/includes/db.php';

start_session($config['SESSION_NAME']);
$db = (new Database($config))->pdo();

// Only admins
if (empty($_SESSION['admin'])) {
  http_response_code(403);
  echo json_encode(['error' => 'Forbidden']); exit;
}

/*
  Your table columns (from screenshot):
  id, booking_id, name, room_id, arrival(date), departure(date), guests, phone,
  luggage(enum), location(text), car_type(varchar), pickup_time(time),
  request_date(timestamp), total_price(decimal)

  We'll build an event datetime like:
  dt = CONCAT(arrival, ' ', pickup_time)  (if both exist)
       else request_date
*/

$sql = "SELECT id, name, phone, location, car_type, luggage, guests, total_price,
               arrival, pickup_time, request_date
        FROM pickup_requests
        ORDER BY COALESCE(request_date, NOW()) DESC";

$res = $db->query($sql);
$events = [];

while ($row = $res->fetch_assoc()) {
  // Build datetime
  $dt = null;
  if (!empty($row['arrival']) && !empty($row['pickup_time'])) {
    $dt = $row['arrival'] . ' ' . $row['pickup_time']; // e.g., 2025-08-08 14:30:00
  } else {
    $dt = $row['request_date']; // fallback
  }

  if (!$dt) continue;

  $title = trim(($row['name'] ?? 'Pickup') . ' — ' . ($row['location'] ?? ''));

  $events[] = [
    'id'    => (string)$row['id'],
    'title' => $title,
    'start' => date('Y-m-d\TH:i:s', strtotime($dt)),
    'allDay'=> false,
    // no status column in your table, so just one color
    'backgroundColor' => '#3b82f6',
    'borderColor'     => '#3b82f6',
    'extendedProps' => [
      'phone'      => $row['phone'] ?? null,
      'car_type'   => $row['car_type'] ?? null,
      'luggage'    => $row['luggage'] ?? null,
      'guests'     => $row['guests'] ?? null,
      'total_price'=> $row['total_price'] ?? null,
      'arrival'    => $row['arrival'] ?? null,
      'pickup_time'=> $row['pickup_time'] ?? null,
    ]
  ];
}

echo json_encode($events);
