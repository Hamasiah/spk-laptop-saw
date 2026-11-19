
<?php
require_once 'koneksi.php';

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
            min-width: 1150px;
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

        @media (max-width: 700px) {
            .table-header {
                align-items: stretch;
            }

            .search-box input {
                width: 100%;
            }

            .search-box {
                width: 100%;
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
            <span>Administrator</span>
        </div>
    </div>

    <div class="table-box">

        <div class="table-header">
            <div>
                <h3>Daftar Alternatif Laptop</h3>
                <p class="jumlah-data">
                    Total <?= count($dataAlternatif) ?> alternatif laptop
                </p>
            </div>

            <div class="search-box">
                <input
                    type="text"
                    id="pencarian"
                    placeholder="Cari kode, merek, atau model laptop..."
                    onkeyup="cariData()"
                >
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
                        </tr>
                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="10" class="empty-data">
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
</script>

</body>
</html>
