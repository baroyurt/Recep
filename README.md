# Casino Bakım Takip Sistemi - Kurulum ve Kullanım Kılavuzu

## 🎰 Sistem Özellikleri

### Yeni Özellikler (v2.1) ⭐

1. **Bakım Tarihi Geçmişi**
   - Tüm bakım tarihleri kaydediliyor
   - Bakım yapan kişi ve notlarla birlikte
   - "📅 Bakım Tarihleri" butonu ile görüntüleme
   - Kronolojik bakım geçmişi

2. **Gelişmiş Cross-Room Arama**
   - Marka, model, oyun türü dahil tüm alanlarda arama
   - Tüm salonlarda eşzamanlı arama
   - Detaylı sonuç gösterimi (ikonlarla)
   - Otomatik salon değiştirme ve makina vurgulama

3. **Güncellenmiş Renk Kodları**
   - 🟢 Yeşil: 0-45 gün (Bakım yapıldı)
   - 🔵 Mavi: 45-60 gün (Bakım yaklaşıyor)
   - 🔴 Kırmızı: 60+ gün (Bakım gerekli)

### Önceki Özellikler (v2.0)

1. **Kimlik Doğrulama Sistemi**
   - Admin ve kullanıcı rolleri
   - Güvenli şifre saklama (password hashing)
   - Oturum yönetimi
   - Role dayalı erişim kontrolü

2. **Bakım Yapan Kişi Takibi**
   - Makina bakım formuna "Bakım Yapan Kişi" alanı eklendi
   - Geçmiş kayıtlarında takip edilir
   - Makina bilgilerinde görüntülenir

3. **CSV Toplu İçe Aktarma**
   - CSV dosyasından toplu makina ekleme
   - Otomatik salon eşleştirme
   - Mükerrer kayıt kontrolü
   - Hata raporlama

4. **Makina Sayaçları**
   - Her salon için makina sayısı
   - Toplam makina sayısı
   - Sayfanın sağ altında gerçek zamanlı gösterim

5. **Geliştirilmiş UI**
   - Profesyonel arayüz tasarımı
   - Kullanıcı bilgisi gösterimi
   - Responsive tasarım
   - Daha iyi görsel geri bildirim

## 📋 Sistem Gereksinimleri

- PHP 7.4 veya üzeri
- MySQL 5.7 veya MariaDB 10.3+
- Apache/Nginx web sunucusu
- Modern web tarayıcı (Chrome, Firefox, Safari, Edge)

## 🚀 Kurulum Adımları

### 1. Dosyaları Yükleyin

```bash
# Projeyi klonlayın veya indirin
git clone https://github.com/baroyurt/Recep.git
cd Recep/recep
```

### 2. Veritabanı Ayarları

