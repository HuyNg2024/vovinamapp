# VovinamApp - Hệ Thống Quản Lý Võ Việt Nam

Đây là dự án backend xây dựng bằng **Laravel 10** cung cấp API cho ứng dụng quản lý môn phái Vovinam (Võ Việt Nam).

## 🌟 Chức năng chính
- 🔐 **Authentication**: Đăng ký, đăng nhập (JWT), OTP quên mật khẩu.
- 🥋 **Quản lý Võ đường (Clubs)**: Tìm kiếm CLB, xem chi tiết, yêu cầu tham gia/rời.
- 👨‍🏫 **Quản lý Lớp học**: Điểm danh (dành cho HLV), xem lịch sử điểm danh.
- 🏅 **Quản lý Đai & Thi thố**: Đăng ký thi lên đai, cập nhật kết quả thi.
- 🛒 **Cửa hàng (E-commerce)**: Xem sản phẩm (đồ võ), giỏ hàng, đặt hàng.
- 💳 **Thanh toán trực tuyến**: Tích hợp thanh toán **VNPay** cho học phí, lệ phí thi, và đơn hàng.
- 📰 **Tin tức & Lý thuyết**: Các thông báo, lý thuyết võ đạo theo từng cấp đai.

## 🛠️ Công nghệ sử dụng
- **Framework**: Laravel 10 (PHP 8.1+)
- **Database**: MySQL / MariaDB
- **Authentication**: `tymon/jwt-auth`
- **File Storage**: Firebase Storage (`kreait/laravel-firebase`)
- **Payment Gateway**: VNPay
- **Geocoding**: OpenCage Geocoder (`geocoder-php/open-cage-provider`)

## 🚀 Cài đặt môi trường (Local)
1. Clone repo: `git clone https://github.com/HuyNg2024/vovinamapp.git`
2. Cài đặt thư viện: `composer install`
3. Copy file môi trường: `cp .env.example .env`
4. Tạo key: `php artisan key:generate`
5. Khởi động server: `php artisan serve`

*Lưu ý: Hệ thống hiện đang dùng một số bảng không qua migration (nhập từ SQL dump). Cần kết nối tới database có sẵn để chạy đủ chức năng.*

## 🔒 Bản quyền
Dự án được bảo trì nội bộ. Không sử dụng cho mục đích thương mại khi chưa có sự đồng ý.
