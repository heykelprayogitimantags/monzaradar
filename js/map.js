const map = L.map("map").setView([3.5952, 98.6722], 13);

// Tile layer
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution: "&copy; OpenStreetMap contributors",
}).addTo(map);

// Custom icon for toko
const shopIcon = L.icon({
  iconUrl: "assets/location.png",
  iconSize: [30, 30],
  iconAnchor: [15, 30],
  popupAnchor: [0, -30],
});

let geoLayer;
let originalFeatures = [];
let markerMap = new Map();

// Update list toko
function updateTokoList(filteredData) {
  const list = document.getElementById("tokoList");
  list.innerHTML = "";

  if (filteredData.length === 0) {
    list.innerHTML = "<li>Toko tidak ditemukan.</li>";
    return;
  }

  filteredData.forEach((feature) => {
    const item = document.createElement("li");
    item.textContent = feature.properties.nama_toko;
    item.onclick = () => {
      const marker = markerMap.get(feature.properties.nama_toko);
      if (marker) {
        map.setView(marker.getLatLng(), 17);
        marker.openPopup();
      }
    };
    list.appendChild(item);
  });
}

// Ambil data GeoJSON
fetch("data/monza.geojson")
  .then((res) => res.json())
  .then((data) => {
    originalFeatures = data.features;
    const kecamatanSet = new Set();

    geoLayer = L.geoJSON(data, {
      pointToLayer: (feature, latlng) => {
        const marker = L.marker(latlng, { icon: shopIcon });
        markerMap.set(feature.properties.nama_toko, marker);
        return marker;
      },
      onEachFeature: (feature, layer) => {
        const props = feature.properties;
        const html = `
          <strong>${props.nama_toko}</strong><br>
          ${props.alamat}<br>
          <em>${props.kecamatan}, ${props.kelurahan}</em><br>
          ⏰ ${props.jam_buka} - ${props.jam_tutup}<br>
          ⭐ Rating: ${props.rating}<br>
          <p>${props.keterangan}</p>
          <a href="https://www.google.com/maps?q=${layer.getLatLng().lat},${
          layer.getLatLng().lng
        }" target="_blank">📍 Arahkan via Google Maps</a>
        `;
        layer.bindPopup(html);
        kecamatanSet.add(props.kecamatan);
      },
    }).addTo(map);

    // Tambahkan kecamatan ke dropdown
    const filterSelect = document.getElementById("filterKecamatan");
    [...kecamatanSet].sort().forEach((kec) => {
      const opt = document.createElement("option");
      opt.value = kec;
      opt.textContent = kec;
      filterSelect.appendChild(opt);
    });

    // Tampilkan list awal
    updateTokoList(originalFeatures);

    // Event filter kecamatan
    filterSelect.addEventListener("change", () => {
      const selected = filterSelect.value;
      const filtered =
        selected === "all"
          ? originalFeatures
          : originalFeatures.filter((f) => f.properties.kecamatan === selected);
      geoLayer.clearLayers();
      geoLayer.addData(filtered);
      updateTokoList(filtered);
    });

    // Event search
    document.getElementById("searchToko").addEventListener("input", (e) => {
      const keyword = e.target.value.toLowerCase();
      const filtered = originalFeatures.filter((f) =>
        f.properties.nama_toko.toLowerCase().includes(keyword)
      );
      geoLayer.clearLayers();
      geoLayer.addData(filtered);
      updateTokoList(filtered);
    });
  });

// Fungsi hitung jarak (Haversine)
function getDistance(lat1, lon1, lat2, lon2) {
  const R = 6371;
  const dLat = ((lat2 - lat1) * Math.PI) / 180;
  const dLon = ((lon2 - lon1) * Math.PI) / 180;
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos((lat1 * Math.PI) / 180) *
      Math.cos((lat2 * Math.PI) / 180) *
      Math.sin(dLon / 2) *
      Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

// Temukan toko terdekat
document.getElementById("findNearest").addEventListener("click", () => {
  if (!navigator.geolocation) {
    alert("Browser Anda tidak mendukung geolokasi.");
    return;
  }

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const userLat = pos.coords.latitude;
      const userLng = pos.coords.longitude;

      // Tambahkan marker user
      const userMarker = L.marker([userLat, userLng], {
        icon: L.icon({
          iconUrl: "https://cdn-icons-png.flaticon.com/512/64/64113.png",
          iconSize: [25, 25],
          iconAnchor: [12, 25],
        }),
      })
        .addTo(map)
        .bindPopup("📍 Lokasi Anda")
        .openPopup();

      // Cari toko terdekat
      let nearest = null;
      let minDistance = Infinity;

      originalFeatures.forEach((feature) => {
        const coords = feature.geometry.coordinates;
        const dist = getDistance(userLat, userLng, coords[1], coords[0]);
        if (dist < minDistance) {
          minDistance = dist;
          nearest = { feature, dist };
        }
      });

      if (nearest) {
        const marker = markerMap.get(nearest.feature.properties.nama_toko);
        map.setView(marker.getLatLng(), 17);
        marker.openPopup();

        const gmapsUrl = `https://www.google.com/maps/dir/${userLat},${userLng}/${
          marker.getLatLng().lat
        },${marker.getLatLng().lng}`;
        window.open(gmapsUrl, "_blank");
      }
    },
    (err) => {
      alert("Gagal mengambil lokasi Anda: " + err.message);
    }
  );
});
