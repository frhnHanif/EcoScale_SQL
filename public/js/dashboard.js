// public/js/dashboard.js (REFACTORED for Laravel API)

// Hapus semua impor Firebase

let weeklyTrendChart;
let typeDistributionChart;

/**
 * Helper untuk mengambil data JSON dari endpoint Laravel
 * @param {string} url - URL API (cth: '/api/data/dashboard')
 * @returns {Promise<object>} - Data JSON
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
        // Kembalikan objek kosong agar UI tidak crash
        return {};
    }
}

/**
 * Memperbarui tanggal terkini pada elemen HTML.
 * @param {string} elementId - ID elemen HTML yang akan diperbarui.
 */
function updateCurrentDate(elementId) {
    const today = new Date();
    const dateElement = document.getElementById(elementId);
    if (dateElement) {
        const options = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        const formattedDate = today.toLocaleDateString('id-ID', options);
        dateElement.textContent = formattedDate;
    } else {
        console.warn(`Element with ID '${elementId}' not found for date update.`);
    }
}

// --- Fungsi Inisialisasi Chart (Tidak berubah banyak) ---

function initWeeklyTrendChart(ctxId) {
    const ctx = document.getElementById(ctxId)?.getContext('2d');
    if (ctx) {
        weeklyTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                datasets: [{
                    label: 'Berat Sampah (kg)',
                    data: [0, 0, 0, 0, 0, 0, 0], // Data awal
                    borderColor: '#14b8a6',
                    backgroundColor: 'rgba(20, 184, 166, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            }
        });
    } else {
        console.warn(`Canvas element with ID '${ctxId}' not found.`);
    }
}

const noDataDoughnutText = {
    // ... (Plugin noDataDoughnutText bisa dicopy-paste dari file lama, tidak berubah)
    id: 'noDataDoughnutText',
    beforeDraw(chart, args, options) {
        const { ctx, data } = chart;
        const total = data.datasets[0].data.reduce((sum, val) => sum + val, 0);
        
        // Cek jika total 0
        if (total === 0) {
            ctx.save();
            ctx.font = '16px Inter, sans-serif';
            ctx.fillStyle = '#888';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            const centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
            const centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;
            ctx.fillText('No Data Today', centerX, centerY);
            ctx.restore();
        }
    }
};

function initTypeDistributionChart(ctxId) {
    const ctx = document.getElementById(ctxId)?.getContext('2d');
    if (ctx) {
        typeDistributionChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Organik (kg)', 'Anorganik (kg)', 'Residu (kg)'],
                datasets: [{
                    data: [0, 0, 0], // Data awal
                    backgroundColor: ['#62B682', '#5C7AF3', '#D35748'],
                    hoverOffset: 4,
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom' } },
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%' // Set cutout
            },
            plugins: [noDataDoughnutText] // Daftarkan plugin
        });
    } else {
        console.warn(`Canvas element with ID '${ctxId}' not found.`);
    }
}

// --- Fungsi Update UI Utama ---

async function updateDashboardUI() {
    // 1. Ambil data global stats
    const globalStats = await fetchData('/api/data/global-stats');
    if (globalStats) {
        updateGlobalStatCards(globalStats);
    }

    // 2. Ambil data spesifik dashboard
    const data = await fetchData('/api/data/dashboard');
    if (!data) return; // Gagal fetch

    const { overview, weekly_trend } = data;

    // 3. Update kartu "Overview Garbage Summary"
    if (overview) {
        document.getElementById('total-sampah').textContent = (overview.organik + overview.anorganik + overview.residu).toFixed(1);
        document.getElementById('total-organik').textContent = overview.organik.toFixed(1);
        document.getElementById('total-anorganik').textContent = overview.anorganik.toFixed(1);
        document.getElementById('total-residu').textContent = overview.residu.toFixed(1);
    }

    // 4. Update Grafik Tren Mingguan
    if (weekly_trend && weeklyTrendChart) {
        weeklyTrendChart.data.datasets[0].data = weekly_trend;
        weeklyTrendChart.update();
    }

    // 5. Update Grafik Distribusi Jenis (Doughnut)
    if (overview && typeDistributionChart) {
        const chartData = [overview.organik, overview.anorganik, overview.residu];
        typeDistributionChart.data.datasets[0].data = chartData;
        typeDistributionChart.update();
    }
}

/**
 * Helper untuk update 4 kartu statistik global
 */
function updateGlobalStatCards(stats) {
    document.getElementById('total-sampah-today').textContent = stats.total_sampah_today;
    document.getElementById('active-faculties').textContent = stats.active_faculties;
    document.getElementById('avg-reduction').textContent = stats.avg_reduction;
    
    // Logika Status Lingkungan
    const targetReduction = stats.target_reduction_last_month;
    const avgReduction = stats.avg_reduction;

    let achievementPercentage = 0;
    if (targetReduction > 0) {
        achievementPercentage = (Math.max(0, avgReduction) / targetReduction) * 100;
    } else if (avgReduction > 0) {
        achievementPercentage = 100; // Jika tidak ada target tapi ada reduksi
    }

    let envStatusText = 'Kurang';
    let envStatusSubtitleText = 'Capaian reduksi < 60%';
    let borderColor = 'bg-red-500';
    let textColor = 'text-red-600';

    if (achievementPercentage >= 85) {
        envStatusText = 'Baik';
        envStatusSubtitleText = 'Capaian Reduksi > 85%';
        borderColor = 'bg-green-500';
        textColor = 'text-green-600';
    } else if (achievementPercentage >= 60) {
        envStatusText = 'Cukup';
        envStatusSubtitleText = 'Capaian Reduksi 60-85%';
        borderColor = 'bg-yellow-500';
        textColor = 'text-yellow-600';
    }

    document.getElementById('env-status').textContent = envStatusText;
    document.getElementById('env-status-subtitle').textContent = envStatusSubtitleText;
    document.getElementById('env-status-border').className = `absolute top-0 left-0 h-full w-1.5 ${borderColor} rounded-l-xl`;
    document.getElementById('env-status-text').className = `text-3xl font-bold ${textColor}`;
}

/**
 * Fungsi inisialisasi utama untuk halaman ini
 * Dipanggil dari file Blade
 */
export function initDashboardPage() {
    // Tidak perlu `firebaseConfig` lagi
    console.log("Dashboard Page Initialized (SQL Mode)");

    updateCurrentDate('current-date');
    initWeeklyTrendChart('weeklyTrendChart');
    initTypeDistributionChart('typeDistributionChart');

    // Langsung panggil update UI
    updateDashboardUI();
    
    // Anda bisa menambahkan polling jika ingin data real-time
    // setInterval(updateDashboardUI, 30000); // Contoh: refresh setiap 30 detik
}
