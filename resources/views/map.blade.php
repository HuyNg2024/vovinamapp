<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map with Clubs</title>
    <!-- Include Leaflet CSS and JavaScript -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>
    <div id="map" style="height: 600px;"></div>
    <div>
        <h2>Find Nearby Clubs</h2>
        <button onclick="findNearby()">Find Nearby</button>
    </div>
    <div>
        <h2>Search Clubs by District and City</h2>
        <form id="search-form">
            <label for="district">District:</label>
            <input type="text" id="district" name="district">
            <label for="city">City:</label>
            <input type="text" id="city" name="city">
            <button type="submit">Search</button>
        </form>
    </div>

    <script>
        var map = L.map('map').setView([21.0285, 105.8542], 13); // Tọa độ trung tâm bản đồ và mức zoom

        // Thêm bản đồ nền
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        // Gọi API để lấy danh sách các câu lạc bộ gần đây và hiển thị lên bản đồ
        function findNearby() {
            fetch('/api/clubs/nearby?latitude=21.0285&longitude=105.8542') // Thay tọa độ bằng tọa độ thực của bạn
                .then(response => response.json())
                .then(clubs => {
                    clubs.forEach(club => {
                        L.marker([club.latitude, club.longitude]).addTo(map)
                            .bindPopup(`<b>${club.name}</b><br>${club.address}`)
                            .openPopup();
                    });
                })
                .catch(error => console.error('Error fetching nearby clubs:', error));
        }

        // Xử lý form tìm kiếm
        document.getElementById('search-form').addEventListener('submit', function(event) {
            event.preventDefault();
            var district = document.getElementById('district').value;
            var city = document.getElementById('city').value;

            fetch(`/api/clubs/search?district=${district}&city=${city}`)
                .then(response => response.json())
                .then(clubs => {
                    clubs.forEach(club => {
                        L.marker([club.latitude, club.longitude]).addTo(map)
                            .bindPopup(`<b>${club.name}</b><br>${club.address}`)
                            .openPopup();
                    });
                })
                .catch(error => console.error('Error fetching clubs by district and city:', error));
        });
    </script>
</body>
</html>
