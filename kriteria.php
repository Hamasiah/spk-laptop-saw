
<?php
require_once 'koneksi.php';

$stmt = $koneksi->query("SELECT * FROM kriteria ORDER BY id ASC");
$dataKriteria = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kriteria</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <h2>SPK LAPTOP</h2>
        <p>Simple Additive Weighting</p>
    </div>

    <div class="menu">
        <a href="index.php">Dashboard</a>
        <a href="alternatif.php">Data Alternatif</a>
        <a href="kriteria.php" class="active">Data Kriteria</a>
        <a href="penilaian.php">Data Penilaian</a>
        <a href="perhitungan.php">Perhitungan SAW</a>
        <a href="hasil.php">Hasil Perangkingan</a>
        <a href="rekomendasi.php">Rekomendasi Laptop</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <h2>Data Kriteria</h2>
            <p>Kriteria yang digunakan dalam pemilihan laptop terbaik.</p>
        </div>

        <div class="admin">
            <div class="admin-icon">A</div>
            <span>Administrator</span>
        </div>
    </div>

    <div class="content-box">
        <h3>Daftar Kriteria Penilaian</h3>
        <p>Kriteria yang digunakan dalam pemilihan laptop terbaik.</p>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Kriteria</th>
                    <th>Bobot</th>
                    <th>Atribut</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($dataKriteria) > 0): ?>
                    <?php foreach ($dataKriteria as $i => $kriteria): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($kriteria['kode']) ?></td>
                        <td><?= htmlspecialchars($kriteria['nama']) ?></td>
                        <td><?= htmlspecialchars($kriteria['bobot']) ?></td>
                        <td><?= htmlspecialchars($kriteria['jenis']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Belum ada data kriteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
