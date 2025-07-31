<!-- check_availability.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Check Availability</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
  <div class="container" style="max-width: 600px; margin: 60px auto;">
    <h2 style="text-align:center;">Check Room Availability</h2>
    <form action="available_rooms.php" method="GET">
      <div class="input__group">
        <label for="arrival">Arrival Date</label>
        <input type="text" id="arrival" name="arrival" placeholder="Select arrival date">
      </div>
      <div class="input__group">
        <label for="departure">Departure Date</label>
        <input type="text" id="departure" name="departure" placeholder="Select departure date">
      </div>
      <div class="input__group">
        <label for="guests">Guests</label>
        <input type="number" name="guests" min="1" required placeholder="No of Guests">
      </div>
      <button class="btn" style="margin-top:20px;">Check Availability</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    flatpickr("#arrival", {
      minDate: "today",
      dateFormat: "Y-m-d"
    });
    flatpickr("#departure", {
      minDate: "today",
      dateFormat: "Y-m-d"
    });
  </script>
</body>
</html>
