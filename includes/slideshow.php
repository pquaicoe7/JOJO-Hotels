<!-- ✅ Feature Card Slideshow -->
<style>
  .slideshow-container {
    max-width: 1100px;
    margin: 60px auto;
    position: relative;
    overflow: hidden;
  }

  .slide {
    display: none;
    flex-wrap: wrap;
    align-items: center;
    gap: 30px;
    background: #f5f7fa;
    border-radius: 12px;
    padding: 40px 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: opacity 0.6s ease-in-out;
  }

  .slide.active {
    display: flex;
  }

  .slide img {
    flex: 1;
    max-width: 500px;
    height: 320px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }

  .slide-content {
    flex: 1;
    min-width: 280px;
  }

  .slide-content h2 {
    color: #003580;
    font-size: 2rem;
    margin-bottom: 15px;
  }

  .slide-content p {
    color: #333;
    font-size: 1.1rem;
    line-height: 1.7;
  }

  .slideshow-nav {
    text-align: center;
    margin-top: 20px;
  }

  .slideshow-nav button {
    background: #febb02;
    color: #003580;
    border: none;
    padding: 10px 16px;
    margin: 0 6px;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease;
  }

  .slideshow-nav button:hover {
    background: #e0a800;
  }
</style>

<div class="slideshow-container">
  <!-- Slide 1 -->
  <div class="slide active">
    <img src="Pictures/roomb.jpg" alt="Rooms and Breakfast">
    <div class="slide-content">
      <h2>🛏️ Cozy Rooms & Delicious Breakfast</h2>
      <p>
        Wake up to the comfort of a well-furnished room and a free delicious breakfast each morning.
        Our spacious rooms offer privacy, comfort, and a great start to your day.
      </p>
    </div>
  </div>

  <!-- Slide 2 -->
  <div class="slide">
    <img src="Pictures/wifi.jpg" alt="Free WiFi">
    <div class="slide-content">
      <h2>📶 Unlimited Free WiFi</h2>
      <p>
        Whether you're working remotely or streaming your favorite shows, enjoy seamless internet access.
        Our high-speed WiFi is available in all rooms and public areas — for free.
      </p>
    </div>
  </div>

  <!-- Slide 3 -->
  <div class="slide">
    <img src="Pictures/pickup.jpg" alt="Hotel Pickup">
    <div class="slide-content">
      <h2>🚘 Hassle-Free Hotel Pickup</h2>
      <p>
        Just booked? We’ve got you. Let us pick you up at your location.
        Share your arrival time and preferred ride — our driver will be there on time.
      </p>
    </div>
  </div>

  <!-- Navigation Buttons -->
  <div class="slideshow-nav">
    <button onclick="showSlide(0)">1</button>
    <button onclick="showSlide(1)">2</button>
    <button onclick="showSlide(2)">3</button>
  </div>
</div>

<script>
  let currentSlide = 0;
  const slides = document.querySelectorAll(".slide");

  function showSlide(index) {
    slides.forEach((slide, i) => {
      slide.classList.toggle("active", i === index);
    });
    currentSlide = index;
  }

  // Optional: Auto-slide every 5s
  setInterval(() => {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
  }, 5000);
</script>
