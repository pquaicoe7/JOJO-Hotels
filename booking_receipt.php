<?php
session_start();
$conn = new mysqli("localhost", "root", "", "jojohotelsdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$booking_id = $_GET['booking_id'] ?? '';

if (!$booking_id) {
    die("Booking ID is required.");
}

// Get booking details
$booking = [];
$stmt = $conn->prepare("SELECT * FROM bookings WHERE BookingID = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $booking = $result->fetch_assoc();
}
$stmt->close();

// Get pickup details (if any)
$pickup = null;
$stmt = $conn->prepare("SELECT * FROM pickup_requests WHERE booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $pickup = $result->fetch_assoc();
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
        }

        .container {
            max-width: 700px;
            margin: 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        h2 {
            color: #003580;
            text-align: center;
        }

        .section {
            margin: 30px 0;
        }

        .section h3 {
            color: #003580;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
        }

        .row span {
            color: #555;
        }

        .btn {
            display: block;
            margin: 20px auto 0;
            padding: 12px 24px;
            font-size: 1rem;
            background: #febb02;
            color: #003580;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
        }

        .notice {
            margin-top: 20px;
            text-align: center;
            font-style: italic;
            color: #333;
            background: #fff6d5;
            padding: 12px;
            border-radius: 8px;
        }

        @media print {
            header, .btn, .notice {
                display: none;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h2>Booking Receipt</h2>

    <div class="section">
        <h3>Room Booking Details</h3>
        <div class="row"><strong>Booking ID:</strong> <span><?= $booking['BookingID'] ?? 'N/A' ?></span></div>
        <div class="row"><strong>Room ID:</strong> <span><?= $booking['RoomID'] ?? 'N/A' ?></span></div>
        <div class="row"><strong>Arrival Date:</strong> <span><?= $booking['CheckIn'] ?? 'N/A' ?></span></div>
        <div class="row"><strong>Departure Date:</strong> <span><?= $booking['CheckOut'] ?? 'N/A' ?></span></div>
        <div class="row"><strong>Guests:</strong> <span><?= $booking['Guests'] ?? 'N/A' ?></span></div>
        <div class="row"><strong>Room Cost:</strong> <span>GHS <?= number_format($booking['TotalPrice'], 2) ?></span></div>
    </div>

    <?php if ($pickup): ?>
    <div class="section">
        <h3>Pickup Details</h3>
        <div class="row"><strong>Pickup Time:</strong> <span><?= $pickup['pickup_time'] ?></span></div>
        <div class="row"><strong>Request Date:</strong> <span><?= $pickup['request_date'] ?></span></div>
        <div class="row"><strong>Location:</strong> <span><?= $pickup['location'] ?></span></div>
        <div class="row"><strong>Phone:</strong> <span><?= $pickup['phone'] ?></span></div>
        <div class="row"><strong>Car Type:</strong> <span><?= $pickup['car_type'] ?></span></div>
        <div class="row"><strong>Luggage:</strong> <span><?= $pickup['luggage'] ?></span></div>
        <div class="row"><strong>Pickup Cost:</strong> <span>GHS <?= number_format($pickup['total_price'], 2) ?></span></div>
    </div>
    <?php endif; ?>

    <div class="section">
        <h3>Total Summary</h3>
        <div class="row"><strong>Total Paid:</strong>
            <span>
                GHS <?= number_format(($booking['TotalPrice'] ?? 0) + ($pickup['total_price'] ?? 0), 2) ?>
            </span>
        </div>
    </div>

    <div class="notice">
        Please save or print this receipt and bring it along when you arrive at the hotel.
    </div>

    <button class="btn" onclick="window.print()">🖨 Print Receipt</button>
    <a class="btn" href="home.php">🏠 Return to Homepage</a>
</div>

</body>
</html>
