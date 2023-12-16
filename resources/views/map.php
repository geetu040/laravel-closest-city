<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>World Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px;
        }

        main {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        #map {
            height: 400px;
            margin-bottom: 20px;
        }

        #loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
        }

        input {
            padding: 8px;
            margin-bottom: 10px;
        }

        select {
            padding: 8px;
            width: 100%;
            margin-bottom: 20px;
        }

        ol {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>


<main>
    <div id="loading-spinner">Loading...</div>

    <h1>Select From Dropdown</h1>

    <input type="text" name="" id="" placeholder="Enter city" oninput="inputchange(this)">
    <br>
    <select name="location" id="locationDropdown" onchange="dropdown_select(this)">
        <!-- Options will be dynamically added here -->
    </select>

    <h1>Select By Clicking on Map</h1>
    <div id="map"></div>
    <h1>
        Selected:
        <span id="sel-city"></span>
        (
        Lat: <span id="sel-lat"></span>
        |
        Long: <span id="sel-lng"></span>
        )
    </h1>
    <h1>5 Closest Cities:</h1>
    <ol id="closest_list"></ol>
</main>


<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script>
    
    // ====> SPINNER

    $(document).ajaxStart(function () {
        // Show loading spinner when AJAX request starts
        $('#loading-spinner').show();
    }).ajaxStop(function () {
        // Hide loading spinner when AJAX request completes
        $('#loading-spinner').hide();
    });

    // ====> GET DATA


    $.get('/get_data', function(data) {
        window.data = data;
    });

    // ====> SELECT CITY

    function select_city(city, lat, lng) {

        let round = 100;

        if (city) {
            document.getElementById("sel-city").innerText = city;
            document.getElementById("sel-lat").innerText = Math.round(lat * round) / round;
            document.getElementById("sel-lng").innerText = Math.round(lng * round) / round;
            calculate();
        }

    }

    // ====> CALCULATE

    function calculate() {
        city = document.getElementById("sel-city").innerText;
        
        lat = parseFloat(document.getElementById("sel-lat").innerText);
        lng = parseFloat(document.getElementById("sel-lng").innerText);

        if (city) {
            let closest = get_closest(lat, lng);

            var ul = $("#closest_list");
            ul.empty();
            for (var i = 0; i < closest.length; i++) {
                var cityData = closest[i];
                var li = $("<li>").text(cityData.join(', '));
                ul.append(li);
            }
        }



    }

    // ====> CLOSEST
    function get_closest(lat, lng) {

        // 1. USE THIS DUMMY OBJECT FOR TESTING WHERE EACH ITEM IS (city_name, lattitude, longitude)
        data = window.data;
        // Function to calculate Earth's distance between two coordinates
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the Earth in kilometers
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            const distance = R * c; // Distance in kilometers
            return distance;
        }

        // Find the closest locations
        var closestLocations = [];
        for (var i = 0; i < data.length; i++) {
            var city = data[i][0];
            var cityLat = data[i][1];
            var cityLng = data[i][2];

            // Calculate distance
            var distance = calculateDistance(lat, lng, cityLat, cityLng);

            // Add to the result array
            closestLocations.push([city, cityLat, cityLng, distance]);
        }

        // Sort the result array based on distance
        closestLocations.sort(function (a, b) {
            return a[3] - b[3];
        });

        // Return the closest locations
        return closestLocations.slice(0, 5); // Return the top 5 closest locations
    }

    // ====> DROPDOWN

    function inputchange(element) {
        query = element.value.toLowerCase();

        // let data = [['abc', 0, 0], ['fasdfs', 0, 0], ['faded', 0, 0], ['dfdsc', 0, 0]];
        let data = window.data;

        if (data && query.length > 1) {

            let new_data = [];
            for (let i=0; i<data.length; i++) {
                let target = data[i][0].toLowerCase()
                if (target.startsWith(query)) {
                    new_data.push(data[i]);
                }
            }
            update_dropdown(new_data);
            if (new_data.length > 0) {
                select_city(new_data[0][0], new_data[0][1], new_data[0][2]);
            }

        }

    }

    function update_dropdown(data) {
        
        // Clear existing options
        $('#locationDropdown').empty();
        
        // Iterate over the data and add options to the dropdown
        data.forEach(element => {
            $('#locationDropdown').append($('<option>', {
                value: JSON.stringify(element),
                text: element[0]
            })); 
        });
    }

    function dropdown_select(selectElement) {
        var val = selectElement.value;
        val = JSON.parse(val);
        select_city(val[0], val[1], val[2]);
    }

    // ====> MAP CLICK

    document.addEventListener('DOMContentLoaded', function () {
        var map = L.map('map').setView([0, 0], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        map.on('click', function (e) {
            handleMapClick(e.latlng.lat, e.latlng.lng);
        });

        function handleMapClick(lat, lng) {
            // Use Axios to make a reverse geocoding request to Nominatim API
            axios.get(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
                .then(response => {
                    // Extract the name of the place from the response
                    var placeName = response.data.display_name;
                    // instead of placeName get the city at this point
                    // round off the lat and long
                    select_city(placeName, lat, lng);
                })
                .catch(error => {
                    console.error(error);
                });
        }

    });

</script>

</body>
</html>
