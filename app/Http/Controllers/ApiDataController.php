<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\Sampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ApiDataController extends Controller
{
    // --- Endpoint untuk IoT Device ---

    /**
     * Menerima data dari perangkat IoT.
     * Ini adalah pengganti IoT -> Firebase
     */
    public function submitData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fakultas' => 'required|string|exists:fakultas,kode', // Memastikan fakultas ada di tabel
            'jenis' => 'required|string|in:Organik,Anorganik,Residu,Umum',
            'berat' => 'required|numeric|min:0',
            // 'device_id' => 'sometimes|string|max:100', // <-- Dihapus
            // 'timestamp' => 'sometimes|integer', // <-- Dihapus
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            Sampah::create([
                // 'ditimbang_pada' => $request->has('timestamp') // <-- Dihapus
                //     ? Carbon::createFromTimestamp($request->timestamp)
                //     : now(),
                'fakultas_kode' => $request->fakultas,
                'jenis' => $request->jenis,
                'berat_kg' => $request->berat,
                // 'device_id' => $request->device_id, // <-- Dihapus
            ]);

            return response()->json(['success' => true, 'message' => 'Data received']);
        } catch (\Exception $e) {
            // Log error
            return response()->json(['success' => false, 'message' => 'Failed to save data'], 500);
        }
    }


    // --- Endpoint untuk Frontend (Pengganti firebaseService.js) ---

    /**
     * Mengambil 4 kartu statistik global (dipakai di semua halaman)
     */
    public function getGlobalStats(Request $request)
    {
        // 1. Total Sampah Hari Ini
        $totalBeratToday = Sampah::whereDate('created_at', now())
            ->where('jenis', '!=', 'Umum')
            ->sum('berat_kg');

        // 2. Fakultas Aktif Bulan Ini
        $activeFacultiesThisMonth = Sampah::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->distinct('fakultas_kode')
            ->count('fakultas_kode');

        // 3. & 4. Rata-rata Reduksi & Status Lingkungan
        $startBulanIni = now()->startOfMonth();
        $startBulanLalu = now()->subMonth()->startOfMonth();
        $endBulanLalu = $startBulanIni->copy()->subSecond();
        $start2BulanLalu = now()->subMonths(2)->startOfMonth();
        $end2BulanLalu = $startBulanLalu->copy()->subSecond();

        $totalBulanIni = Sampah::whereBetween('created_at', [$startBulanIni, now()])
            ->where('jenis', '!=', 'Umum')->sum('berat_kg');
        $totalBulanLalu = Sampah::whereBetween('created_at', [$startBulanLalu, $endBulanLalu])
            ->where('jenis', '!=', 'Umum')->sum('berat_kg');
        $total2BulanLalu = Sampah::whereBetween('created_at', [$start2BulanLalu, $end2BulanLalu])
            ->where('jenis', '!=', 'Umum')->sum('berat_kg');

        $avgReduction = 0;
        if ($totalBulanLalu > 0) {
            $avgReduction = (($totalBulanLalu - $totalBulanIni) / $totalBulanLalu) * 100;
        }

        $targetReductionFromLastMonth = 0;
        if ($total2BulanLalu > 0) {
            $targetReductionFromLastMonth = (($total2BulanLalu - $totalBulanLalu) / $total2BulanLalu) * 100;
        }

        return response()->json([
            'total_sampah_today' => round($totalBeratToday, 1),
            'active_faculties' => $activeFacultiesThisMonth,
            'avg_reduction' => round($avgReduction, 1),
            'target_reduction_last_month' => round($targetReductionFromLastMonth, 1),
        ]);
    }

    /**
     * Mengambil data untuk Halaman Dashboard
     */
    public function getDashboardData(Request $request)
    {
        // 1. Overview Harian
        $overviewToday = Sampah::whereDate('created_at', now())
            ->where('jenis', '!=', 'Umum')
            ->select('jenis', DB::raw('SUM(berat_kg) as total_berat'))
            ->groupBy('jenis')
            ->get()
            ->pluck('total_berat', 'jenis'); // Hasil: ['Organik' => 10.5, 'Anorganik' => 5.2]

        // 2. Tren Mingguan
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
        $weeklyData = Sampah::where('created_at', '>=', $startOfWeek)
            ->where('jenis', '!=', 'Umum')
            ->select(
                DB::raw('DAYOFWEEK(created_at) as day_index'), // Minggu=1, Senin=2, ... Sabtu=7
                DB::raw('SUM(berat_kg) as total_berat')
            )
            ->groupBy('day_index')
            ->get()
            ->pluck('total_berat', 'day_index');

        // Format data mingguan untuk Chart.js (Senin=0, ..., Minggu=6)
        $weeklyTrend = [0, 0, 0, 0, 0, 0, 0]; // [Sen, Sel, Rab, Kam, Jum, Sab, Min]
        foreach ($weeklyData as $day_index => $total) {
            if ($day_index == 1) $weeklyTrend[6] = $total; // Minggu (index 1) -> 6
            else $weeklyTrend[$day_index - 2] = $total; // Senin (index 2) -> 0
        }

        return response()->json([
            'overview' => [
                'organik' => round($overviewToday->get('Organik', 0), 1),
                'anorganik' => round($overviewToday->get('Anorganik', 0), 1),
                'residu' => round($overviewToday->get('Residu', 0), 1),
            ],
            'weekly_trend' => $weeklyTrend,
        ]);
    }

    /**
     * Mengambil data untuk Halaman Laporan
     */
    public function getLaporanData(Request $request)
    {
        // Validasi input
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'fakultas' => 'sometimes|string|exists:fakultas,kode',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // 1. Ambil data tabel
        $query = Sampah::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc');

        if ($request->has('fakultas') && $request->fakultas) {
            $query->where('fakultas_kode', $request->fakultas);
        }

        $laporanData = $query->get()->map(function ($item) {
            return [
                'timestamp' => $item->created_at->toIso8601String(), // Untuk sorting di JS
                'Tanggal' => $item->created_at->isoFormat('D MMMM YYYY'),
                'Hari' => $item->created_at->isoFormat('dddd'),
                'Waktu' => $item->created_at->isoFormat('HH:mm'),
                'Fakultas' => $item->fakultas_kode ?? 'N/A',
                'Jenis Sampah' => $item->jenis,
                'Berat (kg)' => round($item->berat_kg, 1),
            ];
        });

        // 2. Ambil data statistik ringkasan (Summary)
        $startBulanIni = now()->startOfMonth();
        $endBulanIni = now()->endOfMonth();
        $startBulanLalu = now()->subMonth()->startOfMonth();
        $endBulanLalu = $startBulanIni->copy()->subSecond();

        $dataBulanIni = $this->getWasteStatsForPeriod($startBulanIni, $endBulanIni);
        $dataBulanLalu = $this->getWasteStatsForPeriod($startBulanLalu, $endBulanLalu);

        // 3. Pencapaian (Achievements)
        $activeFacultyCount = Sampah::whereBetween('created_at', [$startBulanIni, $endBulanIni])
            ->distinct('fakultas_kode')->count('fakultas_kode');
        
        $consistencyData = Sampah::whereBetween('created_at', [$startBulanIni, $endBulanIni])
            ->select(DB::raw('DATE(created_at) as tgl'))
            ->distinct('tgl')
            ->count('tgl');

        return response()->json([
            'report_data' => $laporanData,
            'summary' => [
                'co2_reduction' => round(max(0, $dataBulanLalu['co2_total'] - $dataBulanIni['co2_total']), 1),
                'monthly_reduction_kg' => round($dataBulanLalu['total_berat'] - $dataBulanIni['total_berat'], 1),
                'monthly_total_kg' => round($dataBulanIni['total_berat'], 1),
            ],
            'achievements' => [
                'active_faculty_count' => $activeFacultyCount,
                'last_month_total_kg' => round($dataBulanLalu['total_berat'], 1),
                'consistency_count' => $consistencyData,
                'consistency_total_days' => now()->day, // Hari ke-n di bulan ini
            ]
        ]);
    }

    /**
     * Mengambil data untuk Halaman Fakultas
     */
    public function getFakultasData(Request $request)
    {
        $request->validate([
            'reduction' => 'string|in:today,weekly,monthly',
            'target' => 'string|in:today,weekly,monthly',
        ]);

        $reductionFilter = $request->get('reduction', 'today');
        $targetFilter = $request->get('target', 'today');

        // Ambil semua target fakultas
        $targets = Fakultas::all()->pluck('target_harian_kg', 'kode');

        // 1. Data untuk Leaderboard Reduksi
        $reductionData = $this->getLeaderboardData($reductionFilter);
        
        // 2. Data untuk Leaderboard Target
        $targetDataRaw = $this->getLeaderboardData($targetFilter);
        
        $targetLeaderboard = $targets->map(function ($target, $kode) use ($targetDataRaw, $targetFilter) {
            $total = $targetDataRaw->get($kode, ['current' => 0])['current'];
            $targetPeriod = $target;

            // Sesuaikan target berdasarkan periode
            if ($targetFilter === 'weekly') $targetPeriod = $target * 7;
            if ($targetFilter === 'monthly') $targetPeriod = $target * 30; // Asumsi 30 hari

            return [
                'name' => $kode,
                'total' => round($total, 1),
                'target' => $targetPeriod,
                'progress' => $targetPeriod > 0 ? min(100, ($total / $targetPeriod) * 100) : 0,
            ];
        })->sortBy('total')->values(); // Urutkan berdasarkan total terendah

        // Kalkulasi leaderboard reduksi
        $reductionLeaderboard = $reductionData->map(function ($data, $kode) {
            $current = $data['current'];
            $previous = $data['previous'];
            $reduction = 0;
            if ($previous > 0) {
                $reduction = (($previous - $current) / $previous) * 100;
            } elseif ($current > 0) {
                $reduction = -100; // Jika sebelumnya 0 dan sekarang ada
            }

            return [
                'name' => $kode,
                'total' => round($current, 1),
                'reduction' => round($reduction, 1),
            ];
        })->sortByDesc('reduction')->values(); // Urutkan berdasarkan reduksi tertinggi

        return response()->json([
            'reduction_leaderboard' => $reductionLeaderboard,
            'target_leaderboard' => $targetLeaderboard,
        ]);
    }

    /**
     * Mengambil data untuk Halaman Analitik (Paling kompleks)
     */
    public function getAnalitikData(Request $request)
    {
        // Tentukan rentang waktu
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
        $startOfMonth = now()->startOfMonth();
        $startOfLast7Days = now()->subDays(6)->startOfDay();
        $startOfLast30Days = now()->subDays(29)->startOfDay();
        $startOfLast6Months = now()->subMonths(5)->startOfMonth();

        // 1. Analisis Harian Fakultas (Chart 1)
        $dailyTotals = Sampah::where('created_at', '>=', $yesterday)
            ->where('jenis', '!=', 'Umum')
            ->select('fakultas_kode', 
                DB::raw('DATE(created_at) as tgl'), 
                DB::raw('SUM(berat_kg) as total_berat'))
            ->groupBy('fakultas_kode', 'tgl')
            ->get();
        
        $todayTotals = $dailyTotals->where('tgl', $today->toDateString())->pluck('total_berat', 'fakultas_kode');
        $yesterdayTotals = $dailyTotals->where('tgl', $yesterday->toDateString())->pluck('total_berat', 'fakultas_kode');
        $allFaculties = $dailyTotals->pluck('fakultas_kode')->unique()->sort();

        $analitikBarChart = [
            'labels' => $allFaculties,
            'datasets' => [
                'sampah_hari_ini' => $allFaculties->map(fn($f) => $todayTotals->get($f, 0)),
                'pengurangan_persen' => $allFaculties->map(function($f) use ($todayTotals, $yesterdayTotals) {
                    $today = $todayTotals->get($f, 0);
                    $yesterday = $yesterdayTotals->get($f, 0);
                    if ($yesterday > 0) return (($yesterday - $today) / $yesterday) * 100;
                    if ($today > 0) return -100; // Dari 0 ke >0
                    return 0;
                }),
            ]
        ];

        // 2. Tren Volume (7 Hari)
        $trendData = Sampah::where('created_at', '>=', $startOfLast7Days)
            ->where('jenis', '!=', 'Umum')
            ->select(DB::raw('DATE(created_at) as tgl'), DB::raw('SUM(berat_kg) as total_berat'))
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get()
            ->pluck('total_berat', 'tgl');
        
        $trendChart = ['labels' => [], 'data' => []];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfLast7Days->copy()->addDays($i);
            $trendChart['labels'][] = $date->isoFormat('D MMM');
            $trendChart['data'][] = round($trendData->get($date->toDateString(), 0), 1);
        }

        // 3. Distribusi (Mingguan & Bulanan)
        $distribusiMingguan = $this->getDistributionForPeriod($startOfWeek, now());
        $distribusiBulanan = $this->getDistributionForPeriod($startOfMonth, now());

        // 4. Komparasi Komposisi Fakultas (Bulan Ini)
        $komparasiFakultas = Sampah::where('created_at', '>=', $startOfMonth)
            ->where('jenis', '!=', 'Umum')
            ->whereNotNull('fakultas_kode')
            ->select('fakultas_kode', 'jenis', DB::raw('SUM(berat_kg) as total_berat'))
            ->groupBy('fakultas_kode', 'jenis')
            ->get();
        
        // 5. Pola Waktu (30 Hari)
        $hourlyPattern = Sampah::where('created_at', '>=', $startOfLast30Days)
            ->select(DB::raw('HOUR(created_at) as jam'), DB::raw('COUNT(*) as jumlah_entri'))
            ->groupBy('jam')
            ->get()
            ->pluck('jumlah_entri', 'jam');
        $hourlyChartData = array_fill(0, 24, 0);
        foreach($hourlyPattern as $jam => $jumlah) { $hourlyChartData[$jam] = $jumlah; }

        // 6. Kartu Statistik (Ekonomi, Emisi, Target)
        $statsBulanIni = $this->getWasteStatsForPeriod($startOfMonth, now());

        // 7. Tren Bulanan (6 Bulan)
        $monthlyData = Sampah::where('created_at', '>=', $startOfLast6Months)
            ->where('jenis', '!=', 'Umum')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as bulan"),
                DB::raw("SUM(CASE WHEN jenis = 'Organik' THEN berat_kg ELSE 0 END) as total_organik"),
                DB::raw("SUM(CASE WHEN jenis = 'Anorganik' THEN berat_kg ELSE 0 END) as total_anorganik"),
                DB::raw("SUM(berat_kg) as total_sampah")
            )
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();
        
        $monthlyCharts = [
            'labels' => [], 'economic' => [], 'emission' => [], 'total_kg' => [], 'reduction' => []
        ];
        $prevTotal = 0;
        foreach($monthlyData as $data) {
            $monthlyCharts['labels'][] = Carbon::createFromFormat('Y-m', $data->bulan)->isoFormat('MMM Y');
            $monthlyCharts['economic'][] = $data->total_anorganik * 2000; // Asumsi harga
            $monthlyCharts['emission'][] = ($data->total_organik * 1.0) + ($data->total_anorganik * 0.4); // Rumus emisi
            $monthlyCharts['total_kg'][] = $data->total_sampah;
            
            $reduction = 0;
            if ($prevTotal > 0) $reduction = (($prevTotal - $data->total_sampah) / $prevTotal) * 100;
            $monthlyCharts['reduction'][] = round($reduction, 1);
            
            $prevTotal = $data->total_sampah;
        }

        return response()->json([
            'analitik_bar_chart' => $analitikBarChart,
            'trend_chart' => $trendChart,
            'distribusi_mingguan' => $distribusiMingguan,
            'distribusi_bulanan' => $distribusiBulanan,
            'komparasi_fakultas' => $komparasiFakultas, // JS akan proses ini
            'pola_waktu_chart' => $hourlyChartData,
            'kartu_statistik' => [
                'potensi_ekonomi_rp' => $statsBulanIni['anorganik_total'] * 2000,
                'emisi_karbon_kg' => $statsBulanIni['co2_total'],
                'target_progress' => [
                    'total' => $statsBulanIni['total_berat'],
                    'target' => 1650, // Hardcoded dari JS
                    'percentage' => (1650 > 0) ? min(100, ($statsBulanIni['total_berat'] / 1650) * 100) : 0,
                ]
            ],
            'tren_bulanan_charts' => $monthlyCharts,
        ]);
    }


    // --- Helper Functions ---

    /**
     * Helper untuk mengambil statistik sampah (total, organik, anorganik, co2)
     */
    private function getWasteStatsForPeriod($startDate, $endDate)
    {
        $stats = Sampah::whereBetween('created_at', [$startDate, $endDate])
            ->where('jenis', '!=', 'Umum')
            ->select(
                DB::raw("SUM(CASE WHEN jenis = 'Organik' THEN berat_kg ELSE 0 END) as total_organik"),
                DB::raw("SUM(CASE WHEN jenis = 'Anorganik' THEN berat_kg ELSE 0 END) as total_anorganik"),
                DB::raw("SUM(CASE WHEN jenis = 'Residu' THEN berat_kg ELSE 0 END) as total_residu"),
                DB::raw("SUM(berat_kg) as total_sampah")
            )
            ->first();

        $organik = $stats->total_organik ?? 0;
        $anorganik = $stats->total_anorganik ?? 0;
        $residu = $stats->total_residu ?? 0;
        $total = $stats->total_sampah ?? 0;
        
        // Rumus CO2 dari analitik.js: (organik * 1.0) + (anorganik * 0.4)
        $co2 = ($organik * 1.0) + ($anorganik * 0.4);

        return [
            'organik_total' => $organik,
            'anorganik_total' => $anorganik,
            'residu_total' => $residu,
            'total_berat' => $total,
            'co2_total' => $co2,
        ];
    }

    /**
     * Helper untuk mengambil data leaderboard berdasarkan filter periode
     */
    private function getLeaderboardData(string $filter)
    {
        $now = now();
        $periods = [];

        if ($filter === 'today') {
            $periods['current_start'] = $now->copy()->startOfDay();
            $periods['current_end'] = $now->copy()->endOfDay();
            $periods['previous_start'] = $now->copy()->subDay()->startOfDay();
            $periods['previous_end'] = $now->copy()->subDay()->endOfDay();
        } elseif ($filter === 'weekly') {
            $periods['current_start'] = $now->copy()->subDays(6)->startOfDay();
            $periods['current_end'] = $now->copy()->endOfDay();
            $periods['previous_start'] = $now->copy()->subDays(13)->startOfDay();
            $periods['previous_end'] = $now->copy()->subDays(7)->startOfDay();
        } else { // monthly
            $periods['current_start'] = $now->copy()->subDays(29)->startOfDay();
            $periods['current_end'] = $now->copy()->endOfDay();
            $periods['previous_start'] = $now->copy()->subDays(59)->startOfDay();
            $periods['previous_end'] = $now->copy()->subDays(30)->startOfDay();
        }

        // Ambil data untuk periode saat ini dan sebelumnya dalam satu kueri
        $data = Sampah::where('created_at', '>=', $periods['previous_start'])
            ->where('created_at', '<=', $periods['current_end'])
            ->where('jenis', '!=', 'Umum')
            ->whereNotNull('fakultas_kode')
            ->select('fakultas_kode', 'created_at', 'berat_kg')
            ->get();

        $aggregated = [];

        foreach ($data as $item) {
            $fakultas = $item->fakultas_kode;
            if (!isset($aggregated[$fakultas])) {
                $aggregated[$fakultas] = ['current' => 0, 'previous' => 0];
            }

            if ($item->created_at >= $periods['current_start']) {
                $aggregated[$fakultas]['current'] += $item->berat_kg;
            } elseif ($item->created_at >= $periods['previous_start'] && $item->created_at <= $periods['previous_end']) {
                $aggregated[$fakultas]['previous'] += $item->berat_kg;
            }
        }
        
        return collect($aggregated);
    }

    /**
     * Helper untuk mengambil data distribusi (Organik, Anorganik, Residu)
     */
    private function getDistributionForPeriod($startDate, $endDate)
    {
        $data = Sampah::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('jenis', ['Organik', 'Anorganik', 'Residu'])
            ->select('jenis', DB::raw('SUM(berat_kg) as total_berat'))
            ->groupBy('jenis')
            ->get()
            ->pluck('total_berat', 'jenis');
        
        return [
            round($data->get('Organik', 0), 1),
            round($data->get('Anorganik', 0), 1),
            round($data->get('Residu', 0), 1),
        ];
    }
}

