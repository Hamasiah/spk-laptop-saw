
<?php
require_once 'koneksi.php';

// Ambil data dari database
$kriteria = $koneksi->query(
    "SELECT * FROM kriteria ORDER BY id ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$alternatif = $koneksi->query(
    "SELECT * FROM alternatif ORDER BY id ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$penilaian = $koneksi->query(
    "SELECT * FROM penilaian"
)->fetchAll(PDO::FETCH_ASSOC);

// Susun matriks keputusan
$matriks = [];

foreach ($penilaian as $p) {
    $matriks[$p['id_alternatif']][$p['id_kriteria']] =
        (float) $p['nilai'];
}

// Cari nilai maksimum dan minimum
$min = [];
$max = [];

foreach ($kriteria as $k) {
    $idK = $k['id'];
    $nilaiKriteria = [];

    foreach ($alternatif as $a) {
        $nilaiKriteria[] =
            $matriks[$a['id']][$idK] ?? 0;
    }

    $nilaiValid = array_filter(
        $nilaiKriteria,
        fn($nilai) => $nilai > 0
    );

    $min[$idK] = !empty($nilaiValid)
        ? min($nilaiValid) : 0;

    $max[$idK] = !empty($nilaiValid)
        ? max($nilaiValid) : 0;
}

// Hitung normalisasi dan nilai preferensi
$hasil = [];

foreach ($alternatif as $a) {
    $total = 0;
    $detail = [];

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

        $bobot = (float) $k['bobot'];
        $nilaiTerbobot = $r * $bobot;

        $detail[$idK] = [
            'normalisasi' => $r,
            'bobot' => $bobot,
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Perangkingan SAW</title>
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
            <span>Administrator</span>
        </div>
    </div>

    <div class="content-box">
        <h3>Hasil Perhitungan Nilai Preferensi</h3>
        <p>
            Nilai preferensi diperoleh dari penjumlahan
            hasil perkalian nilai normalisasi dengan bobot
            masing-masing kriteria.
        </p>

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
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($h['kode']) ?></td>
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

    <div class="content-box">
        <h3>Detail Perhitungan Nilai Terbobot</h3>
        <p>
            Nilai terbobot diperoleh dari nilai normalisasi
            dikalikan dengan bobot kriteria.
        </p>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Alternatif</th>
                        <?php foreach ($kriteria as $k): ?>
                            <th>
                                <?= htmlspecialchars($k['kode']) ?>
                                <br>
                                (<?= number_format(
                                    $k['bobot'], 2
                                ) ?>)
                            </th>
                        <?php endforeach; ?>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hasil as $h): ?>
                    <tr>
                        <td><?= htmlspecialchars($h['kode']) ?></td>

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

</div>
</body>
</html>
