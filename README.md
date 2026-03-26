# 🍔 Fast Food POS — Restoran Boshqaruv Tizimi

Zamonaviy, engil va tez ishlaydigan **Point of Sale (POS)** tizimi. Restoran, kafe va fast-food muassasalari uchun mo'ljallangan. PHP + SQLite asosida qurilgan, hech qanday qo'shimcha server yoki ma'lumotlar bazasi kerak emas.

---

## 🚀 Asosiy Imkoniyatlar

| Modul | Tavsif |
|---|---|
| 🖥️ **Kassa (POS)** | Touch-friendly interfeys, mahsulot va kategoriyalar bo'yicha filtrlash, savat, to'lov |
| 🪑 **Stollar** | Band/bo'sh holat, stolga buyurtma bog'lash, faol buyurtmani davom ettirish |
| 👨‍🍳 **Ishchilar** | Ofitsiant ro'yxati, sessiyaga ko'ra avtomatik tanlash (lock) |
| 📦 **Mahsulotlar** | Rasm bilan, kategoriya bo'yicha, narx boshqaruvi |
| 🏷️ **Kategoriyalar** | Cheksiz kategoriyalar, POS da pill tab ko'rinishi |
| 📊 **Hisobotlar** | Sotuv tarixi, kun/sana bo'yicha filtrlash, jami summalar |
| ⚙️ **Sozlamalar** | Do'kon nomi, manzil, telefon, printer IP lari |
| 🖨️ **Printer** | Kassa va oshxona printeriga tarmoq orqali chek yuborish (port 9100) |
| 👤 **Foydalanuvchilar** | Admin va Kassir rollari, ishchi profiliga bog'lash |
| 🌙 **Dark / Light Mode** | localStorage asosida saqlanadigon rejim |

---

## 🗂️ Loyiha Strukturasi

```
food/
├── index.php           # POS kassir sahifasi (asosiy)
├── categories.php      # Kategoriyalar boshqaruvi
├── products.php        # Mahsulotlar boshqaruvi
├── tables.php          # Stollar boshqaruvi
├── waiters.php         # Ishchilar boshqaruvi
├── users.php           # Foydalanuvchilar boshqaruvi
├── reports.php         # Sotuv hisobotlari
├── settings.php        # Tizim sozlamalari
├── login.php           # Kirish sahifasi
├── logout.php          # Chiqish
├── process.php         # Buyurtma saqlash (AJAX)
├── get_active_order.php# Faol buyurtmani olish (AJAX)
├── get_receipt.php     # Chek ma'lumotlari (AJAX)
├── print_handler.php   # Printer boshqaruvi (AJAX)
├── delete.php          # Yozuv o'chirish
├── database.sqlite     # SQLite ma'lumotlar bazasi
├── includes/
│   ├── db.php          # Ma'lumotlar bazasi ulanishi + jadval yaratish
│   ├── header.php      # Umumiy header (Bootstrap, sidebar)
│   ├── footer.php      # Umumiy footer
│   └── printer.php     # Printer sinfi (network raw print)
├── assets/
│   ├── img/            # Statik rasmlar
│   ├── js/             # JavaScript fayllar
│   └── uploads/        # Mahsulot rasmlari (yuklangan)
├── Dockerfile          # Docker image konfiguratsiyasi
├── docker-compose.yml  # Docker Compose sozlamalari
└── manifest.json       # PWA manifest
```

---

## 🗃️ Ma'lumotlar Bazasi Sxemasi

```
categories       → id, name
products         → id, category_id, name, price, image
tables           → id, name
waiters          → id, name
users            → id, username, password, role, waiter_id
orders           → id, waiter_id, table_id, total_amount, status, payment_method, created_at
order_items      → id, order_id, product_id, quantity, price
settings         → id, key, value
```

---

## 🔑 Standart Foydalanuvchilar

| Login | Parol | Rol |
|---|---|---|
| `admin` | `admin123` | Admin (to'liq kirish) |
| `kassir` | `kassir123` | Kassir (faqat kassa) |

> ⚠️ Birinchi ishga tushirgandan so'ng parollarni o'zgartirishni unutmang!

---

## ⚙️ O'rnatish va Ishga Tushirish

### 1. Lokal (OSPanel / XAMPP / OpenServer)

```bash
# Papkani web server papkasiga joylashtiring
# Masalan: C:\OSPanel\domains\food\

# Brauzerda oching:
http://food.localhost/
```

PHP 7.4+ va `pdo_sqlite` kengaytmasi yoqilgan bo'lishi kerak.

### 2. Docker orqali

```bash
# Loyiha papkasida:
docker compose up -d

# Brauzerda oching:
http://localhost:8080/
```

---

## 🖨️ Printer Sozlash

Tizim **network thermal printer** (ESC/POS) bilan ishlaydi:

1. `settings.php` sahifasiga o'ting
2. **Kassa Printer IP** — chek chiqaradigan printer IP si
3. **Oshxona Printer IP** — oshxona buyurtma printerining IP si
4. Port: **9100** (standart RAW port)
5. "Test" tugmasi orqali ulanishni tekshiring

---

## 🔐 Rol Huquqlari

| Imkoniyat | Admin | Kassir |
|---|---|---|
| Kassa (POS) | ✅ | ✅ |
| Mahsulotlar | ✅ | ❌ |
| Kategoriyalar | ✅ | ❌ |
| Stollar | ✅ | ❌ |
| Ishchilar | ✅ | ❌ |
| Foydalanuvchilar | ✅ | ❌ |
| Hisobotlar | ✅ | ❌ |
| Sozlamalar | ✅ | ❌ |

---

## 🛠️ Texnologiyalar

- **Backend:** PHP 7.4+
- **Ma'lumotlar Bazasi:** SQLite 3 (faylga asoslangan, server shart emas)
- **Frontend:** Bootstrap 5, Bootstrap Icons, vanilla JavaScript
- **Printer:** ESC/POS protokoli, TCP/RAW (port 9100)
- **PWA:** `manifest.json` + `sw.js` (Service Worker)
- **Konteyner:** Docker + Apache

---

## 📱 PWA Qo'llab-quvvatlash

Tizim **Progressive Web App (PWA)** sifatida o'rnatilishi mumkin. Brauzerning "Add to Home Screen" funksiyasidan foydalaning — monoblock yoki planshetda ilovadek ishlaydi.

---

## 📝 Litsenziya

Ushbu loyiha ochiq manba asosida yaratilgan. Shaxsiy va tijorat maqsadlarida erkin foydalanish mumkin.
