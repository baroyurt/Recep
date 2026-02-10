<?php
// api.php - BRAND/MODEL AYRIŞTIRILDI + AUTHENTICATION + MAINTENANCE_PERSON
header('Content-Type: application/json; charset=utf-8');
// Session başlat ve kimlik doğrulama kontrolü
session_start();

// Kimlik doğrulama gerekli mi kontrol et
$publicActions = ['login']; // Login işlemi hariç tüm işlemler kimlik doğrulama gerektirir
$action = $_REQUEST['action'] ?? '';

if (!in_array($action, $publicActions) && !isset($_SESSION['user_id'])) {
http_response_code(401);
echo json_encode(['ok' => false, 'error' => 'Oturum açılmamış'], JSON_UNESCAPED_UNICODE);
exit;
}

// Kullanıcı bilgileri
$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['user_role'] ?? 'user';
$username = $_SESSION['username'] ?? 'system';
$isAdmin = ($userRole === 'admin');

require_once __DIR__ . '/config.php';
try {
$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
} catch (Exception $e) {
http_response_code(500);
echo json_encode(['ok'=>false,'error'=>'DB bağlantı hatası'], JSON_UNESCAPED_UNICODE);
exit;
}
$allowedRooms = ['ALÇAK TAVAN','YÜKSEK TAVAN','YENİ VİP SALON','ALT SALON'];
function jsonExit($data, $code=200){
http_response_code($code);
echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit;
}

// Admin kontrolü gerektiren işlemler
$adminOnlyActions = ['create', 'delete', 'move_group', 'batch_update', 'import_csv'];
if (in_array($action, $adminOnlyActions) && !$isAdmin) {
jsonExit(['ok' => false, 'error' => 'Bu işlem için yönetici yetkisi gerekli'], 403);
}

