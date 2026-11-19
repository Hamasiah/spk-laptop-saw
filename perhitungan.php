
<?php
require_once 'koneksi.php';

// Ambil kriteria
$stmt = $koneksi->query("SELECT * FROM kriteria ORDER BY id ASC");
$kriteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil alternatif
$stmt = $koneksi->query("SELECT * FROM alternatif ORDER BY id ASC");
$alternatif = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil seluruh penilaian
$stmt = $koneksi->query("SELECT * FROM penilaian");
$penilaian = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Susun matriks keputusan
$matriks = [];

foreach ($penilaian as $p) {
    $matriks[$p['id_alternatif']][$p['id_kriteria']] = (float)$p['nilai'];
}

// Cari nilai maksimum dan minimum setiap kriteria
$nilaiMaksimum = [];
$nilaiMinimum = [];

foreach ($kriteria as $k) {
    $idKriteria = $k['id'];
    $semuaNilai = [];

    foreach ($alternatif as $a) {
        $semuaNilai[] = $matriks[$a['id']][$idKriteria] ?? 0;
    }

    $nilaiMaksimum[$idKriteria] = max($semuaNilai);
    $nilaiMinimum[$idKriteria] = min($semuaNilai);
}

// Hitung normalisasi
$normalisasi = [];

foreach ($alternatif as $a) {
    foreach ($kriteria as $k) {
        $idAlternatif = $a['id'];
        $idKriteria = $k['id'];

        $nilai = $matriks[$idAlternatif][$idKriteria] ?? 0;

        if ($k['jenis'] == 'cost') {
            $hasil = $nilai > 0
                ? $nilaiMinimum[$idKriteria] / $nilai
                : 0;
        } else {
            $hasil = $nilaiMaksimum[$idKriteria] > 0
                ? $nilai / $nilaiMaksimum[$idKriteria]
                : 0;
        }

        $normalisasi[$idAlternatif][$idKriteria] = $hasil;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perhitungan SAW</title>
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
        <a href="kriteria.php">Data Kriteria</a>
        <a href="penilaian.php">Data Penilaian</a>
        <a href="perhitungan.php" class="active">Perhitungan SAW</a>
        <a href="hasil.php">Hasil Perangkingan</a>
        <a href="rekomendasi.php">Rekomendasi Laptop</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <h2>Perhitungan SAW</h2>
            <p>Matriks keputusan dan matriks normalisasi.</p>
        </div>

        <div class="admin">
            <div class="admin-icon">A</div>
            <span>Administrator</span>
        </div>
    </div>

    <div class="content-box">
        <h3>Matriks Keputusan (X)</h3>
        <p>Matriks keputusan berdasarkan nilai setiap alternatif dan kriteria.</p>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Alternatif</th>
                        <?php foreach ($kriteria as $k): ?>
                            <th><?= htmlspecialchars($k['kode']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alternatif as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['kode']) ?></td>
                        <?php foreach ($kriteria as $k): ?>
                            <td>
                                <?= number_format(
                                    $matriks[$a['id']][$k['id']] ?? 0,
                                    2, ',', '.'
                                ) ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="content-box">
        <h3>Matriks Normalisasi (R)</h3>
        <p>
            Kriteria cost menggunakan rumus nilai minimum dibagi nilai alternatif.
            Kriteria benefit menggunakan nilai alternatif dibagi nilai maksimum.
        </p>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Alternatif</th>
                        <?php foreach ($kriteria as $k): ?>
                            <th><?= htmlspecialchars($k['kode']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alternatif as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['kode']) ?></td>
                        <?php foreach ($kriteria as $k): ?>
                            <td>
                                <?= number_format(
                                    $normalisasi[$a['id']][$k['id']] ?? 0,
                                    4, ',', '.'
                                ) ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
