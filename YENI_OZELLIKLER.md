# Yeni Özellikler - v2.1 Güncellemesi

## 🎯 Eklenen Özellikler

### 1. ✅ Bakım Tarihi Geçmişi Takibi

**Problem:** Önceki bakım tarihleri kaydedilmiyordu, sadece son bakım tarihi tutuluyordu.

**Çözüm:**
- Yeni `maintenance_dates` tablosu oluşturuldu
- Her bakım tarihi güncellemesinde otomatik olarak geçmişe kaydediliyor
- Bakım yapan kişi ve not bilgileri de saklanıyor
- Makina bilgilerinde "📅 Bakım Tarihleri" butonu eklendi
- Tüm geçmiş bakım kayıtları kronolojik sırada görüntülenebiliyor

**Kullanım:**
1. Bir makinaya tıklayın
2. "📅 Bakım Tarihleri" butonuna basın
3. Tüm geçmiş bakım kayıtlarını görün:
   - Bakım tarihi
   - Bakım yapan kişi
   - Notlar
   - Kayıt zamanı

### 2. ✅ Gelişmiş Salonlar Arası Arama

**Problem:** Arama sadece makina numarasında ve limitli alanlarda çalışıyordu.

**Çözüm:**
- Arama artık TÜM alanlarda çalışıyor:
  - Makina numarası
  - Marka (brand)
  - Model
  - Oyun türü (game_type)
  - Notlar
- Arama sonuçları çok daha detaylı:
  - Marka ve model ikonlarla gösteriliyor
  - Oyun türü görüntüleniyor
  - Salon bilgisi her sonuçta
  - Bakım durumu renk kodlu
- Tüm salonlarda arama yapılıyor
- Sonuca tıklandığında otomatik salon değişimi

**Kullanım:**
1. Arama kutusuna yazın (örn: "EGT", "Slot", "Deluxe")
2. Tüm salonlardan eşleşen makinalar listelenir
3. İstediğinize tıklayın
4. Otomatik olarak o salona geçer ve makina vurgulanır

### 3. ✅ Renk Kodu Değişiklikleri

**Eski Sistem:**
- 🟢 Yeşil: 0-21 gün
- 🔵 Mavi: 21-28 gün
- 🔴 Kırmızı: 28+ gün

**Yeni Sistem:**
- 🟢 **Yeşil: 0-45 gün** - Bakım yapıldı
- 🔵 **Mavi: 45-60 gün** - Bakım yaklaşıyor
- 🔴 **Kırmızı: 60+ gün** - Bakım gerekli

Daha uzun bakım aralıkları için optimize edildi.

## 📊 Teknik Detaylar

### Veritabanı Değişiklikleri

```sql
CREATE TABLE `maintenance_dates` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `machine_id` INT NOT NULL,
  `maintenance_date` DATE NOT NULL,
  `maintenance_person` VARCHAR(128),
  `note` TEXT,
  `performed_by` VARCHAR(128) DEFAULT 'system',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
  INDEX idx_machine_id (machine_id),
  INDEX idx_maintenance_date (maintenance_date),
  INDEX idx_created_at (created_at)
);
```

### API Değişiklikleri

**Yeni Endpoint:**
- `GET /api.php?action=get_maintenance_dates&machine_id={id}`
  - Makinanın tüm bakım tarihlerini döndürür
  - Response: `{ok: true, maintenance_dates: [...]}`

**Güncellenen Davranış:**
- `action=update` - Bakım tarihi değiştiğinde otomatik olarak `maintenance_dates` tablosuna kayıt ekler

### JavaScript Değişiklikleri

**app.js:**
- `getMaintenanceStatus()` - Eşik değerleri 45/60 güne güncellendi
- `searchInCache()` - brand, model, game_type alanlarında arama eklendi
- Search results display - Detaylı bilgi gösterimi

**history.js:**
- `loadMaintenanceDates()` - Yeni fonksiyon
- Maintenance dates modal handler eklendi

### CSS Değişiklikleri

Yeni stiller eklendi:
- `.search-detail-line` - Arama sonuç detayları
- `.maintenance-dates-list` - Bakım tarihleri listesi
- `.maintenance-date-item` - Bakım tarihi kartı
- Enhanced search result styling

## 🚀 Kullanıcı İçin Faydalar

1. **Bakım Takibi:** Artık tüm bakım geçmişi görülebiliyor
2. **Daha İyi Arama:** Marka, model, oyun türüne göre arama yapabilme
3. **Hızlı Erişim:** Herhangi bir salondan makina bulup ulaşabilme
4. **Gerçekçi Uyarılar:** 60 günlük bakım döngüsü daha mantıklı
5. **Detaylı Bilgi:** Arama sonuçlarında tüm detaylar görünüyor

## 📝 Kurulum Notları

**Mevcut Sistemden Güncelleme:**

1. Veritabanını yeniden oluşturun:
   ```
   php db_init_mysql.php
   ```
   
   **UYARI:** Bu komut mevcut verileri siler!

2. Veya manuel olarak tabloyu ekleyin:
   ```sql
   CREATE TABLE `maintenance_dates` (...);
   ```

3. Dosyaları güncelleyin (Git pull)

4. Tarayıcı önbelleğini temizleyin (Ctrl+F5)

## 🔮 Gelecek İyileştirmeler

Önerilen ek özellikler:
- ✨ Bakım tarihi hatırlatıcıları (e-posta/SMS)
- ✨ Bakım istatistikleri ve raporlar
- ✨ Otomatik bakım planlama
- ✨ Bakım geçmişi PDF export
- ✨ Bakım yapan kişi bazlı istatistikler
- ✨ Salon bazlı bakım özeti

## 📞 Destek

Sorular veya öneriler için:
- GitHub Issues: https://github.com/baroyurt/Recep/issues
- Dökümanlar: README.md

---

**Versiyon:** 2.1  
**Tarih:** Şubat 2026  
**Değişiklikler:** Bakım geçmişi, gelişmiş arama, yeni renk kodları
