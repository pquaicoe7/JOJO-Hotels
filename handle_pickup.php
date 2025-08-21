<?php
session_start();

// Load SDK first, then import namespace
require_once 'vendor/autoload.php';
use AfricasTalking\SDK\AfricasTalking;

// Connect to DB
$conn = new mysqli("localhost", "root", "", "jojohotelsdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data safely
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

// Validate required fields
if (!$phone || !$location || !$car_type || !$pickup_time || !$name) {
    die("Missing required fields.");
}

// Save pickup request
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

if (!$stmt->execute()) {
    die("Database error: " . $stmt->error);
}
$stmt->close();

// ✅ Send SMS to driver
$username = "JOJO-Hotels";  // Your Africa's Talking username
$apiKey = "atsk_848e44cf48e0af69b20bffaf3c38d9283ec3a26c4fcfd1bcffaae4622a7ed6c553a8affc";  // Live key

$AT = new AfricasTalking($username, $apiKey);
$sms = $AT->sms();

$driverNumber = "+233596353309";  // Driver phone number
$message = "🚕 NEW PICKUP REQUEST:
Name: $name
Phone: $phone
Guests: $guests
Pickup Time: $pickup_time
Location: $location
Luggage: $luggage
Car: $car_type
Room ID: $room_id
Arrival: $arrival - Departure: $departure";

// Send SMS
try {
    $sms->send([
        'to' => $driverNumber,
        'message' => $message
    ]);
} catch (Exception $e) {
    echo "SMS failed: " . $e->getMessage();
    exit();
}

// ✅ Redirect to confirmation
header("Location: pickup_confirmation.php?success=1&booking_id=$booking_id");
exit();
?>
