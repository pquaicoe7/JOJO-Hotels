<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pickup Request</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCCSVMipDBPOFUy4OLHQVgNDzXhS3icZCk&libraries=places&callback=initMap"
        async defer></script>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fa;
        }

        .header {
            background-color: #003580;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        .container {
            max-width: 960px;
            background: #fff;
            margin: 40px auto;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);
        }

        h2 {
            text-align: center;
            color: #003580;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-top: 20px;
            font-weight: 600;
            color: #2c3e50;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 15px;
        }

        .search-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .location-btn {
            background: #febb02;
            color: #003580;
            padding: 11px 16px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .location-btn:hover {
            background: #e0a600;
        }

        #map {
            margin-top: 16px;
            height: 300px;
            border-radius: 10px;
            border: 1px solid #ccc;
        }

        .car-options {
            display: flex;
            gap: 16px;
            margin-top: 24px;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .car-card {
            flex: 1;
            text-align: center;
            border: 2px solid #ccc;
            border-radius: 10px;
            padding: 14px;
            cursor: pointer;
            background: #fafafa;
            transition: 0.3s ease;
        }

        .car-card:hover {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
        }

        .car-card img {
            width: 100%;
            height: 130px;
            object-fit: cover;
            border-radius: 6px;
        }

        .car-card.selected {
            border-color: #003580;
            background-color: #e9f1ff;
        }

        .submit-btn {
            margin-top: 30px;
            width: 100%;
            background: #febb02;
            color: #003580;
            font-weight: bold;
            padding: 14px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #e0a600;
        }

        .toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #003580;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 15px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s ease;
        }

        .toast.show {
            opacity: 1;
        }

        #pricingInfo {
            margin-top: 20px;
            font-weight: bold;
            color: #003580;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .car-options {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
     
