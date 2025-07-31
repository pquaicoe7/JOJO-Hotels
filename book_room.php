<?php
session_start();
header('Content-Type: application/json');

// ✅ GET request — show success message
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['success']) && $_GET['success'] == 1) {
    $price = $_GET['price'] ?? '0.00';
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Booking Successful</title>
        <style>
            body {
                font-family: sans-serif;
                background: #eaf3fa;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }

            .card {
                background: white;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                text-align: center;
            }

            .card h2 {
                color: #2ecc71;
            }

            .card p {
                font-size: 1.2rem;
                margin-top: 10px;
            }

            a.btn {
                margin-top: 20px;
                display: inline-block;
                padding: 12px 20px;
                background: #2d7cff;
                color: #fff;
                text-decoration: none;
                border-radius: 6px;
            }
        </style>
    </head>

    <body>
        <div class="card">
            <h2>🎉 Booking Successful!</h2>
            <p>Total Price: <strong>GHS <?= htmlspecialchars($price) ?></strong></p>
            <a href="index.php" class="btn">Go Home</a>
        </div>
    </body>

    </html>
    <?php
    exit;
}

// ✅ POST request — process booking
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "jojohotelsdb");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$roomID = $_POST['RoomID'] ?? '';
$checkIn = $_POST['arrival'] ?? '';
$checkOut = $_POST['departure'] ?? '';
$guests = $_POST['guests'] ?? '';
$userID = $_SESSION['user_id'];

// Input validation
if (empty($roomID) || empty($checkIn) || empty($checkOut) || empty($guests)) {
    echo json_encode(["success" => false, "message" => "Incomplete booking data."]);
    exit;
}

// Get room price
$stmt = $conn->prepare("SELECT Price FROM rooms WHERE RoomID = ?");
$stmt->bind_param("i", $roomID);
$stmt->execute();
$stmt->bind_result($price);
if (!$stmt->fetch()) {
    echo json_encode(["success" => false, "message" => "Room not found"]);
    exit;
}
$stmt->close();

// Calculate total
$checkInDate = new DateTime($checkIn);
$checkOutDate = new DateTime($checkOut);
$nights = $checkInDate->diff($checkOutDate)->days;
$totalPrice = $nights * $price;

// Insert booking
$stmt = $conn->prepare("INSERT INTO bookings (UserID, RoomID, CheckInDate, CheckOutDate, TotalPrice, NumGuests, BookingStatus)
                        VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param("iissdi", $userID, $roomID, $checkIn, $checkOut, $totalPrice, $guests);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Booking successful",
        "booking_id" => $stmt->insert_id,
        "totalPrice" => $totalPrice
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Error booking room: " . $stmt->error]);
}

$stmt->close();
$conn->close();
