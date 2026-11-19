<?php
session_start();

if (!isset($_SESSION['pengguna'])) {
    header("Location: login.php");
    exit;
}

require_once 'koneksi.php';

// Ambil kriteria
$stmt = $koneksi->query("SELECT * FROM kriteria ORDER BY id ASC");
$kriteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil alternatif
$stmt = $koneksi->query("SELECT * FROM alternatif ORDER BY id ASC");
$alternatif = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil penilaian
$stmt = $koneksi->query("SELECT * FROM penilaian");
$penilaian = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Susun matriks keputusan
$matriks = [];

foreach ($penilaian as $p) {
    $matriks[$p['id_alternatif']][$p['id_kriteria']] =
        (float)$p['nilai'];
}

// Hitung total bobot
$totalBobot = 0;

foreach ($kriteria as $k) {
    $totalBobot += (float)$k['bobot'];
}

// Periksa kelengkapan data
$dataLengkap = count($alternatif) > 0
    && count($kriteria) > 0
    && $totalBobot > 0;

foreach ($alternatif as $a) {
    foreach ($kriteria as $k) {
        if (
            !isset($matriks[$a['id']][$k['id']]) ||
            !is_finite($matriks[$a['id']][$k['id']])
        ) {
            $dataLengkap = false;
        }
    }
}

// Inisialisasi perhitungan
$nilaiMaksimum = [];
$nilaiMinimum = [];
$normalisasi = [];
$normalisasiTerbobot = [];
$preferensi = [];

if ($dataLengkap) {

    // Cari nilai maksimum dan minimum
    foreach ($kriteria as $k) {

        $idKriteria = $k['id'];
        $semuaNilai = [];

        foreach ($alternatif as $a) {
            $semuaNilai[] =
                $matriks[$a['id']][$idKriteria];
        }

        $nilaiMaksimum[$idKriteria] = max($semuaNilai);
        $nilaiMinimum[$idKriteria] = min($semuaNilai);
    }

    // Normalisasi matriks
    foreach ($alternatif as $a) {

        foreach ($kriteria as $k) {

            $idAlternatif = $a['id'];
            $idKriteria = $k['id'];

            $nilai = $matriks[$idAlternatif][$idKriteria];

            if ($k['jenis'] === 'cost') {

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

    // Hitung matriks normalisasi terbobot
    foreach ($alternatif as $a) {

        $idAlternatif = $a['id'];
        $totalPreferensi = 0;

        foreach ($kriteria as $k) {

            $idKriteria = $k['id'];

            // Bobot dinormalisasi berdasarkan total bobot
            $bobotNormal = (float)$k['bobot'] / $totalBobot;

            $nilaiTerbobot =
                $normalisasi[$idAlternatif][$idKriteria] * $bobotNormal;

            $normalisasiTerbobot[$idAlternatif][$idKriteria] =
                $nilaiTerbobot;

            $totalPreferensi += $nilaiTerbobot;
        }

        // Nilai akhir preferensi
        $preferensi[$idAlternatif] = $totalPreferensi;
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

    <style>
        .keterangan {
            padding: 13px 15px;
            background: #f8f0f2;
            border-left: 4px solid #800020;
            border-radius: 5px;
            margin-bottom: 18px;
            line-height: 1.7;
        }

        .pesan-error {
            padding: 14px;
            background: #fff0f0;
            border-left: 4px solid #dc3545;
            border-radius: 5px;
            margin-bottom: 18px;
        }

        .content-box table {
            width: 100%;
        }

        .content-box td,
        .content-box th {
            white-space: nowrap;
        }
    </style>
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
            <p>Matriks keputusan dan proses perhitungan metode SAW.</p>
        </div>

        <div class="admin">
            <div class="admin-icon">A</div>
            <span>
                <?= htmlspecialchars(
                    $_SESSION['pengguna']['nama'] ?? 'Administrator'
                ) ?>
            </span>
        </div>
    </div>

    <?php if (!$dataLengkap): ?>

        <div class="content-box">
            <div class="pesan-error">
                <strong>Data belum lengkap!</strong>
                <p>
                    Pastikan alternatif, kriteria, bobot, dan seluruh nilai
                    penilaian sudah tersedia. Total bobot juga harus lebih
                    besar dari nol.
                </p>
                <p>
                    Silakan periksa kembali halaman Data Alternatif,
                    Data Kriteria, dan Data Penilaian.
                </p>
            </div>
        </div>

    <?php else: ?>

        <!-- MATRIKS KEPUTUSAN -->
        <div class="content-box">

            <h3>1. Matriks Keputusan (X)</h3>

            <p>
                Matriks keputusan berisi nilai setiap alternatif
                berdasarkan masing-masing kriteria.
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
                                            $matriks[$a['id']][$k['id']],
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

        <!-- NORMALISASI -->
        <div class="content-box">

            <h3>2. Matriks Normalisasi (R)</h3>

            <div class="keterangan">
                <strong>Rumus normalisasi:</strong><br>
                Cost: nilai minimum dibagi nilai alternatif.<br>
                Benefit: nilai alternatif dibagi nilai maksimum.
            </div>

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
                                            $normalisasi[$a['id']][$k['id']],
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

        <!-- NORMALISASI TERBOBOT -->
        <div class="content-box">

            <h3>3. Matriks Normalisasi Terbobot</h3>

            <div class="keterangan">
                Setiap nilai normalisasi dikalikan dengan bobot kriteria
                yang telah dinormalisasi.

                <br><br>

                <strong>Rumus:</strong>
                Yij = Rij × Wj
            </div>

            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Alternatif</th>

                            <?php foreach ($kriteria as $k): ?>
                                <th>
                                    <?= htmlspecialchars($k['kode']) ?>
                                    <br>
                                    (<?= htmlspecialchars($k['bobot']) ?>)
                                </th>
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
                                            $normalisasiTerbobot[$a['id']][$k['id']],
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

        <!-- NILAI PREFERENSI -->
        <div class="content-box">

            <h3>4. Nilai Preferensi (V)</h3>

            <div class="keterangan">
                Nilai preferensi diperoleh dari penjumlahan seluruh
                nilai normalisasi terbobot pada setiap alternatif.

                <br><br>

                <strong>Rumus:</strong>
                Vi = Σ (Wj × Rij)
            </div>

            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Alternatif</th>
                            <th>Nama Laptop</th>
                            <th>Nilai Preferensi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($alternatif as $a): ?>

                            <tr>
                                <td><?= htmlspecialchars($a['kode']) ?></td>

                                <td>
                                    <?= htmlspecialchars(
                                        $a['merek'] . ' ' . $a['model']
                                    ) ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= number_format(
                                            $preferensi[$a['id']],
                                            4, ',', '.'
                                        ) ?>
                                    </strong>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>

            <p>
                Nilai preferensi yang lebih tinggi menunjukkan
                alternatif yang lebih direkomendasikan berdasarkan
                kriteria dan bobot yang digunakan.
            </p>

        </div>

    <?php endif; ?>

</div>

</body>
</html>
