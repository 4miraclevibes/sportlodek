<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Lapangan - Sportlodek</title>
    <meta name="description" content="Kelola lapangan futsal merchant">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#10B981">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Sportlodek Merchant">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    <link rel="apple-touch-icon" href="/icons/icon.svg">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .mobile-container {
            max-width: 480px;
            margin: 0 auto;
            background: #f8fafc;
            min-height: 100vh;
        }
        .status-bar {
            height: 24px;
            background: #1f2937;
            color: white;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
        }
        .product-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 480px;
            background: white;
            border-top: 1px solid #e5e7eb;
            z-index: 50;
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 4px;
            font-size: 12px;
            color: #6b7280;
        }
        .nav-item.active {
            color: #10B981;
        }
        .nav-item i {
            font-size: 20px;
            margin-bottom: 4px;
        }
        .custom-alert {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .custom-alert.show {
            display: flex;
        }
        .alert-content {
            background: white;
            border-radius: 16px;
            max-width: 90%;
            width: 320px;
            margin: 20px;
            overflow: hidden;
            animation: alertSlideIn 0.3s ease-out;
        }
        @keyframes alertSlideIn {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        .alert-header {
            padding: 20px 20px 0 20px;
            text-align: center;
        }
        .alert-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .alert-icon.success {
            background: #10B981;
            color: white;
        }
        .alert-icon.error {
            background: #EF4444;
            color: white;
        }
        .alert-icon.warning {
            background: #F59E0B;
            color: white;
        }
        .alert-title {
            font-size: 18px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 8px;
        }
        .alert-message {
            font-size: 14px;
            color: #6B7280;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .alert-buttons {
            display: flex;
            border-top: 1px solid #E5E7EB;
        }
        .alert-button {
            flex: 1;
            padding: 16px;
            border: none;
            background: none;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .alert-button:first-child {
            border-right: 1px solid #E5E7EB;
        }
        .alert-button.primary {
            color: #10B981;
        }
        .alert-button.secondary {
            color: #6B7280;
        }
        .alert-button:hover {
            background: #F9FAFB;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="mobile-container">
        @include('layouts.merchant.navbar', ['headerTitle' => 'Kelola Lapangan'])

        <!-- Content -->
        <div class="px-4 py-6 pb-20">
            <!-- Header Section -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Lapangan Saya</h2>
                    <p class="text-sm text-gray-500">Kelola lapangan futsal Anda</p>
                </div>
                <button onclick="showAddProductModal()"
                        class="bg-green-500 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah
                </button>
            </div>

            <!-- Products List -->
            @if($products->count() > 0)
                <div class="space-y-4">
                    @foreach($products as $product)
                    <div class="product-card">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-futbol text-green-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $product->name }}</h3>
                                    <p class="text-sm text-gray-500">Lapangan {{ $loop->iteration }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-500">per jam</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                            <div class="flex space-x-2">
                                <button onclick="editProduct({{ $product->id }})"
                                        class="text-blue-600 text-sm font-medium">
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit
                                </button>
                                <button onclick="deleteProduct({{ $product->id }})"
                                        class="text-red-600 text-sm font-medium">
                                    <i class="fas fa-trash mr-1"></i>
                                    Hapus
                                </button>
                            </div>
                            <div class="flex items-center">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                <span class="text-xs text-gray-500">Aktif</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-futbol text-4xl text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Lapangan</h3>
                    <p class="text-gray-500 mb-6">Mulai dengan menambahkan lapangan pertama Anda</p>
                    <button onclick="showAddProductModal()"
                            class="bg-green-500 text-white px-6 py-3 rounded-lg font-medium">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Lapangan Pertama
                    </button>
                </div>
            @endif
        </div>

        @include('layouts.merchant.footer')
    </div>

    <script>
        function showAddProductModal() {
            showCustomAlert({
                title: 'Tambah Lapangan',
                message: 'Fitur tambah lapangan akan segera hadir!',
                type: 'info'
            });
        }

        function editProduct(productId) {
            showCustomAlert({
                title: 'Edit Lapangan',
                message: 'Fitur edit lapangan akan segera hadir!',
                type: 'info'
            });
        }

        function deleteProduct(productId) {
            showCustomAlert({
                title: 'Hapus Lapangan',
                message: 'Apakah Anda yakin ingin menghapus lapangan ini?',
                type: 'warning',
                confirmText: 'Ya, Hapus',
                cancelText: 'Batal',
                onConfirm: () => {
                    showCustomAlert({
                        title: 'Berhasil',
                        message: 'Lapangan berhasil dihapus',
                        type: 'success'
                    });
                }
            });
        }
    </script>
</body>
</html>
