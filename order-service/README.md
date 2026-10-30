# Order Service – Microservices

## 1. Deskripsi

Order Service merupakan layanan yang bertanggung jawab untuk mengelola pesanan pengguna dalam sistem berbasis Microservices.

Layanan ini menggunakan Laravel dan MySQL. Order Service berkomunikasi dengan Cart Service untuk mengambil keranjang belanja dan Product Service untuk memperoleh informasi produk.

## 2. Teknologi

- PHP dan Laravel
- MySQL 8
- Docker dan Docker Compose
- REST API
- Eloquent ORM

## 3. Arsitektur

Order Service berkomunikasi dengan dua layanan eksternal:

- **Cart Service:** mengambil dan menghapus isi keranjang setelah checkout berhasil.
- **Product Service:** mengambil informasi produk, harga, dan stok untuk validasi pesanan.
- **Order Database:** menyimpan data pesanan dan detail item secara terpisah.

Order Service memiliki database sendiri dan tidak mengakses database layanan lain secara langsung.

## 4. Konfigurasi dan Menjalankan Layanan

Order Service dijalankan melalui Docker Compose dari direktori utama repository.

```bash
docker compose up --build -d
```

Alamat layanan:

- Order Service: `http://localhost:3003`
- Product Service: `http://localhost:3000`
- Cart Service: `http://localhost:3001`
- Review Service: `http://localhost:3002`

Untuk memeriksa container:

```bash
docker compose ps
```

Untuk melihat log Order Service:

```bash
docker compose logs order-service
```

## 5. Database

Order Service menggunakan database MySQL tersendiri dengan dua tabel utama:

### orders

Menyimpan informasi pesanan:

- id
- user_id
- total_amount
- status
- created_at
- updated_at

### order_items

Menyimpan detail produk dalam pesanan:

- id
- order_id
- product_id
- product_name
- price
- quantity
- subtotal
- created_at
- updated_at

Nama dan harga produk disimpan sebagai snapshot agar informasi pesanan tetap konsisten meskipun data produk berubah.

## 6. Daftar Endpoint

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/health` | Memeriksa kesehatan layanan |
| POST | `/api/orders` | Membuat pesanan |
| GET | `/api/orders/{id}` | Melihat detail pesanan |
| GET | `/api/orders/user/{userId}` | Melihat pesanan pengguna |
| PATCH | `/api/orders/{id}/pay` | Mengubah status pembayaran |

## 7. Contoh Penggunaan API

### Memeriksa layanan

```http
GET http://localhost:3003/api/health
```

### Membuat pesanan

```http
POST http://localhost:3003/api/orders
Content-Type: application/json
Accept: application/json
```

Body:

```json
{
  "user_id": 1
}
```

Order Service akan mengambil keranjang pengguna, memvalidasi produk dan stok, menyimpan pesanan, kemudian mencoba mengosongkan keranjang.

### Melihat detail pesanan

```http
GET http://localhost:3003/api/orders/1
```

### Melihat pesanan pengguna

```http
GET http://localhost:3003/api/orders/user/1
```

Filter berdasarkan status:

```http
GET http://localhost:3003/api/orders/user/1?status=PENDING
```

Status yang didukung: `PENDING` dan `PAID`.

### Simulasi pembayaran

```http
PATCH http://localhost:3003/api/orders/1/pay
```

Pembayaran hanya disimulasikan dengan mengubah status pesanan dari `PENDING` menjadi `PAID`.

## 8. Penanganan Status HTTP

| HTTP | Keterangan |
|---|---|
| 200 | Request berhasil |
| 201 | Pesanan berhasil dibuat |
| 404 | Pesanan atau resource tidak ditemukan |
| 409 | Konflik, seperti keranjang kosong atau stok tidak mencukupi |
| 422 | Validasi request gagal |
| 503 | Product Service atau Cart Service tidak tersedia |
| 500 | Terjadi kesalahan internal |

## 9. Aturan Proses Checkout

1. Pengguna mengirimkan `user_id`.
2. Order Service mengambil data keranjang dari Cart Service.
3. Order Service memeriksa apakah keranjang berisi item.
4. Order Service mengambil informasi setiap produk dari Product Service.
5. Sistem memvalidasi keberadaan produk dan kecukupan stok.
6. Pesanan dan detail item disimpan dalam satu transaksi database.
7. Setelah pesanan berhasil disimpan, Order Service mencoba mengosongkan keranjang.
8. Jika pengosongan keranjang gagal, pesanan yang sudah tersimpan tidak dibatalkan dan kegagalan dicatat dalam log.

Stok produk hanya diperiksa dan tidak dikurangi.

## 10. Batasan Sistem

- Tidak menggunakan autentikasi.
- Tidak menggunakan pembayaran sungguhan.
- Tidak menyediakan pembatalan pesanan.
- Tidak menggunakan API Gateway atau Message Broker.
- Tidak melakukan pengurangan stok produk.
- Pembayaran hanya mengubah status pesanan.

## 11. Konfigurasi Environment

Konfigurasi layanan menggunakan environment variables, antara lain:

- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `PRODUCT_SERVICE_URL`
- `CART_SERVICE_URL`

File `.env` tidak disertakan dalam repository. Gunakan `.env.example` sebagai referensi konfigurasi.