-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2025 at 08:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toko_rahma`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` varchar(25) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL CHECK (`harga` >= 0),
  `stok` int(11) NOT NULL CHECK (`stok` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `nama_barang`, `harga`, `stok`) VALUES
('B001', 'SCARLETT BRIGHTENING FACIAL WASH', 48000.00, 27),
('B002', 'SOMETHINC NIACINAMIDE TONER', 115000.00, 23),
('B003', 'SOMETHINC NIACINAMIDE SERUM', 115000.00, 24),
('B004', 'EMINA BRIGHT STUFF MOISTURIZER', 30000.00, 38),
('B005', 'GARNIER SERUM MASK SAKURA WHITE', 25000.00, 58),
('B006', 'PURBASARI SKIN FIRMING BODY SERUM', 30000.00, 22),
('B007', 'glad2glow moisturizer', 40000.00, 10),
('B008', 'marina uv white', 20000.00, 10);

-- --------------------------------------------------------

--
-- Table structure for table `pembeli`
--

CREATE TABLE `pembeli` (
  `id_pembeli` varchar(25) NOT NULL,
  `nama_pembeli` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `no_hp` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembeli`
--

INSERT INTO `pembeli` (`id_pembeli`, `nama_pembeli`, `alamat`, `no_hp`) VALUES
('P001', 'Tina Dwi Anjani', 'Surabaya', '085732276777'),
('P002', 'Riska Fadila', 'Surabaya', '087554675234'),
('P003', 'Lena Maharani', 'Gresik', '0822345466528'),
('P004', 'Ririn Anggraini', 'Surabaya', '089665732456'),
('P005', 'Eli Faliani', 'Gresik', '088433765365'),
('P006', 'Hidayahtul Fortuna', 'Gresik', '081445245346'),
('p007', 'Riana Maulidya', 'Surabaya', '089776323556');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` varchar(25) NOT NULL,
  `id_pembeli` varchar(25) NOT NULL,
  `id_barang` varchar(25) NOT NULL,
  `jumlah` int(11) NOT NULL CHECK (`jumlah` > 0),
  `total_harga` decimal(10,2) NOT NULL,
  `tanggal` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_pembeli`, `id_barang`, `jumlah`, `total_harga`, `tanggal`) VALUES
('T001', 'P003', 'B004', 3, 90000.00, '2025-10-12'),
('T002', 'P001', 'B005', 2, 50000.00, '2025-10-13'),
('T003', 'P002', 'B002', 2, 230000.00, '2025-10-13'),
('t004', 'P006', 'B003', 1, 115000.00, '2025-10-13'),
('T005', 'p007', 'B006', 1, 30000.00, '2025-10-14'),
('T006', 'P005', 'B004', 2, 60000.00, '2025-10-14'),
('T007', 'P004', 'B004', 1, 30000.00, '2025-10-15'),
('T008', 'P003', 'B001', 2, 96000.00, '2025-10-15'),
('T010', 'P005', 'B004', 1, 30000.00, '2025-10-15'),
('T011', 'P006', 'B006', 1, 30000.00, '2025-10-15'),
('T012', 'P006', 'B006', 1, 30000.00, '2025-10-16'),
('T013', 'P005', 'B001', 1, 48000.00, '2025-10-17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(25) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('admin','kasir') DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`) VALUES
('USR1', 'admin', 'admin123', 'Administrator', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`);

--
-- Indexes for table `pembeli`
--
ALTER TABLE `pembeli`
  ADD PRIMARY KEY (`id_pembeli`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `fk_pembeli` (`id_pembeli`),
  ADD KEY `fk_barang` (`id_barang`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pembeli` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
