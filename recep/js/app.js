// js/app.js - BASİT GRUP SİSTEMİ - GÜNCELLENDİ (ARAMA SİSTEMİ EKLENDİ)
(function(){
// ARAMA SİSTEMİ DEĞİŞKENLERİ
let searchTimeout = null;
let allMachinesCache = null;
let lastSearchQuery = '';

const api = (params={}) => {
    const method = (params.method || 'GET').toUpperCase();
    let url = 'api.php';
    if (params.query) {
        url += '?' + new URLSearchParams(params.query);
    }
    if (method === 'GET') return fetch(url).then(r=>r.json());
    return fetch(url, {method:'POST', body: (params.body instanceof FormData)?params.body:new URLSearchParams(params.body)}).then(r=>r.json());
};

const map = document.getElementById('map');
const roomLabel = document.getElementById('current-room');
const modal = document.getElementById('modal');
const infoModal = document.getElementById('info-modal');
const editModal = document.getElementById('edit-modal');
const modalRoom = document.getElementById('modal-room');
const editRoom = document.getElementById('edit-room');
const form = document.getElementById('machine-form');
const editForm = document.getElementById('edit-form');
const addBtn = document.getElementById('add-machine');
const cancel = document.getElementById('cancel');
const cancelEdit = document.getElementById('cancel-edit');
const closeInfo = document.getElementById('close-info');
const editBtn = document.getElementById('edit-btn');
const deleteBtn = document.getElementById('delete-btn');
const roomBtns = document.querySelectorAll('.room-btn');

let currentRoom = ROOMS[0];
let machines = [];
let currentMachineId = null;
let dragging = null;
let draggingGroup = null;
let start = null;
let origin = null;
let shouldPreventClick = false;
let currentGroups = [];

// GRUP AYARLARI - DÜZELTİLDİ
const GROUP_DISTANCE = 85; // Daha geniş mesafe (63px makina + 22px boşluk)
const GROUP_SIZE = 40; // Grup butonu boyutu

// ARAMA SİSTEMİ BAŞLAT
function initializeSearch() {
    const searchInput = document.getElementById('machine-search');
    const searchBtn = document.getElementById('search-btn');
    const clearBtn = document.getElementById('clear-search');
    const resultsContainer = document.getElementById('search-results');
    
    if (!searchInput || !searchBtn) return;
    
    // Ara butonu tıklama
    searchBtn.addEventListener('click', () => {
        performSearch(searchInput.value.trim());
    });
    
    // Enter tuşu ile ara
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            performSearch(searchInput.value.trim());
        }
    });
    
    // Gerçek zamanlı arama (typing)
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        
        // Clear butonunu göster/gizle
        if (clearBtn) {
            clearBtn.style.display = query ? 'flex' : 'none';
        }
        
        // Eski timeout'u temizle
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        // Boş sorgu için sonuçları temizle
        if (!query) {
            hideSearchResults();
            return;
        }
        
        // 500ms sonra ara (debouncing)
        searchTimeout = setTimeout(() => {
            performSearch(query, true); // true = real-time search
        }, 500);
    });
    
    // Clear butonu
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            searchInput.focus();
            clearBtn.style.display = 'none';
            hideSearchResults();
            removeFoundAnimation();
        });
    }
    
    // Input focus olduğunda
    searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim() && allMachinesCache) {
            performSearch(searchInput.value.trim(), true);
        }
    });
    
    // Dışarı tıklayınca sonuçları gizle
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && 
            !searchBtn.contains(e.target) && 
            !clearBtn.contains(e.target) &&
            !resultsContainer.contains(e.target)) {
            hideSearchResults();
        }
    });
    
    // Escape tuşu ile sonuçları gizle
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            hideSearchResults();
            searchInput.blur();
        }
    });
    
    console.log('🔍 Arama sistemi başlatıldı');
}

// TÜM MAKİNALARI ÇEK (CACHE İÇİN)
async function loadAllMachines() {
    try {
        console.log('📦 Tüm makineler cache için yükleniyor...');
        
        // Tüm salonlar için makineleri topla
        const allMachines = [];
        
        for (const room of ROOMS) {
            const res = await api({query:{action:'list', room: room}});
            if (res.ok && res.machines) {
                // Salon bilgisini ekle
                res.machines.forEach(machine => {
                    machine.room = room;
                    allMachines.push(machine);
                });
            }
        }
        
        allMachinesCache = allMachines;
        console.log(`✅ ${allMachines.length} makina cache'lendi`);
        return allMachines;
        
    } catch (error) {
        console.error('❌ Tüm makineler yüklenirken hata:', error);
        return [];
    }
}

// ARAMA YAP
async function performSearch(query, isRealTime = false) {
    if (!query || query.length < 1) {
        hideSearchResults();
        return;
    }
    
    lastSearchQuery = query;
    
    // Arama yapılıyor göster
    showSearchingIndicator();
    
    try {
        // Önce cache'den ara
        let results = [];
        
        if (allMachinesCache) {
            results = searchInCache(query);
        } else {
            // Cache yoksa, tüm makineleri yükle ve ara
            const allMachines = await loadAllMachines();
            results = searchInMachines(allMachines, query);
        }
        
        // Sonuçları göster
        displaySearchResults(results, query, isRealTime);
        
    } catch (error) {
        console.error('❌ Arama hatası:', error);
        showSearchError();
    } finally {
        removeSearchingIndicator();
    }
}

