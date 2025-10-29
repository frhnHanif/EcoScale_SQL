{{-- resources/views/analitik.blade.php --}}

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoScale - Analitik Mendalam</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Aset & Library CSS/JS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F2FCF8;
        }
    </style>
</head>

<body class="text-gray-800">

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">

        {{-- ==================================================================== --}}
        {{-- --- BAGIAN 1: KOMPONEN HEADER & NAVIGASI --- --}}
        {{-- ==================================================================== --}}
        <x-header></x-header>
        <x-cards-stats></x-cards-stats>
        <x-navbar></x-navbar>
        
        {{-- Judul Halaman Analitik --}}
        <div class="mb-6">
            <h2 class="font-bold text-3xl" style="color: #447F40;">Analisis Mendalam</h2>
            <p class="text-gray-500">Insight dari data tren dan komparasi EcoScale.</p>
        </div>


        {{-- ==================================================================== --}}
        {{-- --- BAGIAN 2: KARTU STATISTIK & TARGET BULANAN --- --}}
        {{-- ==================================================================== --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            {{-- Kartu Potensi Ekonomi --}}
            <div class="bg-white p-6 rounded-xl shadow-md flex flex-col justify-between">
                <div>
                    <h4 class="font-semibold text-gray-500">Potensi Ekonomi Daur Ulang</h4>
                    <p class="text-3xl font-bold text-green-600" id="potensi-ekonomi-value">Rp 0</p>
                </div>
                <p class="text-xs text-gray-400 mt-2">Estimasi dari sampah anorganik (Bulan Ini).</p>
            </div>

            {{-- Kartu Emisi Karbon --}}
            <div class="bg-white p-6 rounded-xl shadow-md flex flex-col justify-between">
                <div>
                    <h4 class="font-semibold text-gray-500">Pengurangan Emisi Karbon</h4>
                    <p class="text-3xl font-bold text-blue-500" id="emisi-karbon-value">0 kg CO₂e</p>
                </div>
                <p class="text-xs text-gray-400 mt-2">Estimasi emisi yang dihindari (Bulan Ini).</p>
            </div>

            {{-- Kartu Progress Target Bulanan --}}
            <div class="bg-white p-6 rounded-xl shadow-md lg:col-span-2">
               <h4 class="font-semibold text-gray-500 mb-2">Progres Target Pengurangan Sampah Bulan Ini</h4>
               <div class="w-full bg-gray-200 rounded-full h-6">
                   <div id="progres-bar-fill" class="bg-yellow-400 h-6 text-xs font-medium text-blue-800 text-center p-1 leading-none rounded-full" style="width: 0%">
                       <span id="progres-bar-text">0%</span>
                   </div>
               </div>
               <p class="text-center text-sm text-gray-500 mt-2" id="progres-bar-label">0 kg / 500 kg</p>
            </div>
        </div>


        {{-- ==================================================================== --}}
        {{-- --- BAGIAN 3: GRID UTAMA UNTUK SEMUA GRAFIK --- --}}
        {{-- ==================================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Grafik Analisis Harian Fakultas --}}
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md">
                <h3 class="font-semibold text-xl mb-1">Analisis Harian Fakultas</h3>
                <p class="text-sm mb-4 text-gray-500">Perbandingan total sampah hari ini dan persentase pengurangan.</p>
                <div class="h-96">
                    <canvas id="analitikBarChart"></canvas>
                </div>
            </div>
            
            {{-- Grafik Tren Volume Sampah 7 Hari --}}
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md">
                <h3 class="font-semibold text-xl mb-4">Tren Volume Sampah (7 Hari Terakhir)</h3>
                <div class="h-80">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            {{-- Grafik Distribusi Jenis Sampah (Placeholder Lama) --}}
            {{-- Kode ini bisa Anda hapus/ganti jika sudah memakai 2 donat baru --}}
            {{-- <div class="bg-white p-6 rounded-xl shadow-md">
                <h3 class="font-semibold text-xl mb-4">Distribusi Jenis Sampah</h3>
                <div class="h-80 flex justify-center items-center">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div> --}}

            {{-- Grafik Distribusi Ganda (Mingguan & Bulanan) --}}
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md">
                <h3 class="font-semibold text-xl mb-4">Distribusi Jenis Sampah (kg)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 h-80">
                    <div class="flex flex-col items-center">
                        <h4 class="font-medium text-gray-600 mb-2">Minggu Ini Berjalan</h4>
                        <div class="relative w-full h-full">
                            <canvas id="distributionChartWeekly"></canvas>
                        </div>
                    </div>
                    <div class="flex flex-col items-center">
                        <h4 class="font-medium text-gray-600 mb-2">Bulan Ini Berjalan</h4>
                        <div class="relative w-full h-full">
                            <canvas id="distributionChartMonthly"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grafik Komparasi Komposisi per Fakultas --}}
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md">
                <h3 class="font-semibold text-xl mb-4">Komparasi Komposisi Sampah per Fakultas</h3>
                <div class="h-96">
                    <canvas id="facultyStackedChart"></canvas>
                </div>
            </div>
            
            {{-- Grafik Pola Waktu Pembuangan --}}
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md">
                <h3 class="font-semibold text-xl mb-4">Pola Waktu Pembuangan (Jam Sibuk) Selama 30 Hari Terakhir</h3>
                <div class="h-96">
                    <canvas id="hourlyPatternChart"></canvas>
                </div>
            </div>

            {{-- Grafik Tren Potensi Ekonomi Bulanan --}}
            <div class="bg-white p-6 rounded-xl shadow-md">
                <h3 class="font-semibold text-xl mb-4">Tren Potensi Ekonomi Bulanan</h3>
                <div class="h-80">
                    <canvas id="monthlyEconomicChart"></canvas>
                </div>
            </div>

            {{-- Grafik Tren Emisi Karbon Bulanan --}}
            <div class="bg-white p-6 rounded-xl shadow-md">
                <h3 class="font-semibold text-xl mb-4">Tren Emisi Karbon Bulanan</h3>
                <div class="h-80">
                    <canvas id="monthlyEmissionChart"></canvas>
                </div>
            </div>

            {{-- Grafik Tren Pengurangan Sampah Bulanan --}}
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md">
                <h3 class="font-semibold text-xl mb-4">Tren Pengurangan Sampah Bulanan</h3>
                <div class="h-96">
                    <canvas id="monthlyReductionChart"></canvas>
                </div>
            </div>
            
        </div>
    </div>

    {{-- ==================================================================== --}}
    {{-- --- BAGIAN 4: PEMUATAN SKRIP JAVASCRIPT --- --}}
    {{-- ==================================================================== --}}
    <script type="module">
        import { initAnalitikPage } from '{{ asset('js/analitik.js') }}';
        
        // Panggil fungsi init baru (tanpa config)
        document.addEventListener('DOMContentLoaded', initAnalitikPage);
    </script>

</body>
</html>