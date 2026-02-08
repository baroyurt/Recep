<?php
// api.php - BASİTLEŞTİRİLMİŞ
header('Content-Type: application/json; charset=utf-8');

// Session başlat
session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dbHost = '127.0.0.1';
$dbName = 'slot_db';
$dbUser = 'root';
$dbPass = '';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'DB bağlantı hatası'], JSON_UNESCAPED_UNICODE);
    exit;
}

$allowedRooms = ['ALÇAK TAVAN','YÜKSEK TAVAN','YENİ VİP SALON','ALT SALON'];
$action = $_REQUEST['action'] ?? '';

function jsonExit($data, $code=200){
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'list') {
    $room = $_GET['room'] ?? '';
    if (!in_array($room, $allowedRooms)) jsonExit(['ok'=>false,'error'=>'Geçersiz salon'],400);
    
    $stmt = $pdo->prepare("SELECT * FROM machines WHERE room = :room ORDER BY id");
    $stmt->execute([':room'=>$room]);
    $machines = $stmt->fetchAll();
    jsonExit(['ok'=>true,'machines'=>$machines]);
}

if ($action === 'create') {
    $room = $_POST['room'] ?? '';
    if (!in_array($room, $allowedRooms)) jsonExit(['ok'=>false,'error'=>'Geçersiz salon'],400);
    
    $number = trim($_POST['machine_number'] ?? '');
    $brand = trim($_POST['brand_model'] ?? '');
    $date = trim($_POST['maintenance_date'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $x = (int)($_POST['x'] ?? 30);
    $y = (int)($_POST['y'] ?? 30);
    $size = (int)($_POST['size'] ?? 63);
    $rotation = (int)($_POST['rotation'] ?? 0);
    
    if ($number === '' || $brand === '' || $date === '') {
        jsonExit(['ok'=>false,'error'=>'Eksik alan'],400);
    }
    
    $stmt = $pdo->prepare("INSERT INTO machines (room, machine_number, brand_model, maintenance_date, note, x, y, size, rotation, created_at, updated_at) VALUES (:room,:num,:brand,:date,:note,:x,:y,:size,:rotation,NOW(),NOW())");
    $stmt->execute([
        ':room'=>$room, ':num'=>$number, ':brand'=>$brand, ':date'=>$date, ':note'=>$note,
        ':x'=>$x, ':y'=>$y, ':size'=>$size, ':rotation'=>$rotation
    ]);
    $id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM machines WHERE id = :id");
    $stmt->execute([':id'=>$id]);
    $machine = $stmt->fetch();
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
    $brand = trim($_POST['brand_model'] ?? '');
    $date = trim($_POST['maintenance_date'] ?? '');
    $note = trim($_POST['note'] ?? '');
    
    if ($number === '' || $brand === '' || $date === '') {
        jsonExit(['ok'=>false,'error'=>'Eksik alan'],400);
    }
    
    $stmt = $pdo->prepare("UPDATE machines SET machine_number=:num, brand_model=:brand, maintenance_date=:date, note=:note, updated_at=NOW() WHERE id=:id");
    $stmt->execute([
        ':num'=>$number, ':brand'=>$brand, ':date'=>$date, ':note'=>$note, ':id'=>$id
    ]);
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
    
    $allowedFields = ['maintenance_date', 'note', 'room'];
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

jsonExit(['ok'=>false,'error'=>'Bilinmeyen action'],400);
?>