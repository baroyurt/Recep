<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trello Entegrasyonu - Ayarlar</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .container {
            background: rgba(20, 20, 20, 0.95);
            border-radius: 10px;
            padding: 30px;
            margin: 20px 0;
        }
        h1, h2 {
            color: #c9a94f;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #fff;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            background: rgba(40, 40, 40, 0.8);
            border: 1px solid #444;
            border-radius: 5px;
            color: #fff;
            font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #c9a94f;
        }
        .btn {
            padding: 12px 24px;
            background: linear-gradient(145deg, #c9a94f, #a68a3d);
            color: #000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            margin-right: 10px;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(201, 169, 79, 0.4);
        }
        .btn-secondary {
            background: linear-gradient(145deg, #666, #444);
            color: #fff;
        }
        .btn-danger {
            background: linear-gradient(145deg, #f44336, #d32f2f);
            color: #fff;
        }
        .btn-success {
            background: linear-gradient(145deg, #4caf50, #388e3c);
            color: #fff;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid #4caf50;
            color: #4caf50;
        }
        .alert-error {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid #f44336;
            color: #f44336;
        }
        .alert-info {
            background: rgba(33, 150, 243, 0.2);
            border: 1px solid #2196f3;
            color: #2196f3;
        }
        .sync-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: rgba(40, 40, 40, 0.6);
            border-radius: 5px;
            margin: 15px 0;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-badge.success {
            background: #4caf50;
            color: #fff;
        }
        .status-badge.warning {
            background: #ff9800;
            color: #fff;
        }
        .help-text {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        .nav-back {
            display: inline-block;
            margin-bottom: 20px;
            color: #c9a94f;
            text-decoration: none;
            font-size: 14px;
        }
        .nav-back:hover {
            text-decoration: underline;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #c9a94f;
        }
        .loading.active {
            display: block;
        }
    </style>
</head>
<body>
    <a href="index.php" class="nav-back"><i class="fas fa-arrow-left"></i> Ana Sayfaya Dön</a>
    
    <div class="container">
        <h1><i class="fab fa-trello"></i> Trello Entegrasyonu</h1>
        
        <div id="alert-container"></div>
        
        <!-- Sync Status -->
        <div class="sync-status">
            <div>
                <strong>Son Senkronizasyon:</strong>
                <span id="last-sync">Henüz yapılmadı</span>
            </div>
            <button class="btn btn-success" id="sync-now-btn">
                <i class="fas fa-sync"></i> Şimdi Senkronize Et
            </button>
        </div>
        
        <!-- Configuration Form -->
        <h2>Trello API Ayarları</h2>
        <form id="trello-config-form">
            <div class="form-group">
                <label for="api-key">Trello API Key</label>
                <input type="text" id="api-key" name="api_key" required>
                <div class="help-text">
                    <a href="https://trello.com/app-key" target="_blank" style="color: #c9a94f;">
                        Trello API Key'inizi buradan alabilirsiniz
                    </a>
                </div>
            </div>
            
            <div class="form-group">
                <label for="api-token">Trello API Token</label>
                <input type="text" id="api-token" name="api_token" required>
                <div class="help-text">
                    API Key sayfasında "Token" linkine tıklayarak token oluşturun
                </div>
            </div>
            
            <div class="form-group">
                <label for="board-id">Board ID (Opsiyonel)</label>
                <select id="board-id" name="board_id">
                    <option value="">-- Board Seçin --</option>
                </select>
                <div class="help-text">
                    API bilgilerini girdikten sonra board'larınız otomatik yüklenecek
                </div>
            </div>
            
            <div class="form-group">
                <label for="list-id">List ID (Opsiyonel)</label>
                <select id="list-id" name="list_id">
                    <option value="">-- Board seçildikten sonra listeler yüklenecek --</option>
                </select>
                <div class="help-text">
                    Sadece belirli bir listeden arıza çekmek istiyorsanız seçin
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-save"></i> Ayarları Kaydet
            </button>
            <button type="button" class="btn btn-secondary" id="test-connection-btn">
                <i class="fas fa-plug"></i> Bağlantıyı Test Et
            </button>
        </form>
    </div>
    
    <div class="container">
        <h2>Nasıl Çalışır?</h2>
        <ol style="color: #ccc; line-height: 1.8;">
            <li>Trello API Key ve Token'ınızı yukarıdaki forma girin</li>
            <li>Arızaların bulunduğu Board ve List'i seçin (opsiyonel)</li>
            <li>"Şimdi Senkronize Et" butonuna tıklayın</li>
            <li>Sistem Trello kartlarındaki makina numaralarını otomatik eşleştirecek</li>
            <li>Eşleşen arızalar makina geçmişine kaydedilecek</li>
        </ol>
        
        <h3 style="color: #c9a94f; margin-top: 30px;">Makina Numarası Formatları</h3>
        <p style="color: #ccc;">Sistem şu formatlardaki makina numaralarını algılayabilir:</p>
        <ul style="color: #999;">
            <li><code>2192</code> - Sadece numara</li>
            <li><code>Makina 2192</code> - "Makina" öneki ile</li>
            <li><code>M2192</code> - "M" harfi ile</li>
            <li><code>#2192</code> - "#" işareti ile</li>
        </ul>
    </div>
    
    <div class="loading" id="loading">
        <i class="fas fa-spinner fa-spin fa-3x"></i>
        <p>Yükleniyor...</p>
    </div>
    
    <script>
        const API = {
            saveConfig: (data) => fetch('api.php?action=trello_save_config', {
                method: 'POST',
                body: new URLSearchParams(data)
            }).then(r => r.json()),
            
            getConfig: () => fetch('api.php?action=trello_get_config').then(r => r.json()),
            
            syncFaults: () => fetch('api.php?action=trello_sync', {
                method: 'POST'
            }).then(r => r.json()),
            
            listBoards: () => fetch('api.php?action=trello_list_boards').then(r => r.json()),
            
            getLists: (boardId) => fetch(`api.php?action=trello_get_lists&board_id=${boardId}`).then(r => r.json())
        };
        
        function showAlert(message, type = 'success') {
            const container = document.getElementById('alert-container');
            const alertClass = type === 'error' ? 'alert-error' : type === 'info' ? 'alert-info' : 'alert-success';
            container.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }
        
        function showLoading(show) {
            document.getElementById('loading').classList.toggle('active', show);
        }
        
        // Load existing configuration
        async function loadConfig() {
            try {
                const result = await API.getConfig();
                if (result.ok && result.config) {
                    const config = result.config;
                    
                    if (config.last_sync) {
                        const lastSync = new Date(config.last_sync);
                        document.getElementById('last-sync').textContent = lastSync.toLocaleString('tr-TR');
                    }
                    
                    // Note: We don't populate the actual keys/tokens for security
                    // User needs to re-enter them to update
                }
            } catch (error) {
                console.error('Config yüklenemedi:', error);
            }
        }
        
        // Load boards when API credentials are entered
        document.getElementById('api-key').addEventListener('blur', loadBoards);
        document.getElementById('api-token').addEventListener('blur', loadBoards);
        
        async function loadBoards() {
            const apiKey = document.getElementById('api-key').value.trim();
            const apiToken = document.getElementById('api-token').value.trim();
            
            if (!apiKey || !apiToken) return;
            
            try {
                // First save the credentials temporarily
                await API.saveConfig({ api_key: apiKey, api_token: apiToken });
                
                showLoading(true);
                const result = await API.listBoards();
                showLoading(false);
                
                if (result.ok && result.boards) {
                    const boardSelect = document.getElementById('board-id');
                    boardSelect.innerHTML = '<option value="">-- Board Seçin --</option>';
                    
                    result.boards.forEach(board => {
                        const option = document.createElement('option');
                        option.value = board.id;
                        option.textContent = board.name;
                        boardSelect.appendChild(option);
                    });
                    
                    showAlert('Board\'lar başarıyla yüklendi!', 'success');
                } else {
                    showAlert('Board\'lar yüklenemedi: ' + (result.error || 'Bilinmeyen hata'), 'error');
                }
            } catch (error) {
                showLoading(false);
                showAlert('Board\'lar yüklenemedi: ' + error.message, 'error');
            }
        }
        
        // Load lists when board is selected
        document.getElementById('board-id').addEventListener('change', async function() {
            const boardId = this.value;
            if (!boardId) return;
            
            try {
                showLoading(true);
                const result = await API.getLists(boardId);
                showLoading(false);
                
                if (result.ok && result.lists) {
                    const listSelect = document.getElementById('list-id');
                    listSelect.innerHTML = '<option value="">-- Tüm Listeler (Opsiyonel) --</option>';
                    
                    result.lists.forEach(list => {
                        if (!list.closed) {
                            const option = document.createElement('option');
                            option.value = list.id;
                            option.textContent = list.name;
                            listSelect.appendChild(option);
                        }
                    });
                    
                    showAlert('Listeler başarıyla yüklendi!', 'success');
                } else {
                    showAlert('Listeler yüklenemedi: ' + (result.error || 'Bilinmeyen hata'), 'error');
                }
            } catch (error) {
                showLoading(false);
                showAlert('Listeler yüklenemedi: ' + error.message, 'error');
            }
        });
        
        // Save configuration
        document.getElementById('trello-config-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            try {
                showLoading(true);
                const result = await API.saveConfig(data);
                showLoading(false);
                
                if (result.ok) {
                    showAlert('Trello ayarları başarıyla kaydedildi!', 'success');
                    loadConfig();
                } else {
                    showAlert('Kayıt hatası: ' + (result.error || 'Bilinmeyen hata'), 'error');
                }
            } catch (error) {
                showLoading(false);
                showAlert('Kayıt hatası: ' + error.message, 'error');
            }
        });
        
        // Sync now
        document.getElementById('sync-now-btn').addEventListener('click', async function() {
            if (!confirm('Trello\'dan arızalar senkronize edilecek. Devam edilsin mi?')) {
                return;
            }
            
            try {
                showLoading(true);
                const result = await API.syncFaults();
                showLoading(false);
                
                if (result.success) {
                    let message = `✅ Senkronizasyon tamamlandı!\n\n`;
                    message += `📊 Toplam ${result.total_cards} kart tarandı\n`;
                    message += `✅ ${result.synced_count} arıza kaydedildi\n`;
                    message += `🔗 ${result.matched_count} makina ile eşleşti`;
                    
                    if (result.unmatched_cards && result.unmatched_cards.length > 0) {
                        message += `\n\n⚠️ ${result.unmatched_cards.length} kart makina numarası içermiyor`;
                    }
                    
                    showAlert(message.replace(/\n/g, '<br>'), 'success');
                    loadConfig();
                } else {
                    showAlert('Senkronizasyon hatası: ' + (result.error || 'Bilinmeyen hata'), 'error');
                }
            } catch (error) {
                showLoading(false);
                showAlert('Senkronizasyon hatası: ' + error.message, 'error');
            }
        });
        
        // Test connection
        document.getElementById('test-connection-btn').addEventListener('click', async function() {
            const apiKey = document.getElementById('api-key').value.trim();
            const apiToken = document.getElementById('api-token').value.trim();
            
            if (!apiKey || !apiToken) {
                showAlert('API Key ve Token gerekli!', 'error');
                return;
            }
            
            await loadBoards();
        });
        
        // Initialize
        loadConfig();
    </script>
</body>
</html>
