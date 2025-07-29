# API Documentation - Sportlodek Booking System

## Base URL
```
http://localhost:8000/api
```

## Authentication
API menggunakan Laravel Sanctum untuk authentication. Setelah login, gunakan Bearer token di header:
```
Authorization: Bearer {token}
```

## 0. Authentication

### Register User
```
POST /api/register
```

**Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:**
```json
{
    "message": "User berhasil didaftarkan",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "token": "1|abc123def456...",
        "token_type": "Bearer"
    }
}
```

### Login User
```
POST /api/login
```

**Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "token": "1|abc123def456...",
        "token_type": "Bearer"
    }
}
```

### Logout User
```
POST /api/logout
```

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "message": "Logout berhasil"
}
```

### Get User Profile
```
GET /api/profile
```

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": null,
        "created_at": "15/01/2025 10:30",
        "updated_at": "15/01/2025 10:30"
    }
}
```

### Update User Profile
```
PUT /api/profile
```

**Headers:**
```
Authorization: Bearer {token}
```

**Body:**
```json
{
    "name": "John Updated",
    "email": "john.updated@example.com"
}
```

### Change Password
```
PUT /api/change-password
```

**Headers:**
```
Authorization: Bearer {token}
```

**Body:**
```json
{
    "current_password": "password123",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

### Refresh Token
```
POST /api/refresh-token
```

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "message": "Token berhasil diperbarui",
    "data": {
        "token": "2|xyz789abc123...",
        "token_type": "Bearer"
    }
}
```

## 1. Merchant Management

### Create Merchant
```
POST /merchants
```

**Body:**
```json
{
    "user_id": 1,
    "name": "Futsal Center Jakarta",
    "address": "Jl. Sudirman No. 123, Jakarta",
    "phone": "021-1234567",
    "status": "active",
    "open": 8,
    "close": 22,
    "banner": "banner.jpg"
}
```

### Update Merchant
```
PUT /merchants/{merchant_id}
```

### Get Validation Rules
```
GET /merchants/validation-rules
```

## 2. Product Management (Lapangan)

### Create Product (Lapangan)
```
POST /products
```

**Body:**
```json
{
    "merchant_id": 1,
    "name": "Lapangan A",
    "price": 150000
}
```

### Update Product
```
PUT /products/{product_id}
```

### Get Products by Merchant
```
GET /products/merchant/{merchant_id}
```

### Get Product Details
```
GET /products/{product_id}
```

## 3. Cart Management

### Add Item to Cart
```
POST /cart
```

**Body:**
```json
{
    "merchant_id": 1,
    "product_id": 1,
    "start": 14,
    "quantity": 2
}
```

**Note:** 
- `start`: jam mulai (0-23)
- `quantity`: jumlah jam booking
- `price_per_hour` dan `total_price` akan dihitung otomatis

### Get User's Cart
```
GET /cart
```

### Update Cart Item
```
PUT /cart/{cart_id}
```

### Remove Item from Cart
```
DELETE /cart/{cart_id}
```

### Clear Cart
```
DELETE /cart
```

## 4. Transaction Management (Booking)

### Create Booking from Cart
```
POST /transactions
```

**Body:**
```json
{
    "payment_method": "transfer"
}
```

**Note:** 
- Akan mengambil semua item dari cart user
- Membuat transaction per merchant
- Membuat transaction details untuk setiap jam booking
- **Otomatis membuat payment** untuk setiap transaction
- Cart akan dikosongkan setelah checkout

### Update Booking Status
```
PUT /transactions/{transaction_id}
```

**Body:**
```json
{
    "status": "confirmed",
    "payment_method": "transfer"
}
```

### Get Available Time Slots
```
GET /transactions/available-slots?product_id=1&date=2025-01-15
```

### Get User's Booking History
```
GET /transactions/my-bookings
```

## 5. Payment Management (EduPay Integration)

### Create Payment
```
POST /payments
```

**Body:**
```json
{
    "transaction_id": 1,
    "merchant_id": 1
}
```

**Note:** 
- Akan membuat payment dengan kode unik
- Auto generate kode: EDUPAY-XXXXXXXX
- Status default: pending

### Get Payment Details
```
GET /payments/{payment_id}
```

### Update Payment Status (Callback dari EduPay)
```
POST /payments/status
```

**Body:**
```json
{
    "code": "EDUPAY-ABC12345",
    "status": "success",
    "signature": "hash_signature_from_edupay"
}
```

### Get User's Payment History
```
GET /payments/my-payments
```

### Check Payment Status
```
GET /payments/{payment_id}/status
```

## Struktur Database

### Cart Table
- `user_id`: ID user
- `merchant_id`: ID merchant
- `product_id`: ID product (lapangan)
- `start`: jam mulai booking
- `quantity`: jumlah jam booking
- `price_per_hour`: harga per jam
- `total_price`: total harga (price_per_hour × quantity)

### Transaction Table
- `user_id`: ID user
- `merchant_id`: ID merchant
- `start`: jam mulai booking
- `total_price`: total harga semua jam
- `status`: pending, confirmed, cancelled, completed
- `payment_method`: cash, transfer, ewallet

### TransactionDetail Table
- `transaction_id`: ID transaction
- `product_id`: ID product (lapangan)
- `hour`: jam ke berapa (start + index)
- `price_per_hour`: harga per jam

### Payment Table
- `transaction_id`: ID transaction
- `merchant_id`: ID merchant
- `user_id`: ID user
- `code`: kode pembayaran dari EduPay (unique)
- `total`: total pembayaran
- `status`: pending, success, failed, expired
- `paid_at`: waktu pembayaran berhasil

## Flow Booking

1. **User pilih lapangan** → lihat available slots
2. **Add to cart** → pilih jam mulai dan durasi
3. **Cart management** → bisa update/remove item
4. **Checkout** → dari cart ke transaction
5. **System otomatis buat**:
   - 1 transaction per merchant
   - N transaction details (sesuai quantity)
   - Setiap detail = 1 jam booking

## Contoh Response

### Add to Cart Success:
```json
{
    "message": "Item berhasil ditambahkan ke cart",
    "data": {
        "id": 1,
        "user_id": 1,
        "merchant_id": 1,
        "product_id": 1,
        "start": 14,
        "quantity": 2,
        "price_per_hour": 150000,
        "total_price": 300000
    },
    "cart_info": {
        "field_name": "Lapangan A",
        "start_time": "14:00",
        "end_time": "16:00",
        "duration": "2 jam",
        "total_price": "Rp 300,000"
    }
}
```

### Get Cart:
```json
{
    "cart_items": [
        {
            "id": 1,
            "field_name": "Lapangan A",
            "merchant_name": "Futsal Center Jakarta",
            "start_time": "14:00",
            "end_time": "16:00",
            "duration": "2 jam",
            "price_per_hour": "Rp 150,000",
            "total_price": "Rp 300,000"
        }
    ],
    "total_price": "Rp 300,000",
    "item_count": 1
}
```

### Create Booking Success:
```json
{
    "message": "Booking berhasil dibuat",
    "transactions": [
        {
            "id": 1,
            "user_id": 1,
            "merchant_id": 1,
            "start": 14,
            "total_price": 300000,
            "status": "pending",
            "payment_method": "transfer",
            "merchant": {
                "id": 1,
                "name": "Futsal Center Jakarta"
            },
            "payment": {
                "id": 1,
                "transaction_id": 1,
                "merchant_id": 1,
                "user_id": 1,
                "code": "EDUPAY-ABC12345",
                "total": 300000,
                "status": "pending",
                "paid_at": null
            },
            "transaction_details": [
                {
                    "id": 1,
                    "transaction_id": 1,
                    "product_id": 1,
                    "hour": 14,
                    "price_per_hour": 150000,
                    "product": {
                        "id": 1,
                        "name": "Lapangan A"
                    }
                },
                {
                    "id": 2,
                    "transaction_id": 1,
                    "product_id": 1,
                    "hour": 15,
                    "price_per_hour": 150000,
                    "product": {
                        "id": 1,
                        "name": "Lapangan A"
                    }
                }
            ]
        }
    ],
    "total_transactions": 1,
    "payment_info": {
        "total_payments": 1,
        "total_amount": "Rp 300,000"
    }
}
```

### Get User Bookings:
```json
{
    "bookings": [
        {
            "id": 1,
            "merchant_name": "Futsal Center Jakarta",
            "start_time": "14:00",
            "end_time": "16:00",
            "duration": "2 jam",
            "total_price": "Rp 300,000",
            "status": "pending",
            "payment_method": "transfer",
            "booking_date": "15/01/2025 10:30",
            "payment": {
                "code": "EDUPAY-ABC12345",
                "status": "pending",
                "total": "Rp 300,000",
                "paid_at": null
            },
            "fields": [
                {
                    "field_name": "Lapangan A",
                    "hours": ["14:00", "15:00"]
                }
            ]
        }
    ]
}
```

### Create Payment Success:
```json
{
    "message": "Payment berhasil dibuat",
    "data": {
        "id": 1,
        "transaction_id": 1,
        "merchant_id": 1,
        "user_id": 1,
        "code": "EDUPAY-ABC12345",
        "total": 300000,
        "status": "pending",
        "paid_at": null
    },
    "payment_info": {
        "code": "EDUPAY-ABC12345",
        "total": "Rp 300,000",
        "status": "pending",
        "edupay_url": "https://edupay.example.com/pay?code=EDUPAY-ABC12345&amount=300000"
    }
}
```

### Payment Status Callback:
```json
{
    "message": "Payment status berhasil diperbarui",
    "payment": {
        "code": "EDUPAY-ABC12345",
        "status": "success",
        "paid_at": "15/01/2025 11:30"
    }
}
```

### Get User Payments:
```json
{
    "payments": [
        {
            "id": 1,
            "code": "EDUPAY-ABC12345",
            "total": "Rp 300,000",
            "status": "success",
            "merchant_name": "Futsal Center Jakarta",
            "transaction_info": {
                "start_time": "14:00",
                "duration": "2 jam"
            },
            "created_at": "15/01/2025 10:30",
            "paid_at": "15/01/2025 11:30"
        }
    ]
}
```

## Validasi yang Diterapkan

### Merchant Validasi:
- `user_id`: required, exists di users table
- `name`: required, string, max 255
- `address`: required, string, max 500
- `phone`: required, string, max 20
- `status`: required, in: active, inactive, pending
- `open`: required, integer, min 0, max 24
- `close`: required, integer, min 1, max 24, business_hours (custom rule)
- `banner`: nullable, string, max 255

### Product Validasi:
- `merchant_id`: required, exists di merchants table
- `name`: required, string, max 255
- `price`: required, integer, min 0

### Cart Validasi:
- `user_id`: required, exists di users table
- `merchant_id`: required, exists di merchants table
- `product_id`: required, exists di products table
- `start`: required, integer, min 0, max 23, no_booking_conflict
- `quantity`: required, integer, min 1, max 12
- `price_per_hour`: required, integer, min 0
- `total_price`: required, integer, min 0

### Transaction Validasi:
- `user_id`: required, exists di users table
- `merchant_id`: required, exists di merchants table
- `start`: required, integer, min 0, max 23
- `total_price`: required, integer, min 0
- `status`: required, in: pending, confirmed, cancelled, completed
- `payment_method`: required, in: cash, transfer, ewallet

### TransactionDetail Validasi:
- `transaction_id`: required, exists di transactions table
- `product_id`: required, exists di products table
- `hour`: required, integer, min 0, max 23
- `price_per_hour`: required, integer, min 0

### Payment Validasi:
- `transaction_id`: required, exists di transactions table
- `merchant_id`: required, exists di merchants table
- `user_id`: required, exists di users table
- `code`: required, string, unique
- `total`: required, integer, min 0
- `status`: required, in: pending, success, failed, expired
- `paid_at`: nullable, date

## Custom Validation Rules

### 1. Business Hours Rule
Memastikan jam tutup lebih besar dari jam buka:
```php
'close' => 'required|integer|min:1|max:24|business_hours'
```

### 2. No Booking Conflict Rule
Memastikan tidak ada konflik booking untuk waktu yang sama:
```php
'start' => 'required|integer|min:0|max:23|no_booking_conflict'
```

## Testing di Postman

1. **Setup Collection:**
   - Buat collection baru "Sportlodek API"
   - Set base URL: `http://localhost:8000/api`

2. **Authentication Flow:**
   - **Register** → POST `/api/register` (jika belum punya akun)
   - **Login** → POST `/api/login` → dapat token
   - **Set Authorization** → Bearer Token di header semua request

3. **Test Flow Lengkap:**
   - **Register/Login** → dapat token
   - **Create Merchant** → POST `/api/merchants`
   - **Create Product** → POST `/api/products`
   - **Get Available Slots** → GET `/api/transactions/available-slots`
   - **Add to Cart** → POST `/api/cart`
   - **Get Cart** → GET `/api/cart`
   - **Create Booking** → POST `/api/transactions` (otomatis buat payment)
   - **Get User Bookings** → GET `/api/transactions/my-bookings`
   - **Get User Payments** → GET `/api/payments/my-payments`

4. **Error Handling:**
   - Test validasi business hours
   - Test booking conflict
   - Test required fields
   - Test cart empty saat checkout
   - Test invalid token
   - Test expired token 
