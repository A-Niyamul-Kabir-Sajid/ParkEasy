<!DOCTYPE html>
<html>
<head>

    <title>Create Parking Lot</title>

    <link
    href="https://unpkg.com/maplibre-gl@5.6.2/dist/maplibre-gl.css"
    rel="stylesheet">

    <script
    src="https://unpkg.com/maplibre-gl@5.6.2/dist/maplibre-gl.js">
    </script>
    <style>
    #map{
        height:600px;
        width:100%;
    }
    </style>

</head>
<body>

<h2>Create Parking Lot</h2>

@if(session('success'))
    <p>{{ session('success') }}</p>
@endif

<form method="POST" action="/owner/parking-lots">

    @csrf

    <input
        type="text"
        name="name"
        placeholder="Lot Name"
        required>

    <br><br>

    <textarea
        name="description"
        placeholder="Description"></textarea>

    <br><br>

    <input
        type="number"
        name="hourly_rate"
        placeholder="Hourly Rate"
        required>

    <br><br>

    <input
        type="number"
        name="total_capacity"
        placeholder="Capacity"
        required>

    <br><br>

    <h2>Select Parking Lot Location</h2>

    <div
        id="map"
        style="height:500px;width:100%;">
    </div>

    <br>

    <input
        type="text"
        id="latitude"
        name="latitude"
        readonly>

    <input
        type="text"
        id="longitude"
        name="longitude"
        readonly>

    <br><br>
    <input
    type="text"
    id="searchBox"
    placeholder="Search Location">

<button
    type="button"
    onclick="searchLocation()">

    Search

</button>
<br><br>

    <button type="submit">
        Create Parking Lot
    </button>
    <!-- <input type="text" id="searchBox" placeholder="Search Location"> -->

</form>

<script>

const map = new maplibregl.Map({

    container: 'map',

    style: {
        version: 8,
        sources: {
            osm: {
                type: "raster",
                tiles: [
                    "https://tile.openstreetmap.org/{z}/{x}/{y}.png"
                ],
                tileSize: 256,
                attribution: "© OpenStreetMap Contributors"
            }
        },
        layers: [
            {
                id: "osm",
                type: "raster",
                source: "osm"
            }
        ]
    },

    center: [89.5403,22.8456],
    zoom: 14

});

map.addControl(new maplibregl.NavigationControl());

let marker;

map.on('click', (e) => {

    const lng = e.lngLat.lng;
    const lat = e.lngLat.lat;

    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;

    if(marker){
        marker.remove();
    }

    marker = new maplibregl.Marker()
        .setLngLat([lng,lat])
        .addTo(map);

});

async function searchLocation() {

    const query =
        document.getElementById('searchBox').value;

    const response = await fetch(
        `https://nominatim.openstreetmap.org/search?format=json&q=${query}`
    );

    const data = await response.json();

    if(data.length > 0){

        const lat = parseFloat(data[0].lat);
        const lon = parseFloat(data[0].lon);

        map.flyTo({
            center: [lon, lat],
            zoom: 16
        });

    }
}


</script>

</body>
</html>