<?php
session_start();

// Validate incoming POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pickup_request.php");
    exit();
}

$data = $_POST;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Review Pickup Request</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f5f7fa;
      margin: 0;
    }

    .header {
      background-color: #003580;
      color: white;
      padding: 20px;
      text-align: center;
    }

    .container {
      max-width: 700px;
      margin: 40px auto;
      background: #fff;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);
    }

    h2 {
      color: #003580;
      text-align: center;
      margin-bottom: 25px;
    }

    .info {
      margin-bottom: 20px;
      font-size: 16px;
    }

    .info strong {
      display: inline-block;
      width: 150px;
      color: #333;
    }

    .actions {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
    }

    .btn {
      padding: 12px 20px;
      font-size: 15px;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }

    .btn-edit {
      background-color: #ccc;
      color: #003580;
    }

    .btn-confirm {
      background-color: #febb02;
      color: #003580;
    }

    .btn:hover {
      opacity: 0.9;
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>Pickup Review</h1>
  </div>

  <div class="container">
    <h2>Please Confirm Your Details</h2>

    <?php
    function safe($key) {
      return htmlspecialchars($_POST[$key] ?? '');
    }
    ?>

<div class="info"><strong>Full Name:</strong> <?= safe('name') ?></div>
    <div class="info"><strong>Phone:</strong> <?= safe('phone') ?></div>
    <div class="info"><strong>Luggage:</strong> <?= safe('luggage') ?></div>
    <div class="info"><strong>Pickup Time:</strong> <?= safe('pickup_time') ?></div>
    <div class="info"><strong>Pickup Location:</strong> <?= safe('location') ?></div>
    <div class="info"><strong>Car Type:</strong> <?= safe('car_type') ?></div>
    <div class="info"><strong>Distance:</strong> <?= safe('distance_km') ?> km</div>
    <div class="info"><strong>Estimated Cost:</strong> ₵<?= safe('estimated_cost') ?></div>

    <form action="handle_pickup.php" method="post">
      <!-- Hidden all values again for final submission -->
      <?php foreach ($data as $key => $val): ?>
        <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>">
      <?php endforeach; ?>

      <div class="actions">
        <button type="button" onclick="history.back()" class="btn btn-edit">⬅ Back to Edit</button>
        <button type="submit" class="btn btn-confirm">✅ Confirm Pickup</button>
      </div>
    </form>
  </div>
</body>
</html>
