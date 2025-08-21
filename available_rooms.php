<?php
session_start();
$conn = new mysqli("localhost", "root", "", "jojohotelsdb");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$arrival   = $_GET['arrival']   ?? '';
$departure = $_GET['departure'] ?? '';
$guests    = (int)($_GET['guests'] ?? 0);
$sort      = $_GET['sort']      ?? '';
$useAIOrder = strtolower($sort) === 'ai';

$stmt = $conn->prepare("SELECT * FROM rooms WHERE Guests >= ? AND Status = 'Available'");
$stmt->bind_param("i", $guests);
$stmt->execute();
$result = $stmt->get_result();
$rooms = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

/* ---------------- AI (bandit) — ONLY when ?sort=ai ---------------- */
$recommendedIDs = [];
$roomIDs        = array_map(fn($r)=>(int)$r['RoomID'], $rooms ?? []);
if ($useAIOrder && !empty($roomIDs)) {
  $idList = implode(',', array_map('intval', $roomIDs));

  $conn2 = new mysqli("localhost", "root", "", "jojohotelsdb");
  if (!$conn2->connect_error) {
    $stats = [];
    $totalShown = 0;

    if ($rs = $conn2->query("SELECT RoomID, shown, booked FROM rec_stats WHERE RoomID IN ($idList)")) {
      while ($row = $rs->fetch_assoc()) {
        $rid = (int)$row['RoomID'];
        $stats[$rid] = ['shown'=>(int)$row['shown'], 'booked'=>(int)$row['booked']];
        $totalShown += (int)$row['shown'];
      }
      $rs->free();
    }
    $conn2->close();

    $totalShown = max(1, $totalShown);

    // score + reorder
    $scoreById = [];
    $scored = [];
    foreach ($rooms as $r) {
      $rid = (int)$r['RoomID'];
      $s = $stats[$rid]['shown']  ?? 0;
      $b = $stats[$rid]['booked'] ?? 0;
      $conv    = ($b + 1) / ($s + 2);
      $explore = sqrt(log($totalShown + 1) / ($s + 1));
      $score   = $conv + 0.8 * $explore;
      $scoreById[$rid] = $score;
      $scored[] = ['RoomID'=>$rid, 'score'=>$score];
    }

    usort($scored, fn($a,$b)=>$b['score'] <=> $a['score']);
    $recommendedIDs = array_column(array_slice($scored, 0, 3), 'RoomID');

    usort($rooms, function($a,$b) use($scoreById){
      $sa = $scoreById[(int)$a['RoomID']] ?? -1;
      $sb = $scoreById[(int)$b['RoomID']] ?? -1;
      if ($sa === $sb) {
        $pa = (float)$a['Price']; $pb = (float)$b['Price'];
        if ($pa == $pb) return ((int)$a['RoomID']) <=> ((int)$b['RoomID']);
        return $pa <=> $pb; // cheaper first on tie
      }
      return $sb <=> $sa;
    });
  }
}

