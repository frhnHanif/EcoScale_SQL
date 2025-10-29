{{-- resources/views/laporan.blade.php --}}

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoScale - Laporan</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Google Fonts (Inter) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F2FCF8;
        }

        /* Styles for date/select inputs */
        input[type="date"],
        select {
            @apply p-2 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500;
        }

                .sortable-header {
            cursor: pointer;
            position: relative;
            user-select: none; /* Mencegah teks ter-highlight saat diklik */
        }
        .sortable-header:hover {
            background-color: #f0f4f8;
        }
        .sort-icon {
            font-size: 1.2em;
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; /* gray-400 */
        }
        .sort-icon.active {
            color: #1e293b; /* gray-800 */
        }



        /* Style for achievement icons - Confirmed w-4 h-4 (16px) */
    </style>
</head>

<body class="text-gray-800">

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">

        <x-header></x-header>

        <x-cards-stats></x-cards-stats>

        <x-navbar></x-navbar>


        <div class="lg:col-span-3 bg-white p-6 rounded-xl shadow-md mb-8">
            <h3 class="font-semibold text-2xl" style="color: #447F40;">Laporan Dampak Lingkungan</h3>
            <p class="text-sm mb-4 text-gray-500">Kontribusi EcoScale terhadap keberlanjutan kampus</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                {{-- Card 1: CO2 Dikurangi --}}
                <div class="bg-blue-50 p-4 rounded-lg flex flex-col items-center justify-center text-center">
                    {{-- Icon for CO2 Reduction (Cloud Arrow Down) --}}
                    <svg class="w-10 h-10 text-blue-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.5" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    {{-- Value --}}
                    <p class="text-3xl font-bold text-blue-600"><span id="co2-reduction">0</span> kg</p>
                    {{-- Description Text (now below value) --}}
                    <p class="text-xs font-medium text-gray-800 mt-1">CO2 berhasil Dikurangi</p>
                </div>

                {{-- Card 2: Penurunan Sampah Bulan Ini --}}
                <div class="bg-green-50 p-4 rounded-lg flex flex-col items-center justify-center text-center">
                    {{-- Icon for Reduction (Arrow Down) --}}
                    <svg class="w-10 h-10 text-green-400 mb-2" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 10.414V14a1 1 0 102 0v-3.586l1.293 1.293a1 1 0 001.414-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                    {{-- Value --}}
                    <p class="text-3xl font-bold text-green-600"><span id="monthly-reduction">0</span>kg</p>
                    {{-- Description Text (now below value) --}}
                    <p class="text-xs font-medium text-gray-800 mt-1">Penurunan Sampah Bulan Ini</p>
                </div>

                {{-- Card 3: Total Sampah Bulan Ini --}}
                <div class="bg-purple-50 p-4 rounded-lg flex flex-col items-center justify-center text-center">
                    {{-- Icon for Total Waste (Trash Can) --}}
                    <svg class="w-10 h-10 text-purple-400 mb-2" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    {{-- Value --}}
                    <p class="text-3xl font-bold text-purple-600"><span id="monthly-total">0</span> kg</p>
                    {{-- Description Text (now below value) --}}
                    <p class="text-xs font-medium text-gray-800 mt-1">Total Sampah Bulan Ini</p>
                </div>
            </div>

        </div>


        <div class="bg-white p-6 rounded-xl shadow-md mb-8">
            <h3 class="font-semibold text-2xl" style="color: #447F40;">Pencapaian Bulan Ini</h3>
            <p class="text-sm mb-4 text-gray-500">Melihat kilas balik pencapaian pada Bulan ini</p>
            <ul id="achievements-list" class="mt-4 space-y-3">
                <li class="flex items-center text-gray-700">
                    <svg class="achievement-icon text-gray-400 w-4 h-4 mr-3 flex-shrink-0" fill="none"
                        viewBox="0 0 24 24 " stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Memuat pencapaian...</span>
                </li>
            </ul>
        </div>


        <div class="bg-white p-6 rounded-xl shadow-md mb-8">
            <h3 class="font-semibold text-2xl" style="color: #447F40;">Filter Laporan</h3>
            <p class="text-sm mb-4 text-gray-500">Memfilter laporan sesuai fakultas</p>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4 items-end">
                <div>
                    <label for="start-date" class="block text-sm font-medium text-gray-700">Tanggal Mulai:</label>
                    <input type="date" id="start-date"
                        class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500"
                        value="{{ date('Y-m-01') }}">
                </div>
                <div>
                    <label for="end-date" class="block text-sm font-medium text-gray-700">Tanggal Akhir:</label>
                    <input type="date" id="end-date"
                        class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label for="faculty-filter" class="block text-sm font-medium text-gray-700">Fakultas:</label>
                    <select id="faculty-filter"
                        class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500">
                        <option value="">Semua Fakultas</option>
                        <option value="FT">Teknik</option>
                        <option value="FK">Kedokteran</option>
                        <option value="FEB">Ekonomika dan Bisnis</option>
                        <option value="FH">Hukum</option>
                        <option value="FIB">Ilmu Budaya</option>
                        <option value="FPP">Peternakan dan Pertanian</option>
                        <option value="FPIK">Perikanan dan Ilmu Kelautan</option>
                        <option value="FKM">Kesehatan Masyarakat</option>
                        <option value="FSM">Sains dan Matematika</option>
                        <option value="SV">Vokasi</option>
                    </select>
                </div>
                <div class="col-span-1 md:col-span-3 lg:col-span-1 flex justify-end">
                    <button id="generate-report-btn"
                        class="px-6 py-2 bg-teal-600 text-white font-semibold rounded-md shadow-sm hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                        Buat Laporan
                    </button>
                </div>
            </div>
        </div>

        {{-- === Hasil Laporan === --}}
        <div class="bg-white p-6 rounded-xl shadow-md mb-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
                <div>
                    <h3 class="font-semibold text-2xl" style="color: #447F40;">Hasil Laporan</h3>
                    <p class="text-sm mb-2 text-gray-500">Generate laporan EcoScale</p>
                </div>
        </div>

            <div id="report-results" class="overflow-x-auto">
                <p id="loading-report" class="text-center text-gray-500 py-4">Memuat data laporan...</p>
                <p id="no-data-report" class="text-center text-gray-500 py-4 hidden">Tidak ada data laporan untuk periode ini.</p>

        <table class="min-w-full divide-y divide-gray-200 hidden text-s text-center">
            <thead class="bg-gray-50">
                <tr>
                    {{-- 🔧 DIUBAH: Header tabel sekarang bisa diklik untuk sorting --}}
                    <th data-sort-key="timestamp" class="sortable-header px-4 py-2 font-semibold text-gray-600 uppercase tracking-wider">
                        Tanggal <span class="sort-icon"></span>
                    </th>
                    <th data-sort-key="Hari" class="sortable-header px-4 py-2 font-semibold text-gray-600 uppercase tracking-wider">
                        Hari <span class="sort-icon"></span>
                    </th>
                    <th data-sort-key="Waktu" class="sortable-header px-4 py-2 font-semibold text-gray-600 uppercase tracking-wider">
                        Waktu <span class="sort-icon"></span>
                    </th>
                    <th data-sort-key="Fakultas" class="sortable-header px-4 py-2 font-semibold text-gray-600 uppercase tracking-wider">
                        Fakultas <span class="sort-icon"></span>
                    </th>
                    <th data-sort-key="Jenis Sampah" class="sortable-header px-4 py-2 font-semibold text-gray-600 uppercase tracking-wider">
                        Jenis Sampah <span class="sort-icon"></span>
                    </th>
                    <th data-sort-key="Berat (kg)" class="sortable-header px-4 py-2 font-semibold text-gray-600 uppercase tracking-wider">
                        Berat (kg) <span class="sort-icon"></span>
                    </th>
                </tr>
            </thead>
            <tbody id="report-table-body" class="bg-white divide-y divide-gray-200"></tbody>
        </table>
            </div>

            <div class="mt-6 flex justify-end">
                <button id="export-report-btn"
                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Export ke Excel
                </button>
            </div>
        </div>
    </div>

    <script type="module">
        import {
            initLaporanPage
        } from "{{ asset('js/laporan.js') }}";

        // Panggil fungsi init baru (tanpa config)
        document.addEventListener('DOMContentLoaded', initLaporanPage);
    </script>
</body>

</html>
