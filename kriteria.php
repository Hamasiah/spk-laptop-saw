<?php
session_start();

if (!isset($_SESSION['pengguna'])) {
    header("Location: login.php");
    exit;
}

require_once 'koneksi.php';

// Token keamanan formulir
if (empty($_SESSION['token_kriteria'])) {
    $_SESSION['token_kriteria'] = bin2hex(random_bytes(32));
}

$pesan = $_SESSION['pesan_kriteria'] ?? '';
unset($_SESSION['pesan_kriteria']);

// Proses tambah, edit, dan hapus
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['token']) ||
        !hash_equals($_SESSION['token_kriteria'], $_POST['token'])
    ) {
        die("Permintaan tidak valid. Silakan muat ulang halaman.");
    }

    $aksi = $_POST['aksi'] ?? '';

    try {

        // TAMBAH DAN EDIT
        if ($aksi === 'tambah' || $aksi === 'edit') {

            $kode = strtoupper(trim($_POST['kode'] ?? ''));
            $nama = trim($_POST['nama'] ?? '');
            $bobot = $_POST['bobot'] ?? '';
            $jenis = $_POST['jenis'] ?? '';

            if ($kode === '' || $nama === '' || $bobot === '' || $jenis === '') {
                throw new Exception("Semua kolom wajib diisi.");
            }

            if (strlen($kode) > 10 || strlen($nama) > 100) {
                throw new Exception("Kode atau nama kriteria terlalu panjang.");
            }

            if (!is_numeric($bobot) || $bobot < 0 || $bobot > 100) {
                throw new Exception("Bobot harus berupa angka antara 0 sampai 100.");
            }

            if (!in_array($jenis, ['cost', 'benefit'], true)) {
                throw new Exception("Atribut kriteria tidak valid.");
            }

            if ($aksi === 'tambah') {

                $stmt = $koneksi->prepare(
                    "INSERT INTO kriteria (kode, nama, bobot, jenis)
                     VALUES (?, ?, ?, ?)"
                );

                $stmt->execute([$kode, $nama, $bobot, $jenis]);

                $_SESSION['pesan_kriteria'] = "Kriteria berhasil ditambahkan.";

            } else {

                $id = (int) ($_POST['id'] ?? 0);

                if ($id <= 0) {
                    throw new Exception("ID kriteria tidak valid.");
                }

                $stmt = $koneksi->prepare(
                    "UPDATE kriteria
                     SET kode = ?, nama = ?, bobot = ?, jenis = ?
                     WHERE id = ?"
                );

                $stmt->execute([$kode, $nama, $bobot, $jenis, $id]);

                $_SESSION['pesan_kriteria'] = "Kriteria berhasil diperbarui.";
            }

        // HAPUS
        } elseif ($aksi === 'hapus') {

            $id = (int) ($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception("ID kriteria tidak valid.");
            }

            $koneksi->beginTransaction();

            // Hapus penilaian yang menggunakan kriteria ini
            $stmt = $koneksi->prepare(
                "DELETE FROM penilaian WHERE id_kriteria = ?"
            );
            $stmt->execute([$id]);

            // Hapus kriteria
            $stmt = $koneksi->prepare(
                "DELETE FROM kriteria WHERE id = ?"
            );
            $stmt->execute([$id]);

            $koneksi->commit();

            $_SESSION['pesan_kriteria'] = "Kriteria berhasil dihapus.";

        } else {
            throw new Exception("Aksi tidak dikenal.");
        }

    } catch (Exception $e) {

        if ($koneksi->inTransaction()) {
            $koneksi->rollBack();
        }

        $_SESSION['pesan_kriteria'] = "Gagal: " . $e->getMessage();

    } catch (PDOException $e) {

        if ($koneksi->inTransaction()) {
            $koneksi->rollBack();
        }

        if ($e->getCode() === '23000') {
            $_SESSION['pesan_kriteria'] = "Gagal: Kode kriteria sudah digunakan.";
        } else {
            $_SESSION['pesan_kriteria'] = "Terjadi kesalahan pada database.";
        }
    }

    header("Location: kriteria.php");
    exit;
}

