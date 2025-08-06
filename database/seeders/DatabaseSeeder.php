<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Users
        $this->createUsers();

        // Create Merchants
        $this->createMerchants();

        // Create Products
        $this->createProducts();
    }

    /**
     * Create users (3 merchant + 3 regular users)
     */
    private function createUsers(): void
    {
        // Merchant Users
        User::create([
            'name' => 'Ahmad Futsal Center',
            'email' => 'ahmad@futsal.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Budi Sport Center',
            'email' => 'budi@sport.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Citra Futsal Arena',
            'email' => 'citra@arena.com',
            'password' => Hash::make('password123'),
        ]);

        // Regular Users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Mike Johnson',
            'email' => 'mike@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->command->info('✅ 6 users created successfully!');
    }

    /**
     * Create merchants
     */
    private function createMerchants(): void
    {
        // Merchant 1: Ahmad Futsal Center
        Merchant::create([
            'user_id' => 1,
            'name' => 'Ahmad Futsal Center',
            'address' => 'Jl. Imam Bonjol No. 123, Padang',
            'phone' => '021-1234567',
            'status' => 'active',
            'open' => 8, // 08:00
            'close' => 22, // 22:00
            'banner' => 'ahmad-banner.jpg',
        ]);

        // Merchant 2: Budi Sport Center
        Merchant::create([
            'user_id' => 2,
            'name' => 'Budi Sport Center',
            'address' => 'Jl. Sudirman No. 456, Padang',
            'phone' => '021-7654321',
            'status' => 'active',
            'open' => 9, // 09:00
            'close' => 23, // 23:00
            'banner' => 'budi-banner.jpg',
        ]);

        // Merchant 3: Citra Futsal Arena
        Merchant::create([
            'user_id' => 3,
            'name' => 'Citra Futsal Arena',
            'address' => 'Jl. Khatib Sulaiman No. 789, Padang',
            'phone' => '021-9876543',
            'status' => 'active',
            'open' => 7, // 07:00
            'close' => 21, // 21:00
            'banner' => 'citra-banner.jpg',
        ]);

        $this->command->info('✅ 3 merchants created successfully!');
    }

    /**
     * Create products (lapangan) for each merchant
     */
    private function createProducts(): void
    {
        // Products for Ahmad Futsal Center (Merchant ID: 1)
        Product::create([
            'merchant_id' => 1,
            'name' => 'Lapangan A - Indoor',
            'price' => 150000, // Rp 150.000 per jam
        ]);

        Product::create([
            'merchant_id' => 1,
            'name' => 'Lapangan B - Indoor',
            'price' => 150000, // Rp 150.000 per jam
        ]);

        Product::create([
            'merchant_id' => 1,
            'name' => 'Lapangan C - Indoor',
            'price' => 150000, // Rp 150.000 per jam
        ]);

        // Products for Budi Sport Center (Merchant ID: 2)
        Product::create([
            'merchant_id' => 2,
            'name' => 'Lapangan 1 - Premium',
            'price' => 200000, // Rp 200.000 per jam
        ]);

        Product::create([
            'merchant_id' => 2,
            'name' => 'Lapangan 2 - Premium',
            'price' => 200000, // Rp 200.000 per jam
        ]);

        Product::create([
            'merchant_id' => 2,
            'name' => 'Lapangan 3 - Premium',
            'price' => 200000, // Rp 200.000 per jam
        ]);

        // Products for Citra Futsal Arena (Merchant ID: 3)
        Product::create([
            'merchant_id' => 3,
            'name' => 'Lapangan X - Standard',
            'price' => 120000, // Rp 120.000 per jam
        ]);

        Product::create([
            'merchant_id' => 3,
            'name' => 'Lapangan Y - Standard',
            'price' => 120000, // Rp 120.000 per jam
        ]);

        Product::create([
            'merchant_id' => 3,
            'name' => 'Lapangan Z - Standard',
            'price' => 120000, // Rp 120.000 per jam
        ]);

        $this->command->info('✅ 9 products created successfully!');
    }
}
