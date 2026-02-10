-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 10 Şub 2026, 16:02:09
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `casino.bakim`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bakim_kayitlari`
--

CREATE TABLE `bakim_kayitlari` (
  `id` int(11) NOT NULL,
  `slot_makine_id` int(11) NOT NULL,
  `bakim_tarihi` date NOT NULL,
  `personel_adi` varchar(100) NOT NULL,
  `yapilan_islemler` text NOT NULL,
  `notlar` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `salons`
--

CREATE TABLE `salons` (
  `id` int(11) NOT NULL,
  `salon_adi` varchar(100) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_width` int(11) DEFAULT NULL,
  `image_height` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `slot_makineleri`
--

CREATE TABLE `slot_makineleri` (
  `id` int(11) NOT NULL,
  `salon_id` int(11) NOT NULL,
  `slot_label` varchar(100) DEFAULT NULL,
  `image_thumb` varchar(255) DEFAULT NULL,
  `x_pct` double NOT NULL,
  `y_pct` double NOT NULL,
  `w_pct` double NOT NULL,
  `h_pct` double NOT NULL,
  `angle_deg` double DEFAULT 0,
  `ocr_confidence` double DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `bakim_kayitlari`
--
ALTER TABLE `bakim_kayitlari`
  ADD PRIMARY KEY (`id`),
  ADD KEY `slot_makine_id` (`slot_makine_id`);

--
-- Tablo için indeksler `salons`
--
ALTER TABLE `salons`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `slot_makineleri`
--
ALTER TABLE `slot_makineleri`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `bakim_kayitlari`
--
ALTER TABLE `bakim_kayitlari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `salons`
--
ALTER TABLE `salons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `slot_makineleri`
--
ALTER TABLE `slot_makineleri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `bakim_kayitlari`
--
ALTER TABLE `bakim_kayitlari`
  ADD CONSTRAINT `bakim_kayitlari_ibfk_1` FOREIGN KEY (`slot_makine_id`) REFERENCES `slot_makineleri` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `slot_makineleri`
--
ALTER TABLE `slot_makineleri`
  ADD CONSTRAINT `slot_makineleri_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