// Ambil data kriteria
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

    <style>
        .aksi-data {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .btn-tambah, .btn-edit, .btn-hapus {
            border: none;
            padding: 8px 13px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-tambah {
            background: #800020;
            color: white;
            margin-bottom: 18px;
        }

        .btn-edit {
            background: #f0ad4e;
            color: white;
        }

        .btn-hapus {
            background: #dc3545;
            color: white;
        }

        .pesan-kriteria {
            padding: 12px 15px;
            margin-bottom: 15px;
            background: #f1f1f1;
            border-radius: 6px;
        }

        .modal-kriteria {
            display: none;
            position: fixed;
            z-index: 1000;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            padding: 15px;
        }

        .modal-konten {
            background: white;
            width: 100%;
            max-width: 450px;
            padding: 25px;
            border-radius: 10px;
        }

        .modal-konten h3 {
            margin-top: 0;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .modal-tombol {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-simpan, .btn-batal {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-simpan {
            background: #800020;
            color: white;
        }

        .btn-batal {
            background: #ddd;
            color: #333;
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
            <span><?= htmlspecialchars($_SESSION['pengguna']['nama'] ?? 'Administrator') ?></span>
        </div>
    </div>

    <div class="content-box">

        <h3>Daftar Kriteria Penilaian</h3>
        <p>Kriteria yang digunakan dalam pemilihan laptop terbaik.</p>

        <?php if ($pesan !== ''): ?>
            <div class="pesan-kriteria">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <button class="btn-tambah" onclick="bukaTambah()">
            + Tambah Kriteria
        </button>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Kriteria</th>
                    <th>Bobot</th>
                    <th>Atribut</th>
                    <th>Aksi</th>
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
                            <td>
                                <?= $kriteria['jenis'] === 'cost' ? 'Cost' : 'Benefit' ?>
                            </td>
                            <td>
                                <div class="aksi-data">

                                    <button
                                        type="button"
                                        class="btn-edit"
                                        onclick='bukaEdit(
                                            <?= json_encode(
                                                $kriteria,
                                                JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP
                                            ) ?>
                                        )'>
                                        Edit
                                    </button>

                                    <form method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus kriteria ini? Data penilaian terkait juga akan dihapus.');">

                                        <input type="hidden" name="token"
                                               value="<?= htmlspecialchars($_SESSION['token_kriteria']) ?>">

                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id"
                                               value="<?= (int) $kriteria['id'] ?>">

                                        <button type="submit" class="btn-hapus">
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6">Belum ada data kriteria.</td>
                    </tr>

                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>

<!-- MODAL TAMBAH DAN EDIT -->
<div class="modal-kriteria" id="modalKriteria">

    <div class="modal-konten">

        <h3 id="judulModal">Tambah Kriteria</h3>

        <form method="POST">

            <input type="hidden" name="token"
                   value="<?= htmlspecialchars($_SESSION['token_kriteria']) ?>">

            <input type="hidden" name="aksi" id="aksi" value="tambah">
            <input type="hidden" name="id" id="id">

            <div class="form-group">
                <label>Kode Kriteria</label>
                <input type="text" name="kode" id="kode"
                       maxlength="10" placeholder="Contoh: C1" required>
            </div>

            <div class="form-group">
                <label>Nama Kriteria</label>
                <input type="text" name="nama" id="nama"
                       maxlength="100" placeholder="Nama kriteria" required>
            </div>

            <div class="form-group">
                <label>Bobot (%)</label>
                <input type="number" name="bobot" id="bobot"
                       min="0" max="100" step="0.01"
                       placeholder="Contoh: 30" required>
            </div>

            <div class="form-group">
                <label>Atribut Kriteria</label>
                <select name="jenis" id="jenis" required>
                    <option value="">-- Pilih Atribut --</option>
                    <option value="cost">Cost</option>
                    <option value="benefit">Benefit</option>
                </select>
            </div>

            <div class="modal-tombol">
                <button type="submit" class="btn-simpan">Simpan</button>
                <button type="button" class="btn-batal"
                        onclick="tutupModal()">Batal</button>
            </div>

        </form>

    </div>
</div>

<script>
function bukaTambah() {
    document.getElementById('judulModal').textContent = 'Tambah Kriteria';
    document.getElementById('aksi').value = 'tambah';
    document.getElementById('id').value = '';
    document.getElementById('kode').value = '';
    document.getElementById('nama').value = '';
    document.getElementById('bobot').value = '';
    document.getElementById('jenis').value = '';

    document.getElementById('modalKriteria').style.display = 'flex';
}

function bukaEdit(data) {
    document.getElementById('judulModal').textContent = 'Edit Kriteria';
    document.getElementById('aksi').value = 'edit';
    document.getElementById('id').value = data.id;
    document.getElementById('kode').value = data.kode;
    document.getElementById('nama').value = data.nama;
    document.getElementById('bobot').value = data.bobot;
    document.getElementById('jenis').value = data.jenis;

    document.getElementById('modalKriteria').style.display = 'flex';
}

function tutupModal() {
    document.getElementById('modalKriteria').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('modalKriteria');

    if (event.target === modal) {
        tutupModal();
    }
}
</script>

</body>
</html>