<div class="header">
        <h1>Hotel Pickup Request</h1>
    </div>

    <div class="container">
        <h2>Request a Pickup</h2>
        <form id="pickupForm" action="pickup_review.php" method="post" onsubmit="return checkCostBeforeSubmit()">
            <input type="hidden" name="booking_id" value="<?= htmlspecialchars($_GET['booking_id'] ?? '') ?>">
            <input type="hidden" name="room_id" value="<?= htmlspecialchars($_GET['room_id'] ?? '') ?>">
            <input type="hidden" name="arrival" value="<?= htmlspecialchars($_GET['arrival'] ?? '') ?>">
            <input type="hidden" name="departure" value="<?= htmlspecialchars($_GET['departure'] ?? '') ?>">
            <input type="hidden" name="guests" value="<?= htmlspecialchars($_GET['guests'] ?? '') ?>">
            <input type="hidden" name="total" value="<?= htmlspecialchars($_GET['total'] ?? '') ?>">
            <input type="hidden" name="estimated_cost" id="estimatedCostInput">
            <input type="hidden" name="distance_km" id="distanceInput">

            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>

            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" required>

            <label for="luggage">Luggage Size</label>
            <select name="luggage" id="luggage" required>
                <option value="few">Few</option>
                <option value="many">Many</option>
            </select>

            <label for="pickup_time">Preferred Pickup Time</label>
            <input type="time" id="pickup_time" name="pickup_time" required>

            <label for="searchBox">Your Location</label>
            <div class="search-wrapper">
                <input type="text" id="searchBox" placeholder="Search your location..." />
                <button type="button" class="location-btn" onclick="useCurrentLocation()">📍 Use Current</button>
            </div>

            <div id="map"></div>
            <input type="hidden" name="location" id="locationInput">

            <label>Choose Car Type</label>
            <div class="car-options">
                <div class="car-card" onclick="selectCar('luxury')">
                    <img src="Pictures/luxury.jpg" alt="Luxury Car">
                    <p><strong>Luxury</strong></p>
                    <input type="radio" name="car_type" value="luxury" hidden>
                </div>
                <div class="car-card" onclick="selectCar('comfort')">
                    <img src="Pictures/comfort.jpg" alt="Comfort Car">
                    <p><strong>Comfort</strong></p>
                    <input type="radio" name="car_type" value="comfort" hidden>
                </div>
                <div class="car-card" onclick="selectCar('economic')">
                    <img src="Pictures/economic.jpg" alt="Economic Car">
                    <p><strong>Economic</strong></p>
                    <input type="radio" name="car_type" value="economic" hidden>
                </div>
            </div>

            <div id="pricingInfo">Please set your location and car type to calculate cost.</div>

            <button type="submit" class="submit-btn">Submit Pickup Request</button>
        </form>
    </div>

    <div id="toast" class="toast">Location set!</div>

    <script>
        const hotelCoords = { lat: 6.669379, lng: -1.616091 };
        let map, marker, geocoder;

        function selectCar(type) {
            document.querySelectorAll('.car-card').forEach(card => {
                card.classList.remove('selected');
                card.querySelector('input[type=radio]').checked = false;
            });

            const selected = document.querySelector(`.car-card img[alt="${capitalize(type)} Car"]`).parentElement;
            selected.classList.add('selected');
            selected.querySelector('input[type=radio]').checked = true;

            if (marker) {
                calculateDistanceAndPrice(marker.getPosition());
            }
        }

        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function showToast(message) {
            const toast = document.getElementById("toast");
            toast.textContent = message;
            toast.classList.add("show");
            setTimeout(() => toast.classList.remove("show"), 3000);
        }

        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                center: hotelCoords,
                zoom: 13
            });

            marker = new google.maps.Marker({
                map,
                position: hotelCoords,
                draggable: true
            });

            geocoder = new google.maps.Geocoder();

            marker.addListener('dragend', () => {
                geocodePosition(marker.getPosition());
            });

            const input = document.getElementById("searchBox");
            const searchBox = new google.maps.places.SearchBox(input);

            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();
                if (!places.length) return;
                const place = places[0];
                if (!place.geometry) return;
                map.setCenter(place.geometry.location);
                marker.setPosition(place.geometry.location);
                geocodePosition(place.geometry.location);
            });
        }

        function useCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(pos => {
                    const coords = {
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude
                    };
                    map.setCenter(coords);
                    marker.setPosition(coords);
                    geocodePosition(coords);
                }, () => {
                    alert("Could not fetch your current location.");
                });
            } else {
                alert("Your browser does not support location access.");
            }
        }

        function geocodePosition(pos) {
            geocoder.geocode({ location: pos }, (results, status) => {
                if (status === "OK" && results[0]) {
                    const address = results[0].formatted_address;
                    document.getElementById("locationInput").value = address;
                    document.getElementById("searchBox").value = address;
                    showToast("Location set to: " + address);
                    calculateDistanceAndPrice(pos);
                }
            });
        }

        function calculateDistanceAndPrice(userCoords) {
            const service = new google.maps.DirectionsService();

            service.route({
                origin: userCoords,
                destination: hotelCoords,
                travelMode: google.maps.TravelMode.DRIVING
            }, (response, status) => {
                if (status === "OK") {
                    const distanceInMeters = response.routes[0].legs[0].distance.value;
                    const distanceInKm = distanceInMeters / 1000;

                    const carType = document.querySelector('input[name="car_type"]:checked')?.value || "comfort";
                    const baseRate = 5;
                    const carModifiers = {
                        luxury: 2.0,
                        comfort: 1.0,
                        economic: 0.8
                    };

                    const price = Math.ceil(distanceInKm * baseRate * (carModifiers[carType] || 1.0));

                    document.getElementById("pricingInfo").textContent =
                        `Distance: ${distanceInKm.toFixed(2)} km | Estimated Cost: ₵${price}`;

                    document.getElementById("estimatedCostInput").value = price;
                    document.getElementById("distanceInput").value = distanceInKm.toFixed(2);
                }
            });
        }

        function checkCostBeforeSubmit() {
            const car = document.querySelector('input[name="car_type"]:checked');
            const location = document.getElementById("locationInput").value;
            const cost = document.getElementById("estimatedCostInput").value;
            const dist = document.getElementById("distanceInput").value;

            if (!car) {
                alert("Please select a car type.");
                return false;
            }
            if (!location) {
                alert("Please set your location.");
                return false;
            }
            if (!cost || !dist) {
                alert("Please wait for the cost to be calculated.");
                return false;
            }

            return true;
        }
    </script>
</body>

</html>