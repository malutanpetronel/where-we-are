function parseBool(value) {
    return value === true || value === "true" || value === 1 || value === "1";
}
document.addEventListener('DOMContentLoaded', function () {

    // Informații despre companie
    var company = mapData.company;
    var slogan = mapData.slogan;
    var address = mapData.address;
    // Coordonate pentru marker
    var lat = parseFloat(mapData.latitude);
    var lng = parseFloat(mapData.longitude);
    var zoom= parseInt(mapData.zoom);
    var paid= parseBool(mapData.paid);

    // Inițializează harta la 46.7568158,23.5585006,16z
    var map = L.map('mapDiv').setView([lat, lng], zoom); // Setează coordonatele inițiale și nivelul de zoom

    // Adaugă stratul OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    if (!paid) {
        map.attributionControl.addAttribution('<span class="aquis-attribution blink">map created with "Where we are" plugin.</span>');
    }

    var logoIcon = L.icon({
        iconUrl: pluginPetroData.pluginUrl + 'images/leaf.png',
        shadowUrl: pluginPetroData.pluginUrl + 'images/leaf-shadow.png',
        iconSize:     [44, 53], // size of the icon
        shadowSize:   [40, 10], // size of the shadow
        iconAnchor:   [22, 26], // point of the icon which will correspond to marker's location
        shadowAnchor: [20, -22],  // the same for the shadow
        popupAnchor:  [0, -3] // point from which the popup should open relative to the iconAnchor
    });

    // add marker to the map
    defaultMarker = L.marker([lat, lng], {icon: logoIcon}).addTo(map);
    // add popup to the marker
    defaultMarker.bindPopup(
        "<b>" + company + "</b>"
        + "<br />" + slogan
        + "<br />" + address
        + "<br />" + "<b>" + lat + "</b>" + ", " + "<b>" + lng + "</b>"
        + "<br />" + "<a href='https://www.google.com/search?q=directions+to+" + lat + "%2C+" + lng + "&oq=directions+to+" + lat + "%2C+" + lng + "&aqs=chrome..69i57.6063j0j7&sourceid=chrome&ie=UTF-8'>Arata traseul</a>"
        // + "<br /><button onclick='startAreaCalculation()'>Calculeaza Aria</button>"
    ).openPopup();


    // Grup pentru marker-ele adăugate
    var markers = [];
    var areaMode = false; // Variabilă pentru modul de calcul al ariei
    var polygonLayer = null; // Strat pentru poligonul hasurat

    // Funcție pentru a începe calculul ariei
    window.startAreaCalculation = function () {
        // Închide popup-ul și elimină marker-ul implicit de pe hartă
        map.removeLayer(defaultMarker);

        // Activează modul de adăugare a marker-elor pentru calculul ariei
        areaMode = true;
        alert("Click pe hartă pentru a adăuga puncte. Adaugă cel puțin 3 puncte pentru a calcula aria.");
    };

    // Funcție pentru a crea și actualiza poligonul
    function updatePolygon() {
        if (polygonLayer) {
            map.removeLayer(polygonLayer); // Elimină poligonul anterior, dacă există
        }

        if (markers.length >= 3) {
            var latLngs = markers.map(function(marker) {
                return marker.getLatLng();
            });

            // Crează un nou poligon cu latLng-urile markerelor
            polygonLayer = L.polygon(latLngs, {
                color: 'blue',         // Culoarea conturului
                fillColor: 'blue',     // Culoarea de umplere
                fillOpacity: 0.3,      // Opacitatea hașurii
                dashArray: '5, 10'     // Hașurare sub formă de linii punctate
            }).addTo(map);
        }
    }

    // Funcție pentru a adăuga marker cu popup și buton de calcul
    function addMarker(lat, lng) {
        var marker = L.marker([lat, lng], {icon: logoIcon}).addTo(map);
        markers.push(marker);

        // Actualizează poligonul la fiecare marker nou
        updatePolygon();

        // Adaugă un popup la marker cu un buton „Calculate”
        marker.bindPopup(
            "<button onclick='calculateArea()'>Calculeaza Aria</button>"
        ).openPopup();
    }

    // Adaugă marker la click pe hartă
    map.on('click', function (e) {
        if (areaMode) {
            addMarker(e.latlng.lat, e.latlng.lng);
        }
    });

    // Funcție pentru a calcula aria poligonului
    window.calculateArea = function () {
        if (markers.length < 3) {
            alert("Trebuie să aveți cel puțin 3 marker-e pentru a forma un poligon.");
            return;
        }

        // Colectăm coordonatele markerelor pentru a crea poligonul
        var latLngs = markers.map(function(marker) {
            return [marker.getLatLng().lng, marker.getLatLng().lat];
        });

        // Închidem poligonul, adăugând primul punct la sfârșit
        latLngs.push(latLngs[0]);

        // Creăm poligonul și calculăm aria cu Turf.js
        var polygon = turf.polygon([latLngs]);
        var area = turf.area(polygon);

        // Afișăm aria în metri pătrați
        alert("Aria poligonului este de " + area.toFixed(2) + " metri pătrați.");

        // Resetăm modul de calcul și eliminăm marker-ele existente
        markers.forEach(function(marker) {
            map.removeLayer(marker);
        });

        markers = [];
        if (polygonLayer) {
            map.removeLayer(polygonLayer);
            polygonLayer = null;
        }
        areaMode = false;

        map.addLayer(defaultMarker);
    };
});
