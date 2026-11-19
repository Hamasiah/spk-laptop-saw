
<?php
require_once 'koneksi.php';

$jumlahAlternatif = $koneksi->query("SELECT COUNT(*) FROM alternatif")->fetchColumn();
$jumlahKriteria = $koneksi->query("SELECT COUNT(*) FROM kriteria")->fetchColumn();
$jumlahPenilaian = $koneksi->query("SELECT COUNT(*) FROM penilaian")->fetchColumn();
$jumlahPengguna = $koneksi->query("SELECT COUNT(*) FROM pengguna")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SPK Laptop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <h2>SPK LAPTOP</h2>
        <p>Simple Additive Weighting</p>
    </div>

    <div class="menu">
        <a href="index.php" class="active">Dashboard</a>
        <a href="alternatif.php">Data Alternatif</a>
        <a href="kriteria.php">Data Kriteria</a>
        <a href="penilaian.php">Data Penilaian</a>
        <a href="perhitungan.php">Perhitungan SAW</a>
        <a href="hasil.php">Hasil Perangkingan</a>
        <a href="rekomendasi.php">Rekomendasi Laptop</a>
    </div>
</div>

<div class="main">

    <div class="topbar">
        <div>
            <h2>Dashboard</h2>
            <p>Selamat datang di Sistem Pendukung Keputusan Pemilihan Laptop.</p>
        </div>

        <div class="admin">
            <div class="admin-icon">A</div>
            <span>Administrator</span>
        </div>
    </div>

    <div class="welcome">
        <div>
            <span class="welcome-label">SISTEM PENDUKUNG KEPUTUSAN</span>
            <h1>Pemilihan Laptop Terbaik</h1>
            <p>
                Sistem ini menggunakan metode Simple Additive Weighting
                (SAW) untuk membantu menentukan laptop terbaik berdasarkan
                kriteria yang telah ditentukan.
            </p>
        </div>
        <div class="welcome-icon">💻</div>
    </div>

    <h3 class="section-title">Ringkasan Data</h3>

        <div class="cards">

        <a href="alternatif.php" class="card card-link">
            <div class="card-icon">💻</div>
            <div>
                <p>Data Alternatif</p>
                <h2><?= $jumlahAlternatif ?></h2>
                <small>Laptop tersedia</small>
            </div>
        </a>

        <a href="kriteria.php" class="card card-link">
            <div class="card-icon">📋</div>
            <div>
                <p>Data Kriteria</p>
                <h2><?= $jumlahKriteria ?></h2>
                <small>Kriteria penilaian</small>
            </div>
        </a>

        <a href="penilaian.php" class="card card-link">
            <div class="card-icon">📊</div>
            <div>
                <p>Data Penilaian</p>
                <h2><?= $jumlahPenilaian ?></h2>
                <small>Total nilai penilaian</small>
            </div>
        </a>

        <div class="card">
            <div class="card-icon">👤</div>
            <div>
                <p>Data Pengguna</p>
                <h2><?= $jumlahPengguna ?></h2>
                <small>Pengguna sistem</small>
            </div>
        </div>

    </div>

    <div class="content-box">
        <h3>Informasi Sistem</h3>
        <p>
            Sistem Pendukung Keputusan ini dirancang untuk membantu
            mahasiswa memilih laptop terbaik berdasarkan lima kriteria.
        </p>

        <div class="criteria-list">
            <div>Harga Laptop <span class="cost">Cost</span></div>
            <div>Kapasitas RAM <span class="benefit">Benefit</span></div>
            <div>Kapasitas SSD <span class="benefit">Benefit</span></div>
            <div>Performa Prosesor <span class="benefit">Benefit</span></div>
            <div>Kapasitas Baterai <span class="benefit">Benefit</span></div>
        </div>
    </div>

</div>

</body>
</html>
