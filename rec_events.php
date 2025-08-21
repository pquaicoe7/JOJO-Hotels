<?php
// rec_event.php
session_start();

/* DB */
$conn = new mysqli("localhost", "root", "", "jojohotelsdb");
if ($conn->connect_error) {
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'error' => 'db']);
  exit;
}

/* Only POST */
$type = $_POST['type'] ?? '';
header('Content-Type: application/json');

if ($type === 'shown') {
  // expects: ids = "12,34,56"
  $idsCsv = $_POST['ids'] ?? '';
  // keep only integers, drop empties
  $ids = array_filter(array_map('intval', preg_split('/\D+/', $idsCsv)));

  if ($ids) {
    // Build VALUES list like: (12,1,0),(34,1,0)...
    $values = implode(',', array_map(fn($id) => "($id,1,0)", $ids));
    $sql = "INSERT INTO rec_stats (RoomID, shown, booked) VALUES $values
            ON DUPLICATE KEY UPDATE shown = shown + VALUES(shown)";
    $conn->query($sql); // best-effort; no hard fail needed
  }

  echo json_encode(['ok' => true, 'type' => 'shown', 'count' => count($ids)]);
  exit;
}

if ($type === 'booked') {
  // expects: id = 12
  $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  if ($id > 0) {
    $sql = "INSERT INTO rec_stats (RoomID, shown, booked) VALUES ($id,0,1)
            ON DUPLICATE KEY UPDATE booked = booked + 1";
    $conn->query($sql);
  }

  echo json_encode(['ok' => true, 'type' => 'booked', 'id' => $id]);
  exit;
}

// Fallback: unknown type
echo json_encode(['ok' => false, 'error' => 'bad_type']);
