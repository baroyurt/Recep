<?php
// index.php - BRAND/MODEL AYRIŞTIRILDI + ARAMA ÇUBUĞU + AUTHENTICATION
session_start();

// Kimlik doğrulama kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// CSRF Token oluştur
if (empty($_SESSION['csrf_token'])) {
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Kullanıcı bilgileri
$username = $_SESSION['username'] ?? 'User';
$userRole = $_SESSION['user_role'] ?? 'user';
$fullName = $_SESSION['full_name'] ?? $username;
$isAdmin = ($userRole === 'admin');

$rooms = ['ALÇAK TAVAN', 'YÜKSEK TAVAN', 'YENİ VİP SALON', 'ALT SALON'];
$cache_buster = time();
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
<title>CASİNO BAKIM TAKİP PROGRAMI</title>
<link rel="stylesheet" href="css/style.css?v=<?php echo $cache_buster; ?>" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
<header class="topbar">
<h1>CASİNO BAKIM TAKİP PROGRAMI</h1>
<div class="topbar-right">
<!-- USER INFO -->
<div class="user-info">
<i class="fas fa-user-circle"></i>
<span class="user-name"><?php echo htmlspecialchars($fullName); ?></span>
<?php if ($isAdmin): ?>
<span class="user-badge admin-badge" title="Yönetici">👑 Admin</span>
<?php else: ?>
<span class="user-badge regular-user-badge" title="Kullanıcı">👤 User</span>
<?php endif; ?>
<a href="logout.php" class="logout-btn" title="Çıkış Yap">
<i class="fas fa-sign-out-alt"></i>
</a>
</div>
<!-- ARAMA ÇUBUĞU -->
<div class="search-container">
<div class="search-wrapper">
<input type="text" id="machine-search" placeholder="Makina No Ara (Örn: 2192)"
title="Makina numarası girin (sadece rakam)">
<button id="search-btn" title="Makina Ara">
<i class="fas fa-search"></i>
</button>
<button id="clear-search" title="Aramayı Temizle" style="display:none;">
<i class="fas fa-times"></i>
</button>
</div>
<div class="search-results" id="search-results"></div>
</div>
<!-- TRELLO VE ARIZA YÖNETİMİ -->
<div class="action-buttons-container">
<a href="statistics.php" class="action-btn statistics-btn" title="İstatistiksel analizler ve raporlar">
<i class="fas fa-chart-bar"></i>
<span>İstatistikler</span>
</a>
<a href="trello_settings.php" class="action-btn trello-btn" title="Trello entegrasyonu ayarları">
<i class="fab fa-trello"></i>
<span>Trello</span>
</a>
<a href="faults.php" class="action-btn faults-btn" title="Arıza yönetimi ve takibi">
<i class="fas fa-exclamation-triangle"></i>
<span>Arızalar</span>
</a>
<a href="backup.php" class="action-btn backup-btn" title="Veritabanı yedekleme ve geri yükleme">
<i class="fas fa-database"></i>
<span>Yedek</span>
</a>
</div>
</div>
<nav class="rooms">
<?php foreach($rooms as $i=>$r): ?>
<button class="room-btn" data-room="<?php echo htmlspecialchars($r, ENT_QUOTES); ?>"><?php echo htmlspecialchars($r); ?></button>
<?php endforeach; ?>
</nav>
</header>
<main>
<section class="controls">
<div class="room-label">Salon: <span id="current-room"><?php echo $rooms[0]; ?></span></div>
<?php if ($isAdmin): ?>
<button id="add-machine">➕ Makina Oluştur</button>
<button id="import-csv-btn" class="import-btn" title="CSV'den makina ekle">📤 CSV İçe Aktar</button>
<?php endif; ?>
<div class="hint">Makinaları tutup sürükleyin. Yakın konumdayken kenarlara yapışır (snap). Makinaya tıklayıp bilgileri düzenleyebilirsiniz.</div>
</section>
<section id="map" class="map room-<?php echo strtolower(str_replace([' ', 'İ', 'Ö', 'Ü', 'Ş', 'Ç', 'Ğ'], ['-', 'i', 'o', 'u', 's', 'c', 'g'], $rooms[0])); ?>">
<!-- Makinalar burada absolute olarak renderlanacak -->
</section>
</main>
<!-- Modal: Makina Bilgileri -->
<div id="info-modal" class="modal hidden">
<div class="modal-content info-modal">
<h2>💡 MAKİNA BİLGİLERİ</h2>
<div class="machine-info">
<!-- Bakım durumu buraya JavaScript ile eklenecek -->
</div>
<div class="form-actions">
<button id="history-btn">📜 Geçmiş</button>
<button id="faults-btn">⚠️ Arızalar</button>
<button id="edit-btn">✏️ Düzenle</button>
<button id="delete-btn" class="danger">🗑️ Sil</button>
<button id="close-info">Kapat</button>
</div>
</div>
</div>
<!-- Modal: Makina Geçmişi -->
<div id="history-modal" class="modal hidden">
<div class="modal-content" style="max-width: 800px;">
<h2>📜 MAKİNA GEÇMİŞİ</h2>
<div id="history-content" style="max-height: 500px; overflow-y: auto;">
<!-- History buraya JavaScript ile eklenecek -->
</div>
<div class="form-actions">
<button id="close-history">Kapat</button>
</div>
</div>
</div>
<!-- Modal: Makina Arızaları -->
<div id="machine-faults-modal" class="modal hidden">
<div class="modal-content" style="max-width: 800px;">
<h2>⚠️ MAKİNA ARIZALARI</h2>
<div id="machine-faults-content" style="max-height: 500px; overflow-y: auto;">
<!-- Faults buraya JavaScript ile eklenecek -->
</div>
<div class="form-actions">
<button id="close-machine-faults">Kapat</button>
</div>
</div>
</div>
<!-- Modal: Makina Oluştur - GÜNCELLENDİ -->
<div id="modal" class="modal hidden">
<div class="modal-content">
<h2>➕ Makina Oluştur - <span id="modal-room"></span></h2>
<form id="machine-form">
<label>MAKİNA NUMARASI<input name="machine_number" required /></label>
<label>MAKİNA MARKASI<input name="brand" required /></label>
<label>MAKİNA MODELİ<input name="model" required /></label>
<label>OYUN ÇEŞİDİ<input name="game_type" placeholder="Slot, Link, vb." /></label>
<label>BAKIM YAPILDIĞI TARİH<input name="maintenance_date" type="date" required /></label>
<label>BAKIM YAPAN KİŞİ<input name="maintenance_person" placeholder="Bakım yapan teknisyen" /></label>
<label>NOT<textarea name="note" rows="3"></textarea></label>
<div class="form-actions">
<button type="submit">Oluştur</button>
<button type="button" id="cancel">Vazgeç</button>
</div>
</form>
</div>
</div>
<!-- Modal: Makina Düzenle - GÜNCELLENDİ -->
<div id="edit-modal" class="modal hidden">
<div class="modal-content">
<h2>✏️ Makina Düzenle - <span id="edit-room"></span></h2>
<form id="edit-form">
<input type="hidden" name="id" id="edit-id" />
<label>MAKİNA NUMARASI<input name="machine_number" id="edit-number" required /></label>
<label>MAKİNA MARKASI<input name="brand" id="edit-brand" required /></label>
<label>MAKİNA MODELİ<input name="model" id="edit-model" required /></label>
<label>OYUN ÇEŞİDİ<input name="game_type" id="edit-game-type" placeholder="Slot, Link, vb." /></label>
<label>BAKIM YAPILDIĞI TARİH<input name="maintenance_date" id="edit-date" type="date" required /></label>
<label>BAKIM YAPAN KİŞİ<input name="maintenance_person" id="edit-maintenance-person" placeholder="Bakım yapan teknisyen" /></label>
<label>NOT<textarea name="note" id="edit-note" rows="3"></textarea></label>
<div class="form-actions">
<button type="submit">Kaydet</button>
<button type="button" id="cancel-edit">Vazgeç</button>
</div>
</form>
</div>
</div>
<!-- Modal: CSV İçe Aktar -->
<div id="csv-import-modal" class="modal hidden">
<div class="modal-content">
<h2>📤 CSV İÇE AKTAR</h2>
<p style="margin-bottom: 15px; color: #aaa;">CSV dosyasından makinaları sisteme aktarın.</p>
<form id="csv-import-form" enctype="multipart/form-data">
<div class="file-upload-area">
<input type="file" id="csv-file" name="csv_file" accept=".csv" required />
<label for="csv-file" class="file-upload-label">
<i class="fas fa-file-csv"></i>
<span>CSV Dosyası Seçin</span>
</label>
</div>
<div id="csv-preview" style="display:none; margin-top: 15px;">
<h3>Önizleme:</h3>
<div id="csv-preview-content" style="max-height: 200px; overflow-y: auto; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 5px;"></div>
</div>
<div class="form-actions">
<button type="submit" id="csv-upload-btn">Yükle ve İçe Aktar</button>
<button type="button" id="cancel-csv-import">İptal</button>
</div>
</form>
<div id="csv-import-progress" style="display:none; margin-top: 15px;">
<div class="progress-bar">
<div class="progress-bar-fill" id="csv-progress-fill"></div>
</div>
<p id="csv-progress-text" style="text-align: center; margin-top: 10px;">0%</p>
</div>
</div>
</div>
<!-- Machine Counters -->
<div class="machine-counters">
<div class="counter-item">
<i class="fas fa-door-open"></i>
<span id="room-machine-count">0</span>
<small>Bu Salon</small>
</div>
<div class="counter-item">
<i class="fas fa-dice"></i>
<span id="total-machine-count">0</span>
<small>Toplam</small>
</div>
</div>
<script>
const ROOMS = <?php echo json_encode($rooms, JSON_UNESCAPED_UNICODE); ?>;
const USER_ROLE = <?php echo json_encode($userRole); ?>;
const IS_ADMIN = <?php echo json_encode($isAdmin); ?>;
</script>
<script src="js/history.js?v=<?php echo $cache_buster; ?>"></script>
<script src="js/app.js?v=<?php echo $cache_buster; ?>"></script>
</body>
</html>