if ($action === 'list') {
$room = $_GET['room'] ?? '';
if (!in_array($room, $allowedRooms)) jsonExit(['ok'=>false,'error'=>'Geçersiz salon'],400);
$stmt = $pdo->prepare("SELECT * FROM machines WHERE room = :room ORDER BY id");
$stmt->execute([':room'=>$room]);
$machines = $stmt->fetchAll();
// Geriye uyumluluk için brand_model alanı ekle
foreach ($machines as &$m) {
$m['brand_model'] = $m['brand'] . ' ' . $m['model'];
}
jsonExit(['ok'=>true,'machines'=>$machines]);
}
if ($action === 'create') {
$room = $_POST['room'] ?? '';
if (!in_array($room, $allowedRooms)) jsonExit(['ok'=>false,'error'=>'Geçersiz salon'],400);
$number = trim($_POST['machine_number'] ?? '');
$brand = trim($_POST['brand'] ?? '');
$model = trim($_POST['model'] ?? '');
$gameType = trim($_POST['game_type'] ?? '');
$date = trim($_POST['maintenance_date'] ?? '');
$maintenancePerson = trim($_POST['maintenance_person'] ?? '');
$note = trim($_POST['note'] ?? '');
$x = (int)($_POST['x'] ?? 30);
$y = (int)($_POST['y'] ?? 30);
$size = (int)($_POST['size'] ?? 63);
$rotation = (int)($_POST['rotation'] ?? 0);
if ($number === '' || $brand === '' || $model === '' || $date === '') {
jsonExit(['ok'=>false,'error'=>'Eksik alan'],400);
}
$stmt = $pdo->prepare("INSERT INTO machines (room, machine_number, brand, model, game_type, maintenance_date, maintenance_person, note, x, y, size, rotation, created_at, updated_at) VALUES (:room,:num,:brand,:model,:gameType,:date,:maintenancePerson,:note,:x,:y,:size,:rotation,NOW(),NOW())");
$stmt->execute([
':room'=>$room, ':num'=>$number, ':brand'=>$brand, ':model'=>$model, ':gameType'=>$gameType, ':date'=>$date, ':maintenancePerson'=>$maintenancePerson, ':note'=>$note,
':x'=>$x, ':y'=>$y, ':size'=>$size, ':rotation'=>$rotation
]);
$id = $pdo->lastInsertId();
// History'ye kaydet
$stmt = $pdo->prepare("
INSERT INTO maintenance_history
(machine_id, action_type, details, performed_by)
VALUES (:machineId, 'created', :details, :performedBy)
");
$stmt->execute([
':machineId'=>$id,
':details'=>"Makina oluşturuldu: {$number} - {$brand} {$model} ({$room})",
':performedBy'=>$username
]);
$stmt = $pdo->prepare("SELECT * FROM machines WHERE id = :id");
$stmt->execute([':id'=>$id]);
$machine = $stmt->fetch();
// Geriye uyumluluk için brand_model ekle
$machine['brand_model'] = $machine['brand'] . ' ' . $machine['model'];
jsonExit(['ok'=>true,'machine'=>$machine],201);
}
if ($action === 'move') {
$id = (int)($_POST['id'] ?? 0);
$x = (int)($_POST['x'] ?? 0);
$y = (int)($_POST['y'] ?? 0);
if (!$id) jsonExit(['ok'=>false,'error'=>'Eksik id'],400);
$stmt = $pdo->prepare("UPDATE machines SET x=:x,y=:y,updated_at=NOW() WHERE id=:id");
$stmt->execute([':x'=>$x,':y'=>$y,':id'=>$id]);
jsonExit(['ok'=>true]);
}
if ($action === 'rotate') {
$id = (int)($_POST['id'] ?? 0);
$rotation = (int)($_POST['rotation'] ?? 0);
if (!$id) jsonExit(['ok'=>false,'error'=>'Eksik id'],400);
$stmt = $pdo->prepare("UPDATE machines SET rotation=:rotation,updated_at=NOW() WHERE id=:id");
$stmt->execute([':rotation'=>$rotation,':id'=>$id]);
jsonExit(['ok'=>true]);
}
if ($action === 'update') {
$id = (int)($_POST['id'] ?? 0);
if (!$id) jsonExit(['ok'=>false,'error'=>'Eksik id'],400);
$number = trim($_POST['machine_number'] ?? '');
$brand = trim($_POST['brand'] ?? '');
$model = trim($_POST['model'] ?? '');
$gameType = trim($_POST['game_type'] ?? '');
$date = trim($_POST['maintenance_date'] ?? '');
$maintenancePerson = trim($_POST['maintenance_person'] ?? '');
$note = trim($_POST['note'] ?? '');
if ($number === '' || $brand === '' || $model === '' || $date === '') {
jsonExit(['ok'=>false,'error'=>'Eksik alan'],400);
}
// Eski değerleri al (history için)
$stmt = $pdo->prepare("SELECT * FROM machines WHERE id=:id");
$stmt->execute([':id'=>$id]);
$oldMachine = $stmt->fetch();
$stmt = $pdo->prepare("UPDATE machines SET machine_number=:num, brand=:brand, model=:model, game_type=:gameType, maintenance_date=:date, maintenance_person=:maintenancePerson, note=:note, updated_at=NOW() WHERE id=:id");
$stmt->execute([
':num'=>$number, ':brand'=>$brand, ':model'=>$model, ':gameType'=>$gameType, ':date'=>$date, ':maintenancePerson'=>$maintenancePerson, ':note'=>$note, ':id'=>$id
]);
// History'ye kaydet
if ($oldMachine) {
$changes = [];
if ($oldMachine['machine_number'] !== $number) $changes[] = "Makina No: {$oldMachine['machine_number']} → {$number}";
if ($oldMachine['brand'] !== $brand) $changes[] = "Marka: {$oldMachine['brand']} → {$brand}";
if ($oldMachine['model'] !== $model) $changes[] = "Model: {$oldMachine['model']} → {$model}";
if ($oldMachine['game_type'] !== $gameType) $changes[] = "Oyun Çeşidi: {$oldMachine['game_type']} → {$gameType}";
if ($oldMachine['maintenance_date'] !== $date) $changes[] = "Bakım Tarihi: {$oldMachine['maintenance_date']} → {$date}";
if (($oldMachine['maintenance_person'] ?? '') !== $maintenancePerson) $changes[] = "Bakım Yapan: {$oldMachine['maintenance_person']} → {$maintenancePerson}";
if ($oldMachine['note'] !== $note) $changes[] = "Not güncellendi";
if (!empty($changes)) {
$stmt = $pdo->prepare("
INSERT INTO maintenance_history
(machine_id, action_type, details, performed_by)
VALUES (:machineId, 'updated', :details, :performedBy)
");
$stmt->execute([
':machineId'=>$id,
':details'=>implode(', ', $changes),
':performedBy'=>$username
]);
}
// Eğer bakım tarihi güncellendiyse, ayrı bir maintenance kaydı da ekle
if ($oldMachine['maintenance_date'] !== $date) {
$stmt = $pdo->prepare("
INSERT INTO maintenance_history
(machine_id, action_type, details, old_value, new_value, performed_by)
VALUES (:machineId, 'maintenance', 'Bakım tarihi güncellendi', :oldDate, :newDate, :performedBy)
");
$stmt->execute([
':machineId'=>$id,
':oldDate'=>$oldMachine['maintenance_date'],
':newDate'=>$date,
':performedBy'=>$username
]);
}
}
jsonExit(['ok'=>true]);
}
if ($action === 'delete') {
$id = (int)($_POST['id'] ?? 0);
if (!$id) jsonExit(['ok'=>false,'error'=>'Eksik id'],400);
$stmt = $pdo->prepare("DELETE FROM machines WHERE id=:id");
$stmt->execute([':id'=>$id]);
jsonExit(['ok'=>true]);
}
if ($action === 'get') {
$id = (int)($_GET['id'] ?? 0);
if (!$id) jsonExit(['ok'=>false,'error'=>'Eksik id'],400);
$stmt = $pdo->prepare("SELECT * FROM machines WHERE id = :id");
$stmt->execute([':id'=>$id]);
$machine = $stmt->fetch();
if (!$machine) jsonExit(['ok'=>false,'error'=>'Makina bulunamadı'],404);
// Geriye uyumluluk için brand_model ekle
$machine['brand_model'] = $machine['brand'] . ' ' . $machine['model'];
jsonExit(['ok'=>true,'machine'=>$machine]);
}
// Grup hareketi
if ($action === 'move_group') {
$groupData = json_decode($_POST['group_data'] ?? '[]', true);
if (!is_array($groupData) || empty($groupData)) {
jsonExit(['ok'=>false,'error'=>'Geçersiz grup verisi'],400);
}
$pdo->beginTransaction();
try {
foreach ($groupData as $item) {
$id = (int)($item['id'] ?? 0);
$x = (int)($item['x'] ?? 0);
$y = (int)($item['y'] ?? 0);
if ($id) {
$stmt = $pdo->prepare("UPDATE machines SET x=:x, y=:y, updated_at=NOW() WHERE id=:id");
$stmt->execute([':x'=>$x, ':y'=>$y, ':id'=>$id]);
}
}
$pdo->commit();
jsonExit(['ok'=>true]);
} catch (Exception $e) {
$pdo->rollBack();
jsonExit(['ok'=>false,'error'=>'Grup hareketi hatası'],500);
}
}
// TOPLU GÜNCELLEME
if ($action === 'batch_update') {
$ids = $_POST['ids'] ?? '';
$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';
if (empty($ids) || empty($field) || empty($value)) {
jsonExit(['ok'=>false,'error'=>'Eksik alan'],400);
}
$allowedFields = ['maintenance_date', 'note', 'room', 'brand', 'model', 'game_type'];
if (!in_array($field, $allowedFields)) {
jsonExit(['ok'=>false,'error'=>'Geçersiz alan'],400);
}
if ($field === 'room' && !in_array($value, $allowedRooms)) {
jsonExit(['ok'=>false,'error'=>'Geçersiz salon'],400);
}
$idArray = explode(',', $ids);
$idArray = array_filter($idArray, 'is_numeric');
if (empty($idArray)) {
jsonExit(['ok'=>false,'error'=>'Geçersiz ID listesi'],400);
}
$placeholders = str_repeat('?,', count($idArray) - 1) . '?';
$sql = "UPDATE machines SET $field = ?, updated_at = NOW() WHERE id IN ($placeholders)";
$params = array_merge([$value], $idArray);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
jsonExit(['ok'=>true,'updated_count'=>$stmt->rowCount()]);
}
// MAKİNA GEÇMİŞİNİ GÖRÜNTÜLE
if ($action === 'get_history') {
$machineId = (int)($_GET['machine_id'] ?? 0);
if (!$machineId) jsonExit(['ok'=>false,'error'=>'Eksik machine_id'],400);
$stmt = $pdo->prepare("
SELECT * FROM maintenance_history
WHERE machine_id = :machineId
ORDER BY created_at DESC
");
$stmt->execute([':machineId'=>$machineId]);
$history = $stmt->fetchAll();
jsonExit(['ok'=>true,'history'=>$history]);
}
// MAKİNANIN ARIZALARINI GÖRÜNTÜLE
if ($action === 'get_faults') {
$machineId = (int)($_GET['machine_id'] ?? 0);
if (!$machineId) jsonExit(['ok'=>false,'error'=>'Eksik machine_id'],400);
$stmt = $pdo->prepare("
SELECT * FROM machine_faults
WHERE machine_id = :machineId
ORDER BY reported_date DESC
");
$stmt->execute([':machineId'=>$machineId]);
$faults = $stmt->fetchAll();
jsonExit(['ok'=>true,'faults'=>$faults]);
}
// TÜM ARIZALARI LİSTELE
if ($action === 'list_all_faults') {
$status = $_GET['status'] ?? 'all';
$sql = "
SELECT mf.*, m.machine_number, m.brand, m.model, m.game_type, m.room
FROM machine_faults mf
LEFT JOIN machines m ON mf.machine_id = m.id
";
if ($status !== 'all') {
$sql .= " WHERE mf.status = :status";
}
$sql .= " ORDER BY mf.reported_date DESC";
$stmt = $pdo->prepare($sql);
if ($status !== 'all') {
$stmt->execute([':status'=>$status]);
} else {
$stmt->execute();
}
$faults = $stmt->fetchAll();
jsonExit(['ok'=>true,'faults'=>$faults]);
}
// ARIZA DURUMUNU GÜNCELLE
if ($action === 'update_fault_status') {
$faultId = (int)($_POST['fault_id'] ?? 0);
$status = $_POST['status'] ?? '';
if (!$faultId || !$status) jsonExit(['ok'=>false,'error'=>'Eksik alan'],400);
$allowedStatuses = ['open', 'in_progress', 'resolved'];
if (!in_array($status, $allowedStatuses)) {
jsonExit(['ok'=>false,'error'=>'Geçersiz durum'],400);
}
// Update fault status
if ($status === 'resolved') {
$stmt = $pdo->prepare("
UPDATE machine_faults
SET status = :status,
resolved_date = NOW(),
updated_at = NOW()
WHERE id = :faultId
");
} else {
$stmt = $pdo->prepare("
UPDATE machine_faults
SET status = :status,
resolved_date = NULL,
updated_at = NOW()
WHERE id = :faultId
");
}
$stmt->execute([':status'=>$status, ':faultId'=>$faultId]);
// History'ye ekle
$stmt = $pdo->prepare("SELECT machine_id FROM machine_faults WHERE id = :faultId");
$stmt->execute([':faultId'=>$faultId]);
$fault = $stmt->fetch();
if ($fault && $fault['machine_id']) {
$stmt = $pdo->prepare("
INSERT INTO maintenance_history
(machine_id, action_type, details, performed_by)
VALUES (:machineId, 'repair', :details, 'user')
");
$stmt->execute([
':machineId'=>$fault['machine_id'],
':details'=>"Arıza durumu güncellendi: {$status}"
]);
}
jsonExit(['ok'=>true]);
}
// TRELLO ENTEGRASYONU: Konfigürasyon kaydet
if ($action === 'trello_save_config') {
require_once __DIR__ . '/integrations/trello_connector.php';
$apiKey = trim($_POST['api_key'] ?? '');
$apiToken = trim($_POST['api_token'] ?? '');
$boardId = trim($_POST['board_id'] ?? '');
$listId = trim($_POST['list_id'] ?? '');
if (empty($apiKey) || empty($apiToken)) {
jsonExit(['ok'=>false,'error'=>'API key ve token gerekli'],400);
}
try {
$trello = new TrelloConnector($pdo);
$trello->saveConfig($apiKey, $apiToken, $boardId, $listId);
jsonExit(['ok'=>true,'message'=>'Trello konfigürasyonu kaydedildi']);
} catch (Exception $e) {
jsonExit(['ok'=>false,'error'=>$e->getMessage()],500);
}
}
// TRELLO ENTEGRASYONU: Konfigürasyon görüntüle
if ($action === 'trello_get_config') {
require_once __DIR__ . '/integrations/trello_connector.php';
try {
$trello = new TrelloConnector($pdo);
$config = $trello->getConfig();
// API token'ı maskele
if ($config && !empty($config['api_token'])) {
$config['api_token_masked'] = substr($config['api_token'], 0, 8) . '...' . substr($config['api_token'], -4);
unset($config['api_token']);
}
if ($config && !empty($config['api_key'])) {
$config['api_key_masked'] = substr($config['api_key'], 0, 8) . '...' . substr($config['api_key'], -4);
unset($config['api_key']);
}
jsonExit(['ok'=>true,'config'=>$config]);
} catch (Exception $e) {
jsonExit(['ok'=>false,'error'=>$e->getMessage()],500);
}
}
// TRELLO ENTEGRASYONU: Senkronize et
if ($action === 'trello_sync') {
require_once __DIR__ . '/integrations/trello_connector.php';
try {
$trello = new TrelloConnector($pdo);
$result = $trello->syncFaults();
jsonExit($result);
} catch (Exception $e) {
jsonExit(['ok'=>false,'error'=>$e->getMessage()],500);
}
}
// TRELLO ENTEGRASYONU: Board'ları listele
if ($action === 'trello_list_boards') {
require_once __DIR__ . '/integrations/trello_connector.php';
try {
$trello = new TrelloConnector($pdo);
$boards = $trello->listBoards();
jsonExit(['ok'=>true,'boards'=>$boards]);
} catch (Exception $e) {
jsonExit(['ok'=>false,'error'=>$e->getMessage()],500);
}
}
// TRELLO ENTEGRASYONU: Board listelerini getir
if ($action === 'trello_get_lists') {
require_once __DIR__ . '/integrations/trello_connector.php';
$boardId = $_GET['board_id'] ?? '';
if (empty($boardId)) jsonExit(['ok'=>false,'error'=>'Board ID gerekli'],400);
try {
$trello = new TrelloConnector($pdo);
$lists = $trello->getBoardLists($boardId);
jsonExit(['ok'=>true,'lists'=>$lists]);
} catch (Exception $e) {
jsonExit(['ok'=>false,'error'=>$e->getMessage()],500);
}
}
// MAKİNA SAYILARINI GETIR
if ($action === 'get_machine_counts') {
// Tüm makinalar
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM machines");
$stmt->execute();
$totalCount = $stmt->fetch()['total'];
// Odaya göre sayılar
$stmt = $pdo->prepare("SELECT room, COUNT(*) as count FROM machines GROUP BY room");
$stmt->execute();
$roomCounts = $stmt->fetchAll();
$counts = ['total' => $totalCount, 'rooms' => []];
foreach ($roomCounts as $rc) {
$counts['rooms'][$rc['room']] = $rc['count'];
}
jsonExit(['ok'=>true,'counts'=>$counts]);
}
// CSV İÇE AKTAR
if ($action === 'import_csv') {
// Admin kontrolü zaten adminOnlyActions array'inde yapılıyor
if (!isset($_FILES['csv_file'])) {
jsonExit(['ok'=>false,'error'=>'CSV dosyası yüklenmedi'],400);
}
$file = $_FILES['csv_file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
jsonExit(['ok'=>false,'error'=>'Dosya yükleme hatası'],400);
}
$filePath = $file['tmp_name'];
$imported = 0;
$errors = [];

// Makina pozisyon sabitleri
define('CSV_IMPORT_MIN_X', 100);
define('CSV_IMPORT_MAX_X', 900);
define('CSV_IMPORT_MIN_Y', 100);
define('CSV_IMPORT_MAX_Y', 600);

try {
$pdo->beginTransaction();
if (($handle = fopen($filePath, 'r')) !== false) {
// İlk satırı (başlık) atla
$header = fgetcsv($handle);
$defaultDate = date('Y-m-d');
while (($row = fgetcsv($handle)) !== false) {
if (count($row) < 3) continue; // En az 3 sütun olmalı
// CSV formatı: Sıra,Salon,Makine No,Marka,Model,Oyun Türü
$machineNumber = trim($row[2] ?? '');
$room = trim($row[1] ?? '');
$brand = trim($row[3] ?? 'EGT');
$model = trim($row[4] ?? '');
$gameType = trim($row[5] ?? 'Slot');
if (empty($machineNumber) || empty($room)) {
$errors[] = "Geçersiz satır: " . implode(',', $row);
continue;
}
// Salon adını kontrol et ve uyarla
if (!in_array($room, $allowedRooms)) {
// Yakın eşleşme bul
$foundRoom = null;
foreach ($allowedRooms as $ar) {
if (stripos($ar, $room) !== false || stripos($room, $ar) !== false) {
$foundRoom = $ar;
break;
}
}
if (!$foundRoom) {
$errors[] = "Geçersiz salon: $room (Makina: $machineNumber)";
continue;
}
$room = $foundRoom;
}
// Aynı makina numarası zaten varsa atla
$stmt = $pdo->prepare("SELECT id FROM machines WHERE machine_number = :num");
$stmt->execute([':num' => $machineNumber]);
if ($stmt->fetch()) {
continue; // Duplicate, skip
}
// Random pozisyon
$x = rand(CSV_IMPORT_MIN_X, CSV_IMPORT_MAX_X);
$y = rand(CSV_IMPORT_MIN_Y, CSV_IMPORT_MAX_Y);
$stmt = $pdo->prepare("
INSERT INTO machines
(room, machine_number, brand, model, game_type, maintenance_date, x, y, size, rotation)
VALUES (:room, :num, :brand, :model, :gameType, :date, :x, :y, 63, 0)
");
$stmt->execute([
':room' => $room,
':num' => $machineNumber,
':brand' => $brand,
':model' => $model,
':gameType' => $gameType,
':date' => $defaultDate,
':x' => $x,
':y' => $y
]);
$imported++;
}
fclose($handle);
}
$pdo->commit();
jsonExit([
'ok' => true,
'imported' => $imported,
'errors' => $errors,
'message' => "$imported makina başarıyla içe aktarıldı"
]);
} catch (Exception $e) {
$pdo->rollBack();
jsonExit(['ok'=>false,'error'=>'İçe aktarma hatası: ' . $e->getMessage()],500);
}
}
jsonExit(['ok'=>false,'error'=>'Bilinmeyen action'],400);
?>