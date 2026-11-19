<?php
session_start();

if (!isset($_SESSION['pengguna'])) {
    header("Location: login.php");
    exit;
}

require_once 'koneksi.php';

// Token keamanan
if (empty($_SESSION['token_penilaian'])) {
    $_SESSION['token_penilaian'] = bin2hex(random_bytes(32));
}

$pesan = $_SESSION['pesan_penilaian'] ?? '';
unset($_SESSION['pesan_penilaian']);

// Ambil data alternatif
$stmt = $koneksi->query("SELECT * FROM alternatif ORDER BY id ASC");
$alternatif = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data kriteria
$stmt = $koneksi->query("SELECT * FROM kriteria ORDER BY id ASC");
$kriteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pemetaan nilai berdasarkan kode kriteria
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

// Proses penyimpanan seluruh matriks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {

    if (
        !isset($_POST['token']) ||
        !hash_equals($_SESSION['token_penilaian'], $_POST['token'])
    ) {
        die("Permintaan tidak valid. Silakan muat ulang halaman.");
    }

    try {

        if (count($alternatif) === 0 || count($kriteria) === 0) {
            throw new Exception(
                "Data alternatif atau kriteria belum tersedia."
            );
        }

        // Validasi seluruh nilai sebelum menyimpan
        $nilaiMatriks = [];

        foreach ($alternatif as $laptop) {
            foreach ($kriteria as $k) {

                $nilai = $_POST['nilai'][$laptop['id']][$k['id']] ?? null;

                if ($nilai === null || $nilai === '' || !is_numeric($nilai)) {
                    throw new Exception(
                        "Semua nilai penilaian harus diisi dengan angka."
                    );
                }

                if ((float)$nilai < 0) {
                    throw new Exception(
                        "Nilai penilaian tidak boleh negatif."
                    );
                }

                $nilaiMatriks[] = [
                    'id_alternatif' => $laptop['id'],
                    'id_kriteria' => $k['id'],
                    'nilai' => $nilai
                ];
            }
        }

        // Simpan seluruh matriks secara bersamaan
        $koneksi->beginTransaction();

        $koneksi->exec("DELETE FROM penilaian");

        $stmt = $koneksi->prepare(
            "INSERT INTO penilaian
            (id_alternatif, id_kriteria, nilai)
            VALUES (?, ?, ?)"
        );

        foreach ($nilaiMatriks as $data) {
            $stmt->execute([
                $data['id_alternatif'],
                $data['id_kriteria'],
                $data['nilai']
            ]);
        }

        $koneksi->commit();

        $_SESSION['pesan_penilaian'] =
            "Seluruh data penilaian berhasil disimpan!";

    } catch (Throwable $e) {

        if ($koneksi->inTransaction()) {
            $koneksi->rollBack();
        }

        $_SESSION['pesan_penilaian'] =
            "Gagal menyimpan penilaian: " . $e->getMessage();
    }

    header("Location: penilaian.php");
    exit;
}

// Ambil nilai penilaian yang sudah tersimpan
$stmt = $koneksi->query("SELECT * FROM penilaian");

$dataTersimpan = [];

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $dataTersimpan[$row['id_alternatif']][$row['id_kriteria']] =
        $row['nilai'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penilaian</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .pesan-penilaian {
            padding: 12px 15px;
            margin-bottom: 18px;
            background: #f1f1f1;
            border-radius: 6px;
        }

        .tombol-simpan {
            background: #800020;
            color: white;
            padding: 11px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .tombol-simpan:hover {
            opacity: 0.9;
        }

        .keterangan-penilaian {
            margin-bottom: 18px;
            padding: 12px;
            background: #f8f0f2;
            border-left: 4px solid #800020;
            border-radius: 5px;
        }

        .tabel-penilaian input {
            width: 100px;
            padding: 7px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
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
            <span>
                <?= htmlspecialchars(
                    $_SESSION['pengguna']['nama'] ?? 'Administrator'
                ) ?>
            </span>
        </div>
    </div>

    <div class="content-box">

        <h3>Matriks Penilaian Alternatif</h3>

        <p>Nilai diambil dari spesifikasi masing-masing laptop.</p>

        <?php if ($pesan !== ''): ?>
            <div class="pesan-penilaian">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <div class="keterangan-penilaian">
            <strong>Petunjuk:</strong>
            Kamu dapat mengubah nilai pada setiap kolom sesuai kebutuhan.
            Setelah selesai, klik tombol Simpan Penilaian.
        </div>

        <?php if (count($alternatif) > 0 && count($kriteria) > 0): ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="token"
                    value="<?= htmlspecialchars($_SESSION['token_penilaian']) ?>"
                >

                <div style="overflow-x:auto;">

                    <table class="tabel-penilaian">

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

                                    <td>
                                        <?= htmlspecialchars($laptop['kode']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $laptop['merek'] . ' ' . $laptop['model']
                                        ) ?>
                                    </td>

                                    <?php foreach ($kriteria as $k): ?>

                                        <td>
                                            <?php
                                            $nilaiAwal = ambilNilai(
                                                $laptop,
                                                $k['kode']
                                            );

                                            $nilaiTersimpan =
                                                $dataTersimpan[$laptop['id']][$k['id']]
                                                ?? $nilaiAwal;
                                            ?>

                                            <input
                                                type="number"
                                                step="any"
                                                min="0"
                                                name="nilai[<?= (int)$laptop['id'] ?>][<?= (int)$k['id'] ?>]"
                                                value="<?= htmlspecialchars((string)$nilaiTersimpan) ?>"
                                                required
                                            >
                                        </td>

                                    <?php endforeach; ?>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

                <br>

                <button
                    type="submit"
                    name="simpan"
                    class="tombol-simpan"
                >
                    Simpan Penilaian
                </button>

            </form>

        <?php else: ?>

            <p>
                Data alternatif atau kriteria belum tersedia.
                Silakan tambahkan data terlebih dahulu.
            </p>

        <?php endif; ?>

    </div>

</div>

</body>
</html>