// CACHE'DE ARA
function searchInCache(query) {
    if (!allMachinesCache || !query) return [];
    
    const searchTerm = query.toLowerCase();
    const results = [];
    
    allMachinesCache.forEach(machine => {
        // Makina numarasında ara
        const machineNumber = String(machine.machine_number || '').toLowerCase();
        
        // Marka/modelde ara
        const brandModel = String(machine.brand_model || '').toLowerCase();
        
        // Not'ta ara
        const note = String(machine.note || '').toLowerCase();
        
        // Arama kriterleri
        if (machineNumber.includes(searchTerm) || 
            brandModel.includes(searchTerm) || 
            note.includes(searchTerm)) {
            
            // Bakım durumunu hesapla
            const status = getMaintenanceStatus(machine.maintenance_date);
            
            results.push({
                ...machine,
                status: status.status,
                statusText: status.text
            });
        }
    });
    
    return results;
}

// ARAMA SONUÇLARINI GÖSTER
function displaySearchResults(results, query, isRealTime = false) {
    const resultsContainer = document.getElementById('search-results');
    if (!resultsContainer) return;
    
    // Eski sonuçları temizle
    resultsContainer.innerHTML = '';
    
    if (results.length === 0) {
        // Sonuç bulunamadı
        resultsContainer.innerHTML = `
            <div class="search-no-results">
                <i class="fas fa-search"></i>
                <div style="margin-top:10px; font-weight:bold; color:#e74c3c;">
                    "${query}" bulunamadı
                </div>
                <div style="margin-top:5px; font-size:13px; color:rgba(255,255,255,0.6);">
                    Farklı bir makina numarası deneyin
                </div>
            </div>
        `;
        resultsContainer.classList.add('active');
        return;
    }
    
    // Bilgi mesajı
    const infoHtml = `
        <div class="search-info">
            <i class="fas fa-info-circle"></i>
            ${results.length} sonuç bulundu
            ${isRealTime ? '(gerçek zamanlı)' : ''}
        </div>
    `;
    
    resultsContainer.innerHTML = infoHtml;
    
    // Sonuçları ekle
    results.forEach((machine, index) => {
        const resultItem = document.createElement('div');
        resultItem.className = 'search-result-item';
        resultItem.dataset.machineId = machine.id;
        resultItem.dataset.room = machine.room;
        
        // Status color
        let statusColor = 'green';
        if (machine.status === 'blue') statusColor = 'blue';
        if (machine.status === 'red') statusColor = 'red';
        
        resultItem.innerHTML = `
            <div class="search-result-info">
                <div class="search-result-number">
                    <span class="search-result-status ${statusColor}"></span>
                    ${machine.machine_number}
                </div>
                <div class="search-result-details">
                    ${machine.brand_model} • ${machine.statusText}
                </div>
            </div>
            <div class="search-result-room">
                ${machine.room}
            </div>
        `;
        
        // Tıklama olayı
        resultItem.addEventListener('click', () => {
            goToMachine(machine);
        });
        
        resultsContainer.appendChild(resultItem);
    });
    
    // Sonuçları göster
    resultsContainer.classList.add('active');
    
    // Real-time değilse, ilk sonuca git
    if (!isRealTime && results.length > 0) {
        setTimeout(() => {
            goToMachine(results[0]);
        }, 100);
    }
}

// MAKİNAYA GİT
function goToMachine(machine) {
    if (!machine) return;
    
    // Arama sonuçlarını gizle
    hideSearchResults();
    
    // Arama input'una makina numarasını yaz
    const searchInput = document.getElementById('machine-search');
    if (searchInput) {
        searchInput.value = machine.machine_number;
    }
    
    // Clear butonunu göster
    const clearBtn = document.getElementById('clear-search');
    if (clearBtn) {
        clearBtn.style.display = 'flex';
    }
    
    // Salon değişikliği animasyonu
    const currentRoomEl = document.getElementById('current-room');
    if (currentRoomEl && currentRoomEl.textContent !== machine.room) {
        currentRoomEl.classList.add('search-room-switch');
        
        setTimeout(() => {
            // Salonu değiştir
            setActiveRoom(machine.room);
            
            // Salon değişikliği tamamlandığında makinayı bul
            setTimeout(() => {
                findAndHighlightMachine(machine.id);
                currentRoomEl.classList.remove('search-room-switch');
            }, 300);
        }, 100);
    } else {
        // Aynı salondaysa direkt bul
        findAndHighlightMachine(machine.id);
    }
}

// MAKİNAYI BUL VE VURGULA
function findAndHighlightMachine(machineId) {
    // Mevcut salonun makinelerini yükle (eğer yüklenmediyse)
    if (!machines || machines.length === 0) {
        loadMachines().then(() => {
            setTimeout(() => highlightMachine(machineId), 300);
        });
        return;
    }
    
    // Makinayı bul
    const machine = machines.find(m => String(m.id) === String(machineId));
    if (!machine) {
        console.warn(`❌ Makina ${machineId} bulunamadı`);
        return;
    }
    
    highlightMachine(machineId);
}

// MAKİNAYI VURGULA (ANİMASYON)
function highlightMachine(machineId) {
    // Önceki animasyonları temizle
    removeFoundAnimation();
    
    // Makina elementini bul
    const machineElement = document.querySelector(`.machine[data-id="${machineId}"]`);
    if (!machineElement) {
        console.warn(`❌ Makina elementi ${machineId} bulunamadı`);
        return;
    }
    
    // Makinanın mevcut pozisyonunu al
    const transform = machineElement.style.transform;
    const matches = transform.match(/translate\(([^,]+)px,\s*([^)]+)px\)/);
    
    if (matches) {
        const x = parseFloat(matches[1]);
        const y = parseFloat(matches[2]);
        
        // Rotasyonu al
        const rotation = getRotation(machineElement);
        
        // CSS değişkenlerini ayarla
        machineElement.style.setProperty('--found-x', `${x}px`);
        machineElement.style.setProperty('--found-y', `${y}px`);
        machineElement.style.setProperty('--found-rotation', `${rotation}deg`);
    }
    
    // Animasyon class'ını ekle
    machineElement.classList.add('machine-found');
    
    // Elementi görünür yap (z-index)
    machineElement.style.zIndex = '10000';
    
    // Haritayı makina pozisyonuna kaydır
    scrollToMachine(machineElement);
    
    // 3 saniye sonra animasyonu kaldır
    setTimeout(() => {
        machineElement.classList.remove('machine-found');
        machineElement.style.zIndex = '';
    }, 3000);
    
    console.log(`✅ Makina ${machineId} bulundu ve vurgulandı`);
}

