<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MonzaRadar - Peta Toko Thrift Medan</title>

  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet-control-search@2.9.8/dist/leaflet-search.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>

  <style>
    * {
      box-sizing: border-box;
    }

    html, body {
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
      background-color: #f9fafb;
    }

    /* Navbar */
    #navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background-color:rgb(90, 0, 117);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      font-weight: 600;
      z-index: 1200;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Sidebar */
    #sidebar {
      position: fixed;
      top: 60px;
      left: 0;
      width: 300px;
      height: calc(100vh - 60px);
      background-color: #f0f4f8;
      padding: 1.5rem;
      box-shadow: 4px 0 20px rgba(0, 0, 0, 0.06);
      transform: translateX(-100%);
      transition: transform 0.3s ease;
      z-index: 1000;
      overflow-y: auto;
      border-right: 1px solid #e5e7eb;
      border-top: 1px solid #e5e7eb;
      border-bottom: 1px solid #e5e7eb;
      border-radius: 0 10px 10px 0;
    }

    #sidebar.active {
      transform: translateX(0);
    }

    /* Toggle Button */
    #toggleSidebar {
      position: fixed;
      top: 70px;
      left: 0;
      background-color: #1d4ed8;
      color: white;
      border: none;
      width: 42px;
      height: 42px;
      font-size: 24px;
      text-align: center;
      border-radius: 0 8px 8px 0;
      cursor: pointer;
      z-index: 1100;
      box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
    }

    #toggleSidebar:hover {
      background-color: #1e40af;
    }

    /* Map Area */
    #map {
      height: calc(100vh - 60px);
      width: 100%;
      margin-top: 60px;
      z-index: 0;
    }

    /* Sidebar Content */
    h3 {
      margin-bottom: 1rem;
      font-size: 18px;
      font-weight: 600;
      color: #1f2937;
    }

    input,
    select,
    button {
      width: 100%;
      margin-bottom: 1rem;
      padding: 0.6rem 0.9rem;
      border: 1px solid #cbd5e1;
      border-radius: 10px;
      font-size: 14px;
      background-color: #fff;
      transition: 0.3s ease;
      box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }

    input:focus,
    select:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 2px rgba(59,130,246,0.3);
      outline: none;
    }

    button {
      background-color: #3b82f6;
      color: #fff;
      font-weight: 600;
      border: none;
      cursor: pointer;
      transition: background-color 0.2s ease;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    button:hover {
      background-color: #2563eb;
    }

    hr {
      border: none;
      border-top: 1px solid #e5e7eb;
      margin: 1rem 0;
    }

    ul#tokoList {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    ul#tokoList li {
      padding: 14px;
      background-color: #ffffff;
      border-radius: 10px;
      margin-bottom: 10px;
      font-size: 15px;
      font-weight: 500;
      color: #1f2937;
      border: 1px solid #e2e8f0;
      transition: background-color 0.2s ease, transform 0.2s ease;
      cursor: pointer;
    }

    ul#tokoList li:hover {
      background-color: #f1f5f9;
      transform: scale(1.02);
    }

    @media (max-width: 768px) {
      #sidebar {
        width: 100%;
        max-width: 320px;
      }

      #toggleSidebar {
        top: 65px;
      }
    }
  </style>
</head>
<body>
  <nav id="navbar">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; padding: 0 1rem;">
      <a href="index.php" style="color: white; text-decoration: none; font-size: 18px; font-weight: 600;">🏠 Home</a>
      <h1 style="margin: 0; font-size: 18px;">MonzaRadar - Peta Toko Thrift Medan</h1>
      <div style="width: 60px;"><!-- Spacer biar seimbang kiri-kanan --></div>
    </div>
  </nav>

  <button id="toggleSidebar">☰</button>

  <div id="sidebar" class="active">
    <h3> Toko Monza Di Medan</h3>
    <input type="text" id="searchToko" placeholder="🔎 Cari nama toko..." />
    
    <label for="filterKecamatan" style="font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px; display: block;">🗺️ Pilih Kecamatan</label>
    <select id="filterKecamatan">
      <option value="all">🌐 Semua Kecamatan</option>
    </select>

    <button id="findNearest">📌 Temukan Toko Terdekat</button>
    <hr />
    <h4 style="font-size: 16px; margin: 0 0 10px; color: #334155;">Daftar Toko Monza Di Medan</h4>
    <ul id="tokoList"></ul>
  </div>

  <div id="map"></div>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-control-search@2.9.8/dist/leaflet-search.min.js"></script>
  <script src="js/map.js"></script>

  <script>
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");

    toggleSidebar.addEventListener("click", () => {
      sidebar.classList.toggle("active");
    });
  </script>
</body>
</html>
