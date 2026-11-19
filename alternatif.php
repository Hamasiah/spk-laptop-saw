<?php
session_start();

if (!isset($_SESSION['pengguna'])) {
    header("Location: login.php");
    exit;
}

require_once 'koneksi.php';

// Token keamanan
if (empty($_SESSION['token_alternatif'])) {
    $_SESSION['token_alternatif'] = bin2hex(random_bytes(32));
}

$token = $_SESSION['token_alternatif'];
$pesan = '';
$jenisPesan = 'success';

// Proses formulir
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($token, $_POST['token'] ?? '')) {
        die("Permintaan tidak valid. Silakan muat ulang halaman.");
    }

    $aksi = $_POST['aksi'] ?? '';

    try {

        // TAMBAH DAN EDIT
        if ($aksi === 'tambah' || $aksi === 'edit') {

            $kode = strtoupper(trim($_POST['kode'] ?? ''));
            $merek = trim($_POST['merek'] ?? '');
            $model = trim($_POST['model'] ?? '');
            $prosesor = trim($_POST['prosesor'] ?? '');

            $ram = filter_var($_POST['kapasitas_ram'] ?? '', FILTER_VALIDATE_INT);
            $ssd = filter_var($_POST['kapasitas_ssd'] ?? '', FILTER_VALIDATE_INT);
            $harga = filter_var($_POST['harga'] ?? '', FILTER_VALIDATE_INT);
            $skor = filter_var($_POST['skor_prosesor'] ?? '', FILTER_VALIDATE_INT);

            $bateraiInput = trim($_POST['kapasitas_baterai'] ?? '');
            $baterai = $bateraiInput === ''
                ? null
                : filter_var($bateraiInput, FILTER_VALIDATE_FLOAT);

            if (
                $kode === '' || $merek === '' || $model === '' || $prosesor === '' ||
                $ram === false || $ram <= 0 ||
                $ssd === false || $ssd <= 0 ||
                $harga === false || $harga <= 0 ||
                $skor === false || $skor <= 0 ||
                ($baterai !== null && ($baterai === false || $baterai <= 0))
            ) {
                throw new Exception("Periksa kembali data yang kamu masukkan.");
            }

            if (
                strlen($kode) > 10 ||
                strlen($merek) > 50 ||
                strlen($model) > 150 ||
                strlen($prosesor) > 100
            ) {
                throw new Exception("Ada teks yang melebihi batas karakter.");
            }

            if ($aksi === 'tambah') {

                $stmt = $koneksi->prepare(
                    "INSERT INTO alternatif
                    (kode, merek, model, prosesor, kapasitas_ram,
                    kapasitas_ssd, harga, kapasitas_baterai, skor_prosesor)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->execute([
                    $kode, $merek, $model, $prosesor,
                    $ram, $ssd, $harga, $baterai, $skor
                ]);

                $_SESSION['pesan_alternatif'] = "Data alternatif berhasil ditambahkan!";
            }

            if ($aksi === 'edit') {

                $id = (int)($_POST['id'] ?? 0);

                if ($id <= 0) {
                    throw new Exception("ID alternatif tidak valid.");
                }

                $stmt = $koneksi->prepare(
                    "UPDATE alternatif SET
                    kode = ?, merek = ?, model = ?, prosesor = ?,
                    kapasitas_ram = ?, kapasitas_ssd = ?, harga = ?,
                    kapasitas_baterai = ?, skor_prosesor = ?
                    WHERE id = ?"
                );

                $stmt->execute([
                    $kode, $merek, $model, $prosesor,
                    $ram, $ssd, $harga, $baterai, $skor, $id
                ]);

                $_SESSION['pesan_alternatif'] = "Data alternatif berhasil diperbarui!";
            }
        }

        // HAPUS DATA
        elseif ($aksi === 'hapus') {

            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception("ID alternatif tidak valid.");
            }

            $koneksi->beginTransaction();

            $stmt = $koneksi->prepare(
                "DELETE FROM penilaian WHERE id_alternatif = ?"
            );
            $stmt->execute([$id]);

            $stmt = $koneksi->prepare(
                "DELETE FROM alternatif WHERE id = ?"
            );
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Data alternatif tidak ditemukan.");
            }

            $koneksi->commit();

            $_SESSION['pesan_alternatif'] =
                "Data alternatif dan penilaiannya berhasil dihapus!";
        }

        else {
            throw new Exception("Aksi tidak dikenali.");
        }

        header("Location: alternatif.php");
        exit;

    } catch (Exception $e) {

        if ($koneksi->inTransaction()) {
            $koneksi->rollBack();
        }

        if ($e instanceof PDOException && $e->getCode() === '23000') {
            $pesan = "Kode alternatif sudah digunakan. Gunakan kode lain.";
        } else {
            $pesan = $e->getMessage();
        }

        $jenisPesan = 'error';
    }
}

