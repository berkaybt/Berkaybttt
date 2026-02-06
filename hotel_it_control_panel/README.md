# Hotel IT Suite

Kurumsal Windows 10/11 ortamı için üretim kalitesinde “Hotel IT Suite” masaüstü uygulaması. PySide6 tabanlı, modüler mimari, audit log ve LAN içi mesajlaşma içerir.

## Özellikler (MVP-1)
- **Dashboard**: Sistem bilgileri, hızlı aksiyonlar, son 10 işlem (audit) + health score.
- **Inventory**: Cihaz ekle, CSV import/export, ağ taramasından envantere aktar.
- **Network Monitor**: IP aralığı/CIDR tarama, ping + reverse DNS, port tarama, CSV/JSON export, diff özeti.
- **Remote Tools**: RDP, AnyDesk/RustDesk/TeamViewer, Windows admin araçları.
- **Approvals**: 4-eyes onay paneli ve bekleyen işlemler listesi.
- **Incidents**: Olay kayıtları (başlık, cihaz, öncelik, durum).
- **Network Security**: Pasif IDS olayları (yeni cihaz vb.).
- **Maintenance Verification**: Before/after ölçüm tablosu.
- **Verification Center**: Son aksiyonların proof kayıtları.
- **Settings**: Tool paths, default scan range, central policy yolu, messaging server ayarları.
- **Messaging**: LAN içi demo chat + sqlite geçmişi + reconnect.
- **Loglar**: `app/logs/app.log`, `app/logs/security.log`, `app/logs/actions.csv`, `app/logs/crash_report.json`, `app/logs/proof.log`.

## Klasör Yapısı
```
hotel_it_control_panel/
├── app/
│   ├── config/
│   ├── core/
│   ├── modules/
│   ├── security/
│   ├── services/
│   ├── ui/
│   ├── utils/
│   ├── data/
│   └── logs/
├── server_messaging/
├── installer/
└── tests/
```

## Kurulum (Client)
```bash
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
```

## Çalıştırma (Client)
```bash
python -m app.main
```

## Messaging Server (Opsiyonel)
```bash
cd server_messaging
pip install fastapi uvicorn[standard]
python server.py
```

Servis olarak kurulum:
```powershell
# server_messaging klasörü içinde
./service_install.ps1 -PythonPath "C:\Path\to\python.exe"
```

## Portlar / Firewall
- Messaging Server varsayılan: **TCP 8000** (`/ws`).
- Gerekirse Windows Firewall’da inbound rule açın.

## Central Policy
- `app/config/admin_policy.json` lokal policy.
- `central_policy_path` ayarlanırsa uygulama açılışta önce merkezi policy dosyasını kullanır.
- Policy değişimi toast ile bildirilir.

## Doğrulama Mantığı
- Tüm işlemler Action Framework üzerinden ölçülür ve doğrulanır.
- Proof kayıtları `app/logs/proof.log` ve sqlite `verification_runs` tablosunda tutulur.
- Verification Center sayfası son aksiyonları listeler.

## PyInstaller Build
```bash
pip install pyinstaller
pyinstaller hotel_it_control_panel.spec
```
Çıktı: `dist/hotel_it_suite.exe`

## Inno Setup Installer
1. Inno Setup kurun.
2. `installer/HotelITSuite.iss` dosyasını açın.
3. `dist/hotel_it_suite.exe` oluştuğundan emin olun.
4. Script’i çalıştırarak installer üretin.

## Troubleshooting
- **psutil bulunamadı**: `pip install psutil`.
- **Messaging bağlantısı yok**: `app/config/settings.json` içinde server URL ve tokenı kontrol edin.
- **Admin gereken işlemler**: Uygulamayı yönetici olarak çalıştırın.
