// public/js/analitik.js (REFACTORED for Laravel API)

// Hapus semua impor Firebase

// --- KONFIGURASI & VARIABEL GLOBAL ---
// Datalabels didaftarkan di file blade atau app.js utama
// Chart.register(ChartDataLabels); 
Chart.defaults.plugins.datalabels.color = '#000';
Chart.defaults.plugins.datalabels.font = { weight: 'bold' };

const HARGA_ANORGANIK_PER_KG = 2000; // Bisa juga diambil dari config
const FAKTOR_EMISI_CO2E_PER_KG = 0.5; // Bisa juga diambil dari config
const TARGET_BULANAN_KG = 1650; // Bisa juga diambil dari config

let analitikBarChart, trendChart, distributionChartWeekly, distributionChartMonthly, 
    facultyStackedChart, hourlyPatternChart, monthlyEconomicChart, 
    monthlyEmissionChart, monthlyReductionChart;

const CHART_COLORS = {
    organik: 'rgba(68, 127, 64, 0.8)',
    anorganik: 'rgba(92, 122, 243, 0.8)',
    residu: 'rgba(156, 163, 175, 0.8)',
};

/**
 * Helper untuk mengambil data JSON dari endpoint Laravel
 */
async function fetchData(url) {
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error(`Error fetching data from ${url}:`, error);
        return null;
    }
}

/**
 * Memperbarui tanggal terkini pada elemen HTML.
 */
function updateCurrentDate(elementId) {
    const today = new Date();
    const dateElement = document.getElementById(elementId);
    if (dateElement) {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const formattedDate = today.toLocaleDateString('id-ID', options);
        dateElement.textContent = formattedDate;
    }
}

// --- FUNGSI INISIALISASI GRAFIK (Copy-paste dari file lama, tidak berubah) ---

function initAnalitikBarChart(ctxId) {
    const ctx = document.getElementById(ctxId)?.getContext('2d');
    if (!ctx) return;
    analitikBarChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Sampah Hari Ini (kg)',
                data: [],
                backgroundColor: '#447F40',
            }, {
                label: 'Pengurangan (%)',
                data: [],
                backgroundColor: '#5C7AF3',
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: {
                x: { title: { display: true, text: 'Fakultas' } },
                y: { beginAtZero: true, title: { display: true, text: 'Nilai' } }
            },
            plugins: { legend: { position: 'top' } }
        }
    });
}

function initTrendChart() {
    const ctx = document.getElementById('trendChart')?.getContext('2d');
    if (!ctx) return;
    trendChart = new Chart(ctx, {
        type: 'line',
        data: { labels: [], datasets: [{ label: 'Total Berat Sampah (kg)', data: [], tension: 0.1, borderColor: '#447F40', fill: true }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, title: { display: true, text: 'Berat (kg)' } } }
        }
    });
}

function initDistributionChart(ctxId) {
    const ctx = document.getElementById(ctxId)?.getContext('2d');
    if (!ctx) return null; 
    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Organik', 'Anorganik', 'Residu'],
            datasets: [{ 
                data: [0, 0, 0], // Data awal
                backgroundColor: [CHART_COLORS.organik, CHART_COLORS.anorganik, CHART_COLORS.residu]
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'bottom', labels: { boxWidth: 12 } },
                datalabels: {
                    color: '#000', font: { weight: 'bold' },
                    formatter: (value, context) => {
                        const total = context.chart.data.datasets[0].data.reduce((sum, val) => sum + val, 0);
                        const percentage = total > 0 ? (value / total * 100) : 0;
                        if (percentage < 5) return null;
                        return `${value.toFixed(1)} kg\n(${percentage.toFixed(1)}%)`;
                    }
                }
            }
        }
    });
}

function initFacultyStackedChart() {
    const ctx = document.getElementById('facultyStackedChart')?.getContext('2d');
    if (!ctx) return;
    facultyStackedChart = new Chart(ctx, {
        type: 'bar',
        data: { labels: [], datasets: [] },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Berat (kg)' } } },
            plugins: {
                datalabels: {
                    color: '#000', font: { weight: 'bold' },
                    formatter: (value, context) => {
                        if (value < 5) return null;
                        const total = context.chart.data.datasets.reduce((sum, dataset) => sum + (dataset.data[context.dataIndex] || 0), 0);
                        const percentage = total > 0 ? (value / total * 100) : 0;
                        return `${value.toFixed(1)} kg\n(${percentage.toFixed(0)}%)`;
                    }
                }
            }
        }
    });
}

function initHourlyPatternChart() {
    const ctx = document.getElementById('hourlyPatternChart')?.getContext('2d');
    if (!ctx) return;
    hourlyPatternChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Array.from({ length: 24 }, (_, i) => `${i.toString().padStart(2, '0')}:00`),
            datasets: [{ label: 'Jumlah Entri Sampah', data: Array(24).fill(0), backgroundColor: '#F59E0B' }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, title: { display: true, text: 'Jumlah Entri' } } }
        }
    });
}