// Ambil notifikasi
if (isset($_SESSION['pesan_alternatif'])) {
    $pesan = $_SESSION['pesan_alternatif'];
    unset($_SESSION['pesan_alternatif']);
}

// Ambil data alternatif
$stmt = $koneksi->query(
    "SELECT * FROM alternatif ORDER BY id ASC"
);

$dataAlternatif = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Alternatif - SPK Laptop</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .table-box {
            background: white;
            padding: 25px;
            border: 1px solid #E9DDE0;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(74, 31, 31, 0.05);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .table-header h3 {
            margin: 0;
            color: #3B1919;
            font-size: 18px;
        }

        .jumlah-data {
            color: #8A7478;
            font-size: 13px;
            margin: 7px 0 0;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 280px;
            padding: 12px 15px;
            border: 1px solid #E9DDE0;
            border-radius: 9px;
            outline: none;
            font-size: 13px;
        }

        .search-box input:focus {
            border-color: #800020;
            box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.08);
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .table-spk {
            width: 100%;
            min-width: 1300px;
            border-collapse: collapse;
            font-size: 13px;
        }

        .table-spk thead th {
            background: #5B1E2D !important;
            color: white !important;
            padding: 15px 13px;
            text-align: left;
            white-space: nowrap;
        }

        .table-spk tbody td {
            padding: 15px 13px;
            border-bottom: 1px solid #E9DDE0;
            white-space: nowrap;
            color: #38252A;
        }

        .table-spk tbody tr:nth-child(even) {
            background: #FBF6F5;
        }

        .table-spk tbody tr:hover {
            background: #F1E3E5;
        }

        .kode {
            display: inline-block;
            background: #F3E3E7;
            color: #800020;
            padding: 6px 11px;
            border-radius: 6px;
            font-weight: bold;
        }

        .harga {
            font-weight: 600;
            color: #5B1E2D;
        }

        .empty-data {
            padding: 30px;
            text-align: center;
            color: #8A7478;
        }

        .hasil-pencarian {
            margin-top: 15px;
            color: #8A7478;
            font-size: 12px;
        }

        /* Tombol */
        .btn {
            border: none;
            border-radius: 7px;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }

        .btn-tambah {
            background: #800020;
            color: white;
        }

        .btn-tambah:hover {
            background: #600018;
        }

        .btn-edit {
            background: #F3E3E7;
            color: #800020;
        }

        .btn-hapus {
            background: #FCE8E8;
            color: #B42318;
        }

        .aksi {
            display: flex;
            gap: 7px;
            align-items: center;
        }

        /* Notifikasi */
        .alert {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13px;
        }

        .alert-success {
            background: #E8F5E9;
            color: #256029;
        }

        .alert-error {
            background: #FCE8E8;
            color: #B42318;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            width: 550px;
            max-width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 14px;
            padding: 25px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .modal-header h3 {
            color: #5B1E2D;
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 26px;
            cursor: pointer;
            color: #777;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #38252A;
            margin-bottom: 7px;
        }

        .form-group input {
            width: 100%;
            padding: 11px;
            border: 1px solid #E9DDE0;
            border-radius: 7px;
            outline: none;
        }

        .form-group input:focus {
            border-color: #800020;
        }

        .form-note {
            font-size: 11px;
            color: #8A7478;
            margin-top: 5px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-batal {
            background: #EEEEEE;
            color: #444;
        }

        .btn-simpan {
            background: #800020;
            color: white;
        }

        @media (max-width: 700px) {
            .table-header {
                align-items: stretch;
            }

            .search-box,
            .search-box input {
                width: 100%;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }
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
        <a href="alternatif.php" class="active">Data Alternatif</a>
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
            <h2>Data Alternatif</h2>
            <p>Daftar laptop yang menjadi alternatif dalam proses pemilihan.</p>
        </div>

        <div class="admin">
            <div class="admin-icon">A</div>
            <span><?= htmlspecialchars($_SESSION['pengguna']['nama']) ?></span>
            <a href="logout.php" class="btn btn-hapus">Logout</a>
        </div>
    </div>

    <div class="table-box">

        <?php if ($pesan !== ''): ?>
            <div class="alert <?= $jenisPesan === 'error' ? 'alert-error' : 'alert-success' ?>">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <div class="table-header">
            <div>
                <h3>Daftar Alternatif Laptop</h3>
                <p class="jumlah-data">
                    Total <?= count($dataAlternatif) ?> alternatif laptop
                </p>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <div class="search-box">
                    <input
                        type="text"
                        id="pencarian"
                        placeholder="Cari kode, merek, atau model laptop..."
                        onkeyup="cariData()"
                    >
                </div>

                <button class="btn btn-tambah" onclick="bukaTambah()">
                    + Tambah Alternatif
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-spk" id="tabelAlternatif">

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Kode</th>
                        <th>Merek</th>
                        <th>Model</th>
                        <th>Prosesor</th>
                        <th>RAM (GB)</th>
                        <th>SSD (GB)</th>
                        <th>Harga (Rp)</th>
                        <th>Baterai (Wh)</th>
                        <th>Skor Prosesor</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($dataAlternatif) > 0): ?>

                        <?php foreach ($dataAlternatif as $index => $laptop): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <span class="kode">
                                    <?= htmlspecialchars($laptop['kode'] ?? '-') ?>
                                </span>
                            </td>

                            <td><?= htmlspecialchars($laptop['merek'] ?? '-') ?></td>

                            <td><?= htmlspecialchars($laptop['model'] ?? '-') ?></td>

                            <td><?= htmlspecialchars($laptop['prosesor'] ?? '-') ?></td>

                            <td><?= htmlspecialchars($laptop['kapasitas_ram'] ?? '-') ?></td>

                            <td><?= htmlspecialchars($laptop['kapasitas_ssd'] ?? '-') ?></td>

                            <td class="harga">
                                Rp <?= number_format((float)($laptop['harga'] ?? 0), 0, ',', '.') ?>
                            </td>

                            <td>
                                <?= number_format((float)($laptop['kapasitas_baterai'] ?? 0), 2, ',', '.') ?>
                            </td>

                            <td>
                                <?= number_format((float)($laptop['skor_prosesor'] ?? 0), 0, ',', '.') ?>
                            </td>

                            <td>
                                <div class="aksi">

                                    <button
                                        type="button"
                                        class="btn btn-edit"
                                        onclick="bukaEdit(this)"
                                        data-id="<?= (int)$laptop['id'] ?>"
                                        data-kode="<?= htmlspecialchars($laptop['kode'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-merek="<?= htmlspecialchars($laptop['merek'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-model="<?= htmlspecialchars($laptop['model'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-prosesor="<?= htmlspecialchars($laptop['prosesor'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-ram="<?= (int)$laptop['kapasitas_ram'] ?>"
                                        data-ssd="<?= (int)$laptop['kapasitas_ssd'] ?>"
                                        data-harga="<?= (int)$laptop['harga'] ?>"
                                        data-baterai="<?= htmlspecialchars((string)$laptop['kapasitas_baterai'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-skor="<?= (int)$laptop['skor_prosesor'] ?>"
                                    >
                                        Edit
                                    </button>

                                    <form method="POST" onsubmit="return confirm('Yakin ingin menghapus alternatif ini? Data penilaian laptop ini juga akan dihapus.');">
                                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id" value="<?= (int)$laptop['id'] ?>">
                                        <button type="submit" class="btn btn-hapus">Hapus</button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="11" class="empty-data">
                                Belum ada data alternatif laptop.
                            </td>
                        </tr>

                    <?php endif; ?>
                </tbody>

            </table>
        </div>

        <div class="hasil-pencarian" id="hasilPencarian"></div>

    </div>

</div>

<!-- MODAL TAMBAH DAN EDIT -->
<div class="modal" id="modalAlternatif">
    <div class="modal-content">

        <div class="modal-header">
            <h3 id="judulModal">Tambah Alternatif Laptop</h3>
            <button type="button" class="close-modal" onclick="tutupModal()">&times;</button>
        </div>

        <form method="POST" id="formAlternatif">

            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <input type="hidden" name="aksi" id="aksi" value="tambah">
            <input type="hidden" name="id" id="id">

            <div class="form-grid">

                <div class="form-group">
                    <label>Kode Alternatif</label>
                    <input type="text" name="kode" id="kode" maxlength="10" placeholder="Contoh: A11" required>
                </div>

                <div class="form-group">
                    <label>Merek Laptop</label>
                    <input type="text" name="merek" id="merek" maxlength="50" placeholder="Contoh: ASUS" required>
                </div>

                <div class="form-group full">
                    <label>Model Laptop</label>
                    <input type="text" name="model" id="model" maxlength="150" placeholder="Masukkan model laptop" required>
                </div>

                <div class="form-group full">
                    <label>Prosesor</label>
                    <input type="text" name="prosesor" id="prosesor" maxlength="100" placeholder="Masukkan jenis prosesor" required>
                </div>

                <div class="form-group">
                    <label>Kapasitas RAM (GB)</label>
                    <input type="number" name="kapasitas_ram" id="kapasitas_ram" min="1" step="1" required>
                </div>

                <div class="form-group">
                    <label>Kapasitas SSD (GB)</label>
                    <input type="number" name="kapasitas_ssd" id="kapasitas_ssd" min="1" step="1" required>
                </div>

                <div class="form-group full">
                    <label>Harga Laptop (Rp)</label>
                    <input type="number" name="harga" id="harga" min="1" step="1" placeholder="Contoh: 8500000" required>
                    <p class="form-note">Masukkan angka tanpa titik atau Rp.</p>
                </div>

                <div class="form-group">
                    <label>Kapasitas Baterai (Wh)</label>
                    <input type="number" name="kapasitas_baterai" id="kapasitas_baterai" min="0.01" step="0.01" placeholder="Contoh: 50">
                    <p class="form-note">Boleh dikosongkan jika tidak diketahui.</p>
                </div>

                <div class="form-group">
                    <label>Skor Prosesor</label>
                    <input type="number" name="skor_prosesor" id="skor_prosesor" min="1" step="1" required>
                </div>

            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-batal" onclick="tutupModal()">Batal</button>
                <button type="submit" class="btn btn-simpan" id="tombolSimpan">Simpan Data</button>
            </div>

        </form>
    </div>
</div>

<script>
function cariData() {
    const input = document.getElementById("pencarian");
    const filter = input.value.toLowerCase();
    const tabel = document.getElementById("tabelAlternatif");
    const baris = tabel.querySelectorAll("tbody tr");

    let jumlahTampil = 0;

    baris.forEach(function(baris) {
        const teks = baris.textContent.toLowerCase();

        if (teks.includes(filter)) {
            baris.style.display = "";
            jumlahTampil++;
        } else {
            baris.style.display = "none";
        }
    });

    document.getElementById("hasilPencarian").textContent =
        filter === "" ? "" : jumlahTampil + " data ditemukan";
}

// Buka formulir tambah
function bukaTambah() {
    document.getElementById("formAlternatif").reset();

    document.getElementById("judulModal").textContent = "Tambah Alternatif Laptop";
    document.getElementById("aksi").value = "tambah";
    document.getElementById("id").value = "";
    document.getElementById("tombolSimpan").textContent = "Simpan Data";

    document.getElementById("modalAlternatif").style.display = "flex";
}

// Buka formulir edit
function bukaEdit(tombol) {
    const form = document.getElementById("formAlternatif");

    form.reset();

    document.getElementById("judulModal").textContent = "Edit Alternatif Laptop";
    document.getElementById("aksi").value = "edit";
    document.getElementById("id").value = tombol.dataset.id;

    document.getElementById("kode").value = tombol.dataset.kode;
    document.getElementById("merek").value = tombol.dataset.merek;
    document.getElementById("model").value = tombol.dataset.model;
    document.getElementById("prosesor").value = tombol.dataset.prosesor;
    document.getElementById("kapasitas_ram").value = tombol.dataset.ram;
    document.getElementById("kapasitas_ssd").value = tombol.dataset.ssd;
    document.getElementById("harga").value = tombol.dataset.harga;
    document.getElementById("kapasitas_baterai").value = tombol.dataset.baterai;
    document.getElementById("skor_prosesor").value = tombol.dataset.skor;

    document.getElementById("tombolSimpan").textContent = "Simpan Perubahan";
    document.getElementById("modalAlternatif").style.display = "flex";
}

// Tutup formulir
function tutupModal() {
    document.getElementById("modalAlternatif").style.display = "none";
}

// Tutup modal jika klik area luar
document.getElementById("modalAlternatif").addEventListener("click", function(event) {
    if (event.target === this) {
        tutupModal();
    }
});
</script>

</body>
</html>
