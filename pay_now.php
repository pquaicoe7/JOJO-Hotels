<?php
session_start();

$total = $_GET['total'] ?? 0;
$bookingId = $_GET['booking_id'] ?? '';
$userEmail = $_SESSION['email'] ?? 'test@example.com';
$userName = $_SESSION['name'] ?? 'John Doe';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Make Payment</title>
  <script src="https://js.paystack.co/v1/inline.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f7fa;
      margin: 0;
    }

    .container {
      max-width: 600px;
      margin: 60px auto;
      background: #fff;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    h2 {
      color: #003580;
      margin-bottom: 20px;
    }

    .info {
      margin: 20px 0;
      font-size: 1.1rem;
    }

    .btn {
      padding: 12px 24px;
      font-size: 1rem;
      background: #febb02;
      color: #003580;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Finalize Your Booking Payment</h2>
  <p class="info"><strong>Booking ID:</strong> <?= htmlspecialchars($bookingId) ?></p>
  <p class="info"><strong>Total Amount:</strong> GHS <?= number_format($total, 2) ?></p>
  <button class="btn" onclick="payWithPaystack()">Pay Now</button>
</div>

<script>
  function payWithPaystack() {
    let handler = PaystackPop.setup({
      key: 'pk_test_018ea9b14a696336ef0a9606d1e5045a203a41c4', // Replace with your public key
      email: '<?= $userEmail ?>',
      amount: <?= $total ?> * 100, // Amount in kobo
      currency: "GHS",
      ref: 'HOTELBOOK_' + Math.floor((Math.random() * 1000000000) + 1), // Random reference
      metadata: {
        custom_fields: [
          {
            display_name: "Booking ID",
            variable_name: "booking_id",
            value: "<?= $bookingId ?>"
          }
        ]
      },
      callback: function(response) {
        alert('Payment successful! Ref: ' + response.reference);
        window.location.href = "payment_success.php?ref=" + response.reference + "&booking_id=<?= $bookingId ?>";
      },
      onClose: function() {
        alert('Transaction was cancelled.');
      }
    });

    handler.openIframe();
  }
</script>

</body>
</html>
