<?php
// index.php - CSRF TOKEN EKLENDİ + ARAMA ÇUBUĞU EKLENDİ
session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF Token oluştur
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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
<button id="add-machine">➕ Makina Oluştur</button>
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
<button id="edit-btn">✏️ Düzenle</button>
<button id="delete-btn" class="danger">🗑️ Sil</button>
<button id="close-info">Kapat</button>
</div>
</div>
</div>

<!-- Modal: Makina Oluştur -->
<div id="modal" class="modal hidden">
<div class="modal-content">
<h2>➕ Makina Oluştur - <span id="modal-room"></span></h2>
<form id="machine-form">
<label>MAKİNA NUMARASI<input name="machine_number" required /></label>
<label>MAKİNA MARKA/MODELİ<input name="brand_model" required /></label>
<label>BAKIM YAPILDIĞI TARİH<input name="maintenance_date" type="date" required /></label>
<label>NOT<textarea name="note" rows="3"></textarea></label>
<div class="form-actions">
<button type="submit">Oluştur</button>
<button type="button" id="cancel">Vazgeç</button>
</div>
</form>
</div>
</div>

<!-- Modal: Makina Düzenle -->
<div id="edit-modal" class="modal hidden">
<div class="modal-content">
<h2>✏️ Makina Düzenle - <span id="edit-room"></span></h2>
<form id="edit-form">
<input type="hidden" name="id" id="edit-id" />
<label>MAKİNA NUMARASI<input name="machine_number" id="edit-number" required /></label>
<label>MAKİNA MARKA/MODELİ<input name="brand_model" id="edit-brand" required /></label>
<label>BAKIM YAPILDIĞI TARİH<input name="maintenance_date" id="edit-date" type="date" required /></label>
<label>NOT<textarea name="note" id="edit-note" rows="3"></textarea></label>
<div class="form-actions">
<button type="submit">Kaydet</button>
<button type="button" id="cancel-edit">Vazgeç</button>
</div>
</form>
</div>
</div>

<script>
const ROOMS = <?php echo json_encode($rooms, JSON_UNESCAPED_UNICODE); ?>;
</script>
<script src="js/app.js?v=<?php echo $cache_buster; ?>"></script>
</body>
</html>