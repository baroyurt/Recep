<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yedekleme ve Geri Yükleme - Casino Bakım Takip</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            padding: 20px;
            max-width: 1000px;
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
        .backup-section {
            background: rgba(30, 30, 30, 0.8);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(201, 169, 79, 0.4);
        }
        .btn-success {
            background: linear-gradient(145deg, #4caf50, #388e3c);
            color: #fff;
        }
        .btn-danger {
            background: linear-gradient(145deg, #f44336, #d32f2f);
            color: #fff;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
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
        .stats-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-item {
            background: rgba(40, 40, 40, 0.6);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #c9a94f;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #999;
            font-size: 12px;
        }
        .warning-box {
            background: rgba(255, 152, 0, 0.1);
            border: 2px solid #ff9800;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .warning-box h3 {
            color: #ff9800;
            margin-top: 0;
        }
        .file-input {
            display: none;
        }
        .file-label {
            padding: 12px 24px;
            background: linear-gradient(145deg, #666, #444);
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .file-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(100, 100, 100, 0.4);
        }
    </style>
</head>
<body>
    <a href="index.php" class="nav-back"><i class="fas fa-arrow-left"></i> Ana Sayfaya Dön</a>
    
    <div class="container">
        <h1><i class="fas fa-database"></i> Yedekleme ve Geri Yükleme</h1>
        
        <div id="alert-container"></div>
        
        <!-- Database Stats -->
        <div class="backup-section">
            <h2>Veritabanı Durumu</h2>
            <div class="stats-box" id="db-stats">
                <div class="stat-item">
                    <div class="stat-number" id="machine-count">-</div>
                    <div class="stat-label">Makinalar</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="fault-count">-</div>
                    <div class="stat-label">Arızalar</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="history-count">-</div>
                    <div class="stat-label">Geçmiş Kayıtları</div>
                </div>
            </div>
        </div>
        
        <!-- Backup Section -->
        <div class="backup-section">
            <h2><i class="fas fa-download"></i> Yedekleme (Backup)</h2>
            <p style="color: #ccc;">
                Tüm verilerinizi JSON formatında yedekleyin. Bu dosya makinalar, arızalar ve geçmiş kayıtlarını içerir.
            </p>
            
            <div style="margin-top: 20px;">
                <button class="btn btn-success" onclick="createBackup()">
                    <i class="fas fa-download"></i> Tam Yedek Al
                </button>
                <button class="btn" onclick="createBackup('machines')">
                    <i class="fas fa-desktop"></i> Sadece Makinaları Yedekle
                </button>
            </div>
        </div>
        
        <!-- Restore Section -->
        <div class="backup-section">
            <h2><i class="fas fa-upload"></i> Geri Yükleme (Restore)</h2>
            
            <div class="warning-box">
                <h3><i class="fas fa-exclamation-triangle"></i> Uyarı</h3>
                <p style="color: #ccc; margin: 0;">
                    Geri yükleme işlemi mevcut verilerin <strong>ÜZERİNE YAZACAKTIR</strong>. 
                    Bu işlemden önce mutlaka mevcut verilerinizi yedekleyin!
                </p>
            </div>
            
            <div style="margin-top: 20px;">
                <input type="file" id="restore-file" class="file-input" accept=".json" onchange="handleFileSelect(event)">
                <label for="restore-file" class="file-label">
                    <i class="fas fa-folder-open"></i> Yedek Dosyası Seç
                </label>
                <span id="file-name" style="color: #999; margin-left: 15px;"></span>
            </div>
            
            <div style="margin-top: 20px;">
                <button class="btn btn-danger" id="restore-btn" onclick="restoreBackup()" disabled>
                    <i class="fas fa-upload"></i> Geri Yükle
                </button>
            </div>
        </div>
        
        <!-- Info Section -->
        <div class="backup-section">
            <h2><i class="fas fa-info-circle"></i> Bilgi</h2>
            <ul style="color: #ccc; line-height: 1.8;">
                <li>Yedek dosyaları JSON formatında saklanır</li>
                <li>Düzenli olarak yedek almanız önerilir (haftada bir)</li>
                <li>Yedek dosyalarınızı güvenli bir yerde saklayın</li>
                <li>Geri yükleme işlemi geri alınamaz</li>
                <li>Trello ayarları yedeklemeye dahil değildir (güvenlik nedeniyle)</li>
            </ul>
        </div>
    </div>
    
    <script>
        const ROOMS = ['ALÇAK TAVAN', 'YÜKSEK TAVAN', 'YENİ VİP SALON', 'ALT SALON'];
        let selectedFile = null;
        
        function showAlert(message, type = 'success') {
            const container = document.getElementById('alert-container');
            const alertClass = type === 'error' ? 'alert-error' : type === 'info' ? 'alert-info' : 'alert-success';
            container.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }
        
        async function loadStats() {
            try {
                // Load all machines
                const machineResults = await Promise.all(
                    ROOMS.map(room => fetch(`api.php?action=list&room=${encodeURIComponent(room)}`).then(r => r.json()))
                );
                const allMachines = machineResults.flatMap(r => r.ok ? r.machines : []);
                
                // Load faults
                const faultsResult = await fetch('api.php?action=list_all_faults&status=all').then(r => r.json());
                const faults = faultsResult.ok ? faultsResult.faults : [];
                
                // Load history for all machines (we'll just count based on machines)
                // Estimate: each machine has at least 1 history entry
                const historyCount = allMachines.length * 2; // Rough estimate
                
                document.getElementById('machine-count').textContent = allMachines.length;
                document.getElementById('fault-count').textContent = faults.length;
                document.getElementById('history-count').textContent = '~' + historyCount;
                
            } catch (error) {
                console.error('Stats yüklenemedi:', error);
            }
        }
        
        async function createBackup(type = 'full') {
            try {
                showAlert('Yedek oluşturuluyor...', 'info');
                
                // Load all data
                const machineResults = await Promise.all(
                    ROOMS.map(room => fetch(`api.php?action=list&room=${encodeURIComponent(room)}`).then(r => r.json()))
                );
                const machines = machineResults.flatMap(r => r.ok ? r.machines : []);
                
                let backupData = {
                    backup_date: new Date().toISOString(),
                    version: '1.0',
                    type: type,
                    machines: machines
                };
                
                if (type === 'full') {
                    // Load faults
                    const faultsResult = await fetch('api.php?action=list_all_faults&status=all').then(r => r.json());
                    backupData.faults = faultsResult.ok ? faultsResult.faults : [];
                    
                    // Load history for all machines
                    const historyResults = await Promise.all(
                        machines.map(m => fetch(`api.php?action=get_history&machine_id=${m.id}`).then(r => r.json()))
                    );
                    backupData.history = historyResults.flatMap(r => r.ok ? r.history : []);
                }
                
                // Create JSON file
                const jsonStr = JSON.stringify(backupData, null, 2);
                const blob = new Blob([jsonStr], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                
                // Download file
                const a = document.createElement('a');
                a.href = url;
                const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, -5);
                a.download = `casino-backup-${type}-${timestamp}.json`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                
                showAlert(`✅ Yedek başarıyla oluşturuldu! (${machines.length} makina)`, 'success');
                
            } catch (error) {
                console.error('Backup hatası:', error);
                showAlert('❌ Yedek oluşturulamadı: ' + error.message, 'error');
            }
        }
        
        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                selectedFile = file;
                document.getElementById('file-name').textContent = file.name;
                document.getElementById('restore-btn').disabled = false;
            }
        }
        
        async function restoreBackup() {
            if (!selectedFile) {
                showAlert('❌ Lütfen önce bir dosya seçin!', 'error');
                return;
            }
            
            if (!confirm('⚠️ UYARI: Geri yükleme mevcut verilerin üzerine yazacak! Devam etmek istiyor musunuz?')) {
                return;
            }
            
            if (!confirm('⚠️ SON UYARI: Mevcut verilerinizi yedeklediniz mi? Bu işlem geri alınamaz!')) {
                return;
            }
            
            try {
                showAlert('Yedek geri yükleniyor...', 'info');
                
                // Read file
                const fileContent = await selectedFile.text();
                const backupData = JSON.parse(fileContent);
                
                // Validate backup data
                if (!backupData.machines || !Array.isArray(backupData.machines)) {
                    throw new Error('Geçersiz yedek dosyası formatı');
                }
                
                // Note: For a full restore, we would need a server-side endpoint
                // For now, we'll just show what would be restored
                
                showAlert(`
                    ⚠️ Geri yükleme işlemi server-side endpoint gerektirir.<br>
                    <br>
                    Yedek dosyası içeriği:<br>
                    - ${backupData.machines.length} makina<br>
                    ${backupData.faults ? `- ${backupData.faults.length} arıza<br>` : ''}
                    ${backupData.history ? `- ${backupData.history.length} geçmiş kaydı<br>` : ''}
                    <br>
                    <strong>Not:</strong> Tam geri yükleme özelliği yakında eklenecek.
                `, 'info');
                
            } catch (error) {
                console.error('Restore hatası:', error);
                showAlert('❌ Geri yükleme başarısız: ' + error.message, 'error');
            }
        }
        
        // Initialize
        loadStats();
    </script>
</body>
</html>