// HARİTAYI MAKİNAYA KAYDIR
function scrollToMachine(machineElement) {
    if (!machineElement || !map) return;
    
    const machineRect = machineElement.getBoundingClientRect();
    const mapRect = map.getBoundingClientRect();
    
    // Makina haritanın neresinde?
    const machineCenterX = machineRect.left + machineRect.width / 2;
    const machineCenterY = machineRect.top + machineRect.height / 2;
    
    // Harita merkezine göre offset hesapla
    const targetScrollLeft = map.scrollLeft + (machineCenterX - mapRect.width / 2);
    const targetScrollTop = map.scrollTop + (machineCenterY - mapRect.height / 2);
    
    // Yumuşak kaydırma
    map.scrollTo({
        left: targetScrollLeft,
        top: targetScrollTop,
        behavior: 'smooth'
    });
}

// ARAMA SONUÇLARINI GİZLE
function hideSearchResults() {
    const resultsContainer = document.getElementById('search-results');
    if (resultsContainer) {
        resultsContainer.classList.remove('active');
    }
}

// ARAMA YAPILIYOR GÖSTERGE
function showSearchingIndicator() {
    const searchWrapper = document.querySelector('.search-wrapper');
    if (searchWrapper) {
        searchWrapper.classList.add('searching');
    }
}

// ARAMA GÖSTERGESİNİ KALDIR
function removeSearchingIndicator() {
    const searchWrapper = document.querySelector('.search-wrapper');
    if (searchWrapper) {
        searchWrapper.classList.remove('searching');
    }
}

// ARAMA HATASI GÖSTER
function showSearchError() {
    const resultsContainer = document.getElementById('search-results');
    if (!resultsContainer) return;
    
    resultsContainer.innerHTML = `
        <div class="search-no-results">
            <i class="fas fa-exclamation-triangle"></i>
            <div style="margin-top:10px; font-weight:bold; color:#ff9800;">
                Arama sırasında hata oluştu
            </div>
            <div style="margin-top:5px; font-size:13px; color:rgba(255,255,255,0.6);">
                Lütfen tekrar deneyin
            </div>
        </div>
    `;
    resultsContainer.classList.add('active');
}

// BULUNAN ANİMASYONU TEMİZLE
function removeFoundAnimation() {
    document.querySelectorAll('.machine-found').forEach(el => {
        el.classList.remove('machine-found');
        el.style.zIndex = '';
    });
}

// ORİJİNAL FONKSİYONLAR (DEĞİŞMEDİ)
function setActiveRoom(room){
    currentRoom = room;
    roomLabel.textContent = room;
    modalRoom.textContent = room;
    roomBtns.forEach(b=>b.classList.toggle('active', b.dataset.room===room));
    
    let roomClass = '';
    if (room === 'ALÇAK TAVAN') roomClass = 'room-alcak-tavan';
    else if (room === 'YÜKSEK TAVAN') roomClass = 'room-yuksek-tavan';
    else if (room === 'YENİ VİP SALON') roomClass = 'room-yeni-vip-salon';
    else if (room === 'ALT SALON') roomClass = 'room-alt-salon';
    else roomClass = 'room-' + room.toLowerCase().replace(/\s+/g, '-');
    
    map.className = 'map ' + roomClass;
    
    loadMachines();
}

roomBtns.forEach(b=>{
    b.addEventListener('click', ()=> setActiveRoom(b.dataset.room));
});

setActiveRoom(currentRoom);

addBtn.addEventListener('click', ()=> {
    modal.classList.remove('hidden');
});

cancel.addEventListener('click', ()=> {
    modal.classList.add('hidden');
    form.reset();
});

cancelEdit.addEventListener('click', ()=> {
    editModal.classList.add('hidden');
    editForm.reset();
});

closeInfo.addEventListener('click', ()=> {
    infoModal.classList.add('hidden');
    currentMachineId = null;
});

editBtn.addEventListener('click', async ()=> {
    if (!currentMachineId) return;
    const res = await api({query:{action:'get', id:currentMachineId}});
    if (res.ok) {
        const m = res.machine;
        document.getElementById('edit-id').value = m.id;
        document.getElementById('edit-number').value = m.machine_number;
        document.getElementById('edit-brand').value = m.brand_model;
        document.getElementById('edit-date').value = m.maintenance_date;
        document.getElementById('edit-note').value = m.note || '';
        editRoom.textContent = m.room;
        infoModal.classList.add('hidden');
        editModal.classList.remove('hidden');
    }
});

deleteBtn.addEventListener('click', async ()=> {
    if (!currentMachineId || !confirm('Bu makina silinecek. Emin misiniz?')) return;
    const res = await api({method:'POST', body:{action:'delete', id:currentMachineId}});
    if (res.ok) {
        infoModal.classList.add('hidden');
        currentMachineId = null;
        loadMachines();
    } else {
        alert('Silme hatası: ' + (res.error || ''));
    }
});

form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new FormData(form);
    fd.set('room', currentRoom);
    fd.set('x', 30);
    fd.set('y', 30);
    fd.set('size', 63);
    fd.set('rotation', 0);
    const res = await api({method:'POST', body:fd, query:{action:'create'}});
    if (res.ok) {
        modal.classList.add('hidden');
        form.reset();
        machines.push(res.machine);
        renderMachines();
    } else {
        alert('Hata: ' + (res.error || ' oluşturulamadı'));
    }
});

editForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new FormData(editForm);
    const id = document.getElementById('edit-id').value;
    const res = await api({method:'POST', body:fd, query:{action:'update', id:id}});
    if (res.ok) {
        editModal.classList.add('hidden');
        editForm.reset();
        loadMachines();
    } else {
        alert('Hata: ' + (res.error || ' güncellenemedi'));
    }
});

async function loadMachines(){
    const res = await api({query:{action:'list', room: currentRoom}});
    if (res.ok) {
        machines = res.machines;
        renderMachines();
    } else {
        console.error(res);
    }
}

function getMaintenanceStatus(maintenanceDate) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const maintDate = new Date(maintenanceDate);
    maintDate.setHours(0, 0, 0, 0);
    
    const diffTime = today - maintDate;
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays <= 21) {
        return {
            class: 'maintenance-green',
            text: `✅ Bakım yapıldı (${diffDays} gün önce)`,
            days: diffDays,
            status: 'green'
        };
    } else if (diffDays <= 28) {
        return {
            class: 'maintenance-blue',
            text: `ℹ️ Bakım yaklaşıyor (${diffDays} gün geçti)`,
            days: diffDays,
            status: 'blue'
        };
    } else {
        return {
            class: 'maintenance-red',
            text: `⚠️ BAKIM GEREKLİ! (${diffDays} gün geçti)`,
            days: diffDays,
            status: 'red'
        };
    }
}

// DÜZELTİLMİŞ GRUP TESPİTİ - ÇOK DAHA BASİT VE ETKİLİ
function detectGroups() {
    const groups = [];
    const visited = new Set();
    
    machines.forEach((machine) => {
        if (visited.has(machine.id)) return;
        
        const group = [machine.id];
        visited.add(machine.id);
        
        // Bu makina ile temas eden diğer makineleri bul
        const findConnected = (currentId) => {
            const currentMachine = machines.find(m => m.id === currentId);
            if (!currentMachine) return;
            
            machines.forEach(other => {
                if (other.id === currentId || visited.has(other.id)) return;
                
                // İki makina arasındaki mesafe
                const dx = Math.abs(currentMachine.x - other.x);
                const dy = Math.abs(currentMachine.y - other.y);
                
                // Makina boyutunu al (varsayılan 63px)
                const machineSize = currentMachine.size || 63;
                
                // Eğer makineler birbirine yakınsa (temas veya 22px içinde)
                if (dx <= machineSize + 22 && dy <= machineSize + 22) {
                    group.push(other.id);
                    visited.add(other.id);
                    findConnected(other.id); // Rekursif olarak devam et
                }
            });
        };
        
        findConnected(machine.id);
        
        if (group.length > 1) {
            groups.push(group);
        }
    });
    
    currentGroups = groups;
    return groups;
}

// GRUP MERKEZİNİ HESAPLA (GRUBUN ORTASI)
function calculateGroupCenter(groupIds) {
    const groupMachines = machines.filter(m => groupIds.includes(m.id));
    if (groupMachines.length === 0) return {x: 0, y: 0};
    
    // Grup sınırlarını bul
    let minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
    
    groupMachines.forEach(m => {
        const size = m.size || 63;
        minX = Math.min(minX, m.x);
        maxX = Math.max(maxX, m.x + size);
        minY = Math.min(minY, m.y);
        maxY = Math.max(maxY, m.y + size);
    });
    
    // Grubun üst orta noktası (grup butonu için)
    return {
        x: (minX + maxX) / 2 - GROUP_SIZE/2,
        y: minY - GROUP_SIZE - 10, // Grubun 10px üstünde
        minX, maxX, minY, maxY,
        count: groupIds.length
    };
}

// GRUP BUTONU OLUŞTUR - GÜNCELLENDİ (BAKIM GÜNCELLEME EKLENDİ)
function createGroupButton(groupIds, groupIndex) {
    const center = calculateGroupCenter(groupIds);
    
    const groupBtn = document.createElement('div');
    groupBtn.className = 'group-handle';
    groupBtn.id = `group-${groupIndex}`;
    groupBtn.dataset.groupIds = JSON.stringify(groupIds);
    groupBtn.style.left = center.x + 'px';
    groupBtn.style.top = center.y + 'px';
    groupBtn.style.width = GROUP_SIZE + 'px';
    groupBtn.style.height = GROUP_SIZE + 'px';
    
    groupBtn.innerHTML = `
        <div class="group-circle" title="${center.count} makinalı grup - Tıkla: Bilgi, Sürükle: Taşı">
            <span class="group-count">${center.count}</span>
        </div>
    `;
    
    // Tıklama olayı - GÜNCELLENDİ
    groupBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        showGroupInfo(groupIds);
    });
    
    // Sürükleme olayı (orijinal kod)
    makeGroupDraggable(groupBtn, groupIds);
    
    return groupBtn;
}

