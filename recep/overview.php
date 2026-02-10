<?php
// overview.php - Tüm Salonların Şematik Görünümü
session_start();

// Kimlik doğrulama kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php';

$username = $_SESSION['username'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'user';
$fullName = $_SESSION['full_name'] ?? $username;
$isAdmin = ($userRole === 'admin');

$rooms = ['ALÇAK TAVAN', 'YÜKSEK TAVAN', 'YENİ VİP SALON', 'ALT SALON'];

// Veritabanı bağlantısı
try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
} catch (Exception $e) {
    die("Veritabanı bağlantı hatası");
}

// Her salon için makina sayıları ve bakım durumları
$roomStats = [];
foreach ($rooms as $room) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM machines WHERE room = :room");
    $stmt->execute([':room' => $room]);
    $total = $stmt->fetch()['total'];
    
    // Bakım durumlarına göre sayılar (0-45 yeşil, 45-60 mavi, 60+ kırmızı)
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN DATEDIFF(CURDATE(), maintenance_date) <= 45 THEN 1 ELSE 0 END) as green,
            SUM(CASE WHEN DATEDIFF(CURDATE(), maintenance_date) > 45 AND DATEDIFF(CURDATE(), maintenance_date) <= 60 THEN 1 ELSE 0 END) as blue,
            SUM(CASE WHEN DATEDIFF(CURDATE(), maintenance_date) > 60 THEN 1 ELSE 0 END) as red
        FROM machines 
        WHERE room = :room
    ");
    $stmt->execute([':room' => $room]);
    $status = $stmt->fetch();
    
    $roomStats[$room] = [
        'total' => $total,
        'green' => $status['green'] ?? 0,
        'blue' => $status['blue'] ?? 0,
        'red' => $status['red'] ?? 0
    ];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salonlar Genel Görünümü - Casino Bakım Takip</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            padding: 20px;
            background: linear-gradient(180deg, #0b0b0b, #070707);
            min-height: 100vh;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(20, 20, 20, 0.95);
            border-radius: 15px;
            border: 2px solid rgba(201, 169, 79, 0.3);
        }
        .header h1 {
            color: #c9a94f;
            margin: 0;
            font-size: 28px;
        }
        .nav-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: linear-gradient(145deg, #c9a94f, #9a7728);
            color: #000;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .nav-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(201, 169, 79, 0.4);
        }
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .room-card {
            background: linear-gradient(145deg, rgba(40, 40, 40, 0.95), rgba(30, 30, 30, 0.9));
            padding: 30px;
            border-radius: 15px;
            border: 2px solid rgba(201, 169, 79, 0.3);
            transition: all 0.3s;
            cursor: pointer;
        }
        .room-card:hover {
            border-color: rgba(201, 169, 79, 0.6);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .room-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(201, 169, 79, 0.2);
        }
        .room-name {
            font-size: 24px;
            font-weight: 700;
            color: #c9a94f;
        }
        .room-total {
            font-size: 32px;
            font-weight: 700;
            color: #fff;
        }
        .status-bars {
            margin-top: 20px;
        }
        .status-bar {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            gap: 15px;
        }
        .status-label {
            min-width: 150px;
            font-size: 14px;
            color: #ddd;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .status-indicator {
            width: 16px;
            height: 16px;
            border-radius: 50%;
        }
        .status-indicator.green {
            background: #2ecc71;
            box-shadow: 0 0 10px rgba(46, 204, 113, 0.5);
        }
        .status-indicator.blue {
            background: #3498db;
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.5);
        }
        .status-indicator.red {
            background: #e74c3c;
            box-shadow: 0 0 10px rgba(231, 76, 60, 0.5);
        }
        .status-progress {
            flex: 1;
            height: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        .status-fill {
            height: 100%;
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: #000;
        }
        .status-fill.green {
            background: linear-gradient(90deg, #2ecc71, #27ae60);
        }
        .status-fill.blue {
            background: linear-gradient(90deg, #3498db, #2980b9);
        }
        .status-fill.red {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
        }
        .status-count {
            min-width: 40px;
            text-align: right;
            font-weight: 600;
            color: #fff;
            font-size: 16px;
        }
        .legend {
            background: rgba(20, 20, 20, 0.95);
            padding: 25px;
            border-radius: 15px;
            border: 2px solid rgba(201, 169, 79, 0.3);
            margin-top: 30px;
        }
        .legend h3 {
            color: #c9a94f;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .legend-items {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ddd;
            font-size: 14px;
        }
        @media (max-width: 768px) {
            .rooms-grid {
                grid-template-columns: 1fr;
            }
            .status-label {
                min-width: 120px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-th-large"></i> SALONLAR GENEL GÖRÜNÜMÜ</h1>
            <a href="index.php" class="nav-back">
                <i class="fas fa-arrow-left"></i> Ana Sayfaya Dön
            </a>
        </div>

        <div class="rooms-grid">
            <?php foreach ($rooms as $room): 
                $stats = $roomStats[$room];
                $total = $stats['total'];
                $greenPercent = $total > 0 ? ($stats['green'] / $total) * 100 : 0;
                $bluePercent = $total > 0 ? ($stats['blue'] / $total) * 100 : 0;
                $redPercent = $total > 0 ? ($stats['red'] / $total) * 100 : 0;
            ?>
            <div class="room-card" onclick="window.location.href='index.php?room=<?php echo urlencode($room); ?>'">
                <div class="room-header">
                    <div class="room-name">
                        <i class="fas fa-door-open"></i> <?php echo htmlspecialchars($room); ?>
                    </div>
                    <div class="room-total">
                        <?php echo $total; ?> <small style="font-size: 16px; color: #999;">makina</small>
                    </div>
                </div>

                <div class="status-bars">
                    <div class="status-bar">
                        <div class="status-label">
                            <span class="status-indicator green"></span>
                            <span>Bakım Yapıldı</span>
                        </div>
                        <div class="status-progress">
                            <div class="status-fill green" style="width: <?php echo $greenPercent; ?>%">
                                <?php if ($greenPercent > 15) echo round($greenPercent) . '%'; ?>
                            </div>
                        </div>
                        <div class="status-count"><?php echo $stats['green']; ?></div>
                    </div>

                    <div class="status-bar">
                        <div class="status-label">
                            <span class="status-indicator blue"></span>
                            <span>Bakım Yaklaşıyor</span>
                        </div>
                        <div class="status-progress">
                            <div class="status-fill blue" style="width: <?php echo $bluePercent; ?>%">
                                <?php if ($bluePercent > 15) echo round($bluePercent) . '%'; ?>
                            </div>
                        </div>
                        <div class="status-count"><?php echo $stats['blue']; ?></div>
                    </div>

                    <div class="status-bar">
                        <div class="status-label">
                            <span class="status-indicator red"></span>
                            <span>Bakım Gerekli</span>
                        </div>
                        <div class="status-progress">
                            <div class="status-fill red" style="width: <?php echo $redPercent; ?>%">
                                <?php if ($redPercent > 15) echo round($redPercent) . '%'; ?>
                            </div>
                        </div>
                        <div class="status-count"><?php echo $stats['red']; ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="legend">
            <h3><i class="fas fa-info-circle"></i> Bakım Durumu Açıklamaları</h3>
            <div class="legend-items">
                <div class="legend-item">
                    <span class="status-indicator green"></span>
                    <span><strong>0-45 gün:</strong> Bakım yapıldı (İyi durumda)</span>
                </div>
                <div class="legend-item">
                    <span class="status-indicator blue"></span>
                    <span><strong>45-60 gün:</strong> Bakım yaklaşıyor (Dikkat)</span>
                </div>
                <div class="legend-item">
                    <span class="status-indicator red"></span>
                    <span><strong>60+ gün:</strong> Bakım gerekli (Acil)</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
