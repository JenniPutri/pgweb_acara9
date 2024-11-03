<?php
// Koneksi ke database
$host = 'localhost';
$dbname = 'pgweb_acara8';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Proses update jika form disubmit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $kecamatan = $_POST['kecamatan'];
        $longitude = $_POST['longitude'];
        $latitude = $_POST['latitude'];
        $luas = $_POST['luas'];
        $jumlah_penduduk = $_POST['jumlah_penduduk'];

        $stmt = $pdo->prepare("UPDATE acara8 SET longitude = :longitude, latitude = :latitude, luas = :luas, jumlah_penduduk = :jumlah_penduduk WHERE kecamatan = :kecamatan");
        $stmt->execute([
            'longitude' => $longitude,
            'latitude' => $latitude,
            'luas' => $luas,
            'jumlah_penduduk' => $jumlah_penduduk,
            'kecamatan' => $kecamatan
        ]);

        // Redirect setelah update
        header("Location: index.php"); // Ganti dengan nama file utama Anda
        exit();
    }

    // Ambil data untuk kecamatan yang ingin diedit
    if (isset($_GET['kecamatan'])) {
        $kecamatan = $_GET['kecamatan'];
        $stmt = $pdo->prepare("SELECT * FROM acara8 WHERE kecamatan = :kecamatan");
        $stmt->execute(['kecamatan' => $kecamatan]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Kecamatan</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #faacdc; 
            color: #db1273; 
        }
        h1 {
            text-align: center;
            color: #db1273; 
            margin-top: 20px;
        }
        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #def3ff; 
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        input[type="text"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #db1273; 
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #db1273; 
        }
        a {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #db1273; 
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Edit Data Kecamatan</h1>
    <form method="POST">
        <input type="hidden" name="kecamatan" value="<?= htmlspecialchars($data['kecamatan']) ?>">
        <label>Longitude:</label>
        <input type="text" name="longitude" value="<?= htmlspecialchars($data['longitude']) ?>" required>
        <label>Latitude:</label>
        <input type="text" name="latitude" value="<?= htmlspecialchars($data['latitude']) ?>" required>
        <label>Luas (km²):</label>
        <input type="text" name="luas" value="<?= htmlspecialchars($data['luas']) ?>" required>
        <label>Jumlah Penduduk:</label>
        <input type="text" name="jumlah_penduduk" value="<?= htmlspecialchars($data['jumlah_penduduk']) ?>" required>
        <input type="submit" value="Update">
    </form>
    <a href="index.php">Kembali</a> 
</body>
</html>