// YENİ: GRUP BİLGİSİ GÖSTER (BAKIM GÜNCELLEME EKLENDİ)
function showGroupInfo(groupIds) {
    const groupMachines = machines.filter(m => groupIds.includes(m.id));
    
    let modalHtml = `
        <div class="modal" id="group-info-modal">
            <div class="modal-content" style="width:500px; max-height:80vh; overflow-y:auto;">
                <h2 style="color:#9c27b0; text-align:center;">👥 MAKİNA GRUBU (${groupMachines.length} Makina)</h2>
                
                <!-- GRUP YÖNETİM BUTONLARI -->
                <div style="margin:15px 0; padding:10px; background:rgba(156,39,176,0.1); border-radius:8px; text-align:center;">
                    <button onclick="updateGroupMaintenanceDate([${groupIds}])" 
                            style="background:#4caf50; color:white; border:none; padding:10px 15px; border-radius:6px; cursor:pointer; font-weight:bold; margin:5px;">
                        📅 Tüm Grubun Bakım Tarihini Güncelle
                    </button>
                </div>
                
                <div style="margin:20px 0;">
    `;
    
    groupMachines.forEach(m => {
        const status = getMaintenanceStatus(m.maintenance_date);
        modalHtml += `
            <div style="background:rgba(0,0,0,0.05); padding:10px; margin:5px 0; border-radius:5px; border-left:3px solid #9c27b0;">
                <strong>${m.machine_number}</strong> - ${m.brand_model}
                <div style="font-size:12px; color:${status.status === 'red' ? '#e74c3c' : status.status === 'blue' ? '#3498db' : '#2ecc71'}">
                    ${status.text}
                </div>
            </div>
        `;
    });
    
    modalHtml += `
                </div>
                <div class="form-actions" style="justify-content:center;">
                    <button onclick="document.getElementById('group-info-modal').remove()">Kapat</button>
                </div>
            </div>
        </div>
    `;
    
    // Eski modal varsa kaldır
    const oldModal = document.getElementById('group-info-modal');
    if (oldModal) oldModal.remove();
    
    // Yeni modal ekle
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

// YENİ: GRUP BAKIM TARİHİ GÜNCELLEME FONKSİYONU
async function updateGroupMaintenanceDate(groupIds) {
    const groupMachines = machines.filter(m => groupIds.includes(m.id));
    if (groupMachines.length === 0) return;
    
    // En eski bakım tarihini al (varsayılan olarak)
    const earliestDate = groupMachines.reduce((earliest, m) => {
        return m.maintenance_date < earliest ? m.maintenance_date : earliest;
    }, groupMachines[0].maintenance_date);
    
    // Tarih seçim modal'ı - GÜNCELLENMİŞ
    const modalHtml = `
        <div class="modal" id="group-date-modal">
            <div class="modal-content" style="width:420px;">
                <h2 style="color:#4caf50; text-align:center; display:flex; align-items:center; justify-content:center; gap:10px;">
                    <span style="font-size:28px;">📅</span> Grup Bakım Tarihi
                </h2>
                <p style="text-align:center; margin:15px 0; color:rgba(255,255,255,0.8);">
                    <strong style="color:#4caf50;">${groupIds.length} makina</strong> için bakım tarihi güncelle
                </p>
                <div style="margin:25px 0;">
                    <label style="display:block; margin-bottom:10px; font-weight:bold; color:#4caf50;">
                        📋 Yeni Bakım Tarihi:
                    </label>
                    <div class="custom-date-wrapper" style="position:relative;">
                        <input type="date" id="group-new-date" 
                               value="${earliestDate}"
                               style="width:100%; padding:12px 40px 12px 15px; border-radius:8px; 
                                      border:2px solid #4caf50; background:rgba(0,0,0,0.5); color:white;
                                      font-size:16px;">
                        <span style="position:absolute; right:15px; top:50%; transform:translateY(-50%); 
                              color:#4caf50; font-size:20px; pointer-events:none;">📅</span>
                    </div>
                    <div style="margin-top:10px; font-size:12px; color:rgba(255,255,255,0.6);">
                        <span style="color:#ff9800;">💡 İpucu:</span> Takvim simgesine tıklayarak tarih seçebilirsiniz.
                    </div>
                </div>
                <div class="form-actions" style="justify-content:center; gap:15px;">
                    <button onclick="saveGroupMaintenanceDate([${groupIds}])" 
                            style="background:#4caf50; padding:12px 25px; font-size:16px; display:flex; align-items:center; gap:8px;">
                        <span style="font-size:18px;">💾</span> Kaydet
                    </button>
                    <button onclick="document.getElementById('group-date-modal').remove()"
                            style="background:rgba(255,255,255,0.1); padding:12px 25px;">
                        İptal
                    </button>
                </div>
            </div>
        </div>
    `;
    
    const oldModal = document.getElementById('group-date-modal');
    if (oldModal) oldModal.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Takvimi otomatik aç
    setTimeout(() => {
        const dateInput = document.getElementById('group-new-date');
        if (dateInput) {
            dateInput.focus();
            // Mobile için show picker
            if ('showPicker' in HTMLInputElement.prototype) {
                dateInput.showPicker();
            }
        }
    }, 300);
}

// YENİ: GRUP BAKIM TARİHİ KAYDET
async function saveGroupMaintenanceDate(groupIds) {
    const dateInput = document.getElementById('group-new-date');
    const newDate = dateInput.value;
    
    if (!newDate) {
        alert('Lütfen bir tarih seçin!');
        return;
    }
    
    if (!confirm(`${groupIds.length} makina için bakım tarihini "${newDate}" olarak güncellemek istediğinize emin misiniz?`)) {
        return;
    }
    
    try {
        // Her makina için tek tek güncelle (basit yöntem)
        let successCount = 0;
        
        for (const id of groupIds) {
            const machine = machines.find(m => m.id === id);
            if (!machine) continue;
            
            const formData = new FormData();
            formData.append('id', id);
            formData.append('machine_number', machine.machine_number);
            formData.append('brand_model', machine.brand_model);
            formData.append('maintenance_date', newDate);
            formData.append('note', machine.note || '');
            
            const res = await api({
                method: 'POST',
                body: formData,
                query: { action: 'update' }
            });
            
            if (res.ok) {
                successCount++;
                // Lokal veriyi güncelle
                machine.maintenance_date = newDate;
            }
        }
        
        if (successCount === groupIds.length) {
            alert(`✅ Başarılı! ${successCount} makina güncellendi.`);
        } else {
            alert(`⚠️ ${successCount}/${groupIds.length} makina güncellendi.`);
        }
        
        // Modal'ları kapat
        document.getElementById('group-date-modal')?.remove();
        document.getElementById('group-info-modal')?.remove();
        
        // Ekranı yenile
        renderMachines();
        
    } catch (error) {
        console.error('Grup güncelleme hatası:', error);
        alert('Güncelleme sırasında bir hata oluştu.');
    }
}

// GRUP SÜRÜKLEME (ORİJİNAL KOD - DEĞİŞMEDİ)
function makeGroupDraggable(groupBtn, groupIds) {
    let isDragging = false;
    let startX, startY, btnStartX, btnStartY;
    let machineStartPositions = [];
    
    groupBtn.addEventListener('pointerdown', (ev) => {
        ev.stopPropagation();
        ev.preventDefault();
        
        if (ev.button !== 0) return;
        
        isDragging = true;
        startX = ev.clientX;
        startY = ev.clientY;
        btnStartX = parseInt(groupBtn.style.left) || 0;
        btnStartY = parseInt(groupBtn.style.top) || 0;
        
        // Grup makinelerinin başlangıç pozisyonlarını kaydet
        const groupMachines = machines.filter(m => groupIds.includes(m.id));
        machineStartPositions = groupMachines.map(m => ({
            id: m.id,
            element: document.querySelector(`.machine[data-id="${m.id}"]`),
            startX: m.x,
            startY: m.y
        }));
        
        groupBtn.style.transition = 'none';
        groupBtn.classList.add('dragging');
        
        // Mousemove ve mouseup event'lerini dinle
        document.addEventListener('pointermove', onPointerMove);
        document.addEventListener('pointerup', onPointerUp);
        document.addEventListener('pointercancel', onPointerUp);
    });
    
    function onPointerMove(moveEv) {
        if (!isDragging) return;
        moveEv.preventDefault();
        
        const deltaX = moveEv.clientX - startX;
        const deltaY = moveEv.clientY - startY;
        
        // Grup butonunu hareket ettir
        const newBtnX = btnStartX + deltaX;
        const newBtnY = btnStartY + deltaY;
        
        groupBtn.style.left = newBtnX + 'px';
        groupBtn.style.top = newBtnY + 'px';
        
        // Tüm grup makinelerini hareket ettir
        machineStartPositions.forEach(pos => {
            if (pos.element) {
                const newX = pos.startX + deltaX;
                const newY = pos.startY + deltaY;
                const rotation = getRotation(pos.element);
                pos.element.style.transform = `translate(${newX}px, ${newY}px) rotate(${rotation}deg)`;
                pos.element.classList.add('group-dragging');
            }
        });
    }
    
    async function onPointerUp(upEv) {
        if (!isDragging) return;
        
        isDragging = false;
        
        // Event listener'ları kaldır
        document.removeEventListener('pointermove', onPointerMove);
        document.removeEventListener('pointerup', onPointerUp);
        document.removeEventListener('pointercancel', onPointerUp);
        
        const deltaX = upEv.clientX - startX;
        const deltaY = upEv.clientY - startY;
        
        // Eğer yeterince hareket ettiyse (5px'ten fazla)
        if (Math.abs(deltaX) > 5 || Math.abs(deltaY) > 5) {
            // API'ye grup hareketini gönder
            const groupData = machineStartPositions.map(pos => ({
                id: pos.id,
                x: Math.round(pos.startX + deltaX),
                y: Math.round(pos.startY + deltaY)
            }));
            
            try {
                const res = await api({
                    method: 'POST',
                    body: {
                        action: 'move_group',
                        group_data: JSON.stringify(groupData)
                    }
                });
                
                if (res.ok) {
                    // Lokal verileri güncelle
                    groupData.forEach(item => {
                        const machine = machines.find(m => m.id === item.id);
                        if (machine) {
                            machine.x = item.x;
                            machine.y = item.y;
                        }
                    });
                    
                    // Grup butonunu yeni konuma taşı
                    const newCenter = calculateGroupCenter(groupIds);
                    groupBtn.style.left = newCenter.x + 'px';
                    groupBtn.style.top = newCenter.y + 'px';
                    groupBtn.style.transition = 'all 0.2s ease';
                    
                    // Grupları yeniden kontrol et
                    setTimeout(() => {
                        renderMachines();
                    }, 100);
                } else {
                    // Hata durumunda eski pozisyona dön
                    revertGroupPosition();
                    alert('Grup hareketi hatası: ' + (res.error || ''));
                }
            } catch (error) {
                console.error('Grup hareket hatası:', error);
                revertGroupPosition();
            }
        }
        
        // Dragging class'larını kaldır
        groupBtn.classList.remove('dragging');
        machineStartPositions.forEach(pos => {
            if (pos.element) {
                pos.element.classList.remove('group-dragging');
            }
        });
        
        function revertGroupPosition() {
            groupBtn.style.left = btnStartX + 'px';
            groupBtn.style.top = btnStartY + 'px';
            
            machineStartPositions.forEach(pos => {
                if (pos.element) {
                    pos.element.style.transform = `translate(${pos.startX}px, ${pos.startY}px) rotate(${getRotation(pos.element)}deg)`;
                }
            });
        }
    }
}

function renderMachines(){
    map.innerHTML = '';
    
    // Grupları tespit et
    const groups = detectGroups();
    
    // Grup butonlarını oluştur
    groups.forEach((groupIds, index) => {
        const groupBtn = createGroupButton(groupIds, index);
        map.appendChild(groupBtn);
    });
    
    // Tüm makineleri renderla
    machines.forEach(m => {
        const el = document.createElement('div');
        el.className = 'machine';
        el.dataset.id = m.id;
        el.style.transform = `translate(${m.x}px, ${m.y}px) rotate(${m.rotation || 0}deg)`;
        el.style.width = (m.size || 63) + 'px';
        el.style.height = (m.size || 63) + 'px';
        
        const brandUpper = (m.brand_model || '').toUpperCase();
        const maintenanceInfo = getMaintenanceStatus(m.maintenance_date);
        
        // Grup üyesi mi kontrol et
        let isGroupMember = false;
        groups.forEach(group => {
            if (group.includes(m.id)) {
                isGroupMember = true;
            }
        });
        
        el.classList.add(maintenanceInfo.class);
        if (isGroupMember) {
            el.classList.add('group-member');
        }
        
        el.innerHTML = `
            <div class="meta">
                <div class="num">${escapeHtml(m.machine_number)}</div>
                <div class="brand">${escapeHtml(brandUpper)}</div>
            </div>
            <div class="rotate-btn" title="90° Döndür">⟳</div>
        `;
        
        map.appendChild(el);
        makeDraggable(el);
        makeRotatable(el);
        makeClickable(el, m.id);
    });
    
    // Bakım durumu göstergesi ekle
    addMaintenanceLegend();
}

function showMachineInfo(id){
    const m = machines.find(mm=>String(mm.id)===String(id));
    if (!m) return;
    currentMachineId = id;
    
    const infoDiv = document.querySelector('.machine-info');
    const maintenanceInfo = getMaintenanceStatus(m.maintenance_date);
    
    // Grup bilgisi
    let groupInfo = '';
    currentGroups.forEach(group => {
        if (group.includes(m.id)) {
            const groupMachines = machines.filter(machine => group.includes(machine.id));
            const groupSize = groupMachines.length;
            groupInfo = `
                <div class="group-info">
                    👥 Bu makina <strong>${groupSize} makinalı</strong> bir grubun parçası.
                    <button class="show-group-btn" onclick="showGroupInfo([${group.join(',')}])">Grubu Göster</button>
                </div>
            `;
        }
    });
    
    infoDiv.innerHTML = `
        <div class="info-row"><span class="label">Salon:</span><span class="value" id="info-room"></span></div>
        <div class="info-row"><span class="label">Makina No:</span><span class="value" id="info-number"></span></div>
        <div class="info-row"><span class="label">Marka/Model:</span><span class="value" id="info-brand"></span></div>
        <div class="info-row"><span class="label">Bakım Tarihi:</span><span class="value" id="info-date"></span></div>
        <div class="info-row full"><span class="label">Not:</span><span class="value" id="info-note"></span></div>
        <div class="info-row">
            <span class="label">Bakım Durumu:</span>
            <span class="value"><span class="maintenance-text ${maintenanceInfo.status}">${maintenanceInfo.text}</span></span>
        </div>
        ${groupInfo}
    `;
    
    document.getElementById('info-room').textContent = m.room;
    document.getElementById('info-number').textContent = m.machine_number;
    document.getElementById('info-brand').textContent = m.brand_model;
    document.getElementById('info-date').textContent = m.maintenance_date;
    document.getElementById('info-note').textContent = m.note || '-';
    
    infoModal.classList.remove('hidden');
}

function escapeHtml(s){ 
    return String(s).replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[c])); 
}

