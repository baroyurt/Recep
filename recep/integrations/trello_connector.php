<?php
// integrations/trello_connector.php - Trello API Entegrasyonu

class TrelloConnector {
    private $pdo;
    private $apiKey;
    private $apiToken;
    private $boardId;
    private $listId;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadConfig();
    }
    
    /**
     * Trello konfigürasyonunu yükle
     */
    private function loadConfig() {
        $stmt = $this->pdo->query("SELECT * FROM trello_config ORDER BY id DESC LIMIT 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($config) {
            $this->apiKey = $config['api_key'];
            $this->apiToken = $config['api_token'];
            $this->boardId = $config['board_id'];
            $this->listId = $config['list_id'];
        }
    }
    
    /**
     * Trello API'ye istek gönder
     */
    private function makeRequest($endpoint, $params = []) {
        if (empty($this->apiKey) || empty($this->apiToken)) {
            throw new Exception('Trello API anahtarları yapılandırılmamış');
        }
        
        $params['key'] = $this->apiKey;
        $params['token'] = $this->apiToken;
        
        $url = "https://api.trello.com/1/{$endpoint}?" . http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl hatası: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Trello API hatası: HTTP {$httpCode}");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Kartları listeden çek
     */
    public function fetchCards($listId = null) {
        $listId = $listId ?? $this->listId;
        
        if (empty($listId)) {
            throw new Exception('List ID tanımlanmamış');
        }
        
        return $this->makeRequest("lists/{$listId}/cards", [
            'fields' => 'id,name,desc,due,labels,idList,shortUrl',
            'customFieldItems' => 'true'
        ]);
    }
    
    /**
     * Board'daki tüm kartları çek
     */
    public function fetchBoardCards($boardId = null) {
        $boardId = $boardId ?? $this->boardId;
        
        if (empty($boardId)) {
            throw new Exception('Board ID tanımlanmamış');
        }
        
        return $this->makeRequest("boards/{$boardId}/cards", [
            'fields' => 'id,name,desc,due,labels,idList,shortUrl'
        ]);
    }
    
    /**
     * Makina numarasını kart başlığından/açıklamasından çıkar
     * Örnek: "Makina 2192 - Arıza", "2192 numaralı makina", "M2192"
     */
    private function extractMachineNumber($cardName, $cardDesc = '') {
        $text = $cardName . ' ' . $cardDesc;
        
        // Pattern: 4 basamaklı sayı ara
        if (preg_match('/\b(\d{4})\b/', $text, $matches)) {
            return $matches[1];
        }
        
        // Pattern: M veya # ile başlayan 4 basamaklı sayı
        if (preg_match('/[M#](\d{4})/i', $text, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Trello'dan arızaları senkronize et
     */
    public function syncFaults() {
        try {
            $cards = $this->fetchBoardCards();
            
            $syncedCount = 0;
            $matchedCount = 0;
            $unmatchedCards = [];
            
            foreach ($cards as $card) {
                $machineNumber = $this->extractMachineNumber($card['name'], $card['desc'] ?? '');
                
                if (!$machineNumber) {
                    $unmatchedCards[] = [
                        'card_name' => $card['name'],
                        'card_url' => $card['shortUrl']
                    ];
                    continue;
                }
                
                // Makina numarasına göre makina bul
                $stmt = $this->pdo->prepare("SELECT id FROM machines WHERE machine_number = :num LIMIT 1");
                $stmt->execute([':num' => $machineNumber]);
                $machine = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $machineId = $machine ? $machine['id'] : null;
                
                // Öncelik belirle (label'lardan)
                $priority = 'medium';
                if (!empty($card['labels'])) {
                    foreach ($card['labels'] as $label) {
                        $labelName = strtolower($label['name']);
                        if (strpos($labelName, 'critical') !== false || strpos($labelName, 'kritik') !== false) {
                            $priority = 'critical';
                        } elseif (strpos($labelName, 'high') !== false || strpos($labelName, 'yüksek') !== false) {
                            $priority = 'high';
                        } elseif (strpos($labelName, 'low') !== false || strpos($labelName, 'düşük') !== false) {
                            $priority = 'low';
                        }
                    }
                }
                
                // Mevcut arıza var mı kontrol et
                $stmt = $this->pdo->prepare("SELECT id FROM machine_faults WHERE trello_card_id = :cardId");
                $stmt->execute([':cardId' => $card['id']]);
                $existingFault = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existingFault) {
                    // Güncelle
                    $stmt = $this->pdo->prepare("
                        UPDATE machine_faults 
                        SET fault_title = :title, 
                            fault_description = :desc,
                            trello_card_url = :url,
                            priority = :priority,
                            machine_id = :machineId,
                            updated_at = NOW()
                        WHERE trello_card_id = :cardId
                    ");
                    $stmt->execute([
                        ':title' => $card['name'],
                        ':desc' => $card['desc'] ?? '',
                        ':url' => $card['shortUrl'],
                        ':priority' => $priority,
                        ':machineId' => $machineId,
                        ':cardId' => $card['id']
                    ]);
                } else {
                    // Yeni ekle
                    $stmt = $this->pdo->prepare("
                        INSERT INTO machine_faults 
                        (machine_id, trello_card_id, trello_card_url, fault_title, fault_description, priority, status) 
                        VALUES (:machineId, :cardId, :url, :title, :desc, :priority, 'open')
                    ");
                    $stmt->execute([
                        ':machineId' => $machineId,
                        ':cardId' => $card['id'],
                        ':url' => $card['shortUrl'],
                        ':title' => $card['name'],
                        ':desc' => $card['desc'] ?? '',
                        ':priority' => $priority
                    ]);
                    
                    // Eğer makina bulunduysa, history'ye ekle
                    if ($machineId) {
                        $stmt = $this->pdo->prepare("
                            INSERT INTO maintenance_history 
                            (machine_id, action_type, details, performed_by) 
                            VALUES (:machineId, 'fault', :details, 'trello_sync')
                        ");
                        $stmt->execute([
                            ':machineId' => $machineId,
                            ':details' => "Trello'dan arıza kaydedildi: " . $card['name']
                        ]);
                    }
                }
                
                $syncedCount++;
                if ($machineId) {
                    $matchedCount++;
                }
            }
            
            // Son senkronizasyon zamanını güncelle
            $stmt = $this->pdo->query("SELECT MAX(id) as max_id FROM trello_config");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && $result['max_id']) {
                $stmt = $this->pdo->prepare("UPDATE trello_config SET last_sync = NOW() WHERE id = :id");
                $stmt->execute([':id' => $result['max_id']]);
            }
            
            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'matched_count' => $matchedCount,
                'unmatched_cards' => $unmatchedCards,
                'total_cards' => count($cards)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Konfigürasyon kaydet/güncelle
     */
    public function saveConfig($apiKey, $apiToken, $boardId = null, $listId = null) {
        // Mevcut config var mı kontrol et
        $stmt = $this->pdo->query("SELECT MAX(id) as max_id, COUNT(*) as count FROM trello_config");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            // Güncelle
            $stmt = $this->pdo->prepare("
                UPDATE trello_config 
                SET api_key = :apiKey, 
                    api_token = :apiToken, 
                    board_id = :boardId, 
                    list_id = :listId,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                ':apiKey' => $apiKey,
                ':apiToken' => $apiToken,
                ':boardId' => $boardId,
                ':listId' => $listId,
                ':id' => $result['max_id']
            ]);
        } else {
            // Yeni ekle
            $stmt = $this->pdo->prepare("
                INSERT INTO trello_config (api_key, api_token, board_id, list_id) 
                VALUES (:apiKey, :apiToken, :boardId, :listId)
            ");
            $stmt->execute([
                ':apiKey' => $apiKey,
                ':apiToken' => $apiToken,
                ':boardId' => $boardId,
                ':listId' => $listId
            ]);
        }
        
        // Config'i yeniden yükle
        $this->loadConfig();
        
        return true;
    }
    
    /**
     * Mevcut konfigürasyonu al
     */
    public function getConfig() {
        $stmt = $this->pdo->query("SELECT * FROM trello_config ORDER BY id DESC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Board'ları listele (test için)
     */
    public function listBoards() {
        return $this->makeRequest('members/me/boards', [
            'fields' => 'id,name,desc,url'
        ]);
    }
    
    /**
     * Board'daki listeleri getir
     */
    public function getBoardLists($boardId = null) {
        $boardId = $boardId ?? $this->boardId;
        
        if (empty($boardId)) {
            throw new Exception('Board ID tanımlanmamış');
        }
        
        return $this->makeRequest("boards/{$boardId}/lists", [
            'fields' => 'id,name,closed'
        ]);
    }
}
?>
