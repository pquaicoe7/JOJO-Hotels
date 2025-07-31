<?php
session_start();
$conn = new mysqli("localhost", "root", "", "jojohotelsdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form values safely
$booking_id = $_POST['booking_id'] ?? '';
$room_id = $_POST['room_id'] ?? '';
$arrival = $_POST['arrival'] ?? '';
$departure = $_POST['departure'] ?? '';
$guests = $_POST['guests'] ?? '';
$total = $_POST['total'] ?? '';
$phone = $_POST['phone'] ?? '';
$luggage = $_POST['luggage'] ?? '';
$location = $_POST['location'] ?? '';
$car_type = $_POST['car_type'] ?? '';
$pickup_time = $_POST['pickup_time'] ?? '';
$name = $_POST['name'] ?? '';


if (!$phone || !$location || !$car_type || !$pickup_time) {
    die("Please fill in all required fields.");
}

// Save request
$stmt = $conn->prepare("INSERT INTO pickup_requests 
    (booking_id, room_id, arrival, departure, guests, total_price, phone, luggage, location, car_type, pickup_time, name)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "iissidssssss",
    $booking_id,
    $room_id,
    $arrival,
    $departure,
    $guests,
    $total,
    $phone,
    $luggage,
    $location,
    $car_type,
    $pickup_time,
    $name
    
    
);

if ($stmt->execute()) {
    // ✅ Include booking_id in redirect
    header("Location: pickup_confirmation.php?success=1&booking_id=$booking_id");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
