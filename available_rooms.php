<?php
session_start();
$conn = new mysqli("localhost", "root", "", "jojohotelsdb");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$arrival = $_GET['arrival'] ?? '';
$departure = $_GET['departure'] ?? '';
$guests = (int) ($_GET['guests'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM rooms WHERE Guests >= ? AND Status = 'Available'");
$stmt->bind_param("i", $guests);
$stmt->execute();
$result = $stmt->get_result();
$rooms = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Available Rooms</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
      margin: 0;
      background-color: #f5f7fa;
    }

    .header {
      background-color: #003580;
      color: white;
      padding: 30px 20px;
      text-align: center;
    }

    .header h1 {
      margin: 0;
      font-size: 2rem;
    }

    .search-details {
      margin-top: 10px;
      font-size: 0.95rem;
      color: #e0e6ef;
    }

    .container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .room-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      display: flex;
      overflow: hidden;
      margin-bottom: 30px;
      transition: transform .3s;
    }

    .room-card:hover {
      transform: scale(1.01);
    }

    .room-card img {
      width: 300px;
      height: 200px;
      object-fit: cover;
    }

    .room-details {
      padding: 20px;
      flex: 1;
    }

    .room-details h2 {
      margin-top: 0;
      color: #003580;
      font-size: 1.4rem;
    }

    .room-details p {
      margin: 8px 0;
      color: #333;
    }

    .book-btn {
      background: #febb02;
      color: #003580;
      padding: 12px 18px;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      margin-top: 12px;
      transition: background 0.3s;
    }

    .book-btn:hover {
      background: #f3ac00;
    }

    .no-rooms {
      text-align: center;
      font-size: 1.2rem;
      color: #777;
      margin-top: 50px;
    }

    @media (max-width: 768px) {
      .room-card {
        flex-direction: column;
      }

      .room-card img {
        width: 100%;
        height: 180px;
      }
    }
  </style>
</head>
<body>

  <div class="header">
    <h1>Available Rooms</h1>
    <div class="search-details">
      <p>Check-in: <?= htmlspecialchars($arrival) ?> | Check-out: <?= htmlspecialchars($departure) ?> | Guests: <?= htmlspecialchars((string)$guests) ?></p>
    </div>
  </div>

  <div class="container">
    <?php if (empty($rooms)): ?>
      <div class="no-rooms">No rooms available for your selected dates.</div>
    <?php else: ?>
      <?php foreach ($rooms as $room): ?>
        <div class="room-card">
          <img src="<?= htmlspecialchars($room['ImageURL']) ?>" alt="Room Image">
          <div class="room-details">
            <h2>Room <?= htmlspecialchars($room['RoomNumber']) ?></h2>
            <p><strong>Floor:</strong> <?= htmlspecialchars($room['FloorNumber']) ?></p>
            <p><strong>Guests:</strong> <?= htmlspecialchars($room['Guests']) ?></p>
            <p><strong>Price:</strong> GHS <?= htmlspecialchars($room['Price']) ?></p>
            <?php
              $rid = (int) $room['RoomID'];
              $arr = htmlspecialchars($arrival, ENT_QUOTES);
              $dep = htmlspecialchars($departure, ENT_QUOTES);
              $g = (int) $guests;
            ?>
            <button class="book-btn" onclick='bookRoom(<?= $rid ?>, "<?= $arr ?>", "<?= $dep ?>", <?= $g ?>)'>
              Book Now
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- ✅ Include Pickup Confirmation Modal -->
  <?php include 'includes/pickup_modal.php'; ?>

  <!-- ✅ JavaScript for Booking + Modal Logic -->
  <script>
  window.lastBooking = null;

  async function bookRoom(RoomID, arrival, departure, guests) {
    const fd = new FormData();
    fd.append("RoomID", RoomID);
    fd.append("arrival", arrival);
    fd.append("departure", departure);
    fd.append("guests", guests);

    try {
      const res = await fetch("book_room.php", { method: "POST", body: fd });
      const data = await res.json();

      if (!data || !data.success) {
        alert("Booking failed: " + (data?.message ?? "Unknown error"));
        return;
      }

      window.lastBooking = {
        booking_id: data.booking_id ?? null,
        totalPrice: data.totalPrice ?? 0,
        RoomID, arrival, departure, guests
      };

      document.getElementById("pickupModal").style.display = "flex";

    } catch (e) {
      console.error("Booking error:", e);
      alert("An error occurred while booking.");
    }
  }

  window.addEventListener("DOMContentLoaded", () => {
    const yesBtn = document.getElementById("yesPickup");
    const noBtn = document.getElementById("noPickup");

    if (yesBtn && noBtn) {
      yesBtn.onclick = function () {
        const data = window.lastBooking;
        const params = new URLSearchParams({
          booking_id: data.booking_id,
          RoomID: data.RoomID,
          arrival: data.arrival,
          departure: data.departure,
          guests: data.guests,
          total: data.totalPrice
        });
        window.location.href = "pickup_request.php?" + params.toString();
      };

      noBtn.onclick = function () {
        const data = window.lastBooking;
        const params = new URLSearchParams({
          booking_id: data.booking_id,
          room_cost: data.totalPrice,
          pickup_cost: 0,
          total_cost: data.totalPrice
        });
        window.location.href = "final_summary.php?" + params.toString();
      };
    }
  });
</script>


</body>
</html>