function initMonthlyEconomicChart() {
    const ctx = document.getElementById('monthlyEconomicChart')?.getContext('2d');
    if (!ctx) return;
    monthlyEconomicChart = new Chart(ctx, {
        type: 'bar',
        data: { labels: [], datasets: [
                { label: 'Potensi Ekonomi (Rp)', data: [], backgroundColor: 'rgba(22, 163, 74, 0.7)', order: 1 },
                { label: 'Tren Ekonomi', data: [], borderColor: 'rgba(16, 115, 53, 1)', type: 'line', order: 0, tension: 0.3 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { title: { display: true, text: 'Rupiah (Rp)' } } } }
    });
}

function initMonthlyEmissionChart() {
    const ctx = document.getElementById('monthlyEmissionChart')?.getContext('2d');
    if (!ctx) return;
    monthlyEmissionChart = new Chart(ctx, {
        type: 'bar',
        data: { labels: [], datasets: [
                { label: 'Emisi Karbon (kg CO₂e)', data: [], backgroundColor: 'rgba(59, 130, 246, 0.7)', order: 1 },
                { label: 'Tren Emisi', data: [], borderColor: 'rgba(37, 99, 235, 1)', type: 'line', order: 0, tension: 0.3 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { title: { display: true, text: 'kg CO₂e' } } } }
    });
}

function initMonthlyReductionChart() {
    const ctx = document.getElementById('monthlyReductionChart')?.getContext('2d');
    if (!ctx) return;
    monthlyReductionChart = new Chart(ctx, {
        type: 'bar',
        data: { labels: [], datasets: [
                { label: 'Total Sampah (kg)', data: [], backgroundColor: 'rgba(156, 163, 175, 0.6)', yAxisID: 'y' },
                { label: 'Pengurangan (%)', data: [], borderColor: 'rgba(239, 68, 68, 1)', type: 'line', yAxisID: 'y1' }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: {
                y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Berat (kg)' } },
                y1: { type: 'linear', display: true, position: 'right', title: { display: true, text: 'Pengurangan (%)' }, grid: { drawOnChartArea: false } }
            }
        }
    });
}
// --- Akhir Fungsi Inisialisasi Grafik ---


/**
 * Fungsi utama untuk mengambil dan memperbarui SEMUA UI di halaman analitik
 */
async function updateAnalitikUI() {
    console.log("Analitik.js: Fetching data from Laravel API...");
    const data = await fetchData('/api/data/analitik');
    if (!data) {
        console.error("Gagal mengambil data analitik.");
        return;
    }

    // 0. Analisis Harian Fakultas
    if (analitikBarChart && data.analitik_bar_chart) {
        const chartData = data.analitik_bar_chart;
        analitikBarChart.data.labels = chartData.labels;
        analitikBarChart.data.datasets[0].data = chartData.datasets.sampah_hari_ini;
        analitikBarChart.data.datasets[1].data = chartData.datasets.pengurangan_persen;
        analitikBarChart.update();
    }

    // 1. Tren Volume (7 Hari)
    if (trendChart && data.trend_chart) {
        trendChart.data.labels = data.trend_chart.labels;
        trendChart.data.datasets[0].data = data.trend_chart.data;
        trendChart.update();
    }

    // 2. Distribusi (Mingguan & Bulanan)
    if (distributionChartWeekly && data.distribusi_mingguan) {
        distributionChartWeekly.data.datasets[0].data = data.distribusi_mingguan;
        distributionChartWeekly.update();
    }
    if (distributionChartMonthly && data.distribusi_bulanan) {
        distributionChartMonthly.data.datasets[0].data = data.distribusi_bulanan;
        distributionChartMonthly.update();
    }

    // 3. Komparasi Komposisi Fakultas
    if (facultyStackedChart && data.komparasi_fakultas) {
        // Proses data mentah dari Laravel
        const rawData = data.komparasi_fakultas;
        const faculties = [...new Set(rawData.map(d => d.fakultas_kode))].sort();
        const facultyData = {};
        
        rawData.forEach(d => {
            if (!facultyData[d.fakultas_kode]) facultyData[d.fakultas_kode] = {};
            facultyData[d.fakultas_kode][d.jenis] = d.total_berat;
        });

        facultyStackedChart.data.labels = faculties;
        facultyStackedChart.data.datasets = ['Organik', 'Anorganik', 'Residu'].map(jenis => ({
            label: jenis,
            data: faculties.map(fakultas => facultyData[fakultas]?.[jenis] || 0),
            backgroundColor: CHART_COLORS[jenis.toLowerCase()],
        }));
        facultyStackedChart.update();
    }

    // 4. Pola Waktu (Jam Sibuk)
    if (hourlyPatternChart && data.pola_waktu_chart) {
        hourlyPatternChart.data.datasets[0].data = data.pola_waktu_chart;
        hourlyPatternChart.update();
    }

    // 5. Kartu Statistik & Target
    if (data.kartu_statistik) {
        const stats = data.kartu_statistik;
        document.getElementById('potensi-ekonomi-value').textContent = `Rp ${Math.round(stats.potensi_ekonomi_rp).toLocaleString('id-ID')}`;
        document.getElementById('emisi-karbon-value').textContent = `${stats.emisi_karbon_kg.toFixed(1)} kg CO₂e`;
        
        const progress = stats.target_progress;
        document.getElementById('progres-bar-fill').style.width = `${progress.percentage}%`;
        document.getElementById('progres-bar-text').textContent = `${Math.round(progress.percentage)}%`;
        document.getElementById('progres-bar-label').textContent = `${progress.total.toFixed(1)} kg / ${progress.target} kg`;
    }

    // 6. Tren Bulanan
    if (data.tren_bulanan_charts) {
        const tren = data.tren_bulanan_charts;
        if (monthlyEconomicChart) {
            monthlyEconomicChart.data.labels = tren.labels;
            monthlyEconomicChart.data.datasets[0].data = tren.economic;
            monthlyEconomicChart.data.datasets[1].data = tren.economic;
            monthlyEconomicChart.update();
        }
        if (monthlyEmissionChart) {
            monthlyEmissionChart.data.labels = tren.labels;
            monthlyEmissionChart.data.datasets[0].data = tren.emission;
            monthlyEmissionChart.data.datasets[1].data = tren.emission;
            monthlyEmissionChart.update();
        }
        if (monthlyReductionChart) {
            monthlyReductionChart.data.labels = tren.labels;
            monthlyReductionChart.data.datasets[0].data = tren.total_kg;
            monthlyReductionChart.data.datasets[1].data = tren.reduction;
            monthlyReductionChart.update();
        }
    }
}

/**
 * Helper untuk update 4 kartu statistik global
 * (Fungsi ini harus ada di semua file JS halaman atau di file global)
 */
async function updateGlobalStatCards() {
    const stats = await fetchData('/api/data/global-stats');
    if (!stats) return;

    document.getElementById('total-sampah-today').textContent = stats.total_sampah_today;
    document.getElementById('active-faculties').textContent = stats.active_faculties;
    document.getElementById('avg-reduction').textContent = stats.avg_reduction;
    
    const targetReduction = stats.target_reduction_last_month;
    const avgReduction = stats.avg_reduction;

    let achievementPercentage = 0;
    if (targetReduction > 0) {
        achievementPercentage = (Math.max(0, avgReduction) / targetReduction) * 100;
    } else if (avgReduction > 0) {
        achievementPercentage = 100;
    }

    let envStatusText = 'Kurang', envStatusSubtitleText = 'Capaian reduksi < 60%', borderColor = 'bg-red-500', textColor = 'text-red-600';
    if (achievementPercentage >= 85) {
        envStatusText = 'Baik'; envStatusSubtitleText = 'Capaian Reduksi > 85%'; borderColor = 'bg-green-500'; textColor = 'text-green-600';
    } else if (achievementPercentage >= 60) {
        envStatusText = 'Cukup'; envStatusSubtitleText = 'Capaian Reduksi 60-85%'; borderColor = 'bg-yellow-500'; textColor = 'text-yellow-600';
    }

    document.getElementById('env-status').textContent = envStatusText;
    document.getElementById('env-status-subtitle').textContent = envStatusSubtitleText;
    document.getElementById('env-status-border').className = `absolute top-0 left-0 h-full w-1.5 ${borderColor} rounded-l-xl`;
    document.getElementById('env-status-text').className = `text-3xl font-bold ${textColor}`;
}


/**
 * Fungsi inisialisasi utama untuk halaman ini
 */
export function initAnalitikPage() {
    console.log("Analitik Page Initialized (SQL Mode)");
    
    updateCurrentDate('current-date');

    // Inisialisasi SEMUA kerangka grafik
    initAnalitikBarChart('analitikBarChart');
    initTrendChart();
    distributionChartWeekly = initDistributionChart('distributionChartWeekly');
    distributionChartMonthly = initDistributionChart('distributionChartMonthly');
    initFacultyStackedChart();
    initHourlyPatternChart();
    initMonthlyEconomicChart();
    initMonthlyEmissionChart();
    initMonthlyReductionChart();
    
    // Panggil data global stats dan data analitik
    updateGlobalStatCards();
    updateAnalitikUI();
    
    // Tidak ada lagi listener real-time, 
    // Anda bisa tambahkan polling jika perlu
}