function makeDraggable(el){
    el.addEventListener('pointerdown', (ev)=>{
        if (ev.target.classList.contains('rotate-btn')) return;
        el.setPointerCapture(ev.pointerId);
        dragging = el;
        shouldPreventClick = false;
        start = {x: ev.clientX, y: ev.clientY};
        const tr = getTranslate(el);
        origin = {x: tr.x, y: tr.y};
        el.style.transition = 'none';
        el.style.zIndex = 9999;
        el.classList.add('dragging');
    });

    el.addEventListener('pointermove', (ev)=>{
        if (!dragging || dragging !== el) return;
        ev.preventDefault();
        shouldPreventClick = true;
        const dx = ev.clientX - start.x;
        const dy = ev.clientY - start.y;
        const newX = origin.x + dx;
        const newY = origin.y + dy;
        el.style.transform = `translate(${newX}px, ${newY}px) rotate(${getRotation(el)}deg)`;
    });

    el.addEventListener('pointerup', async (ev)=>{
        if (!dragging || dragging !== el) {
            dragging = null;
            return;
        }
        el.releasePointerCapture(ev.pointerId);
        
        const tr = getTranslate(el);
        const snap = calculateSnap(el, tr.x, tr.y);
        const finalX = snap.x;
        const finalY = snap.y;
        const rotation = getRotation(el);
        el.style.transform = `translate(${finalX}px, ${finalY}px) rotate(${rotation}deg)`;
        el.style.transition = 'transform .12s ease';
        el.style.zIndex = '';
        el.classList.remove('dragging');
        
        if (shouldPreventClick) {
            const id = el.dataset.id;
            const res = await api({method:'POST', body:{action:'move', id:id, x:Math.round(finalX), y:Math.round(finalY)}});
            if (!res.ok) console.error(res);
            const m = machines.find(mm=>String(mm.id)===String(id));
            if (m){ 
                m.x = Math.round(finalX); 
                m.y = Math.round(finalY); 
            }
            
            // Grup butonlarını yeniden oluştur
            setTimeout(() => {
                renderMachines();
            }, 100);
        }
        
        dragging = null;
    });

    el.addEventListener('pointercancel', ()=> {
        dragging = null;
        shouldPreventClick = false;
        if (el) el.classList.remove('dragging');
    });
}