// For the "shown" ping (use whatever order we're displaying)
$roomIDsFinal = array_map(fn($r)=>(int)$r['RoomID'], $rooms ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'includes/header.php'; ?>
  <meta charset="UTF-8" />
  <title>Available Rooms</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body { font-family:'Segoe UI', Tahoma, sans-serif; margin:0; background:#f5f7fa; }
    .header { background:#003580; color:#fff; padding:30px 20px; text-align:center; }
    .header h1 { margin:0; font-size:2rem; }
    .search-details { margin-top:10px; font-size:.95rem; color:#e0e6ef; }
    .container { max-width:1200px; margin:40px auto; padding:0 20px; }
    .room-card { background:#fff; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,.06);
                 display:flex; overflow:hidden; margin-bottom:30px; transition:transform .3s; }
    .room-card:hover { transform:scale(1.01); }
    .room-card img { width:300px; height:200px; object-fit:cover; }
    .room-details { padding:20px; flex:1; }
    .room-details h2 { margin-top:0; color:#003580; font-size:1.4rem; }
    .room-details p { margin:8px 0; color:#333; }
    .book-btn { background:#febb02; color:#003580; padding:12px 18px; border:0; border-radius:6px;
                font-size:1rem; font-weight:bold; cursor:pointer; margin-top:12px; transition:background .3s; }
    .book-btn:hover { background:#f3ac00; }
    .no-rooms { text-align:center; font-size:1.2rem; color:#777; margin-top:50px; }
    @media (max-width:768px){ .room-card{flex-direction:column} .room-card img{width:100%; height:180px} }
    <?php if ($useAIOrder): ?>
    .rec-badge{display:inline-block;margin-left:8px;padding:2px 6px;border-radius:6px;font-size:.8rem;
               font-weight:700;background:#fff2bf;color:#8a6d3b;vertical-align:middle}
    <?php endif; ?>
  </style>
</head>
<body>

  <div class="header">
    <h1>Available Rooms</h1>
    <div class="search-details">
      <p>Check-in: <?= htmlspecialchars($arrival) ?> | Check-out: <?= htmlspecialchars($departure) ?> | Guests: <?= htmlspecialchars((string)$guests) ?></p>
      <?php
        $baseQuery = http_build_query(['arrival'=>$arrival,'departure'=>$departure,'guests'=>$guests]);
      ?>
      <p>
        Sort:
        <a style="color:#febb02;<?= !$useAIOrder ? 'font-weight:bold;' : '' ?>" href="?<?= $baseQuery ?>">Original</a>
        &nbsp;·&nbsp;
        <a style="color:#febb02;<?= $useAIOrder ? 'font-weight:bold;' : '' ?>" href="?<?= $baseQuery ?>&sort=ai">AI Recommended</a>
      </p>
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
            <h2>
              Room <?= htmlspecialchars($room['RoomNumber']) ?>
              <?php if ($useAIOrder && in_array((int)$room['RoomID'], $recommendedIDs, true)): ?>
                <span class="rec-badge">Recommended</span>
              <?php endif; ?>
            </h2>
            <p><strong>Floor:</strong> <?= htmlspecialchars($room['FloorNumber']) ?></p>
            <p><strong>Guests:</strong> <?= htmlspecialchars($room['Guests']) ?></p>
            <p><strong>Price:</strong> GHS <?= htmlspecialchars($room['Price']) ?></p>
            <?php
              $rid = (int)$room['RoomID'];
              $arr = htmlspecialchars($arrival, ENT_QUOTES);
              $dep = htmlspecialchars($departure, ENT_QUOTES);
              $g   = (int)$guests;
            ?>

            <!-- ✅ View Virtual Tour (restored) -->
            <button class="book-btn" type="button"
              onclick="openVirtualTour('Pictures/room1_360.jpg')">
              View Virtual Tour
            </button>

            <button class="book-btn" onclick='bookRoom(<?= $rid ?>, "<?= $arr ?>", "<?= $dep ?>", <?= $g ?>)'>
              Book Now
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- ✅ Add the virtual tour modal include back -->
  <?php include 'includes/virtual_tour.php'; ?>

  <?php include 'includes/pickup_modal.php'; ?>

  <script>
  window.lastBooking = null;

  async function bookRoom(RoomID, arrival, departure, guests) {
    const fd = new FormData();
    fd.append("RoomID", RoomID);
    fd.append("arrival", arrival);
    fd.append("departure", departure);
    fd.append("guests", guests);

    try {
      const res = await fetch("book_room.php", {
        method: "POST",
        body: fd,
        credentials: "include"
      });
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

      // keep learning even in Original (ok to keep; remove if you prefer)
      fetch('rec_event.php', {
        method: 'POST',
        body: new URLSearchParams({ type: 'booked', id: String(RoomID) }),
        credentials: 'include'
      }).catch(()=>{});

      document.getElementById("pickupModal").style.display = "flex";
    } catch (e) {
      console.error("Booking error:", e);
      alert("An error occurred while booking.");
    }
  }

  window.addEventListener("DOMContentLoaded", () => {
    const yesBtn = document.getElementById("yesPickup");
    const noBtn  = document.getElementById("noPickup");

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

    // mark rooms shown (optional to keep learning in Original; remove if you prefer)
    (function(){
      const ids = <?= json_encode($roomIDsFinal ?? []) ?>;
      if (Array.isArray(ids) && ids.length) {
        fetch('rec_event.php', {
          method: 'POST',
          body: new URLSearchParams({ type: 'shown', ids: ids.join(',') }),
          credentials: 'include'
        }).catch(()=>{});
      }
    })();
  });
  </script>
</body>
</html>
