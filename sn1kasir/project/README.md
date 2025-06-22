# Sistem Kasir OOP - PHP Murni

Sistem kasir berbasis web menggunakan PHP murni dengan pendekatan Object-Oriented Programming (OOP).

## Fitur Utama

### 1. Manajemen User
- Login/Logout sistem
- Role-based access (Admin, Cashier, Manager)
- Manajemen user dengan CRUD operations

### 2. Manajemen Produk
- CRUD operations untuk produk
- Kategori produk
- Tracking stok dan stok minimum
- Upload gambar produk
- Barcode support

### 3. Manajemen Pelanggan
- Database pelanggan
- Informasi kontak dan alamat
- History transaksi per pelanggan

### 4. Point of Sale (POS)
- Interface kasir yang user-friendly
- Pencarian produk berdasarkan nama/barcode
- Keranjang belanja
- Multiple payment methods
- Print receipt

### 5. Manajemen Transaksi
- History semua transaksi
- Detail transaksi
- Status transaksi (pending, completed, cancelled)
- Kode transaksi otomatis

### 6. Reporting
- Laporan penjualan harian
- Laporan produk terlaris
- Laporan stok rendah
- Dashboard dengan statistik real-time

## Struktur Database

### Tabel Users
- Menyimpan data pengguna sistem
- Role-based access control
- Password encryption

### Tabel Categories
- Kategori produk
- Status active/inactive

### Tabel Products
- Data produk lengkap
- Relasi dengan kategori
- Tracking stok dan harga

### Tabel Customers
- Data pelanggan
- Informasi kontak lengkap

### Tabel Transactions
- Header transaksi
- Informasi pembayaran
- Status transaksi

### Tabel Transaction Details
- Detail item dalam transaksi
- Quantity dan harga per item

## Teknologi yang Digunakan

- **Backend**: PHP 7.4+ (Pure PHP, no framework)
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Architecture**: Object-Oriented Programming (OOP)

## Prinsip OOP yang Diterapkan

### 1. Encapsulation
- Properties private/protected
- Getter/Setter methods
- Data hiding

### 2. Inheritance
- Base model classes
- Controller inheritance
- Code reusability

### 3. Polymorphism
- Method overriding
- Interface implementation
- Dynamic method binding

### 4. Abstraction
- Abstract classes
- Interface definitions
- Clean API design

## Struktur Folder

```
kasir-system/
├── config/
│   └── Database.php          # Database connection
├── models/
│   ├── User.php             # User model
│   ├── Category.php         # Category model
│   ├── Product.php          # Product model
│   ├── Customer.php         # Customer model
│   ├── Transaction.php      # Transaction model
│   └── TransactionDetail.php # Transaction detail model
├── controllers/
│   ├── AuthController.php   # Authentication controller
│   ├── ProductController.php # Product controller
│   └── TransactionController.php # Transaction controller
├── views/
│   ├── login.php           # Login page
│   ├── dashboard.php       # Dashboard
│   ├── pos.php            # Point of Sale
│   ├── products.php       # Product management
│   └── transactions.php   # Transaction history
├── database/
│   └── schema.sql         # Database schema
├── index.php             # Main entry point
└── README.md            # Documentation
```

## Instalasi

1. **Setup Database**
   ```sql
   CREATE DATABASE kasir_db;
   ```

2. **Import Schema**
   ```bash
   mysql -u root -p kasir_db < database/schema.sql
   ```

3. **Konfigurasi Database**
   Edit file `config/Database.php` sesuai dengan konfigurasi database Anda.

4. **Setup Web Server**
   - Pastikan PHP 7.4+ terinstall
   - Aktifkan ekstensi PDO MySQL
   - Setup virtual host atau akses melalui localhost

## Default Login

- **Username**: admin
- **Password**: password

## Security Features

- Password hashing menggunakan PHP password_hash()
- SQL injection protection dengan prepared statements
- XSS protection dengan input sanitization
- Session management yang aman
- Role-based access control

## Best Practices

- Single Responsibility Principle
- DRY (Don't Repeat Yourself)
- SOLID principles
- Clean code standards
- Error handling yang comprehensive
- Input validation dan sanitization

## Development Guidelines

1. Semua model extends dari base class
2. Controllers menggunakan dependency injection
3. Views terpisah dari business logic
4. Database transactions untuk operasi kompleks
5. Logging untuk audit trail
6. Responsive design untuk mobile compatibility

## Future Enhancements

- RESTful API development
- Multi-language support
- Advanced reporting dengan charts
- Inventory management
- Supplier management
- Integration dengan payment gateway
- Mobile app development
- Cloud deployment ready

## Kontribusi

Sistem ini dikembangkan dengan arsitektur yang modular dan extensible, memudahkan untuk pengembangan fitur baru dan maintenance.