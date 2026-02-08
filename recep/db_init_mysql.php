<?php
// db_init_mysql.php - VIP MAKİNELER EKLENDİ
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host={$dbHost};charset={$charset}", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // DB'yi TAM SIFIRLA
    $pdo->exec("DROP DATABASE IF EXISTS `slot_db`");
    $pdo->exec("CREATE DATABASE `slot_db` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");
    $pdo->exec("USE `slot_db`");

    // Machines tablosu
    $pdo->exec("
    CREATE TABLE `machines` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `room` VARCHAR(64) NOT NULL,
        `machine_number` VARCHAR(64) NOT NULL,
        `brand_model` VARCHAR(128) NOT NULL,
        `maintenance_date` DATE NOT NULL,
        `note` TEXT,
        `x` INT NOT NULL DEFAULT 30,
        `y` INT NOT NULL DEFAULT 30,
        `size` INT NOT NULL DEFAULT 63,
        `rotation` INT NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_room (room),
        INDEX idx_machine_number (machine_number),
        INDEX idx_maintenance_date (maintenance_date)
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
    
    // YENİ VİP SALON için makineleri ekle
    echo "<p style='color:#4caf50;'>📦 YENİ VİP SALON makineleri ekleniyor...</p>";
    
    $vipMachines = [
        ['2192', 'EGT'],
        ['2194', 'EGT'],
        ['2635', 'EGT'],
        ['2770', 'EGT'],
        ['2361', 'EGT'],
        ['2362', 'EGT'],
        ['2363', 'EGT'],
        ['2364', 'EGT'],
        ['2257', 'EGT'],
        ['2258', 'EGT'],
        ['2259', 'EGT'],
        ['2260', 'EGT'],
        ['3072', 'XTENSİON LİNK'],
        ['3073', 'XTENSİON LİNK'],
        ['3074', 'XTENSİON LİNK'],
        ['3075', 'XTENSİON LİNK'],
        ['3076', 'XTENSİON LİNK'],
        ['3077', 'XTENSİON LİNK'],
        ['3078', 'XTENSİON LİNK'],
        ['3079', 'XTENSİON LİNK'],
        ['2946', 'VİP EGT'],
        ['2947', 'VİP EGT'],
        ['2738', 'VİP EGT'],
        ['2948', 'VİP EGT'],
        ['2949', 'VİP EGT'],
        ['2443', 'VİP EGT'],
        ['2604', 'VİP EGT'],
        ['2607', 'VİP EGT'],
        ['2722', 'VİP EGT'],
        ['2723', 'VİP EGT'],
        ['2724', 'VİP EGT'],
        ['2725', 'VİP EGT'],
        ['2726', 'VİP EGT'],
        ['2727', 'VİP EGT'],
        ['2728', 'VİP EGT'],
        ['2729', 'VİP EGT'],
        ['2730', 'VİP EGT'],
        ['2731', 'VİP EGT'],
        ['2732', 'VİP EGT'],
        ['2969', 'VİP EGT'],
        ['2970', 'VİP EGT'],
        ['2971', 'VİP EGT'],
        ['3037', 'VİP EGT'],
        ['3038', 'VİP EGT'],
        ['3051', 'VİP EGT'],
        ['3052', 'VİP EGT'],
        ['3053', 'VİP EGT'],
        ['3054', 'VİP EGT'],
        ['3055', 'VİP EGT'],
        ['3056', 'VİP EGT']
    ];
    
    $maintenance_date = date('Y-m-d');
    
    $stmt = $pdo->prepare("INSERT INTO machines (room, machine_number, brand_model, maintenance_date, x, y, size, rotation) VALUES (?, ?, ?, ?, ?, ?, 63, 0)");
    
    foreach ($vipMachines as $index => $machine) {
        // Random pozisyon (100-900px arası, eşit dağılım için)
        $x = rand(100, 900);
        $y = rand(100, 600);
        
        $stmt->execute([
            'YENİ VİP SALON',
            $machine[0],
            $machine[1],
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
    echo "<p>Makinalar random pozisyonlarda yerleştirildi.</p>";
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