# Data Seeder - Sportlodek Booking System

## 📊 Data yang Telah Dibuat

### 👥 Users (6 users)
**Password untuk semua user: `password123`**

#### Merchant Users (3):
1. **Ahmad Futsal Center**
   - Email: `ahmad@futsal.com`
   - Password: `password123`
   - Role: Merchant

2. **Budi Sport Center**
   - Email: `budi@sport.com`
   - Password: `password123`
   - Role: Merchant

3. **Citra Futsal Arena**
   - Email: `citra@arena.com`
   - Password: `password123`
   - Role: Merchant

#### Regular Users (3):
4. **John Doe**
   - Email: `john@example.com`
   - Password: `password123`
   - Role: Customer

5. **Jane Smith**
   - Email: `jane@example.com`
   - Password: `password123`
   - Role: Customer

6. **Mike Johnson**
   - Email: `mike@example.com`
   - Password: `password123`
   - Role: Customer

### 🏢 Merchants (3 merchants)

#### 1. Ahmad Futsal Center
- **ID**: 1
- **User ID**: 1
- **Name**: Ahmad Futsal Center
- **Address**: Jl. Sudirman No. 123, Jakarta Pusat
- **Phone**: 021-1234567
- **Status**: active
- **Jam Operasional**: 08:00 - 22:00 (14 jam)
- **Banner**: ahmad-banner.jpg

#### 2. Budi Sport Center
- **ID**: 2
- **User ID**: 2
- **Name**: Budi Sport Center
- **Address**: Jl. Thamrin No. 456, Jakarta Pusat
- **Phone**: 021-7654321
- **Status**: active
- **Jam Operasional**: 09:00 - 23:00 (14 jam)
- **Banner**: budi-banner.jpg

#### 3. Citra Futsal Arena
- **ID**: 3
- **User ID**: 3
- **Name**: Citra Futsal Arena
- **Address**: Jl. Gatot Subroto No. 789, Jakarta Selatan
- **Phone**: 021-9876543
- **Status**: active
- **Jam Operasional**: 07:00 - 21:00 (14 jam)
- **Banner**: citra-banner.jpg

### ⚽ Products/Lapangan (9 products)

#### Ahmad Futsal Center (Merchant ID: 1)
1. **Lapangan A - Indoor**
   - ID: 1
   - Price: Rp 150.000/jam
   - Type: Indoor

2. **Lapangan B - Indoor**
   - ID: 2
   - Price: Rp 150.000/jam
   - Type: Indoor

3. **Lapangan C - Indoor**
   - ID: 3
   - Price: Rp 150.000/jam
   - Type: Indoor

#### Budi Sport Center (Merchant ID: 2)
4. **Lapangan 1 - Premium**
   - ID: 4
   - Price: Rp 200.000/jam
   - Type: Premium

5. **Lapangan 2 - Premium**
   - ID: 5
   - Price: Rp 200.000/jam
   - Type: Premium

6. **Lapangan 3 - Premium**
   - ID: 6
   - Price: Rp 200.000/jam
   - Type: Premium

#### Citra Futsal Arena (Merchant ID: 3)
7. **Lapangan X - Standard**
   - ID: 7
   - Price: Rp 120.000/jam
   - Type: Standard

8. **Lapangan Y - Standard**
   - ID: 8
   - Price: Rp 120.000/jam
   - Type: Standard

9. **Lapangan Z - Standard**
   - ID: 9
   - Price: Rp 120.000/jam
   - Type: Standard

## 🧪 Testing dengan Postman

### Login Credentials untuk Testing:

#### Merchant Login:
```json
{
    "email": "ahmad@futsal.com",
    "password": "password123"
}
```

#### Customer Login:
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

### Sample API Calls:

#### 1. Get Products by Merchant
```
GET /api/products/merchant/1
```
**Response**: Semua lapangan Ahmad Futsal Center

#### 2. Get Available Time Slots
```
GET /api/transactions/available-slots?product_id=1&date=2025-01-15
```
**Response**: Available slots untuk Lapangan A

#### 3. Add to Cart
```json
POST /api/cart
{
    "merchant_id": 1,
    "product_id": 1,
    "start": 14,
    "quantity": 2
}
```

#### 4. Create Booking
```json
POST /api/transactions
{
    "payment_method": "transfer"
}
```

## 📈 Data Statistics

- **Total Users**: 6 (3 merchant + 3 customer)
- **Total Merchants**: 3
- **Total Products**: 9 (3 per merchant)
- **Price Range**: Rp 120.000 - Rp 200.000 per jam
- **Operating Hours**: 7:00 - 23:00 (berbeda per merchant)

## 🎯 Testing Scenarios

### Scenario 1: Customer Booking
1. Login sebagai `john@example.com`
2. Lihat available slots untuk lapangan tertentu
3. Add to cart
4. Checkout dan buat booking
5. Lihat payment status

### Scenario 2: Merchant Management
1. Login sebagai `ahmad@futsal.com`
2. Update merchant info
3. Update product prices
4. Lihat booking history

### Scenario 3: Different Price Points
- **Standard**: Rp 120.000 (Citra Futsal Arena)
- **Indoor**: Rp 150.000 (Ahmad Futsal Center)
- **Premium**: Rp 200.000 (Budi Sport Center)

## 🔄 Reset Data
Jika ingin reset data:
```bash
php artisan migrate:fresh --seed
```

## 📝 Notes
- Semua password: `password123`
- Jam operasional berbeda-beda untuk testing
- Harga bervariasi untuk testing berbagai skenario
- Data sudah siap untuk testing di Postman 
