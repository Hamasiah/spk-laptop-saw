<?php
session_start();

if (!isset($_SESSION['pengguna'])) {
    header("Location: login.php");
    exit;
}

require_once 'koneksi.php';

// Ambil data kriteria
$kriteria = $koneksi->query(
    "SELECT * FROM kriteria ORDER BY id ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// Ambil data alternatif
$alternatif = $koneksi->query(
    "SELECT * FROM alternatif ORDER BY id ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// Ambil data penilaian
$penilaian = $koneksi->query(
    "SELECT * FROM penilaian"
)->fetchAll(PDO::FETCH_ASSOC);

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

// Inisialisasi hasil
$hasil = [];

if ($dataLengkap) {

    // Cari nilai maksimum dan minimum
    $min = [];
    $max = [];

    foreach ($kriteria as $k) {

        $idK = $k['id'];
        $nilaiKriteria = [];

        foreach ($alternatif as $a) {
            $nilaiKriteria[] =
                $matriks[$a['id']][$idK];
        }

        $min[$idK] = min($nilaiKriteria);
        $max[$idK] = max($nilaiKriteria);
    }

    // Hitung normalisasi dan nilai preferensi
    foreach ($alternatif as $a) {

        $total = 0;
        $detail = [];

        foreach ($kriteria as $k) {

            $idK = $k['id'];
            $nilai = $matriks[$a['id']][$idK];

            // Normalisasi
            if ($k['jenis'] === 'cost') {

                $r = $nilai > 0
                    ? $min[$idK] / $nilai
                    : 0;

            } else {

                $r = $max[$idK] > 0
                    ? $nilai / $max[$idK]
                    : 0;
            }

            // Bobot asli dan bobot normalisasi
            $bobotAsli = (float)$k['bobot'];
            $bobotNormal = $bobotAsli / $totalBobot;

            // Normalisasi terbobot
            $nilaiTerbobot = $r * $bobotNormal;

            $detail[$idK] = [
                'nilai_awal' => $nilai,
                'normalisasi' => $r,
                'bobot_asli' => $bobotAsli,
                'bobot_normal' => $bobotNormal,
                'terbobot' => $nilaiTerbobot
            ];

            $total += $nilaiTerbobot;
        }

        $hasil[] = [
            'kode' => $a['kode'],
            'merek' => $a['merek'],
            'model' => $a['model'],
            'detail' => $detail,
            'total' => $total
        ];
    }

    // Urutkan nilai tertinggi ke terendah
    usort($hasil, function ($a, $b) {
        return $b['total'] <=> $a['total'];
    });
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Perangkingan SAW</title>
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
        <a href="perhitungan.php">Perhitungan SAW</a>
        <a href="hasil.php" class="active">Hasil Perangkingan</a>
        <a href="rekomendasi.php">Rekomendasi Laptop</a>
    </div>
</div>

<div class="main">

    <div class="topbar">
        <div>
            <h2>Hasil Perangkingan</h2>
            <p>Peringkat alternatif laptop berdasarkan nilai preferensi SAW.</p>
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
                    Pastikan seluruh alternatif, kriteria, bobot,
                    dan nilai penilaian sudah tersedia.
                </p>
            </div>
        </div>

    <?php else: ?>

        <!-- HASIL PERANGKINGAN -->
        <div class="content-box">

            <h3>Hasil Perhitungan Nilai Preferensi</h3>

            <p>
                Nilai preferensi diperoleh dari penjumlahan
                hasil perkalian normalisasi dengan bobot
                normalisasi masing-masing kriteria.
            </p>

            <div class="keterangan">
                <strong>Rumus:</strong><br>
                Vi = Σ (Rij × Wj)<br><br>
                Alternatif dengan nilai preferensi tertinggi
                menempati peringkat pertama.
            </div>

            <div style="overflow-x:auto;">

                <table>
                    <thead>
                        <tr>
                            <th>Peringkat</th>
                            <th>Kode</th>
                            <th>Nama Laptop</th>
                            <th>Nilai Preferensi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($hasil as $i => $h): ?>

                            <tr>
                                <td>
                                    <strong><?= $i + 1 ?></strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($h['kode']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $h['merek'] . ' ' . $h['model']
                                    ) ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= number_format(
                                            $h['total'],
                                            4, ',', '.'
                                        ) ?>
                                    </strong>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>

        </div>

        <!-- DETAIL PERHITUNGAN -->
        <div class="content-box">

            <h3>Detail Perhitungan Nilai Terbobot</h3>

            <p>
                Nilai terbobot diperoleh dari nilai normalisasi
                dikalikan dengan bobot normalisasi kriteria.
            </p>

            <div class="keterangan">
                Total bobot asli:
                <strong>
                    <?= number_format($totalBobot, 2, ',', '.') ?>
                </strong>
                <br>
                Bobot normalisasi = Bobot asli ÷ Total bobot.
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
                                    Bobot:
                                    <?= number_format(
                                        $k['bobot'], 2, ',', '.'
                                    ) ?>
                                </th>
                            <?php endforeach; ?>

                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($hasil as $h): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($h['kode']) ?>
                                </td>

                                <?php foreach ($kriteria as $k): ?>

                                    <td>
                                        <?= number_format(
                                            $h['detail'][$k['id']]['terbobot'],
                                            4, ',', '.'
                                        ) ?>
                                    </td>

                                <?php endforeach; ?>

                                <td>
                                    <strong>
                                        <?= number_format(
                                            $h['total'],
                                            4, ',', '.'
                                        ) ?>
                                    </strong>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>

        </div>

        <!-- RINCIAN BOBOT -->
        <div class="content-box">

            <h3>Rincian Bobot Kriteria</h3>

            <p>
                Bobot asli dan bobot setelah normalisasi
                yang digunakan dalam perhitungan SAW.
            </p>

            <div style="overflow-x:auto;">

                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Kriteria</th>
                            <th>Atribut</th>
                            <th>Bobot Asli</th>
                            <th>Bobot Normalisasi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($kriteria as $k): ?>

                            <tr>
                                <td><?= htmlspecialchars($k['kode']) ?></td>

                                <td><?= htmlspecialchars($k['nama']) ?></td>

                                <td>
                                    <?= htmlspecialchars($k['jenis']) ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        $k['bobot'], 2, ',', '.'
                                    ) ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        $k['bobot'] / $totalBobot,
                                        4, ',', '.'
                                    ) ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>

        </div>

    <?php endif; ?>

</div>

</body>
</html>
