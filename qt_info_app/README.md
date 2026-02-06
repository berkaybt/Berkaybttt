# Bilgi İşlem Kontrol Paneli (Qt)

Bu proje, Python + Qt (PySide6) kullanarak hazırlanmış, bilgi işlem ekipleri için örnek bir görsel arayüz sağlar.
Genel sistem bilgileri, ağ/IP listeleri, sunucu envanteri ve hızlandırma önerileri tek panelde toplanır.

## Kurulum

```bash
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

## Çalıştırma

```bash
python main.py
```

## Özellikler

- Sistem özeti (OS, CPU, RAM, disk, açılış zamanı)
- Ağ arayüzleri ve IP adresleri listesi
- Sunucu envanteri yönetimi (ekle/sil)
- Sistem hızlandırma önerileri ve hızlı bakım aksiyonları

> Not: Güç profili ve temizlik aksiyonları gerçek sistem ayarlarını değiştirmez; örnek amaçlıdır.
