<?php
// db_init_mysql.php - VIP MAKİNELER EKLENDİ + BRAND/MODEL AYRIŞTIRILDI + USERS + MAINTENANCE_PERSON
require_once __DIR__ . '/config.php';
try {
$pdo = new PDO(sprintf('mysql:host=%s;charset=%s', DB_HOST, DB_CHARSET), DB_USER, DB_PASS, [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
// DB'yi TAM SIFIRLA
$pdo->exec("DROP DATABASE IF EXISTS `" . DB_NAME . "`");
$pdo->exec("CREATE DATABASE `" . DB_NAME . "` CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_unicode_ci");
$pdo->exec("USE `" . DB_NAME . "`");
// Users tablosu - Kimlik doğrulama için
$pdo->exec("
CREATE TABLE `users` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`username` VARCHAR(64) NOT NULL UNIQUE,
`password` VARCHAR(255) NOT NULL,
`role` ENUM('admin', 'user') DEFAULT 'user',
`full_name` VARCHAR(128),
`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
INDEX idx_username (username),
INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
// Machines tablosu - GÜNCELLENDİ: brand_model yerine brand, model, game_type + maintenance_person
$pdo->exec("
CREATE TABLE `machines` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`room` VARCHAR(64) NOT NULL,
`machine_number` VARCHAR(64) NOT NULL,
`brand` VARCHAR(128) NOT NULL,
`model` VARCHAR(128) NOT NULL,
`game_type` VARCHAR(128),
`maintenance_date` DATE NOT NULL,
`maintenance_person` VARCHAR(128),
`note` TEXT,
`x` INT NOT NULL DEFAULT 30,
`y` INT NOT NULL DEFAULT 30,
`size` INT NOT NULL DEFAULT 63,
`rotation` INT NOT NULL DEFAULT 0,
`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
INDEX idx_room (room),
INDEX idx_machine_number (machine_number),
INDEX idx_brand (brand),
INDEX idx_model (model),
INDEX idx_game_type (game_type),
INDEX idx_maintenance_date (maintenance_date),
INDEX idx_maintenance_person (maintenance_person)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
// Machine Faults tablosu (Trello entegrasyonu için)
$pdo->exec("
CREATE TABLE `machine_faults` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`machine_id` INT,
`trello_card_id` VARCHAR(64),
`trello_card_url` VARCHAR(255),
`fault_title` VARCHAR(255) NOT NULL,
`fault_description` TEXT,
`status` ENUM('open', 'in_progress', 'resolved') DEFAULT 'open',
`priority` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
`reported_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
`resolved_date` TIMESTAMP NULL,
`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
INDEX idx_machine_id (machine_id),
INDEX idx_trello_card_id (trello_card_id),
INDEX idx_status (status),
INDEX idx_reported_date (reported_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
// Maintenance History tablosu (makina geçmişi için)
$pdo->exec("
CREATE TABLE `maintenance_history` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`machine_id` INT NOT NULL,
`action_type` ENUM('maintenance', 'fault', 'repair', 'note', 'created', 'updated', 'moved') NOT NULL,
`details` TEXT,
`old_value` TEXT,
`new_value` TEXT,
`performed_by` VARCHAR(128) DEFAULT 'system',
`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
INDEX idx_machine_id (machine_id),
INDEX idx_action_type (action_type),
INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
// Trello Configuration tablosu
$pdo->exec("
CREATE TABLE `trello_config` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`api_key` VARCHAR(255),
`api_token` VARCHAR(255),
`board_id` VARCHAR(64),
`list_id` VARCHAR(64),
`last_sync` TIMESTAMP NULL,
`sync_enabled` BOOLEAN DEFAULT TRUE,
`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
// Cache dizini oluştur
if (!is_dir(__DIR__ . '/cache')) {
mkdir(__DIR__ . '/cache', 0755, true);
}
// Varsayılan admin kullanıcısı ekle
echo "<p style='color:#4caf50;'>👤 Varsayılan kullanıcılar oluşturuluyor...</p>";
$stmt = $pdo->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)");
// Admin kullanıcı - şifre: admin123
$stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin', 'Sistem Yöneticisi']);
// Normal kullanıcı - şifre: user123
$stmt->execute(['user', password_hash('user123', PASSWORD_DEFAULT), 'user', 'Normal Kullanıcı']);
echo "<p style='color:#4caf50;'>✅ Admin: admin/admin123, User: user/user123</p>";
// YENİ VİP SALON için makineleri ekle
echo "<p style='color:#4caf50;'>📦 YENİ VİP SALON makineleri ekleniyor...</p>";
$vipMachines = [
['2192', 'EGT', 'Deluxe', 'Slot'],
['2194', 'EGT', 'Premium', 'Slot'],
['2635', 'EGT', 'Classic', 'Slot'],
['2770', 'EGT', 'Gold', 'Slot'],
['2361', 'EGT', 'Diamond', 'Slot'],
['2362', 'EGT', 'Diamond', 'Slot'],
['2363', 'EGT', 'Diamond', 'Slot'],
['2364', 'EGT', 'Diamond', 'Slot'],
['2257', 'EGT', 'Royal', 'Slot'],
['2258', 'EGT', 'Royal', 'Slot'],
['2259', 'EGT', 'Royal', 'Slot'],
['2260', 'EGT', 'Royal', 'Slot'],
['3072', 'XTENSION LINK', 'Pro', 'Link'],
['3073', 'XTENSION LINK', 'Pro', 'Link'],
['3074', 'XTENSION LINK', 'Pro', 'Link'],
['3075', 'XTENSION LINK', 'Pro', 'Link'],
['3076', 'XTENSION LINK', 'Pro', 'Link'],
['3077', 'XTENSION LINK', 'Pro', 'Link'],
['3078', 'XTENSION LINK', 'Pro', 'Link'],
['3079', 'XTENSION LINK', 'Pro', 'Link'],
['2946', 'VİP EGT', 'VIP', 'Slot'],
['2947', 'VİP EGT', 'VIP', 'Slot'],
['2738', 'VİP EGT', 'VIP', 'Slot'],
['2948', 'VİP EGT', 'VIP', 'Slot'],
['2949', 'VİP EGT', 'VIP', 'Slot'],
['2443', 'VİP EGT', 'VIP', 'Slot'],
['2604', 'VİP EGT', 'VIP', 'Slot'],
['2607', 'VİP EGT', 'VIP', 'Slot'],
['2722', 'VİP EGT', 'VIP', 'Slot'],
['2723', 'VİP EGT', 'VIP', 'Slot'],
['2724', 'VİP EGT', 'VIP', 'Slot'],
['2725', 'VİP EGT', 'VIP', 'Slot'],
['2726', 'VİP EGT', 'VIP', 'Slot'],
['2727', 'VİP EGT', 'VIP', 'Slot'],
['2728', 'VİP EGT', 'VIP', 'Slot'],
['2729', 'VİP EGT', 'VIP', 'Slot'],
['2730', 'VİP EGT', 'VIP', 'Slot'],
['2731', 'VİP EGT', 'VIP', 'Slot'],
['2732', 'VİP EGT', 'VIP', 'Slot'],
['2969', 'VİP EGT', 'VIP', 'Slot'],
['2970', 'VİP EGT', 'VIP', 'Slot'],
['2971', 'VİP EGT', 'VIP', 'Slot'],
['3037', 'VİP EGT', 'VIP', 'Slot'],
['3038', 'VİP EGT', 'VIP', 'Slot'],
['3051', 'VİP EGT', 'VIP', 'Slot'],
['3052', 'VİP EGT', 'VIP', 'Slot'],
['3053', 'VİP EGT', 'VIP', 'Slot'],
['3054', 'VİP EGT', 'VIP', 'Slot'],
['3055', 'VİP EGT', 'VIP', 'Slot'],
['3056', 'VİP EGT', 'VIP', 'Slot']
];
$maintenance_date = date('Y-m-d');
$stmt = $pdo->prepare("INSERT INTO machines (room, machine_number, brand, model, game_type, maintenance_date, x, y, size, rotation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 63, 0)");
foreach ($vipMachines as $index => $machine) {
// Random pozisyon (100-900px arası, eşit dağılım için)
$x = rand(100, 900);
$y = rand(100, 600);
$stmt->execute([
'YENİ VİP SALON',
$machine[0],
$machine[1],
$machine[2],
$machine[3],
$maintenance_date,
$x,
$y
]);
}
echo "<!DOCTYPE html>
<html lang='tr'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Veritabanı Kurulumu</title>
<style>
body {
font-family: Arial, sans-serif;
max-width: 800px;
margin: 40px auto;
padding: 30px;
background: linear-gradient(145deg, #1a1a1a, #0a0a0a);
color: #fff;
border-radius: 15px;
box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}
.success {
background: linear-gradient(145deg, rgba(76,175,80,0.15), rgba(46,125,50,0.1));
padding: 25px;
border-radius: 10px;
border-left: 5px solid #4caf50;
margin: 30px 0;
}
.btn {
display: inline-block;
padding: 15px 30px;
background: linear-gradient(145deg, #4caf50, #2e7d32);
color: white;
text-decoration: none;
border-radius: 10px;
font-weight: bold;
font-size: 18px;
transition: all 0.3s;
box-shadow: 0 4px 15px rgba(76,175,80,0.3);
margin: 10px;
}
.btn:hover {
transform: translateY(-3px);
box-shadow: 0 6px 20px rgba(76,175,80,0.4);
}
h1 {
color: #c9a94f;
text-align: center;
margin-bottom: 30px;
}
</style>
</head>
<body>";
echo "<h1>🎰 CASINO BAKIM TAKIP - VERITABANI KURULUMU</h1>";
echo "<div class='success'>";
echo "<h2>✅ VERITABANI HAZIR!</h2>";
echo "<p><strong>Slot_db</strong> veritabanı oluşturuldu.</p>";
echo "<p><strong>YENİ VİP SALON</strong> için " . count($vipMachines) . " makina eklendi.</p>";
echo "<p><strong>Kullanıcılar:</strong></p>";
echo "<ul>";
echo "<li>👑 Admin: <code>admin</code> / <code>admin123</code></li>";
echo "<li>👤 User: <code>user</code> / <code>user123</code></li>";
echo "</ul>";
echo "<p><strong>Değişiklikler:</strong></p>";
echo "<ul>";
echo "<li>Kullanıcı kimlik doğrulama sistemi eklendi</li>";
echo "<li>Bakım yapan kişi (maintenance_person) alanı eklendi</li>";
echo "<li>Marka ve Model artık ayrı alanlar</li>";
echo "<li>Yeni 'Oyun Çeşidi' alanı eklendi</li>";
echo "<li>Makinalar random pozisyonlarda yerleştirildi</li>";
echo "</ul>";
echo "</div>";
echo "<div style='text-align:center; margin-top:40px;'>";
echo "<a href='index.php' class='btn'>🚀 ANA SAYFAYA GİT</a>";
echo "</div>";
echo "</body></html>";
} catch (Exception $e) {
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 30px; background: #1a1a1a; color: #fff; border-radius: 15px; border: 3px solid #f44336;'>
<h2 style='color:#f44336;'>❌ HATA OLUŞTU</h2>
<p><strong>Hata Mesajı:</strong> " . $e->getMessage() . "</p>
</div>";
exit(1);
}
?>