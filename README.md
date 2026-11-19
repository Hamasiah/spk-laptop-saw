# SPK Pemilihan Laptop Terbaik untuk Mahasiswa (Metode SAW)

Sistem Pendukung Keputusan berbasis web untuk memilih laptop terbaik bagi mahasiswa menggunakan metode **Simple Additive Weighting (SAW)**.

Mata kuliah: Sistem Pendukung Keputusan (SPK) - Kelompok 7
Anggota: Hamasiah Tiopan Ompusunggu dan Fitrah Hasibuan

## Kriteria dan Bobot

| Kode | Kriteria          | Bobot | Jenis   |
|------|-------------------|-------|---------|
| C1   | Harga Laptop      | 0,30  | Cost    |
| C2   | Performa Prosesor | 0,25  | Benefit |
| C3   | Kapasitas RAM     | 0,20  | Benefit |
| C4   | Kapasitas SSD     | 0,15  | Benefit |
| C5   | Kapasitas Baterai | 0,10  | Benefit |

Rentang harga laptop: Rp7.000.000 - Rp12.000.000 (10 alternatif).

## Teknologi

- PHP (PDO)
- MySQL / MariaDB
- XAMPP
- HTML dan CSS

## Cara Menjalankan

1. Pasang dan jalankan **XAMPP** (aktifkan Apache dan MySQL).
2. Salin folder proyek ini ke `C:\xampp\htdocs\spk`.
3. Buka `http://localhost/phpmyadmin`, lalu buat database baru bernama `spk`.
4. Pilih database `spk`, buka tab **Import**, pilih file `dokumen/spk.sql`, lalu klik **Import**.
5. Buka aplikasi di `http://localhost/spk/`.

Koneksi database ada di `koneksi.php` (user `root`, tanpa password, bawaan XAMPP).

## Struktur File

- `index.php` - Dashboard
- `alternatif.php` - Data alternatif laptop
- `kriteria.php` - Data kriteria dan bobot
- `penilaian.php` - Data penilaian
- `perhitungan.php` - Matriks keputusan dan normalisasi
- `hasil.php` - Hasil perangkingan
- `rekomendasi.php` - Rekomendasi laptop terbaik
- `style.css` - Tampilan
- `dokumen/` - Laporan (Word), perhitungan manual (Excel), dan database (`spk.sql`)