function makeClickable(el, machineId){
    el.addEventListener('click', (ev)=> {
        if (ev.target.classList.contains('rotate-btn')) return;
        if (shouldPreventClick) {
            shouldPreventClick = false;
            return;
        }
        showMachineInfo(machineId);
    });
}

function makeRotatable(el){
    const btn = el.querySelector('.rotate-btn');
    btn.addEventListener('click', async (ev)=>{
        ev.stopPropagation();
        const currentRot = getRotation(el);
        const newRot = (currentRot + 90) % 360;
        el.style.transform = `translate(${getTranslate(el).x}px, ${getTranslate(el).y}px) rotate(${newRot}deg)`;
        
        const id = el.dataset.id;
        const res = await api({method:'POST', body:{action:'rotate', id:id, rotation:newRot}});
        if (res.ok) {
            const m = machines.find(mm=>String(mm.id)===String(id));
            if (m){ m.rotation = newRot; }
        } else {
            console.error(res);
        }
    });
}

function getTranslate(el){
    const st = window.getComputedStyle(el);
    const tr = st.transform || st.webkitTransform;
    if (tr && tr !== 'none') {
        const vals = tr.match(/matrix.*\((.+)\)/)[1].split(', ');
        return {x: parseFloat(vals[4]), y: parseFloat(vals[5])};
    }
    return {x:0,y:0};
}

