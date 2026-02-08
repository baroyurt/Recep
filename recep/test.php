<?php
// test.php - Hızlı test dosyası
?>
<!DOCTYPE html>
<html>
<head>
    <title>TEST - Casino Bakım</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Ek güvenlik CSS */
        body {
            background: #0b0b0b;
            color: #e9e9e9;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        
        .test-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-top: 30px;
        }
        
        .test-machine {
            margin: 10px;
        }
        
        .test-title {
            text-align: center;
            color: #c9a94f;
            margin-bottom: 30px;
            font-size: 28px;
        }
        
        .status-box {
            background: rgba(0,0,0,0.3);
            padding: 10px;
            border-radius: 8px;
            margin: 5px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1 class="test-title">🎰 CASINO BAKIM - CSS/JAVASCRIPT TEST SAYFASI</h1>
    
    <div style="text-align:center; margin-bottom:30px;">
        <div class="status-box">
            <strong>Durum:</strong> 
            <span id="css-status" style="color:orange;">CSS yükleniyor...</span>
        </div>
    </div>
    
    <div class="test-container">
        <!-- TEST 1: Normal Makina -->
        <div class="test-machine">
            <h3>1. Normal Makina</h3>
            <div class="machine" style="position:relative;">
                <div class="meta">
                    <div class="num">2192</div>
                    <div class="brand">EGT</div>
                </div>
                <div class="rotate-btn">⟳</div>
            </div>
            <div class="status-box">Sarı kenarlık normal makina</div>
        </div>
        
        <!-- TEST 2: Grup Makinası -->
        <div class="test-machine">
            <h3>2. Grup Makinası</h3>
            <div class="machine group-member" style="position:relative;">
                <div class="group-indicator"></div>
                <div class="meta">
                    <div class="num">2194</div>
                    <div class="brand">EGT</div>
                </div>
                <div class="rotate-btn">⟳</div>
                <div class="group-select-btn">👥</div>
            </div>
            <div class="status-box"><span style="color:#9c27b0;">Mor renk = Grup üyesi</span></div>
        </div>
        
        <!-- TEST 3: Yeşil Bakım (21 güne kadar) -->
        <div class="test-machine">
            <h3>3. Yeşil Bakım</h3>
            <div class="machine maintenance-green" style="position:relative;">
                <div class="maintenance-status"></div>
                <div class="meta">
                    <div class="num">2196</div>
                    <div class="brand">EGT</div>
                </div>
                <div class="rotate-btn">⟳</div>
            </div>
            <div class="status-box"><span style="color:#2ecc71;">Yeşil = 21 güne kadar</span></div>
        </div>
        
        <!-- TEST 4: Mavi Bakım (21-28 gün) -->
        <div class="test-machine">
            <h3>4. Mavi Bakım</h3>
            <div class="machine maintenance-blue" style="position:relative;">
                <div class="maintenance-status"></div>
                <div class="meta">
                    <div class="num">2198</div>
                    <div class="brand">EGT</div>
                </div>
                <div class="rotate-btn">⟳</div>
            </div>
            <div class="status-box"><span style="color:#3498db;">Mavi = 21-28 gün</span></div>
        </div>
        
        <!-- TEST 5: Kırmızı Bakım (28+ gün) -->
        <div class="test-machine">
            <h3>5. Kırmızı Bakım</h3>
            <div class="machine maintenance-red" style="position:relative;">
                <div class="maintenance-status"></div>
                <div class="meta">
                    <div class="num">2200</div>
                    <div class="brand">EGT</div>
                </div>
                <div class="rotate-btn">⟳</div>
            </div>
            <div class="status-box"><span style="color:#e74c3c;">Kırmızı = 28+ gün</span></div>
        </div>
        
        <!-- TEST 6: Grup + Yeşil -->
        <div class="test-machine">
            <h3>6. Grup + Yeşil</h3>
            <div class="machine group-member maintenance-green" style="position:relative;">
                <div class="group-indicator"></div>
                <div class="maintenance-status"></div>
                <div class="meta">
                    <div class="num">2202</div>
                    <div class="brand">EGT</div>
                </div>
                <div class="rotate-btn">⟳</div>
                <div class="group-select-btn">👥</div>
            </div>
            <div class="status-box">Mor + Yeşil = Grup ve zamanında bakım</div>
        </div>
    </div>
    
    <!-- RENK LEGEND -->
    <div style="position:fixed; bottom:20px; right:20px; background:rgba(0,0,0,0.7); padding:15px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); backdrop-filter:blur(5px);">
        <h4 style="margin-top:0; color:#c9a94f;">🎨 RENK AÇIKLAMASI</h4>
        <div style="display:flex; flex-direction:column; gap:8px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:12px; height:12px; background:#2ecc71; border-radius:50%; border:1px solid rgba(0,0,0,0.3);"></div>
                <span style="color:white; font-size:14px;"><strong>Yeşil:</strong> 21 güne kadar</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:12px; height:12px; background:#3498db; border-radius:50%; border:1px solid rgba(0,0,0,0.3);"></div>
                <span style="color:white; font-size:14px;"><strong>Mavi:</strong> 21-28 gün</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:12px; height:12px; background:#e74c3c; border-radius:50%; border:1px solid rgba(0,0,0,0.3);"></div>
                <span style="color:white; font-size:14px;"><strong>Kırmızı:</strong> 28+ gün (pulse animasyon)</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:12px; height:12px; background:#9c27b0; border-radius:50%; border:1px solid rgba(0,0,0,0.3);"></div>
                <span style="color:white; font-size:14px;"><strong>Mor:</strong> Grup üyesi</span>
            </div>
        </div>
    </div>
    
    <script>
        console.log("🎯 TEST SAYFASI BAŞLATILIYOR...");
        
        // 1. CSS DOSYASI KONTROLÜ
        function checkCSS() {
            const cssStatus = document.getElementById('css-status');
            
            // Tüm stil sayfalarını kontrol et
            const stylesheets = Array.from(document.styleSheets);
            let casinoCSS = null;
            
            console.log("📄 Toplam stil sayfası:", stylesheets.length);
            
            stylesheets.forEach((sheet, index) => {
                if (sheet.href) {
                    console.log(`Stil ${index}: ${sheet.href}`);
                    
                    // Casino CSS'ini bul
                    if (sheet.href.includes('style.css')) {
                        casinoCSS = sheet;
                    }
                }
            });
            
            if (casinoCSS) {
                console.log("✅ CASINO CSS DOSYASI BULUNDU:", casinoCSS.href);
                
                // CSS kurallarını kontrol et
                try {
                    const rules = casinoCSS.cssRules || casinoCSS.rules;
                    console.log("📊 CSS kuralları sayısı:", rules ? rules.length : 0);
                    
                    // Önemli sınıfları kontrol et
                    const importantClasses = [
                        '.machine',
                        '.group-member',
                        '.maintenance-green',
                        '.maintenance-blue',
                        '.maintenance-red',
                        '.group-indicator',
                        '.maintenance-status'
                    ];
                    
                    let foundClasses = [];
                    
                    importantClasses.forEach(className => {
                        const hasClass = Array.from(rules || []).some(rule => 
                            rule.selectorText && rule.selectorText.includes(className)
                        );
                        
                        if (hasClass) {
                            foundClasses.push(className);
                            console.log(`✅ ${className} sınıfı mevcut`);
                        } else {
                            console.log(`❌ ${className} sınıfı BULUNAMADI`);
                        }
                    });
                    
                    if (foundClasses.length === importantClasses.length) {
                        cssStatus.textContent = "✅ TÜM CSS SINIFLARI YÜKLENDİ";
                        cssStatus.style.color = "#2ecc71";
                    } else {
                        cssStatus.textContent = `⚠️ ${foundClasses.length}/${importantClasses.length} sınıf yüklendi`;
                        cssStatus.style.color = "#ff9800";
                    }
                    
                } catch (error) {
                    console.log("⚠️ CSS kuralları okunamadı:", error.message);
                    cssStatus.textContent = "⚠️ CSS kuralları okunamadı (CORS)";
                    cssStatus.style.color = "#ff9800";
                }
            } else {
                console.log("❌ CASINO CSS DOSYASI BULUNAMADI!");
                cssStatus.textContent = "❌ CSS DOSYASI YÜKLENEMEDİ";
                cssStatus.style.color = "#e74c3c";
                
                // Alternatif yol dene
                console.log("🔍 Alternatif CSS yolu deneniyor...");
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'css/style.css?v=' + new Date().getTime();
                document.head.appendChild(link);
                
                setTimeout(checkCSS, 500);
            }
        }
        
        // 2. ELEMENT KONTROLÜ
        function checkElements() {
            console.log("\n🔍 ELEMENT KONTROLÜ:");
            
            const machines = document.querySelectorAll('.machine');
            console.log("🎰 Makina sayısı:", machines.length);
            
            machines.forEach((machine, index) => {
                const classes = machine.className;
                const hasGroupIndicator = machine.querySelector('.group-indicator');
                const hasMaintenanceStatus = machine.querySelector('.maintenance-status');
                const hasRotateBtn = machine.querySelector('.rotate-btn');
                const hasGroupSelectBtn = machine.querySelector('.group-select-btn');
                
                console.log(`\nMakina ${index + 1}:`, {
                    'Sınıflar': classes,
                    'Grup İndikatörü': hasGroupIndicator ? '✅ Var' : '❌ Yok',
                    'Bakım Durumu': hasMaintenanceStatus ? '✅ Var' : '❌ Yok',
                    'Döndürme Butonu': hasRotateBtn ? '✅ Var' : '❌ Yok',
                    'Grup Seçim Butonu': hasGroupSelectBtn ? '✅ Var' : '❌ Yok'
                });
                
                // Görsel kontrol
                const computedStyle = window.getComputedStyle(machine);
                const borderColor = computedStyle.borderColor;
                const backgroundColor = computedStyle.backgroundColor;
                
                console.log(`   Border Color: ${borderColor}`);
                console.log(`   Background: ${backgroundColor}`);
            });
        }
        
        // 3. TEST BUTONLARI EKLE
        function addTestButtons() {
            const buttonContainer = document.createElement('div');
            buttonContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                display: flex;
                flex-direction: column;
                gap: 10px;
                z-index: 10000;
            `;
            
            const refreshBtn = document.createElement('button');
            refreshBtn.textContent = '🔄 Sayfayı Yenile';
            refreshBtn.style.cssText = `
                padding: 10px 15px;
                background: #3498db;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
            `;
            refreshBtn.onclick = () => location.reload(true);
            
            const hardRefreshBtn = document.createElement('button');
            hardRefreshBtn.textContent = '💥 Sert Yenile (Ctrl+F5)';
            hardRefreshBtn.style.cssText = `
                padding: 10px 15px;
                background: #e74c3c;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
            `;
            hardRefreshBtn.onclick = () => {
                // Cache bypass için rastgele parametre ekle
                const url = new URL(window.location.href);
                url.searchParams.set('_', Date.now());
                window.location.href = url.toString();
            };
            
            const consoleBtn = document.createElement('button');
            consoleBtn.textContent = '📋 Konsolu Aç (F12)';
            consoleBtn.style.cssText = `
                padding: 10px 15px;
                background: #2ecc71;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
            `;
            consoleBtn.onclick = () => {
                alert('Lütfen F12 tuşuna basarak konsolu açın ve hataları kontrol edin.');
            };
            
            buttonContainer.appendChild(refreshBtn);
            buttonContainer.appendChild(hardRefreshBtn);
            buttonContainer.appendChild(consoleBtn);
            document.body.appendChild(buttonContainer);
        }
        
        // SAYFA YÜKLENDİĞİNDE ÇALIŞTIR
        document.addEventListener('DOMContentLoaded', () => {
            console.log("🚀 DOM yüklendi, testler başlatılıyor...");
            
            // 2 saniye bekle sonra kontrol et
            setTimeout(() => {
                checkCSS();
                checkElements();
                addTestButtons();
                
                // Final mesaj
                console.log("\n🎉 TÜM TESTLER TAMAMLANDI!");
                console.log("👀 Ekranda şunları görmelisiniz:");
                console.log("   1. 6 farklı makina kutusu");
                console.log("   2. Mor kutular (grup makineleri)");
                console.log("   3. Yeşil/Mavi/Kırmızı kenarlıklar");
                console.log("   4. Sağ altta renk açıklaması");
                console.log("   5. Sağ üstte test butonları");
            }, 2000);
        });
        
        // CSS yükleme hatası dinleyicisi
        document.addEventListener('error', (e) => {
            if (e.target.tagName === 'LINK' && e.target.href.includes('style.css')) {
                console.error('❌ CSS YÜKLEME HATASI:', e.target.href);
                document.getElementById('css-status').textContent = '❌ CSS YÜKLEME HATASI';
                document.getElementById('css-status').style.color = '#e74c3c';
            }
        }, true);
    </script>
</body>
</html>