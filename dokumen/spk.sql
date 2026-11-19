-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 19 Nov 2026 pada 18.38
-- Versi server: 10.11.16-MariaDB
-- Versi PHP: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `spk`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `alternatif`
--

CREATE TABLE `alternatif` (
  `id` int(11) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `merek` varchar(50) NOT NULL,
  `model` varchar(150) NOT NULL,
  `prosesor` varchar(100) NOT NULL,
  `kapasitas_ram` int(11) NOT NULL,
  `kapasitas_ssd` int(11) NOT NULL,
  `harga` bigint(20) NOT NULL,
  `kapasitas_baterai` decimal(6,2) DEFAULT NULL,
  `skor_prosesor` int(11) NOT NULL,
  `dibuat_pada` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `alternatif`
--

INSERT INTO `alternatif` (`id`, `kode`, `merek`, `model`, `prosesor`, `kapasitas_ram`, `kapasitas_ssd`, `harga`, `kapasitas_baterai`, `skor_prosesor`, `dibuat_pada`) VALUES
(1, 'A1', 'Acer', 'Aspire Go 15', 'Intel Core i3-N305', 8, 256, 7000000, 50.00, 7700, '2026-11-16 18:00:56'),
(2, 'A2', 'Asus', 'Vivobook Go 14', 'AMD Ryzen 5 7520U', 8, 512, 7300000, 42.00, 9900, '2026-11-16 17:58:56'),
(3, 'A3', 'HP', '245 G10', 'AMD Ryzen 5 7530U', 8, 256, 7900000, 41.00, 14300, '2026-11-16 17:58:56'),
(4, 'A4', 'Lenovo', 'IdeaPad Slim 3 14IAH8', 'Intel Core i5-12450H', 8, 512, 9300000, 47.00, 17700, '2026-11-16 17:58:56'),
(5, 'A5', 'Axioo', 'Hype 7 AMD', 'AMD Ryzen 7 5700U', 16, 512, 8900000, 46.00, 16700, '2026-11-16 17:58:56'),
(6, 'A6', 'Acer', 'Aspire 5 A515-58P', 'Intel Core i5-1335U', 16, 512, 10500000, 50.00, 14800, '2026-11-16 17:58:56'),
(7, 'A7', 'Asus', 'Vivobook 14 A1405VA', 'Intel Core i5-13500H', 16, 512, 11000000, 50.00, 22500, '2026-11-16 17:58:56'),
(8, 'A8', 'Lenovo', 'IdeaPad Slim 3 15IRU8', 'Intel Core i5-13420H', 16, 1024, 10900000, 47.00, 17500, '2026-11-16 17:58:56'),
(9, 'A9', 'HP', '15-fc0xxx', 'AMD Ryzen 7 7730U', 16, 512, 10500000, 41.00, 17000, '2026-11-16 17:58:56'),
(10, 'A10', 'Acer', 'Aspire 5 A515-58M', 'Intel Core i7-1355U', 16, 1024, 10990000, 50.00, 17500, '2026-11-16 17:58:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kriteria`
--

CREATE TABLE `kriteria` (
  `id` int(11) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `bobot` decimal(5,2) NOT NULL,
  `jenis` enum('cost','benefit') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kriteria`
--

INSERT INTO `kriteria` (`id`, `kode`, `nama`, `bobot`, `jenis`) VALUES
(1, 'C1', 'Harga Laptop', 0.30, 'cost'),
(2, 'C2', 'Performa Prosesor', 0.25, 'benefit'),
(3, 'C3', 'Kapasitas RAM', 0.20, 'benefit'),
(4, 'C4', 'Kapasitas SSD', 0.15, 'benefit'),
(5, 'C5', 'Kapasitas Baterai', 0.10, 'benefit');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nama_pengguna` varchar(50) NOT NULL,
  `kata_sandi` varchar(255) NOT NULL,
  `peran` varchar(20) DEFAULT 'admin',
  `dibuat_pada` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penilaian`
--

CREATE TABLE `penilaian` (
  `id` int(11) NOT NULL,
  `id_alternatif` int(11) NOT NULL,
  `id_kriteria` int(11) NOT NULL,
  `nilai` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penilaian`
--

INSERT INTO `penilaian` (`id`, `id_alternatif`, `id_kriteria`, `nilai`) VALUES
(51, 1, 1, 7000000.00),
(52, 1, 2, 7700.00),
(53, 1, 3, 8.00),
(54, 1, 4, 256.00),
(55, 1, 5, 50.00),
(56, 2, 1, 7300000.00),
(57, 2, 2, 9900.00),
(58, 2, 3, 8.00),
(59, 2, 4, 512.00),
(60, 2, 5, 42.00),
(61, 3, 1, 7900000.00),
(62, 3, 2, 14300.00),
(63, 3, 3, 8.00),
(64, 3, 4, 256.00),
(65, 3, 5, 41.00),
(66, 4, 1, 9300000.00),
(67, 4, 2, 17700.00),
(68, 4, 3, 8.00),
(69, 4, 4, 512.00),
(70, 4, 5, 47.00),
(71, 5, 1, 8900000.00),
(72, 5, 2, 16700.00),
(73, 5, 3, 16.00),
(74, 5, 4, 512.00),
(75, 5, 5, 46.00),
(76, 6, 1, 10500000.00),
(77, 6, 2, 14800.00),
(78, 6, 3, 16.00),
(79, 6, 4, 512.00),
(80, 6, 5, 50.00),
(81, 7, 1, 11000000.00),
(82, 7, 2, 22500.00),
(83, 7, 3, 16.00),
(84, 7, 4, 512.00),
(85, 7, 5, 50.00),
(86, 8, 1, 10900000.00),
(87, 8, 2, 17500.00),
(88, 8, 3, 16.00),
(89, 8, 4, 1024.00),
(90, 8, 5, 47.00),
(91, 9, 1, 10500000.00),
(92, 9, 2, 17000.00),
(93, 9, 3, 16.00),
(94, 9, 4, 512.00),
(95, 9, 5, 41.00),
(96, 10, 1, 10990000.00),
(97, 10, 2, 17500.00),
(98, 10, 3, 16.00),
(99, 10, 4, 1024.00),
(100, 10, 5, 50.00);

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `alternatif`
--
ALTER TABLE `alternatif`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`kode`);

--
-- Indeks untuk tabel `kriteria`
--
ALTER TABLE `kriteria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`kode`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`nama_pengguna`);

--
-- Indeks untuk tabel `penilaian`
--
ALTER TABLE `penilaian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `alternative_id` (`id_alternatif`,`id_kriteria`),
  ADD KEY `criteria_id` (`id_kriteria`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `alternatif`
--
ALTER TABLE `alternatif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `kriteria`
--
ALTER TABLE `kriteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penilaian`
--
ALTER TABLE `penilaian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
