// js/history.js - Machine History and Faults Features

(function() {
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        const historyBtn = document.getElementById('history-btn');
        const faultsBtn = document.getElementById('faults-btn');
        const maintenanceDatesBtn = document.getElementById('maintenance-dates-btn');
        const historyModal = document.getElementById('history-modal');
        const machineFaultsModal = document.getElementById('machine-faults-modal');
        const maintenanceDatesModal = document.getElementById('maintenance-dates-modal');
        const closeHistory = document.getElementById('close-history');
        const closeMachineFaults = document.getElementById('close-machine-faults');
        const closeMaintenanceDates = document.getElementById('close-maintenance-dates');
        
        if (!historyBtn || !faultsBtn) {
            console.warn('History or Faults button not found');
            return;
        }
        
        // Get current machine ID (from the info modal)
        function getCurrentMachineId() {
            const editId = document.getElementById('edit-id');
            return editId ? editId.value : null;
        }
        
        // Format date
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString('tr-TR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // Get action type icon and label
        function getActionIcon(actionType) {
            const icons = {
                'created': '➕',
                'updated': '✏️',
                'maintenance': '🔧',
                'fault': '⚠️',
                'repair': '🛠️',
                'note': '📝',
                'moved': '↔️'
            };
            return icons[actionType] || '•';
        }
        
        function getActionLabel(actionType) {
            const labels = {
                'created': 'Oluşturuldu',
                'updated': 'Güncellendi',
                'maintenance': 'Bakım Yapıldı',
                'fault': 'Arıza Kaydedildi',
                'repair': 'Tamir Edildi',
                'note': 'Not Eklendi',
                'moved': 'Taşındı'
            };
            return labels[actionType] || actionType;
        }
        
        // Load machine history
        async function loadMachineHistory(machineId) {
            try {
                const response = await fetch(`api.php?action=get_history&machine_id=${machineId}`);
                const result = await response.json();
                
                if (!result.ok) {
                    throw new Error(result.error || 'Bilinmeyen hata');
                }
                
                const history = result.history || [];
                const content = document.getElementById('history-content');
                
                if (history.length === 0) {
                    content.innerHTML = `
                        <div style="text-align: center; padding: 40px; color: #666;">
                            <i class="fas fa-history" style="font-size: 48px; margin-bottom: 15px;"></i>
                            <p>Henüz geçmiş kaydı bulunmuyor.</p>
                        </div>
                    `;
                    return;
                }
                
                // Render history timeline
                content.innerHTML = `
                    <div style="position: relative; padding-left: 40px;">
                        ${history.map((item, index) => `
                            <div style="position: relative; margin-bottom: 25px; padding: 15px; background: rgba(40, 40, 40, 0.6); border-radius: 8px; border-left: 3px solid #c9a94f;">
                                <div style="position: absolute; left: -53px; top: 15px; width: 32px; height: 32px; background: #c9a94f; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                                    ${getActionIcon(item.action_type)}
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                    <strong style="color: #c9a94f; font-size: 14px;">
                                        ${getActionLabel(item.action_type)}
                                    </strong>
                                    <span style="color: #999; font-size: 12px;">
                                        ${formatDate(item.created_at)}
                                    </span>
                                </div>
                                ${item.details ? `
                                    <div style="color: #ccc; font-size: 13px; line-height: 1.6;">
                                        ${item.details}
                                    </div>
                                ` : ''}
                                ${item.performed_by && item.performed_by !== 'system' ? `
                                    <div style="color: #999; font-size: 11px; margin-top: 8px;">
                                        <i class="fas fa-user"></i> ${item.performed_by}
                                    </div>
                                ` : ''}
                            </div>
                        `).join('')}
                    </div>
                `;
                
            } catch (error) {
                const content = document.getElementById('history-content');
                content.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #f44336;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <p>Geçmiş yüklenirken hata oluştu:</p>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }
        
        // Load machine faults
        async function loadMachineFaults(machineId) {
            try {
                const response = await fetch(`api.php?action=get_faults&machine_id=${machineId}`);
                const result = await response.json();
                
                if (!result.ok) {
                    throw new Error(result.error || 'Bilinmeyen hata');
                }
                
                const faults = result.faults || [];
                const content = document.getElementById('machine-faults-content');
                
                if (faults.length === 0) {
                    content.innerHTML = `
                        <div style="text-align: center; padding: 40px; color: #4caf50;">
                            <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                            <p>Bu makinada kayıtlı arıza bulunmuyor.</p>
                        </div>
                    `;
                    return;
                }
                
                // Render faults
                content.innerHTML = faults.map(fault => {
                    const statusColors = {
                        'open': '#f44336',
                        'in_progress': '#ff9800',
                        'resolved': '#4caf50'
                    };
                    const statusLabels = {
                        'open': 'Açık',
                        'in_progress': 'Devam Eden',
                        'resolved': 'Çözüldü'
                    };
                    const priorityColors = {
                        'critical': '#d32f2f',
                        'high': '#f44336',
                        'medium': '#ff9800',
                        'low': '#4caf50'
                    };
                    const priorityLabels = {
                        'critical': 'Kritik',
                        'high': 'Yüksek',
                        'medium': 'Orta',
                        'low': 'Düşük'
                    };
                    
                    return `
                        <div style="margin-bottom: 20px; padding: 15px; background: rgba(40, 40, 40, 0.6); border-radius: 8px; border-left: 4px solid ${statusColors[fault.status] || '#666'};">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                <strong style="color: #fff; font-size: 16px;">
                                    ${fault.fault_title}
                                </strong>
                                <div style="display: flex; gap: 8px;">
                                    <span style="padding: 4px 10px; background: ${priorityColors[fault.priority]}; border-radius: 3px; font-size: 11px; font-weight: bold;">
                                        ${priorityLabels[fault.priority] || fault.priority}
                                    </span>
                                    <span style="padding: 4px 10px; background: ${statusColors[fault.status]}; border-radius: 3px; font-size: 11px; font-weight: bold;">
                                        ${statusLabels[fault.status] || fault.status}
                                    </span>
                                </div>
                            </div>
                            ${fault.fault_description ? `
                                <div style="color: #ccc; font-size: 13px; margin-bottom: 10px; line-height: 1.5;">
                                    ${fault.fault_description}
                                </div>
                            ` : ''}
                            <div style="color: #999; font-size: 12px;">
                                <i class="fas fa-clock"></i> 
                                Bildirim: ${formatDate(fault.reported_date)}
                            </div>
                            ${fault.resolved_date ? `
                                <div style="color: #4caf50; font-size: 12px; margin-top: 5px;">
                                    <i class="fas fa-check-circle"></i> 
                                    Çözüm: ${formatDate(fault.resolved_date)}
                                </div>
                            ` : ''}
                            ${fault.trello_card_url ? `
                                <div style="margin-top: 10px;">
                                    <a href="${fault.trello_card_url}" target="_blank" style="color: #c9a94f; text-decoration: none; font-size: 12px;">
                                        <i class="fab fa-trello"></i> Trello'da Görüntüle
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                    `;
                }).join('');
                
            } catch (error) {
                const content = document.getElementById('machine-faults-content');
                content.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #f44336;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <p>Arızalar yüklenirken hata oluştu:</p>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }
        
        // Event listeners
        historyBtn.addEventListener('click', async function() {
            const machineId = getCurrentMachineId();
            if (!machineId) {
                alert('Makina ID bulunamadı');
                return;
            }
            
            historyModal.classList.remove('hidden');
            document.getElementById('history-content').innerHTML = `
                <div style="text-align: center; padding: 40px; color: #c9a94f;">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p>Geçmiş yükleniyor...</p>
                </div>
            `;
            
            await loadMachineHistory(machineId);
        });
        
        faultsBtn.addEventListener('click', async function() {
            const machineId = getCurrentMachineId();
            if (!machineId) {
                alert('Makina ID bulunamadı');
                return;
            }
            
            machineFaultsModal.classList.remove('hidden');
            document.getElementById('machine-faults-content').innerHTML = `
                <div style="text-align: center; padding: 40px; color: #c9a94f;">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p>Arızalar yükleniyor...</p>
                </div>
            `;
            
            await loadMachineFaults(machineId);
        });
        
        
        // Load maintenance dates
        async function loadMaintenanceDates(machineId) {
            try {
                const response = await fetch(`api.php?action=get_maintenance_dates&machine_id=${machineId}`);
                const result = await response.json();
                
                if (!result.ok) {
                    throw new Error(result.error || 'Bilinmeyen hata');
                }
                
                const dates = result.maintenance_dates || [];
                const content = document.getElementById('maintenance-dates-content');
                
                if (dates.length === 0) {
                    content.innerHTML = `
                        <div style="text-align: center; padding: 40px; color: #666;">
                            <i class="fas fa-calendar-times" style="font-size: 48px; margin-bottom: 15px;"></i>
                            <p>Henüz bakım tarihi kaydı bulunmuyor.</p>
                        </div>
                    `;
                    return;
                }
                
                // Render maintenance dates list
                content.innerHTML = `
                    <div class="maintenance-dates-list">
                        ${dates.map((item, index) => `
                            <div class="maintenance-date-item">
                                <div class="maintenance-date-header">
                                    <div class="maintenance-date-date">
                                        <i class="fas fa-calendar-check"></i> ${item.maintenance_date}
                                    </div>
                                    ${item.maintenance_person ? `
                                        <div class="maintenance-date-person">
                                            <i class="fas fa-user-cog"></i> ${item.maintenance_person}
                                        </div>
                                    ` : ''}
                                </div>
                                ${item.note ? `
                                    <div class="maintenance-date-note">
                                        <i class="fas fa-sticky-note"></i> ${item.note}
                                    </div>
                                ` : ''}
                                <div class="maintenance-date-time">
                                    <i class="fas fa-clock"></i> Kayıt: ${formatDate(item.created_at)}
                                    ${item.performed_by !== 'system' ? ` • ${item.performed_by}` : ''}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
                
            } catch (error) {
                const content = document.getElementById('maintenance-dates-content');
                content.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #e74c3c;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <p>Bakım tarihleri yüklenirken hata oluştu:</p>
                        <p style="color: #999; font-size: 14px;">${error.message}</p>
                    </div>
                `;
            }
        }
        
        closeHistory.addEventListener('click', function() {
            historyModal.classList.add('hidden');
        });
        
        closeMachineFaults.addEventListener('click', function() {
            machineFaultsModal.classList.add('hidden');
        });
        
        if (closeMaintenanceDates) {
            closeMaintenanceDates.addEventListener('click', function() {
                maintenanceDatesModal.classList.add('hidden');
            });
        }
        
        // Maintenance dates button handler
        if (maintenanceDatesBtn && maintenanceDatesModal) {
            maintenanceDatesBtn.addEventListener('click', async function() {
                const machineId = getCurrentMachineId();
                if (!machineId) {
                    alert('Makina ID bulunamadı');
                    return;
                }
                
                maintenanceDatesModal.classList.remove('hidden');
                document.getElementById('maintenance-dates-content').innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #c9a94f;">
                        <i class="fas fa-spinner fa-spin fa-3x"></i>
                        <p>Bakım tarihleri yükleniyor...</p>
                    </div>
                `;
                
                await loadMaintenanceDates(machineId);
            });
        }
        
        // Close on outside click
        historyModal.addEventListener('click', function(e) {
            if (e.target === historyModal) {
                historyModal.classList.add('hidden');
            }
        });
        
        machineFaultsModal.addEventListener('click', function(e) {
            if (e.target === machineFaultsModal) {
                machineFaultsModal.classList.add('hidden');
            }
        });
        
        if (maintenanceDatesModal) {
            maintenanceDatesModal.addEventListener('click', function(e) {
                if (e.target === maintenanceDatesModal) {
                    maintenanceDatesModal.classList.add('hidden');
                }
            });
        }
    });
})();
