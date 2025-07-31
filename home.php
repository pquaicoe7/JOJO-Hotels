<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>JOJO Hotels</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #fff;
      color: #333;
    }

    .header {
      background: #003580;
      color: white;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header h1 {
      margin: 0;
      font-size: 24px;
    }

    .hero {
      background: url('Pictures/about-4.jpg') center/cover no-repeat;
      color: white;
      padding: 100px 40px;
      text-align: center;
      position: relative;
    }

    .hero::after {
      content: '';
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
    }

    .hero h2,
    .hero p {
      position: relative;
      z-index: 2;
    }

    .hero h2 {
      font-size: 40px;
      margin-bottom: 10px;
    }

    .hero p {
      font-size: 18px;
    }

    .booking-bar {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      background: #fff;
      padding: 20px;
      margin-top: -40px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      position: relative;
      z-index: 10;
    }

    .booking-bar input,
    .booking-bar select,
    .booking-bar button {
      padding: 10px;
      margin: 5px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    .booking-bar button {
      background: #febb02;
      border: none;
      color: #003580;
      font-weight: bold;
      cursor: pointer;
    }

    .section {
      padding: 60px 40px;
      max-width: 1100px;
      margin: auto;
    }

    .section h3 {
      color: #003580;
      margin-bottom: 20px;
    }

    .btn {
      background: #febb02;
      color: #003580;
      padding: 10px 20px;
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      font-weight: bold;
      border-radius: 4px;
    }

    .features-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
    }

    .feature-box {
      flex: 1 1 250px;
      background: #f5f7fa;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      text-align: center;
    }

    .feature-box h4 {
      color: #003580;
      margin-bottom: 10px;
    }

    footer {
      background: #003580;
      color: white;
      padding: 30px 20px;
    }

    footer a {
      color: #febb02;
      text-decoration: none;
    }

    footer ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    footer .bottom-bar {
      text-align: center;
      margin-top: 30px;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
      padding-top: 10px;
    }

    @media (max-width: 768px) {
      .booking-bar {
        flex-direction: column;
        align-items: center;
      }

      .hero h2 {
        font-size: 28px;
      }
    }
  </style>
</head>

<body>

  <div class="header">
    <h1>JOJO Hotels</h1>
    <a href="login.php" style="color:white;">Login</a>
  </div>

  <div class="hero" style="
  background: url('Pictures/hero.jpg') center/cover no-repeat;
">
  <h2>A Place to Call Home</h2>
  <p>Choose from comfortable rooms and great services at JOJO Hotels</p>
</div>

<div style="margin-top: 20px; text-align: center;">
  <button id="openModal" style="
    background-color: #febb02;
    color: #003580;
    border: none;
    padding: 14px 28px;
    font-size: 18px;
    font-weight: bold;
    border-radius: 8px;
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: background-color 0.3s ease;
  ">
    Book Now
  </button>
</div>


  <!-- ✅ About Section -->
  <div class="section">
    <h3>About Us</h3>
    <p>
      At JOJO Hotels, we blend comfort with excellent customer service to make your stay unforgettable.
      Whether you're a solo traveler, a couple on vacation, or a business professional,
      we have the perfect space for you.
    </p>
    <a href="#" class="btn" id="openModal">Book Now</a>
  </div>

  <!-- ✅ Features Section -->
  <div class="section">
    <h3>Why Choose Us</h3>
    <div class="features-container">
      <div class="feature-box">
        <h4>24/7 Customer Support</h4>
        <p>We’re always here to help with any booking issues or inquiries.</p>
      </div>
      <div class="feature-box">
        <h4>Best Price Guarantee</h4>
        <p>Our rates are unbeatable — find comfort that fits your budget.</p>
      </div>
      <div class="feature-box">
        <h4>Pickup Service</h4>
        <p>We’ll pick you up from your location — just choose the option after booking.</p>
      </div>
    </div>
  </div>

  <?php include 'includes/booking_modal.php'; ?>

  <!-- ✅ Footer -->
  <footer>
    <div style="max-width: 1100px; margin: auto; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 20px;">

      <div>
        <h3 style="margin: 0 0 10px;">JOJO Hotels</h3>
        <p style="max-width: 300px; line-height: 1.6;">Affordable comfort and great hospitality in the heart of Ghana.</p>
      </div>

      <div>
        <h4 style="margin: 0 0 10px;">Links</h4>
        <ul>
          <li><a href="home.php">Home</a></li>
          <li><a href="available_rooms.php">Rooms</a></li>
          <li><a href="menu.php">Menu</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
      </div>

      <div>
        <h4 style="margin: 0 0 10px;">Contact</h4>
        <p style="margin: 5px 0;">📧 info@jojohotels.com</p>
        <p style="margin: 5px 0;">📞 +233 20 000 0000</p>
        <p style="margin: 5px 0;">📍 Accra, Ghana</p>
      </div>
    </div>

    <div class="bottom-bar">
      <p>&copy; <?= date('Y') ?> JOJO Hotels. All rights reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    flatpickr("#arrival", { minDate: "today" });
    flatpickr("#departure", { minDate: "today" });
  </script>

</body>

</html>
