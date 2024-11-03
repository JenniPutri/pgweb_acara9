<?php
// Koneksi ke database
$host = 'localhost';
$dbname = 'pgweb_acara8';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Proses delete jika ada parameter hapus
    if (isset($_GET['hapus'])) {
        $id = $_GET['hapus'];
        $stmt = $pdo->prepare("DELETE FROM acara8 WHERE kecamatan = :kecamatan");
        $stmt->execute(['kecamatan' => $id]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Mengambil data dari tabel acara8
    $query = $pdo->query("SELECT kecamatan, longitude, latitude, luas, jumlah_penduduk FROM acara8");
    $data = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Kabupaten Sleman</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            padding: 0;
            background-color: #faacdc; /* Warna latar belakang */
        }
        h1, h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 10px; 
        }
        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: #db1273;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: flex-end;
            padding: 10px;
        }
        .navbar a {
            color: white;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
            transition: background-color 0.3s ease;
            margin-left: 10px;
        }
        .navbar a:hover {
            background-color: #db1273;
        }
        .container {
            display: flex;
            flex-direction: column; 
            width: 90%;
            max-width: 1200px;
            margin-top: 60px; 
            gap: 20px; 
        }
        #table-container {
            overflow-y: auto;
            background-color: white;
            border-radius: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #f2a2d5;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #db1273;
            color: white;
            padding: 14px;
        }
        .action-btn {
            padding: 5px 10px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 0.9em;
        }
        .delete-btn {
            background-color: #de4747;
            color: white;
        }
        .edit-btn {
            background-color: #32cf69;
            color: white;
        }
        #map {
            width: 100%;
            height: 500px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        #about-container {
            display: none;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <a href="#" onclick="showSection('home')">Beranda</a>
        <a href="#" onclick="showSection('data')">Data Kecamatan</a>
        <a href="#" onclick="showSection('map')">Peta</a>
        <a href="#" onclick="showSection('about')">Tentang</a>
    </div>

    <h1>Sistem Informasi Geografis</h1>
    <h1>Kabupaten Sleman</h1>

    <div class="container">
        <div id="table-container">
            <table>
                <tr>
                    <th>Kecamatan</th>
                    <th>Longitude</th>
                    <th>Latitude</th>
                    <th>Luas (km²)</th>
                    <th>Jumlah Penduduk</th>
                    <th>Aksi</th>
                </tr>
                <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['kecamatan']) ?></td>
                    <td><?= htmlspecialchars($row['longitude']) ?></td>
                    <td><?= htmlspecialchars($row['latitude']) ?></td>
                    <td><?= htmlspecialchars($row['luas']) ?></td>
                    <td><?= htmlspecialchars($row['jumlah_penduduk']) ?></td>
                    <td>
                        <a href="edit.php?kecamatan=<?= urlencode($row['kecamatan']) ?>" class="action-btn edit-btn">Edit</a>
                        <a href="?hapus=<?= urlencode($row['kecamatan']) ?>" class="action-btn delete-btn" onclick="return confirm('Yakin dik mau menghapus data kecamatan ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div id="map"></div>
    </div>

    <div id="about-container">
        <h2>Holla Inilah Sang Pembuat</h2>
        <p>Nama: Jenni Putri Ardani</p> 
        <p>NIM: 23/515612/SV/22586</p> 
        <p><a href="https://github.com/JenniPutri" target="_blank" style="color: #a60f6e;">GitHub: clikdisini</a></p> 
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        // Inisialisasi peta
        var map = L.map("map").setView([-7.77, 110.30], 12);

        // Tile Layer
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);

        // Data Marker dari PHP
        <?php foreach ($data as $row): ?>
            L.marker([<?= htmlspecialchars($row['latitude']) ?>, <?= htmlspecialchars($row['longitude']) ?>])
                .bindPopup("<b>Kecamatan: <?= htmlspecialchars($row['kecamatan']) ?></b><br>Luas: <?= htmlspecialchars($row['luas']) ?> km²<br>Jumlah Penduduk: <?= htmlspecialchars($row['jumlah_penduduk']) ?>")
                .addTo(map);
        <?php endforeach; ?>

        // Fungsi untuk menampilkan section yang berbeda
        function showSection(section) {
            const tableContainer = document.getElementById('table-container');
            const mapContainer = document.getElementById('map');
            const aboutContainer = document.getElementById('about-container');


            tableContainer.style.display = 'none';
            mapContainer.style.display = 'none';
            aboutContainer.style.display = 'none';

            switch(section) {
                case 'home':
                    tableContainer.style.display = 'block';
                    mapContainer.style.display = 'block';
                    break;
                case 'data':
                    tableContainer.style.display = 'block';
                    break;
                case 'map':
                    mapContainer.style.display = 'block';
                    break;
                case 'about':
                    aboutContainer.style.display = 'block';
                    break;
            }
        }
    </script>
</body>
</html>
