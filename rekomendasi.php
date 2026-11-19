
<?php
require_once 'koneksi.php';

$kriteria = $koneksi->query(
    "SELECT * FROM kriteria ORDER BY id ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$alternatif = $koneksi->query(
    "SELECT * FROM alternatif ORDER BY id ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$penilaian = $koneksi->query(
    "SELECT * FROM penilaian"
)->fetchAll(PDO::FETCH_ASSOC);

// Membuat matriks keputusan
$matriks = [];

foreach ($penilaian as $p) {
    $matriks[$p['id_alternatif']][$p['id_kriteria']] =
        (float) $p['nilai'];
}

// Mencari nilai minimum dan maksimum
$min = [];
$max = [];

foreach ($kriteria as $k) {
    $idK = $k['id'];
    $nilai = [];

    foreach ($alternatif as $a) {
        $v = $matriks[$a['id']][$idK] ?? 0;

        if ($v > 0) {
            $nilai[] = $v;
        }
    }

    $min[$idK] = count($nilai) ? min($nilai) : 0;
    $max[$idK] = count($nilai) ? max($nilai) : 0;
}

// Menghitung nilai preferensi
$hasil = [];

foreach ($alternatif as $a) {
    $total = 0;

    foreach ($kriteria as $k) {
        $idK = $k['id'];
        $nilai = $matriks[$a['id']][$idK] ?? 0;

        if ($nilai <= 0) {
            $r = 0;
        } elseif ($k['jenis'] == 'cost') {
            $r = $min[$idK] / $nilai;
        } else {
            $r = $nilai / $max[$idK];
        }

        $total += $r * (float) $k['bobot'];
    }

    $hasil[] = [
        'alternatif' => $a,
        'total' => $total
    ];
}

// Mengurutkan hasil
usort($hasil, fn($a, $b) =>
    $b['total'] <=> $a['total']
);

$terbaik = $hasil[0] ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasi Laptop</title>
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
        <a href="perhitungan.php">Perhitungan SAW</a>
        <a href="hasil.php">Hasil Perangkingan</a>
        <a href="rekomendasi.php" class="active">Rekomendasi Laptop</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <h2>Rekomendasi Laptop</h2>
            <p>Laptop terbaik berdasarkan perhitungan metode SAW.</p>
        </div>

        <div class="admin">
            <div class="admin-icon">A</div>
            <span>Administrator</span>
        </div>
    </div>

    <?php if ($terbaik): ?>
        <?php $a = $terbaik['alternatif']; ?>

        <div class="content-box">
            <h3>Rekomendasi Laptop Terbaik</h3>
            <p>
                Berdasarkan perhitungan metode
                Simple Additive Weighting (SAW),
                berikut laptop dengan nilai tertinggi.
            </p>

            <div style="background:var(--ivory); padding:25px;
                        border-left:5px solid var(--burgundy);
                        border-radius:10px; margin-top:20px;">

                <span style="color:var(--muted);">
                    PERINGKAT PERTAMA
                </span>

                <h1 style="color:var(--burgundy);">
                    <?= htmlspecialchars($a['merek'] . ' ' . $a['model']) ?>
                </h1>

                <h2 style="color:var(--mahogany);">
                    Nilai SAW:
                    <?= number_format($terbaik['total'], 4, ',', '.') ?>
                </h2>

                <p>
                    Laptop ini memperoleh nilai preferensi
                    tertinggi dibandingkan alternatif lainnya.
                </p>
            </div>
        </div>

        <div class="content-box">
            <h3>Spesifikasi Laptop Rekomendasi</h3>

            <table>
                <tr><th>Spesifikasi</th><th>Nilai</th></tr>
                <tr>
                    <td>Nama Laptop</td>
                    <td><?= htmlspecialchars($a['merek'] . ' ' . $a['model']) ?></td>
                </tr>
                <tr>
                    <td>Prosesor</td>
                    <td><?= htmlspecialchars($a['prosesor']) ?></td>
                </tr>
                <tr>
                    <td>RAM</td>
                    <td><?= htmlspecialchars($a['kapasitas_ram']) ?> GB</td>
                </tr>
                <tr>
                    <td>SSD</td>
                    <td><?= htmlspecialchars($a['kapasitas_ssd']) ?> GB</td>
                </tr>
                <tr>
                    <td>Harga</td>
                    <td>Rp <?= number_format($a['harga'], 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td>Baterai</td>
                    <td><?= htmlspecialchars($a['kapasitas_baterai']) ?> Wh</td>
                </tr>
                <tr>
                    <td>Skor Prosesor</td>
                    <td><?= number_format($a['skor_prosesor'], 0, ',', '.') ?></td>
                </tr>
            </table>
        </div>

        <div class="content-box">
            <h3>Kesimpulan</h3>
            <p>
                Berdasarkan hasil perhitungan metode SAW,
                laptop <strong>
                <?= htmlspecialchars($a['merek'] . ' ' . $a['model']) ?>
                </strong> memperoleh nilai preferensi
                <?= number_format($terbaik['total'], 4, ',', '.') ?>
                dan menjadi alternatif dengan peringkat pertama.
            </p>
        </div>

    <?php else: ?>
        <div class="content-box">
            <h3>Data belum tersedia</h3>
            <p>Pastikan data alternatif dan penilaian sudah lengkap.</p>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
