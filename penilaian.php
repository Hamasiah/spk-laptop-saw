
<?php
require_once 'koneksi.php';

// Ambil data alternatif
$stmt = $koneksi->query("SELECT * FROM alternatif ORDER BY id ASC");
$alternatif = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data kriteria
$stmt = $koneksi->query("SELECT * FROM kriteria ORDER BY id ASC");
$kriteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pemetaan nilai alternatif berdasarkan kode kriteria
function ambilNilai($laptop, $kode) {
    switch ($kode) {
        case 'C1':
            return $laptop['harga'];
        case 'C2':
            return $laptop['kapasitas_ram'];
        case 'C3':
            return $laptop['kapasitas_ssd'];
        case 'C4':
            return $laptop['skor_prosesor'];
        case 'C5':
            return $laptop['kapasitas_baterai'];
        default:
            return 0;
    }
}

// Simpan seluruh penilaian
if (isset($_POST['simpan'])) {
    try {
        $koneksi->beginTransaction();

        // Hapus penilaian lama agar tidak terjadi duplikasi
        $koneksi->exec("DELETE FROM penilaian");

        $stmt = $koneksi->prepare(
            "INSERT INTO penilaian (id_alternatif, id_kriteria, nilai)
             VALUES (:id_alternatif, :id_kriteria, :nilai)"
        );

        foreach ($alternatif as $laptop) {
            foreach ($kriteria as $k) {
                $nilai = $_POST['nilai'][$laptop['id']][$k['id']] ?? 0;

                $stmt->execute([
                    ':id_alternatif' => $laptop['id'],
                    ':id_kriteria' => $k['id'],
                    ':nilai' => $nilai
                ]);
            }
        }

        $koneksi->commit();
        $pesan = "Data penilaian berhasil disimpan!";
    } catch (Exception $e) {
        $koneksi->rollBack();
        $pesan = "Gagal menyimpan penilaian: " . $e->getMessage();
    }
}

// Ambil penilaian yang sudah tersimpan
$stmt = $koneksi->query("SELECT * FROM penilaian");
$dataTersimpan = [];

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $dataTersimpan[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penilaian</title>
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
        <a href="penilaian.php" class="active">Data Penilaian</a>
        <a href="perhitungan.php">Perhitungan SAW</a>
        <a href="hasil.php">Hasil Perangkingan</a>
        <a href="rekomendasi.php">Rekomendasi Laptop</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <h2>Data Penilaian</h2>
            <p>Nilai setiap alternatif laptop pada masing-masing kriteria.</p>
        </div>

        <div class="admin">
            <div class="admin-icon">A</div>
            <span>Administrator</span>
        </div>
    </div>

    <div class="content-box">
        <h3>Matriks Penilaian Alternatif</h3>
        <p>Nilai diambil dari spesifikasi masing-masing laptop.</p>

        <?php if (isset($pesan)): ?>
            <p><?= htmlspecialchars($pesan) ?></p>
        <?php endif; ?>

        <form method="POST">
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Laptop</th>
                            <?php foreach ($kriteria as $k): ?>
                                <th>
                                    <?= htmlspecialchars($k['kode']) ?>
                                    <br>
                                    <?= htmlspecialchars($k['nama']) ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($alternatif as $laptop): ?>
                        <tr>
                            <td><?= htmlspecialchars($laptop['kode']) ?></td>
                            <td>
                                <?= htmlspecialchars($laptop['merek'] . ' ' . $laptop['model']) ?>
                            </td>

                            <?php foreach ($kriteria as $k): ?>
                            <td>
                                <?php
                                $nilaiAwal = ambilNilai($laptop, $k['kode']);

                                $nilaiTersimpan =
                                    $dataTersimpan[$laptop['id']][$k['id']]
                                    ?? $nilaiAwal;
                                ?>

                                <input
                                    type="number"
                                    step="any"
                                    min="0"
                                    name="nilai[<?= $laptop['id'] ?>][<?= $k['id'] ?>]"
                                    value="<?= htmlspecialchars((string)$nilaiTersimpan) ?>"
                                    required
                                    style="width:100px;"
                                >
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <br>
            <button type="submit" name="simpan">
                Simpan Penilaian
            </button>
        </form>
    </div>
</div>

</body>
</html>
