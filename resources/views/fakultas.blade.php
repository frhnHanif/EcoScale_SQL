{{-- resources/views/fakultas.blade.php --}}

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoScale - Fakultas</title>

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

        .filter-btn {
            @apply px-4 py-2 text-sm font-medium transition-colors duration-200;
        }

        .filter-btn-active {
            @apply bg-teal-600 text-white;
        }
        .filter-btn-active:hover {
            @apply bg-teal-700;
        }

        .filter-btn:not(.filter-btn-active) {
            @apply bg-white text-gray-900;
        }
        .filter-btn:not(.filter-btn-active):hover {
            @apply bg-gray-100 text-teal-700;
        }
    </style>
</head>

<body style="background-color: #F2FCF8;" class="text-gray-800">

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">

        <x-header></x-header>

        {{-- Global Summary Cards --}}
        <x-cards-stats></x-cards-stats>

        <x-navbar></x-navbar>

        {{-- Card title --}}
        <div class="lg:col-span-3 bg-white p-6 rounded-xl shadow-md ">
            <h3 class="font-semibold text-2xl" style="color: #447F40;">Performa Fakultas</h3>
            <p class="text-sm mb-4 text-gray-500">Monitoring produksi sampah per-fakultas</p>

            <h4 class="font-bold text-lg text-gray-800 mt-8 mb-4">Pengurangan Sampah Tertinggi (%)</h4>
            <div class="flex justify-center mb-6">
                <div class="inline-flex rounded-md shadow-sm" role="group">
                    <button type="button" id="reduction-btn-today" class="filter-btn border border-gray-200 rounded-l-lg">Hari Ini</button>
                    <button type="button" id="reduction-btn-weekly" class="filter-btn border-y border-r border-gray-200 -ml-px">Mingguan</button>
                    <button type="button" id="reduction-btn-monthly" class="filter-btn border border-gray-200 rounded-r-lg -ml-px">Bulanan</button>
                </div>
            </div>
            <div id="reduction-leaderboard-container" class="space-y-4">
                <p id="loading-reduction" class="text-center col-span-full text-gray-500">Memuat data...</p>
            </div>

            <h4 class="font-bold text-lg text-gray-800 mt-8 mb-4">Pencapaian Target Produksi Sampah</h4>
            <div class="flex justify-center mb-6">
                <div class="inline-flex rounded-md shadow-sm" role="group">
                    <button type="button" id="target-btn-today" class="filter-btn border border-gray-200 rounded-l-lg">Hari Ini</button>
                    <button type="button" id="target-btn-weekly" class="filter-btn border-y border-r border-gray-200 -ml-px">Mingguan</button>
                    <button type="button" id="target-btn-monthly" class="filter-btn border border-gray-200 rounded-r-lg -ml-px">Bulanan</button>
                </div>
            </div>
            <div id="target-leaderboard-container" class="space-y-4">
                <p id="loading-target" class="text-center col-span-full text-gray-500">Memuat data...</p>
            </div>
            
        </div>
    </div>

    <script type="module">
        import { initFakultasPage } from "{{ asset('js/fakultas.js') }}";

        // Panggil fungsi init baru (tanpa config)
        document.addEventListener('DOMContentLoaded', initFakultasPage);
    </script>

</body>
</html>