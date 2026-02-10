// js/batch.js - TOPLU İŞLEMLER MODÜLÜ - BRAND/MODEL/GAME_TYPE EKLENDİ
(function(){
const batchBtn = document.getElementById('batch-btn');
const batchModal = document.getElementById('batch-modal');
const batchForm = document.getElementById('batch-form');
const batchInputs = document.getElementById('batch-inputs');
const batchField = document.getElementById('batch-field');
const batchSubmit = document.getElementById('batch-submit');
const cancelBatch = document.getElementById('cancel-batch');
const groupSelector = document.getElementById('group-selector');
let isSelecting = false;
let selectionStart = null;
let selectedMachines = new Set();
// Modal kontrolleri
batchBtn.addEventListener('click', () => {
batchModal.classList.remove('hidden');
startSelectionMode();
});
cancelBatch.addEventListener('click', () => {
batchModal.classList.add('hidden');
stopSelectionMode();
clearSelection();
});
// Seçim modu
function startSelectionMode() {
isSelecting = true;
document.body.classList.add('selection-mode');
// Ctrl+Click için event listener
document.addEventListener('keydown', handleSelectionKey);
document.addEventListener('keyup', handleSelectionKey);
// Alan seçimi için
map.addEventListener('mousedown', startAreaSelection);
}
function stopSelectionMode() {
isSelecting = false;
document.body.classList.remove('selection-mode');
document.removeEventListener('keydown', handleSelectionKey);
document.removeEventListener('keyup', handleSelectionKey);
map.removeEventListener('mousedown', startAreaSelection);
}
function handleSelectionKey(e) {
const machines = document.querySelectorAll('.machine');
if (e.ctrlKey || e.metaKey) {
    document.body.classList.add('multi-select');
    machines.forEach(el => {
        el.style.cursor = 'crosshair';
    });
} else {
    document.body.classList.remove('multi-select');
    machines.forEach(el => {
        el.style.cursor = '';
    });
}
}
// Alan seçimi
function startAreaSelection(e) {
if (!e.shiftKey || e.target.classList.contains('machine')) return;
e.preventDefault();
selectionStart = {x: e.clientX, y: e.clientY};
groupSelector.style.left = selectionStart.x + 'px';
groupSelector.style.top = selectionStart.y + 'px';
groupSelector.classList.remove('hidden');
document.addEventListener('mousemove', updateAreaSelection);
document.addEventListener('mouseup', endAreaSelection);
}
function updateAreaSelection(e) {
const currentX = e.clientX;
const currentY = e.clientY;
const width = currentX - selectionStart.x;
const height = currentY - selectionStart.y;
groupSelector.style.width = Math.abs(width) + 'px';
groupSelector.style.height = Math.abs(height) + 'px';
groupSelector.style.left = (width < 0 ? currentX : selectionStart.x) + 'px';
groupSelector.style.top = (height < 0 ? currentY : selectionStart.y) + 'px';
// Seçili alandaki makineleri bul
const rect = groupSelector.getBoundingClientRect();
selectMachinesInArea(rect);
}
function endAreaSelection() {
groupSelector.classList.add('hidden');
document.removeEventListener('mousemove', updateAreaSelection);
document.removeEventListener('mouseup', endAreaSelection);
updateSelectedCount();
}
function selectMachinesInArea(rect) {
document.querySelectorAll('.machine').forEach(el => {
const machineRect = el.getBoundingClientRect();
    if (machineRect.left >= rect.left &&
        machineRect.right <= rect.right &&
        machineRect.top >= rect.top &&
        machineRect.bottom <= rect.bottom) {
        
        const machineId = parseInt(el.dataset.id);
        selectedMachines.add(machineId);
        el.classList.add('selected');
    } else {
        const machineId = parseInt(el.dataset.id);
        if (!document.body.classList.contains('multi-select')) {
            selectedMachines.delete(machineId);
            el.classList.remove('selected');
        }
    }
});
}
// Clear selection
function clearSelection() {
selectedMachines.clear();
document.querySelectorAll('.machine.selected').forEach(el => {
el.classList.remove('selected');
});
updateSelectedCount();
}
function updateSelectedCount() {
const count = selectedMachines.size;
document.getElementById('selected-count').textContent = `${count} makina seçildi`;
batchSubmit.disabled = count === 0;
}
// Dinamik input oluştur - GÜNCELLENDİ
batchField.addEventListener('change', function() {
const field = this.value;
batchInputs.innerHTML = '';
if (!field) return;
let inputHtml = '';
switch(field) {
    case 'maintenance_date':
        inputHtml = `
             <label>YENİ BAKIM TARİHİ</label>
             <input type="date" name="value" required>
        `;
        break;
    case 'note':
        inputHtml = `
             <label>YENİ NOT</label>
             <textarea name="value" rows="3" placeholder="Tüm seçili makineler için not..."></textarea>
        `;
        break;
    case 'room':
        inputHtml = `
             <label>YENİ SALON</label>
             <select name="value" required>
                 <option value="">Seçiniz</option>
                ${ROOMS.map(room => ` <option value="${room}">${room}</option>`).join('')}
             </select>
        `;
        break;
    case 'brand':
        inputHtml = `
             <label>YENİ MARKA</label>
             <input type="text" name="value" placeholder="EGT, Novomatic, vb." required>
        `;
        break;
    case 'model':
        inputHtml = `
             <label>YENİ MODEL</label>
             <input type="text" name="value" placeholder="Deluxe, Premium, vb." required>
        `;
        break;
    case 'game_type':
        inputHtml = `
             <label>YENİ OYUN ÇEŞİDİ</label>
             <input type="text" name="value" placeholder="Slot, Link, vb." required>
        `;
        break;
}
batchInputs.innerHTML = inputHtml;
});
// Batch form submit
batchForm.addEventListener('submit', async (e) => {
e.preventDefault();
if (selectedMachines.size === 0) {
    alert('Lütfen en az bir makina seçin!');
    return;
}
const field = batchField.value;
const value = batchForm.querySelector('[name="value"]').value;
if (!field || !value) {
    alert('Lütfen tüm alanları doldurun!');
    return;
}
if (!confirm(`${selectedMachines.size} makina için ${field} alanını güncellemek istediğinize emin misiniz?`)) {
    return;
}
const fd = new FormData();
fd.set('ids', Array.from(selectedMachines).join(','));
fd.set('field', field);
fd.set('value', value);
const batchSubmitBtn = document.getElementById('batch-submit');
const originalText = batchSubmitBtn.textContent;
batchSubmitBtn.disabled = true;
batchSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Güncelleniyor...';
try {
    const res = await api({method:'POST', body:fd, query:{action:'batch_update'}});
    
    if (res.ok) {
        alert(`Başarılı! ${res.updated_count} makina güncellendi.`);
        batchModal.classList.add('hidden');
        batchForm.reset();
        clearSelection();
        stopSelectionMode();
        loadMachines(); // Ana app.js'den geliyor
    } else {
        alert('Güncelleme hatası: ' + (res.error || ''));
    }
} catch (error) {
    alert('Güncelleme hatası: ' + error.message);
} finally {
    batchSubmitBtn.disabled = false;
    batchSubmitBtn.textContent = originalText;
}
});
// API wrapper (ana app.js ile uyumlu)
function api(params={}) {
const method = (params.method || 'GET').toUpperCase();
let url = 'api.php';
if (params.query) {
url += '?' + new URLSearchParams(params.query);
}
if (method === 'GET') return fetch(url).then(r=>r.json());
return fetch(url, {method:'POST', body: (params.body instanceof FormData)?params.body:new URLSearchParams(params.body)}).then(r=>r.json());
}
// Ana app.js'den loadMachines fonksiyonuna erişim
// Not: Bu global olarak tanımlanmış olmalı
})();