<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İstatistikler ve Raporlar - Casino Bakım Takip</title>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(145deg, rgba(40, 40, 40, 0.8), rgba(30, 30, 30, 0.6));
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid rgba(201, 169, 79, 0.2);
            transition: all 0.3s;
        }
        .stat-card:hover {
            border-color: rgba(201, 169, 79, 0.5);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(201, 169, 79, 0.2);
        }
        .stat-icon {
            font-size: 42px;
            margin-bottom: 15px;
            color: #c9a94f;
        }
        .stat-number {
            font-size: 48px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 10px;
        }
        .stat-label {
            color: #999;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .chart-container {
            background: rgba(30, 30, 30, 0.8);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .room-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .room-card {
            background: rgba(40, 40, 40, 0.6);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #c9a94f;
        }
        .room-name {
            font-weight: bold;
            color: #c9a94f;
            margin-bottom: 10px;
        }
        .room-detail {
            color: #ccc;
            font-size: 13px;
            margin: 5px 0;
        }
        .maintenance-status {
            display: flex;
            gap: 10px;
            margin: 10px 0;
        }
        .status-indicator {
            flex: 1;
            padding: 8px;
            border-radius: 5px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }
        .status-good {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid #4caf50;
            color: #4caf50;
        }
        .status-warning {
            background: rgba(255, 152, 0, 0.2);
            border: 1px solid #ff9800;
            color: #ff9800;
        }
        .status-danger {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid #f44336;
            color: #f44336;
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
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(201, 169, 79, 0.4);
        }
        .actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin: 25px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        th {
            background: rgba(40, 40, 40, 0.8);
            color: #c9a94f;
            font-weight: bold;
        }
        td {
            color: #ccc;
        }
        tr:hover {
            background: rgba(40, 40, 40, 0.4);
        }
    </style>
</head>
<body>
    <a href="index.php" class="nav-back"><i class="fas fa-arrow-left"></i> Ana Sayfaya Dön</a>
    
    <div class="container">
        <h1><i class="fas fa-chart-bar"></i> İstatistikler ve Raporlar</h1>
        
        <div class="actions">
            <button class="btn" onclick="window.print()">
                <i class="fas fa-print"></i> Yazdır
            </button>
            <button class="btn" onclick="window.location.reload()">
                <i class="fas fa-sync"></i> Yenile
            </button>
        </div>
        
        <!-- Main Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-desktop"></i></div>
                <div class="stat-number" id="total-machines">0</div>
                <div class="stat-label">Toplam Makina</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-wrench"></i></div>
                <div class="stat-number" id="total-maintenance">0</div>
                <div class="stat-label">Toplam Bakım</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-number" id="total-faults">0</div>
                <div class="stat-label">Toplam Arıza</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-number" id="overdue-machines">0</div>
                <div class="stat-label">Bakım Geçmiş</div>
            </div>
        </div>
        
        <!-- Maintenance Status Distribution -->
        <div class="chart-container">
            <h2>Bakım Durum Dağılımı</h2>
            <div class="maintenance-status">
                <div class="status-indicator status-good">
                    <div style="font-size: 24px; margin-bottom: 5px;" id="status-good-count">0</div>
                    <div>İYİ</div>
                    <div style="font-size: 11px; margin-top: 3px;">(0-21 gün)</div>
                </div>
                <div class="status-indicator status-warning">
                    <div style="font-size: 24px; margin-bottom: 5px;" id="status-warning-count">0</div>
                    <div>DİKKAT</div>
                    <div style="font-size: 11px; margin-top: 3px;">(21-28 gün)</div>
                </div>
                <div class="status-indicator status-danger">
                    <div style="font-size: 24px; margin-bottom: 5px;" id="status-danger-count">0</div>
                    <div>GEÇMİŞ</div>
                    <div style="font-size: 11px; margin-top: 3px;">(28+ gün)</div>
                </div>
            </div>
        </div>
        
        <!-- Room Statistics -->
        <div class="chart-container">
            <h2>Salon Bazlı İstatistikler</h2>
            <div class="room-stats" id="room-stats">
                <!-- Room stats will be loaded here -->
            </div>
        </div>
        
        <!-- Top Overdue Machines -->
        <div class="chart-container">
            <h2>Bakım Gecikmiş Makinalar (En Çok Gecikmiş İlk 10)</h2>
            <table id="overdue-table">
                <thead>
                    <tr>
                        <th>Makina No</th>
                        <th>Marka/Model</th>
                        <th>Salon</th>
                        <th>Son Bakım</th>
                        <th>Gecikme (Gün)</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody id="overdue-tbody">
                    <!-- Overdue machines will be loaded here -->
                </tbody>
            </table>
        </div>
        
        <!-- Recent Faults -->
        <div class="chart-container">
            <h2>Son Arızalar (Son 10)</h2>
            <table id="faults-table">
                <thead>
                    <tr>
                        <th>Makina No</th>
                        <th>Arıza</th>
                        <th>Öncelik</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                    </tr>
                </thead>
                <tbody id="faults-tbody">
                    <!-- Recent faults will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        const ROOMS = ['ALÇAK TAVAN', 'YÜKSEK TAVAN', 'YENİ VİP SALON', 'ALT SALON'];
        
        const API = {
            listAllMachines: async () => {
                const results = await Promise.all(
                    ROOMS.map(room => fetch(`api.php?action=list&room=${encodeURIComponent(room)}`).then(r => r.json()))
                );
                return results.flatMap(r => r.ok ? r.machines : []);
            },
            
            listAllFaults: () => fetch('api.php?action=list_all_faults&status=all').then(r => r.json()),
            
            getHistory: () => {
                // Get total maintenance count from all machines
                return API.listAllMachines();
            }
        };
        
        function calculateDaysSince(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            return Math.floor(diff / (1000 * 60 * 60 * 24));
        }
        
        function getMaintenanceStatus(days) {
            if (days <= 21) return 'good';
            if (days <= 28) return 'warning';
            return 'danger';
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('tr-TR');
        }
        
        async function loadStatistics() {
            try {
                const [machines, faultsResult] = await Promise.all([
                    API.listAllMachines(),
                    API.listAllFaults()
                ]);
                
                const faults = faultsResult.ok ? faultsResult.faults : [];
                
                // Main statistics
                document.getElementById('total-machines').textContent = machines.length;
                document.getElementById('total-maintenance').textContent = machines.length; // Each machine has at least one maintenance record
                document.getElementById('total-faults').textContent = faults.length;
                
                // Calculate overdue and status distribution
                let statusCounts = { good: 0, warning: 0, danger: 0 };
                let overdueCount = 0;
                
                machines.forEach(machine => {
                    const days = calculateDaysSince(machine.maintenance_date);
                    const status = getMaintenanceStatus(days);
                    statusCounts[status]++;
                    
                    if (days > 28) overdueCount++;
                });
                
                document.getElementById('overdue-machines').textContent = overdueCount;
                document.getElementById('status-good-count').textContent = statusCounts.good;
                document.getElementById('status-warning-count').textContent = statusCounts.warning;
                document.getElementById('status-danger-count').textContent = statusCounts.danger;
                
                // Room statistics
                const roomStats = {};
                ROOMS.forEach(room => {
                    const roomMachines = machines.filter(m => m.room === room);
                    const roomFaults = faults.filter(f => f.room === room && f.status === 'open');
                    
                    let roomStatusCounts = { good: 0, warning: 0, danger: 0 };
                    roomMachines.forEach(machine => {
                        const days = calculateDaysSince(machine.maintenance_date);
                        const status = getMaintenanceStatus(days);
                        roomStatusCounts[status]++;
                    });
                    
                    roomStats[room] = {
                        total: roomMachines.length,
                        faults: roomFaults.length,
                        status: roomStatusCounts
                    };
                });
                
                const roomStatsHtml = Object.entries(roomStats).map(([room, stats]) => `
                    <div class="room-card">
                        <div class="room-name">${room}</div>
                        <div class="room-detail">
                            <i class="fas fa-desktop"></i> ${stats.total} Makina
                        </div>
                        <div class="room-detail">
                            <i class="fas fa-exclamation-triangle"></i> ${stats.faults} Aktif Arıza
                        </div>
                        <div style="display: flex; gap: 8px; margin-top: 10px; font-size: 11px;">
                            <span style="flex: 1; background: rgba(76,175,80,0.2); padding: 5px; border-radius: 3px; text-align: center;">
                                ${stats.status.good} İyi
                            </span>
                            <span style="flex: 1; background: rgba(255,152,0,0.2); padding: 5px; border-radius: 3px; text-align: center;">
                                ${stats.status.warning} Dikkat
                            </span>
                            <span style="flex: 1; background: rgba(244,67,54,0.2); padding: 5px; border-radius: 3px; text-align: center;">
                                ${stats.status.danger} Geçmiş
                            </span>
                        </div>
                    </div>
                `).join('');
                
                document.getElementById('room-stats').innerHTML = roomStatsHtml;
                
                // Overdue machines table
                const overdueMachines = machines
                    .map(m => ({
                        ...m,
                        days: calculateDaysSince(m.maintenance_date)
                    }))
                    .filter(m => m.days > 28)
                    .sort((a, b) => b.days - a.days)
                    .slice(0, 10);
                
                const overdueHtml = overdueMachines.map(m => `
                    <tr>
                        <td><strong>${m.machine_number}</strong></td>
                        <td>${m.brand_model}</td>
                        <td>${m.room}</td>
                        <td>${formatDate(m.maintenance_date)}</td>
                        <td><strong style="color: #f44336;">${m.days}</strong></td>
                        <td><span class="status-indicator status-danger" style="display: inline-block; padding: 4px 8px;">GEÇMİŞ</span></td>
                    </tr>
                `).join('');
                
                document.getElementById('overdue-tbody').innerHTML = overdueHtml || '<tr><td colspan="6" style="text-align: center; color: #4caf50;">Bakım geçmiş makina yok!</td></tr>';
                
                // Recent faults table
                const recentFaults = faults
                    .sort((a, b) => new Date(b.reported_date) - new Date(a.reported_date))
                    .slice(0, 10);
                
                const faultsHtml = recentFaults.map(f => {
                    const priorityColors = {
                        'critical': '#d32f2f',
                        'high': '#f44336',
                        'medium': '#ff9800',
                        'low': '#4caf50'
                    };
                    const statusColors = {
                        'open': '#f44336',
                        'in_progress': '#ff9800',
                        'resolved': '#4caf50'
                    };
                    return `
                        <tr>
                            <td><strong>${f.machine_number || 'N/A'}</strong></td>
                            <td>${f.fault_title}</td>
                            <td><span style="color: ${priorityColors[f.priority]}; font-weight: bold;">${f.priority.toUpperCase()}</span></td>
                            <td><span style="color: ${statusColors[f.status]}; font-weight: bold;">${f.status.toUpperCase()}</span></td>
                            <td>${formatDate(f.reported_date)}</td>
                        </tr>
                    `;
                }).join('');
                
                document.getElementById('faults-tbody').innerHTML = faultsHtml || '<tr><td colspan="5" style="text-align: center; color: #4caf50;">Arıza kaydı yok!</td></tr>';
                
            } catch (error) {
                console.error('İstatistikler yüklenemedi:', error);
                alert('İstatistikler yüklenemedi: ' + error.message);
            }
        }
        
        // Initialize
        loadStatistics();
    </script>
</body>
</html>
