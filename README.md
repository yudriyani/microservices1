# Microservices Toko Online

## 1. Deskripsi Proyek

Proyek ini merupakan implementasi sistem toko online menggunakan arsitektur **Microservices**. Sistem dibagi menjadi beberapa layanan yang memiliki fungsi dan database masing-masing.

Terdapat empat layanan utama:

1. Product Service
2. Cart Service
3. Review Service
4. Order Service

Komunikasi antarservice menggunakan REST API, sedangkan pengelolaan aplikasi dan database menggunakan Docker Compose.

Fokus pengerjaan UTS adalah Order Service yang menangani proses checkout, pengelolaan pesanan, riwayat pesanan, dan simulasi pembayaran.

## 2. Arsitektur dan Teknologi

| Service | Fungsi | Teknologi | Port |
|---|---|---|---:|
| Product Service | Mengelola produk dan stok | Node.js, Express, MySQL | 3000 |
| Cart Service | Mengelola keranjang belanja | Laravel, PostgreSQL | 3001 |
| Review Service | Mengelola ulasan produk | Python, Flask, MongoDB | 3002 |
| Order Service | Mengelola pesanan dan checkout | Laravel, MySQL | 3003 |

Setiap service memiliki database sendiri. Order Service memperoleh informasi produk dan keranjang melalui REST API, bukan dengan mengakses database service lain secara langsung.

## 3. Struktur Repository

```text
microservices1/
├── product-service/
├── cart-service/
├── review-service/
├── order-service/
├── docker-compose.yml
└── README.md
```

Setiap folder service memiliki kode program dan dokumentasi masing-masing.

## 4. Persyaratan Sistem

Perangkat yang dibutuhkan:

- Git
- Docker Desktop
- Docker Compose
- Koneksi internet untuk proses build pertama

Pastikan Docker Desktop sudah berjalan sebelum menjalankan proyek.

## 5. Cara Menjalankan Proyek

Clone repository:

```bash
git clone https://github.com/yudriyani/microservices1.git
```

Masuk ke direktori proyek:

```bash
cd microservices1
```

Jalankan seluruh layanan:

```bash
docker compose up --build -d
```

Periksa status container:

```bash
docker compose ps
```

Melihat log Order Service:

```bash
docker compose logs order-service
```

Menghentikan seluruh layanan:

```bash
docker compose down
```

Perintah `docker compose down` tidak menghapus volume database. Hindari menggunakan `docker compose down -v` jika ingin mempertahankan data.

## 6. Database

Setiap service menggunakan database terpisah:

| Service | Database |
|---|---|
| Product Service | MySQL |
| Cart Service | PostgreSQL |
| Review Service | MongoDB |
| Order Service | MySQL |

Order Service menggunakan dua tabel utama:

**Tabel `orders`**
- id
- user_id
- total_amount
- status
- created_at
- updated_at

**Tabel `order_items`**
- id
- order_id
- product_id
- product_name
- price
- quantity
- subtotal
- created_at
- updated_at

Nama dan harga produk disimpan sebagai snapshot saat checkout agar riwayat pesanan tetap konsisten meskipun harga produk berubah.

## 7. Order Service

Order Service merupakan fokus utama pengerjaan UTS.

Fungsi utamanya:

- Menerima permintaan pembuatan pesanan.
- Mengambil keranjang pengguna dari Cart Service.
- Mengambil informasi produk dari Product Service.
- Memvalidasi produk dan ketersediaan stok.
- Menghitung total pesanan.
- Menyimpan pesanan dan detail item dalam satu transaksi database.
- Mengosongkan keranjang setelah pesanan berhasil disimpan.
- Menampilkan detail dan daftar pesanan.
- Melakukan simulasi pembayaran.

### Alur checkout

1. Pengguna mengirimkan `user_id`.
2. Order Service mengambil keranjang dari Cart Service.
3. Sistem memeriksa apakah keranjang kosong.
4. Order Service mengambil informasi produk dari Product Service.
5. Sistem memvalidasi keberadaan produk dan kecukupan stok.
6. Pesanan dan detail item disimpan dalam satu transaksi database.
7. Order Service mencoba mengosongkan keranjang.
8. Sistem mengembalikan response keberhasilan pembuatan pesanan.

Jika pengosongan keranjang gagal setelah pesanan tersimpan, pesanan tidak dibatalkan dan kegagalan dicatat dalam log.

Stok produk hanya diperiksa dan tidak dikurangi.

## 8. Endpoint Order Service

Base URL:

```text
http://localhost:3003
```

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/health` | Memeriksa kondisi layanan |
| POST | `/orders` | Membuat pesanan |
| GET | `/orders/{id}` | Melihat detail pesanan |
| GET | `/orders/user/{userId}` | Melihat daftar pesanan pengguna |
| PATCH | `/orders/{id}/pay` | Simulasi pembayaran |

### Contoh request membuat pesanan

```http
POST http://localhost:3003/orders
Content-Type: application/json
Accept: application/json
```

Body:

```json
{
  "user_id": 1
}
```

### Contoh melihat detail pesanan

```http
GET http://localhost:3003/orders/1
```

### Contoh melihat daftar pesanan pengguna

```http
GET http://localhost:3003/orders/user/1
```

Filter berdasarkan status:

```http
GET http://localhost:3003/orders/user/1?status=PENDING
```

Status pesanan yang tersedia adalah `PENDING` dan `PAID`.

### Contoh simulasi pembayaran

```http
PATCH http://localhost:3003/orders/1/pay
```

Pembayaran dilakukan dengan mengubah status pesanan dari `PENDING` menjadi `PAID`. Sistem tidak menggunakan pembayaran sungguhan.

## 9. Penanganan HTTP Status

| Status | Keterangan |
|---|---|
| 200 | Request berhasil |
| 201 | Pesanan berhasil dibuat |
| 404 | Pesanan tidak ditemukan |
| 409 | Konflik bisnis, seperti keranjang kosong atau stok tidak mencukupi |
| 422 | Validasi request gagal |
| 503 | Product Service atau Cart Service tidak tersedia |
| 500 | Kesalahan internal server |

## 10. Keputusan Desain

- **Database terpisah:** setiap service mengelola database sendiri.
- **REST API:** komunikasi antarservice dilakukan melalui HTTP API.
- **Snapshot produk:** nama dan harga produk disimpan saat checkout.
- **Database transaction:** penyimpanan pesanan dan detail item dilakukan dalam satu transaksi.
- **Pemisahan tanggung jawab:** routes, request validation, controller, service, client, dan model memiliki fungsi masing-masing.
- **Penanganan error:** kegagalan komunikasi dengan service lain ditangani menggunakan exception dan logging.

## 11. Batasan Sistem

- Tidak menggunakan autentikasi pengguna.
- Tidak menggunakan payment gateway.
- Tidak menyediakan pembatalan pesanan.
- Tidak menggunakan API Gateway atau Message Broker.
- Stok produk hanya diperiksa dan tidak dikurangi.
- Pembayaran hanya berupa simulasi perubahan status.

## 12. Dokumentasi Service

Dokumentasi masing-masing layanan tersedia pada:

- Product Service: `product-service/README.md`
- Cart Service: `cart-service/README.md`
- Review Service: `review-service/README.md`
- Order Service: `order-service/README.md`

## 13. Repository

GitHub: https://github.com/yudriyani/microservices1