`config.php` dosyasını düzenleyin:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'slot_db');
define('DB_USER', 'root');
define('DB_PASS', 'sizin_sifreniz');
```

### 3. Veritabanını Başlatın

Tarayıcınızda açın:
```
http://localhost/recep/db_init_mysql.php
```

Bu işlem:
- `slot_db` veritabanını oluşturur
- Tüm tabloları yaratır
- Varsayılan kullanıcıları ekler
- YENİ VİP SALON için 48 örnek makina ekler

### 4. Giriş Yapın

```
http://localhost/recep/login.php
```

**Varsayılan Hesaplar:**
- 👑 Admin: `admin` / `admin123`
- 👤 Kullanıcı: `user` / `user123`

## 📤 CSV İçe Aktarma

### CSV Formatı

CSV dosyanız şu formatta olmalıdır:

```csv
Sıra,Salon,Makine No,Marka,Model,Oyun Türü
1,ALÇAK TAVAN,2126,EGT,G50 J1,Green General
2,YÜKSEK TAVAN,2127,EGT,G50 J1,Fruits General HD
3,ALT SALON,2131,EGT VIP,G50/50,Fruits Collection 2
```

**Sütunlar:**
1. Sıra (isteğe bağlı)
2. Salon adı (ALÇAK TAVAN, YÜKSEK TAVAN, YENİ VİP SALON, ALT SALON)
3. Makina numarası (benzersiz olmalı)
4. Marka
5. Model
6. Oyun türü

### İçe Aktarma Adımları

1. Admin hesabıyla giriş yapın
2. "📤 CSV İçe Aktar" butonuna tıklayın
3. CSV dosyasını seçin (örn: `table-9dc7be54-8fb6-4946-9592-7eda4e1178fe.csv`)
4. "Yükle ve İçe Aktar" butonuna tıklayın
5. İşlem tamamlanınca başarı mesajı görünecek

**Not:** Mükerrer makina numaraları otomatik olarak atlanır.

## 👥 Kullanıcı Rolleri ve Yetkiler

### Admin Kullanıcısı
- ✅ Makina oluşturma
- ✅ Makina silme
- ✅ CSV içe aktarma
- ✅ Grup konumu değiştirme
- ✅ Toplu güncelleme
- ✅ Tüm ayarlar

### Normal Kullanıcı
- ✅ Makinaları görüntüleme
- ✅ Makina bilgilerini görüntüleme
- ✅ Geçmiş ve arıza kayıtlarını görüntüleme
- ❌ Makina oluşturma/silme
- ❌ CSV içe aktarma

## 🎨 Kullanıcı Arayüzü

### Makina Renk Kodları

- 🟢 **Yeşil (0-21 gün)**: Bakım yakın zamanda yapıldı
- 🔵 **Mavi (21-28 gün)**: Bakım zamanı yaklaşıyor
- 🔴 **Kırmızı (28+ gün)**: Bakım gerekli

### Makina İşlemleri

1. **Sürükle-Bırak**: Makinaları tutup sürükleyerek konumlandırın
2. **Döndürme**: Makina üzerindeki ok butonuyla döndürün
3. **Bilgi Görüntüleme**: Makinaya tıklayın
4. **Düzenleme**: Bilgi modalında "✏️ Düzenle" butonuna tıklayın
5. **Silme**: Bilgi modalında "🗑️ Sil" butonuna tıklayın (Admin)

### Grup İşlemleri

- **Ctrl + Tıklama**: Çoklu seçim
- **Sürükle**: Alan seçimi
- **Grup Taşıma**: Grup butonunu sürükleyin (Admin)

## 🔒 Güvenlik

### Şifre Değiştirme

Varsayılan admin şifresini mutlaka değiştirin! Veritabanında:

```sql
UPDATE users 
SET password = '$2y$10$...' -- password_hash() kullanın
WHERE username = 'admin';
```

### Dosya İzinleri

```bash
chmod 644 config.php
chmod 755 recep/
chmod 755 recep/cache/
```

### Güvenlik En İyi Uygulamaları

1. ✅ `config.php` dosyasını versiyon kontrolü dışında tutun
2. ✅ Güçlü şifreler kullanın
3. ✅ Düzenli yedek alın
4. ✅ PHP ve MySQL güncellemelerini yapın
5. ✅ HTTPS kullanın (production ortamında)

## 📊 Veritabanı Yapısı

### Ana Tablolar

1. **users**: Kullanıcı hesapları
2. **machines**: Makina kayıtları (+ maintenance_person)
3. **machine_faults**: Arıza kayıtları
4. **maintenance_history**: Bakım geçmişi
5. **trello_config**: Trello entegrasyonu

## 🔧 Sorun Giderme

### "Connection Refused" Hatası
- MySQL servisinin çalıştığından emin olun
- `config.php` ayarlarını kontrol edin

### CSV İçe Aktarma Çalışmıyor
- Admin hesabıyla giriş yaptığınızdan emin olun
- CSV formatını kontrol edin
- Dosya yükleme limitlerini kontrol edin (`php.ini`)

### Makinalar Görünmüyor
- Doğru salonu seçtiğinizden emin olun
- Tarayıcı konsolunda hata var mı kontrol edin (F12)
- Veritabanı bağlantısını kontrol edin

## 📝 Geliştirici Notları

### Kod Yapısı

```
recep/
├── index.php           # Ana sayfa
├── login.php          # Giriş sayfası
├── logout.php         # Çıkış işlemi
├── api.php            # Backend API
├── config.php         # Veritabanı ayarları
├── db_init_mysql.php  # Veritabanı kurulum
├── css/
│   └── style.css      # Stil dosyası
└── js/
    ├── app.js         # Ana JavaScript
    └── history.js     # Geçmiş yönetimi
```

### API Endpoints

- `action=list`: Makinaları listele
- `action=create`: Yeni makina oluştur (Admin)
- `action=update`: Makina güncelle
- `action=delete`: Makina sil (Admin)
- `action=import_csv`: CSV içe aktar (Admin)
- `action=get_machine_counts`: Sayaçları getir

### Yeni Özellik Ekleme

1. Backend: `api.php` içinde yeni action ekleyin
2. Frontend: `js/app.js` içinde ilgili fonksiyonu ekleyin
3. UI: `index.php` ve `css/style.css` güncelleyin

## 📞 Destek

Sorularınız için GitHub Issues kullanabilirsiniz:
https://github.com/baroyurt/Recep/issues

## 📄 Lisans

Bu proje özel kullanım içindir.

---

**Versiyon:** 2.1  
**Tarih:** Şubat 2026  
**Geliştirici:** GitHub Copilot  
**Yenilikler:** Bakım geçmişi takibi, gelişmiş arama, güncellenmiş renk kodları
