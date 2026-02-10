<?php
// config.php - Veritabanı Yapılandırması
// Bu dosya hassas bilgiler içerir ve versiyon kontrolüne eklenmemelidir

// Veritabanı Bağlantı Ayarları
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'slot_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// PDO DSN
define('DB_DSN', sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET));

// PDO Seçenekleri
define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
?>
