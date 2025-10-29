<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoScale - Dashboard</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Chart Js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Google Fonts (Inter) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body style="background-color: #F2FCF8;" class="text-gray-800">

    {{-- Kontainer Utama --}}
    <div class="container mx-auto p-4 sm:p-6 lg:p-8">

        {{-- Header Component --}}
        <x-header></x-header>

        {{-- Statistik Cards --}}
        <x-cards-stats></x-cards-stats>

        {{-- Navbar Component --}}
        <x-navbar></x-navbar>

        {{-- Konten Grafik --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 mb-8">
            <div class="lg:col-span-3 bg-white p-6 rounded-xl shadow-md">
                <h3 class="font-semibold text-2xl" style="color: #447F40;">Tren Sampah Mingguan</h3>
                <p class="text-sm mb-4 text-gray-500">Perbandingan produksi sampah harian dengan target</p>
                <canvas id="weeklyTrendChart"></canvas>
            </div>
            {{-- Distribution Chart and Summary --}}
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md flex flex-col">
                <div>
                    <h3 class="font-semibold text-2xl" style="color: #447F40;">Distribusi Jenis Sampah</h3>
                    <p class="text-sm mb-4 text-gray-500">Komposisi sampah hari ini</p>
                </div>

                {{-- Pie Chart Canvas --}}
                <div class="flex-grow flex items-center justify-center my-4"
                    style="max-height: 250px; min-height: 200px;">
                    <canvas id="typeDistributionChart"></canvas>
                </div>

                {{-- Summary Section --}}
                <div class="mt-auto">
                    {{-- Total Summary --}}
                    <div class="text-center mb-4 border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-500">Total Sampah Hari Ini</h4>
                        <p class="text-2xl font-bold" style="color: #447F40;"><span id="total-sampah">0</span> kg</p>
                    </div>
                    {{-- Breakdown by Type --}}
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Organik</h4>
                            <p class="text-xl font-bold" style="color: #62B682;"><span id="total-organik">0</span> kg
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Anorganik</h4>
                            <p class="text-xl font-bold" style="color: #5C7AF3;"><span id="total-anorganik">0</span> kg
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Residu</h4>
                            <p class="text-xl font-bold" style="color: #D35748;"><span id="total-residu">0</span> kg</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TODO: Ganti dengan konfigurasi Firebase proyek  --}}
        {{-- Script Js untuk Firebase --}}
        <script type="module">
            import { initDashboardPage } from '{{ asset('js/dashboard.js') }}';
            
            // Panggil fungsi init baru (tanpa config)
            document.addEventListener('DOMContentLoaded', initDashboardPage);
        </script>

</body>

</html>
