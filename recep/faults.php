<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arızalar - Casino Bakım Takip</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .container {
            background: rgba(20, 20, 20, 0.95);
            border-radius: 10px;
            padding: 30px;
            margin: 20px 0;
        }
        h1 {
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
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            color: #999;
            font-size: 12px;
            font-weight: bold;
        }
        .filter-group select {
            padding: 8px 12px;
            background: rgba(40, 40, 40, 0.8);
            border: 1px solid #444;
            border-radius: 5px;
            color: #fff;
            font-size: 14px;
        }
        .fault-card {
            background: rgba(30, 30, 30, 0.8);
            border-left: 4px solid #f44336;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .fault-card:hover {
            background: rgba(40, 40, 40, 0.9);
            transform: translateX(5px);
        }
        .fault-card.resolved {
            border-left-color: #4caf50;
            opacity: 0.7;
        }
        .fault-card.in_progress {
            border-left-color: #ff9800;
        }
        .fault-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .fault-title {
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .fault-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #999;
            font-size: 13px;
        }
        .meta-item i {
            color: #c9a94f;
        }
        .fault-description {
            color: #ccc;
            margin: 10px 0;
            line-height: 1.6;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-badge.open {
            background: #f44336;
            color: #fff;
        }
        .status-badge.in_progress {
            background: #ff9800;
            color: #fff;
        }
        .status-badge.resolved {
            background: #4caf50;
            color: #fff;
        }
        .priority-badge {
            padding: 5px 12px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .priority-badge.critical {
            background: #d32f2f;
            color: #fff;
        }
        .priority-badge.high {
            background: #f44336;
            color: #fff;
        }
        .priority-badge.medium {
            background: #ff9800;
            color: #fff;
        }
        .priority-badge.low {
            background: #4caf50;
            color: #fff;
        }
        .fault-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn {
            padding: 8px 16px;
            background: linear-gradient(145deg, #c9a94f, #a68a3d);
            color: #000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(201, 169, 79, 0.4);
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 11px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .stat-card {
            flex: 1;
            min-width: 200px;
            background: rgba(40, 40, 40, 0.6);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #c9a94f;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #999;
            font-size: 14px;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #c9a94f;
        }
    </style>
</head>
<body>
    <a href="index.php" class="nav-back"><i class="fas fa-arrow-left"></i> Ana Sayfaya Dön</a>
    
    <div class="container">
        <h1><i class="fas fa-exclamation-triangle"></i> Arızalar</h1>
        
        <!-- Statistics -->
        <div class="stats" id="stats-container">
            <div class="stat-card">
                <div class="stat-number" id="stat-total">0</div>
                <div class="stat-label">Toplam Arıza</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="stat-open">0</div>
                <div class="stat-label">Açık</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="stat-in-progress">0</div>
                <div class="stat-label">Devam Eden</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="stat-resolved">0</div>
                <div class="stat-label">Çözüldü</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters">
            <div class="filter-group">
                <label>Durum</label>
                <select id="status-filter">
                    <option value="all">Tümü</option>
                    <option value="open" selected>Açık</option>
                    <option value="in_progress">Devam Eden</option>
                    <option value="resolved">Çözüldü</option>
                </select>
            </div>
        </div>
        
        <!-- Faults List -->
        <div id="faults-container">
            <div class="loading">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
                <p>Arızalar yükleniyor...</p>
            </div>
        </div>
    </div>
    
    <script>
        const API = {
            listFaults: (status = 'all') => 
                fetch(`api.php?action=list_all_faults&status=${status}`).then(r => r.json()),
            
            updateFaultStatus: (faultId, status) => 
                fetch('api.php?action=update_fault_status', {
                    method: 'POST',
                    body: new URLSearchParams({ fault_id: faultId, status: status })
                }).then(r => r.json())
        };
        
        let currentStatus = 'open';
        
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('tr-TR', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function getStatusLabel(status) {
            const labels = {
                open: 'Açık',
                in_progress: 'Devam Eden',
                resolved: 'Çözüldü'
            };
            return labels[status] || status;
        }
        
        function getPriorityLabel(priority) {
            const labels = {
                critical: 'Kritik',
                high: 'Yüksek',
                medium: 'Orta',
                low: 'Düşük'
            };
            return labels[priority] || priority;
        }
        
        function renderFault(fault) {
            const statusClass = fault.status || 'open';
            const priorityClass = fault.priority || 'medium';
            
            return `
                <div class="fault-card ${statusClass}" data-fault-id="${fault.id}">
                    <div class="fault-header">
                        <div>
                            <div class="fault-title">${fault.fault_title}</div>
                            <div class="fault-meta">
                                ${fault.machine_number ? `
                                    <div class="meta-item">
                                        <i class="fas fa-desktop"></i>
                                        <strong>Makina ${fault.machine_number}</strong> - ${fault.brand_model || ''}
                                    </div>
                                ` : `
                                    <div class="meta-item" style="color: #f44336;">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Makina eşleşmedi
                                    </div>
                                `}
                                ${fault.room ? `
                                    <div class="meta-item">
                                        <i class="fas fa-door-open"></i>
                                        ${fault.room}
                                    </div>
                                ` : ''}
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    ${formatDate(fault.reported_date)}
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <span class="priority-badge ${priorityClass}">${getPriorityLabel(fault.priority)}</span>
                            <span class="status-badge ${statusClass}">${getStatusLabel(fault.status)}</span>
                        </div>
                    </div>
                    
                    ${fault.fault_description ? `
                        <div class="fault-description">${fault.fault_description}</div>
                    ` : ''}
                    
                    ${fault.resolved_date ? `
                        <div class="meta-item" style="color: #4caf50;">
                            <i class="fas fa-check-circle"></i>
                            Çözüm Tarihi: ${formatDate(fault.resolved_date)}
                        </div>
                    ` : ''}
                    
                    <div class="fault-actions">
                        ${fault.trello_card_url ? `
                            <a href="${fault.trello_card_url}" target="_blank" class="btn btn-sm">
                                <i class="fab fa-trello"></i> Trello'da Aç
                            </a>
                        ` : ''}
                        
                        ${fault.status === 'open' ? `
                            <button class="btn btn-sm" onclick="updateStatus(${fault.id}, 'in_progress')">
                                <i class="fas fa-play"></i> Devam Eden
                            </button>
                            <button class="btn btn-sm" onclick="updateStatus(${fault.id}, 'resolved')">
                                <i class="fas fa-check"></i> Çözüldü
                            </button>
                        ` : ''}
                        
                        ${fault.status === 'in_progress' ? `
                            <button class="btn btn-sm" onclick="updateStatus(${fault.id}, 'resolved')">
                                <i class="fas fa-check"></i> Çözüldü
                            </button>
                            <button class="btn btn-sm" onclick="updateStatus(${fault.id}, 'open')">
                                <i class="fas fa-undo"></i> Geri Al
                            </button>
                        ` : ''}
                        
                        ${fault.status === 'resolved' ? `
                            <button class="btn btn-sm" onclick="updateStatus(${fault.id}, 'open')">
                                <i class="fas fa-undo"></i> Yeniden Aç
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }
        
        async function loadFaults(status = 'open') {
            currentStatus = status;
            const container = document.getElementById('faults-container');
            
            try {
                const result = await API.listFaults(status);
                
                if (result.ok) {
                    const faults = result.faults || [];
                    
                    // Update statistics
                    updateStats(faults);
                    
                    if (faults.length === 0) {
                        container.innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <h2>Arıza Bulunamadı</h2>
                                <p>Seçili filtreye göre arıza bulunmuyor.</p>
                            </div>
                        `;
                    } else {
                        container.innerHTML = faults.map(renderFault).join('');
                    }
                } else {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h2>Hata</h2>
                            <p>${result.error || 'Bilinmeyen hata'}</p>
                        </div>
                    `;
                }
            } catch (error) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h2>Yükleme Hatası</h2>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }
        
        async function updateStats(faults) {
            // Load all faults for stats
            const allResult = await API.listFaults('all');
            const allFaults = allResult.ok ? (allResult.faults || []) : [];
            
            const stats = {
                total: allFaults.length,
                open: allFaults.filter(f => f.status === 'open').length,
                in_progress: allFaults.filter(f => f.status === 'in_progress').length,
                resolved: allFaults.filter(f => f.status === 'resolved').length
            };
            
            document.getElementById('stat-total').textContent = stats.total;
            document.getElementById('stat-open').textContent = stats.open;
            document.getElementById('stat-in-progress').textContent = stats.in_progress;
            document.getElementById('stat-resolved').textContent = stats.resolved;
        }
        
        async function updateStatus(faultId, newStatus) {
            if (!confirm(`Arıza durumu "${getStatusLabel(newStatus)}" olarak güncellenecek. Devam edilsin mi?`)) {
                return;
            }
            
            try {
                const result = await API.updateFaultStatus(faultId, newStatus);
                
                if (result.ok) {
                    // Reload faults
                    await loadFaults(currentStatus);
                } else {
                    alert('Güncelleme hatası: ' + (result.error || 'Bilinmeyen hata'));
                }
            } catch (error) {
                alert('Güncelleme hatası: ' + error.message);
            }
        }
        
        // Status filter change
        document.getElementById('status-filter').addEventListener('change', function() {
            loadFaults(this.value);
        });
        
        // Initialize
        loadFaults('open');
    </script>
</body>
</html>
