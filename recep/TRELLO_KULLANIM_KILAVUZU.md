# Trello Entegrasyonu ve Makina Geçmişi - Kullanım Kılavuzu

## 🎯 Genel Bakış

Bu sistem, Casino Bakım Takip Programı'na aşağıdaki özellikleri ekler:

1. **Trello Entegrasyonu**: Trello board'larınızdan arıza kartlarını otomatik çekme
2. **Makina Eşleştirme**: Arızaları makina numaralarıyla otomatik eşleştirme
3. **Makina Geçmişi**: Her makinanın tüm bakım ve arıza geçmişini görüntüleme
4. **Arıza Yönetimi**: Arızaların durumunu takip etme ve güncelleme

## 📋 Kurulum

### 1. Veritabanı Güncellemesi

Yeni tabloları oluşturmak için:

```bash
# Tarayıcınızda şu adresi açın:
http://localhost/recep/db_init_mysql.php
```

Bu işlem şu tabloları oluşturacak:
- `machine_faults` - Trello'dan gelen arızalar
- `maintenance_history` - Makina geçmişi kayıtları
- `trello_config` - Trello API ayarları

### 2. Trello API Anahtarlarınızı Alın

#### Adım 1: API Key Alın
1. [https://trello.com/app-key](https://trello.com/app-key) adresine gidin
2. Trello hesabınızla giriş yapın
3. **Key** değerini kopyalayın

#### Adım 2: API Token Alın
1. Aynı sayfada **Token** linkine tıklayın
2. Uygulamaya izin verin
3. Oluşan **Token** değerini kopyalayın

### 3. Trello Entegrasyonunu Yapılandırın

1. Ana sayfadan **"Trello Ayarları"** butonuna tıklayın
2. API Key ve Token'ınızı girin
3. **"Bağlantıyı Test Et"** butonuna tıklayın
4. Board'larınız otomatik yüklenecek
5. Arızaların bulunduğu Board'u seçin (opsiyonel)
6. İsterseniz belirli bir List seçin (opsiyonel)
7. **"Ayarları Kaydet"** butonuna tıklayın

## 🔄 Trello Senkronizasyonu

### Otomatik Arıza Çekme

Trello ayarlarını yaptıktan sonra:

1. **"Şimdi Senkronize Et"** butonuna tıklayın
2. Sistem tüm kartları tarayacak ve makina numaralarını tespit edecek
3. Eşleşen arızalar otomatik olarak kaydedilecek

### Desteklenen Makina Numarası Formatları

Sistem şu formatlardaki makina numaralarını algılar:

- `2192` - Sadece numara
- `Makina 2192` - "Makina" öneki ile
- `M2192` - "M" harfi ile
- `#2192` - "#" işareti ile
- `2192 numaralı makina` - Açıklama içinde

**Örnek Trello Kart Başlıkları:**
- ✅ "Makina 2192 - Ekran Arızası"
- ✅ "2194 Slot Makinası Çalışmıyor"
- ✅ "M2770 - Yazılım Güncellemesi Gerekli"
- ❌ "Salon 1 Genel Bakım" (Makina numarası yok)

### Öncelik Belirleme

Trello label'ları otomatik öncelik atar:

- **Kritik**: "Critical", "Kritik" label'ı
- **Yüksek**: "High", "Yüksek" label'ı
- **Orta**: Varsayılan
- **Düşük**: "Low", "Düşük" label'ı

## 📊 Arıza Yönetimi

### Arızaları Görüntüleme

**Ana Sayfadan:**
1. **"Arızalar"** butonuna tıklayın
2. Tüm arızaları listeleyin
3. Durum filtreleriyle arızaları süzün:
   - Açık
   - Devam Eden
   - Çözüldü

### Arıza Durumunu Güncelleme

Her arıza kartında:
- **"Devam Eden"** - Arıza üzerinde çalışıldığını işaretle
- **"Çözüldü"** - Arızanın çözüldüğünü işaretle
- **"Yeniden Aç"** - Çözülen arızayı tekrar aç
- **"Trello'da Aç"** - Orijinal Trello kartını görüntüle

## 📜 Makina Geçmişi

### Geçmişi Görüntüleme

Herhangi bir makina için:

1. Makinaya tıklayın (bilgi modalı açılır)
2. **"📜 Geçmiş"** butonuna tıklayın
3. Zaman çizelgesinde tüm olayları görün:
   - ➕ Makina oluşturulması
   - ✏️ Güncelleme
   - 🔧 Bakım yapılması
   - ⚠️ Arıza kaydedilmesi
   - 🛠️ Tamir işlemleri

### Makina Arızalarını Görüntüleme

1. Makinaya tıklayın
2. **"⚠️ Arızalar"** butonuna tıklayın
3. Bu makinaya ait tüm arızaları görün:
   - Aktif arızalar
   - Çözülen arızalar
   - Trello bağlantıları

## 🔧 API Endpoints

Yeni eklenen API endpoint'leri:

### Trello İşlemleri
```
POST api.php?action=trello_save_config
POST api.php?action=trello_sync
GET  api.php?action=trello_get_config
GET  api.php?action=trello_list_boards
GET  api.php?action=trello_get_lists&board_id={id}
```

### Geçmiş İşlemleri
```
GET api.php?action=get_history&machine_id={id}
```

### Arıza İşlemleri
```
GET  api.php?action=get_faults&machine_id={id}
GET  api.php?action=list_all_faults&status={open|in_progress|resolved|all}
POST api.php?action=update_fault_status
```

## 🎨 Kullanıcı Arayüzü Özellikleri

### Yeni Butonlar

**Ana Sayfa Header:**
- 🔷 **Trello Ayarları** - Entegrasyon yapılandırması
- ⚠️ **Arızalar** - Tüm arızaları görüntüle

**Makina Bilgi Modalı:**
- 📜 **Geçmiş** - Makina geçmişini görüntüle
- ⚠️ **Arızalar** - Makina arızalarını görüntüle

### Renk Kodları

**Arıza Durumları:**
- 🔴 Kırmızı - Açık arızalar
- 🟠 Turuncu - Devam eden arızalar
- 🟢 Yeşil - Çözülen arızalar

**Öncelik Seviyeleri:**
- 🔴 Kırmızı - Kritik
- 🟠 Turuncu - Yüksek
- 🟡 Sarı - Orta
- 🟢 Yeşil - Düşük

## 📝 İpuçları

### Trello Kartlarınızı Optimize Edin

1. **Kart başlıklarına makina numarasını ekleyin**
   - İyi: "Makina 2192 - Ekran Arızası"
   - Kötü: "Ekran arızası var"

2. **Label'ları kullanın**
   - Öncelik seviyesi belirlemek için
   - "Kritik", "Yüksek", "Düşük" gibi

3. **Açıklamalara detay ekleyin**
   - Sistem açıklamaları da tarar
   - Daha iyi sorun takibi için

### Düzenli Senkronizasyon

- Günde en az bir kez senkronize edin
- Yeni arızalar otomatik eşleşecek
- Mevcut arızalar güncellenecek

### Veri Güvenliği

- API anahtarlarınızı kimseyle paylaşmayın
- Sadece gerekli board/list'lere erişim verin
- Düzenli olarak token'ı yenileyin

## 🐛 Sorun Giderme

### Arızalar Eşleşmiyor

**Çözüm:**
- Trello kart başlıklarında makina numarasının doğru formatta olduğundan emin olun
- 4 basamaklı sayıları kullanın (örn: 2192)
- Eğer makina numarası 3 basamaklıysa, önüne 0 ekleyin

### Senkronizasyon Hatası

**Olası Nedenler:**
1. API anahtarları hatalı
2. Token süresi dolmuş
3. Board/List ID'si yanlış
4. İnternet bağlantısı problemi

**Çözüm:**
- Trello Ayarları sayfasında "Bağlantıyı Test Et"
- API anahtarlarını yeniden girin
- Token'ı yenileyin

### Geçmiş Görünmüyor

**Çözüm:**
- Veritabanının güncellendiğinden emin olun
- `db_init_mysql.php` dosyasını çalıştırın
- Tarayıcı cache'ini temizleyin

## 🚀 Gelecek Geliştirmeler

Planlanan özellikler:

- [ ] Otomatik senkronizasyon (cron job)
- [ ] E-posta bildirimleri
- [ ] PDF rapor oluşturma
- [ ] Gelişmiş istatistikler
- [ ] Çoklu Trello board desteği
- [ ] Webhook entegrasyonu

## 📞 Destek

Herhangi bir sorun yaşarsanız:

1. Trello API belgelerini kontrol edin: [https://developer.atlassian.com/cloud/trello/rest/](https://developer.atlassian.com/cloud/trello/rest/)
2. Tarayıcı konsolu hatalarını kontrol edin (F12)
3. PHP hata loglarını inceleyin

---

**Version:** 1.0  
**Son Güncelleme:** 2024
