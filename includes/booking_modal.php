<!-- ✅ Redesigned Booking Modal -->
<div id="bookingModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeModal">&times;</span>
    <h2>Check Availability</h2>
    <form action="available_rooms.php" method="GET">
      <div class="input__group">
        <label for="arrival">Arrival Date</label>
        <input type="text" id="arrival" name="arrival" placeholder="Select arrival date" required>
      </div>
      <div class="input__group">
        <label for="departure">Departure Date</label>
        <input type="text" id="departure" name="departure" placeholder="Select departure date" required>
      </div>
      <div class="input__group">
        <label for="guests">Guests</label>
        <input type="number" name="guests" placeholder="No of Guests" required min="1">
      </div>
      <button class="btn-primary" type="submit">Check Availability</button>
    </form>
  </div>
</div>

<!-- Modal Styles -->
<style>
  .modal {
    display: none;
    position: fixed;
    z-index: 999;
    padding-top: 80px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    font-family: 'Segoe UI', sans-serif;
  }

  .modal-content {
    background-color: #ffffff;
    margin: auto;
    padding: 30px;
    width: 90%;
    max-width: 500px;
    border-radius: 10px;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
  }

  .modal h2 {
    margin-bottom: 20px;
    color: #003580;
    text-align: center;
  }

  .close {
    color: #999;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
  }

  .close:hover {
    color: #000;
  }

  .input__group {
    margin-bottom: 20px;
  }

  .input__group label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #333;
  }

  .input__group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
    box-sizing: border-box;
  }

  .btn-primary {
    background-color: #febb02;
    color: #003580;
    border: none;
    padding: 12px 18px;
    border-radius: 6px;
    font-size: 16px;
    width: 100%;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .btn-primary:hover {
    background-color: #e6a500;
  }
</style>

<!-- JS for modal + flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
  flatpickr("#arrival", { minDate: "today", dateFormat: "Y-m-d" });
  flatpickr("#departure", { minDate: "today", dateFormat: "Y-m-d" });

  const modal = document.getElementById("bookingModal");
  const openBtn = document.getElementById("openModal");
  const closeBtn = document.getElementById("closeModal");

  openBtn.addEventListener("click", (e) => {
    e.preventDefault();
    modal.style.display = "block";
  });

  closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
  });

  window.addEventListener("click", (e) => {
    if (e.target === modal) modal.style.display = "none";
  });
</script>
