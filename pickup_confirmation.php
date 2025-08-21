<?php
$success = $_GET['success'] ?? 0;
$bookingId = $_GET['booking_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Pickup Confirmed</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f7fa;
    }

    .header {
      background-color: #003580;
      color: white;
      padding: 20px 0;
      text-align: center;
    }

    .header h1 {
      margin: 0;
      font-size: 2rem;
    }

    .container {
      max-width: 700px;
      background: #ffffff;
      margin: 60px auto;
      padding: 40px 30px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      text-align: center;
    }

    .container h2 {
      color: #003580;
      margin-bottom: 20px;
    }

    .success-message {
      font-size: 1.1rem;
      margin-top: 10px;
      margin-bottom: 20px;
      color: #2c3e50;
    }

    .note {
      background: #f1f3f6;
      padding: 16px;
      border-radius: 10px;
      margin-bottom: 30px;
      color: #333;
    }

    .btn {
      background-color: #febb02;
      color: #003580;
      padding: 12px 24px;
      border: none;
      font-weight: bold;
      border-radius: 6px;
      font-size: 16px;
      text-decoration: none;
      cursor: pointer;
    }

    .btn:hover {
      background-color: #e0a600;
    }
  </style>
</head>

<body>
  <?php include 'includes/header.php'; ?>

  <div class="header">
    <h1>Pickup Confirmation</h1>
  </div>

  <div class="container">
    <?php if ($success && $bookingId): ?>
      <h2>✅ Your Pickup Request Has Been Confirmed!</h2>
      <p class="success-message">Thank you for choosing us. A driver will contact you shortly to arrange your pickup.</p>
      <div class="note">
        <strong>Note:</strong> This pickup fee will be added to your total room cost. Please proceed to finalize your
        payment.
      </div>
      <a href="final_summary.php?booking_id=<?= urlencode($bookingId) ?>" class="btn">Finish Booking</a>
    <?php else: ?>
      <h2>⚠️ Something Went Wrong</h2>
      <p class="success-message">We couldn't confirm your pickup. Please try again or contact support.</p>
      <a href="pickup_request.php" class="btn">Try Again</a>
    <?php endif; ?>
  </div>

</body>

</html>