function getRotation(el){
    const st = window.getComputedStyle(el);
    const tr = st.transform || st.webkitTransform;
    if (tr && tr !== 'none') {
        const vals = tr.match(/matrix.*\((.+)\)/)[1].split(', ');
        const a = parseFloat(vals[0]);
        const b = parseFloat(vals[1]);
        return Math.round(Math.atan2(b, a) * (180/Math.PI));
    }
    return 0;
}

function calculateSnap(el, x, y){
    const SNAP_PX = 10;
    const size = el.offsetWidth;
    const rect = {left:x, right:x+size, top:y, bottom:y+size, w:size, h:size};
    let chosen = {x:x,y:y,dist:Infinity};

    machines.forEach(m=>{
        if (String(m.id) === String(el.dataset.id)) return;
        const nx = Number(m.x), ny = Number(m.y), ns = Number(m.size || el.offsetWidth);
        const nrect = {left:nx, right:nx+ns, top:ny, bottom:ny+ns, w:ns, h:ns};

        const candR = {x: nrect.right, y: ny};
        const dR = Math.hypot(candR.x - x, candR.y - y);
        if (dR < chosen.dist && Math.abs(y - ny) <= SNAP_PX*4 && Math.abs(rect.left - nrect.right) <= SNAP_PX) {
            chosen = {x:candR.x, y:candR.y, dist:dR};
        }

        const candL = {x: nrect.left - rect.w, y: ny};
        const dL = Math.hypot(candL.x - x, candL.y - y);
        if (dL < chosen.dist && Math.abs(y - ny) <= SNAP_PX*4 && Math.abs(rect.right - nrect.left) <= SNAP_PX) {
            chosen = {x:candL.x, y:candL.y, dist:dL};
        }

        const candB = {x: nx, y: nrect.bottom};
        const dB = Math.hypot(candB.x - x, candB.y - y);
        if (dB < chosen.dist && Math.abs(x - nx) <= SNAP_PX*4 && Math.abs(rect.top - nrect.bottom) <= SNAP_PX) {
            chosen = {x:candB.x, y:candB.y, dist:dB};
        }

        const candT = {x: nx, y: nrect.top - rect.h};
        const dT = Math.hypot(candT.x - x, candL.y - y);
        if (dT < chosen.dist && Math.abs(x - nx) <= SNAP_PX*4 && Math.abs(rect.bottom - nrect.top) <= SNAP_PX) {
            chosen = {x:candT.x, y:candT.y, dist:dT};
        }
    });

    const padding = 8;
    const maxX = Math.max(0, map.scrollWidth - rect.w - padding);
    const maxY = Math.max(0, map.scrollHeight - rect.h - padding);
    const finalX = Math.min(maxX, Math.max(padding, chosen.x));
    const finalY = Math.min(maxY, Math.max(padding, chosen.y));
    return {x: finalX, y: finalY};
}

function addMaintenanceLegend() {
    const oldLegend = document.querySelector('.maintenance-status-info');
    if (oldLegend) oldLegend.remove();
    
    const legend = document.createElement('div');
    legend.className = 'maintenance-status-info';
    legend.style.cssText = `
        position: absolute;
        bottom: 20px;
        right: 20px;
        background: rgba(0,0,0,0.7);
        padding: 10px 15px;
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.1);
        z-index: 1000;
        display: flex;
        gap: 15px;
        backdrop-filter: blur(5px);
    `;
    
    legend.innerHTML = `
        <div class="status-item">
            <div class="status-color status-green"></div>
            <span>0-21 gün: Bakım yapıldı</span>
        </div>
        <div class="status-item">
            <div class="status-color status-blue"></div>
            <span>21-28 gün: Bakım yaklaşıyor</span>
        </div>
        <div class="status-item">
            <div class="status-color status-red"></div>
            <span>28+ gün: Bakım gerekli</span>
        </div>
        <div class="status-item">
            <div class="status-color status-group"></div>
            <span>Grup üyesi</span>
        </div>
    `;
    
    map.appendChild(legend);
}

// GLOBAL FONKSİYONLAR
window.showGroupInfo = showGroupInfo;
window.updateGroupMaintenanceDate = updateGroupMaintenanceDate;
window.saveGroupMaintenanceDate = saveGroupMaintenanceDate;

// SAYFA YÜKLENDİĞİNDE ARAMA SİSTEMİNİ BAŞLAT
document.addEventListener('DOMContentLoaded', () => {
    // Arama sistemini başlat
    setTimeout(initializeSearch, 500);
    
    // Tüm makineleri cache'le (background'da)
    setTimeout(loadAllMachines, 1000);
});

})();