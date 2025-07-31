<?php
session_start();
$conn = new mysqli("localhost", "root", "", "jojohotelsdb");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$bookingId = $_GET['booking_id'] ?? '';

if (!$bookingId) {
  die("Booking ID missing.");
}

// Get booking details
$booking = [];
$stmt = $conn->prepare("SELECT TotalPrice FROM bookings WHERE BookingID = ?");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  $booking = $result->fetch_assoc();
}
$stmt->close();

// Get pickup cost (if any)
$pickupCost = 0;
$stmt = $conn->prepare("SELECT total_price FROM pickup_requests WHERE booking_id = ?");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  $pickup = $result->fetch_assoc();
  $pickupCost = (float) $pickup['total_price'];
}
$stmt->close();

$conn->close();

$total = $booking['TotalPrice'] + $pickupCost;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Finalize Payment</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f5f7fa;
      padding: 30px;
    }
    .card {
      max-width: 600px;
      margin: 0 auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    }
    h2 {
      color: #003580;
    }
    .summary {
      font-size: 1.1rem;
      margin: 20px 0;
    }
    .summary p {
      margin: 10px 0;
    }
    .btn {
      background: #febb02;
      color: #003580;
      padding: 12px 24px;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      text-decoration: none;
      cursor: pointer;
    }
    .btn:hover {
      background: #e0a600;
    }
  </style>
</head>
<body>

  <div class="card">
    <h2>💰 Final Payment Summary</h2>
    <div class="summary">
      <p><strong>Room Cost:</strong> GHS <?= number_format($booking['TotalPrice'], 2) ?></p>
      <p><strong>Pickup Cost:</strong> GHS <?= number_format($pickupCost, 2) ?></p>
      <hr>
      <p><strong>Total:</strong> <span style="font-size:1.3rem;">GHS <?= number_format($total, 2) ?></span></p>
    </div>
    <a href="pay_now.php?booking_id=<?= $bookingId ?>&total=<?= $total ?>" class="btn">
  Proceed to Payment
</a>

  </div>

</body>
</html>
