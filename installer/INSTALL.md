# 🚛 Hướng dẫn Cài đặt — NT Logistics ERP

## ✅ Yêu cầu hệ thống

| Thành phần | Phiên bản tối thiểu |
|---|---|
| Hệ điều hành | Windows 10 / 11 (64-bit) |
| XAMPP | 8.2 trở lên (bao gồm PHP 8.2 + MySQL 8.x) |
| RAM | 4 GB (khuyến nghị 8 GB) |
| Ổ cứng | 2 GB trống |
| Kết nối mạng | Cần có khi cài lần đầu (tải thư viện) |

---

## 📦 Hướng dẫn Cài đặt (Dành cho người dùng cuối)

### Bước 1 — Cài XAMPP (nếu chưa có)

1. Tải XAMPP tại: **https://www.apachefriends.org/download.html**
2. Chọn phiên bản **PHP 8.2** → Cài vào ổ `C:\xampp` (hoặc bất kỳ ổ nào)
3. Khởi động **XAMPP Control Panel** → Bấm **Start** cho **MySQL**

> ⚠️ Apache **không cần** khởi động — hệ thống dùng máy chủ riêng.

---

### Bước 2 — Giải nén bộ cài

Giải nén file `NT-Logistics-ERP.zip` vào thư mục bất kỳ, ví dụ:
```
D:\NT-Logistics-ERP\
```

---

### Bước 3 — Chạy bộ cài

Double-click vào file **`installer/install.bat`** trong thư mục vừa giải nén.

Cửa sổ PowerShell sẽ mở ra và hướng dẫn bạn từng bước:

1. **Hệ thống tự quét** PHP, MySQL trên tất cả các ổ đĩa (C, D, E, ...)
2. **Nhập thông tin database:**
   - Host: `127.0.0.1` (giữ nguyên)
   - Port: `3306` (giữ nguyên)
   - Tên database: `logistics-erp-db` (giữ nguyên hoặc đổi tên)
   - Username: `root` (mặc định của XAMPP)
   - Password: *(để trống nếu XAMPP không đặt mật khẩu)*
3. **Nhập thông tin tài khoản Admin đầu tiên:**
   - Họ và tên
   - Email
   - Mật khẩu (tối thiểu 8 ký tự)
4. **Xác nhận** → Bấm Enter để bắt đầu
5. Bộ cài tự động thực hiện tất cả, sau đó mở trình duyệt

---

### Bước 4 — Đăng nhập

Sau khi cài xong, trình duyệt sẽ tự mở tại:

```
http://127.0.0.1:8000
```

Đăng nhập bằng email và mật khẩu Admin vừa tạo.

---

## 🔄 Khởi động lại sau khi tắt máy

Sau khi tắt máy và bật lại, để dùng phần mềm:

1. Mở **XAMPP Control Panel** → Start **MySQL**
2. Double-click **`installer/start.bat`** (để khởi động lại máy chủ)

---

## 🔧 Xử lý lỗi thường gặp

### ❌ "Không tìm thấy PHP"
- Đảm bảo XAMPP đã được cài. Bộ cài quét tất cả ổ đĩa nhưng cần cài vào thư mục `xampp` (ví dụ: `D:\xampp`).

### ❌ "Không thể kết nối MySQL"
- Mở XAMPP Control Panel → Kiểm tra MySQL có đang **Running** không
- Nếu Port 3306 bị chiếm: Trong XAMPP → Config → `my.ini` → đổi `port=3307`

### ❌ "Composer install thất bại"
- Kiểm tra kết nối mạng
- Thử chạy lại `install.bat`

### ❌ Cửa sổ PowerShell đóng ngay lập tức
- Click chuột phải vào `install.bat` → **Run as Administrator**

---

## 📁 Cấu trúc thư mục quan trọng

```
NT-Logistics-ERP\
├── installer\
│   ├── install.bat      ← File cài đặt (double-click để chạy)
│   ├── install.ps1      ← Script cài đặt PowerShell (không cần mở)
│   ├── start.bat        ← File khởi động hàng ngày
│   ├── start.ps1        ← Script khởi động server LAN
│   ├── INSTALL.md       ← Tài liệu hướng dẫn này
│   └── prerequisites\   ← Đặt php.zip, mariadb.zip, composer.phar vào đây
├── .env                 ← Cấu hình hệ thống (tự tạo sau khi cài)
├── storage\app\backups\ ← Nơi lưu file sao lưu tự động
└── public\              ← Thư mục web công khai
```

---

## 📞 Hỗ trợ kỹ thuật

Liên hệ đội kỹ thuật NT Logistics để được hỗ trợ khi gặp sự cố cài đặt.
