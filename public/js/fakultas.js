// public/js/fakultas.js (REFACTORED for Laravel API)

// Hapus semua impor Firebase

const colors = ['#2dd4bf', '#38bdf8', '#a78bfa', '#facc15', '#fb923c'];

let reductionFilter = 'today';
let targetFilter = 'today';

let reductionLeaderboardContainer;
let targetLeaderboardContainer;
let reductionBtnToday, reductionBtnWeekly, reductionBtnMonthly;
let targetBtnToday, targetBtnWeekly, targetBtnMonthly;

const baseButtonClasses = "px-4 py-2 text-sm font-medium transition-colors duration-200";
const activeClasses = "bg-teal-600 text-white hover:bg-teal-700";
const inactiveClasses = "bg-white text-gray-900 hover:bg-gray-100 hover:text-teal-700";

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

/**
 * Helper untuk update 4 kartu statistik global
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


// Fungsi style (tidak berubah)
function applyFilterButtonStyles(type) {
    // ... (Fungsi ini sama persis, copy-paste dari file lama)
    if (type === 'reduction') {
        const structuralClasses = "border border-gray-200";
        reductionBtnToday.className = baseButtonClasses + " " + structuralClasses + " rounded-l-lg";
        reductionBtnWeekly.className = baseButtonClasses + " border-y border-r border-gray-200 -ml-px";
        reductionBtnMonthly.className = baseButtonClasses + " " + structuralClasses + " rounded-r-lg -ml-px";

        if (reductionFilter === 'today') reductionBtnToday.className += " " + activeClasses;
        else reductionBtnToday.className += " " + inactiveClasses;
        if (reductionFilter === 'weekly') reductionBtnWeekly.className += " " + activeClasses;
        else reductionBtnWeekly.className += " " + inactiveClasses;
        if (reductionFilter === 'monthly') reductionBtnMonthly.className += " " + activeClasses;
        else reductionBtnMonthly.className += " " + inactiveClasses;

    } else if (type === 'target') {
        const structuralClasses = "border border-gray-200";
        targetBtnToday.className = baseButtonClasses + " " + structuralClasses + " rounded-l-lg";
        targetBtnWeekly.className = baseButtonClasses + " border-y border-r border-gray-200 -ml-px";
        targetBtnMonthly.className = baseButtonClasses + " " + structuralClasses + " rounded-r-lg -ml-px";

        if (targetFilter === 'today') targetBtnToday.className += " " + activeClasses;
        else targetBtnToday.className += " " + inactiveClasses;
        if (targetFilter === 'weekly') targetBtnWeekly.className += " " + activeClasses;
        else targetBtnWeekly.className += " " + inactiveClasses;
        if (targetFilter === 'monthly') targetBtnMonthly.className += " " + activeClasses;
        else targetBtnMonthly.className += " " + inactiveClasses;
    }
}

/**
 * Fungsi baru untuk mengambil data dari API dan me-render leaderboard
 */
async function fetchAndRenderLeaderboards() {
    // Tampilkan loading
    reductionLeaderboardContainer.innerHTML = '<p class="text-center text-gray-500">Memuat data...</p>';
    targetLeaderboardContainer.innerHTML = '<p class="text-center text-gray-500">Memuat data...</p>';

    // Bangun URL API dengan parameter filter
    const url = `/api/data/fakultas?reduction=${reductionFilter}&target=${targetFilter}`;
    
    const data = await fetchData(url);
    if (!data) {
        reductionLeaderboardContainer.innerHTML = '<p class="text-center text-red-500">Gagal memuat data.</p>';
        targetLeaderboardContainer.innerHTML = '<p class="text-center text-red-500">Gagal memuat data.</p>';
        return;
    }

    renderReductionLeaderboard(data.reduction_leaderboard);
    renderTargetLeaderboard(data.target_leaderboard);
}


// Fungsi render (SEDIKIT BERUBAH): Menerima data yang sudah jadi
function renderReductionLeaderboard(sortedData) {
    const container = reductionLeaderboardContainer;
    container.innerHTML = '';

    if (!sortedData || sortedData.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500">Tidak ada data untuk ditampilkan pada periode ini.</p>';
        return;
    }
    
    // Data sudah disortir dari backend
    sortedData.forEach((faculty, index) => {
        const color = colors[index % colors.length];
        
        const reductionValue = faculty.reduction;
        let textColorClass = 'text-gray-600'; // Default
        if (reductionValue > 0) textColorClass = 'text-green-600';
        else if (reductionValue < 0) textColorClass = 'text-red-600';
        
        const progress = Math.min(Math.max(reductionValue, 0), 100);
        const rankDisplay = (idx) => idx === 0 ? '🥇' : idx === 1 ? '🥈' : idx === 2 ? '🥉' : `<span class="text-gray-500">${idx + 1}</span>`;
        
        container.innerHTML += `
            <div class="bg-white p-6 rounded-xl shadow-md border-2" style="border-color:${color};">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full mr-4" style="background-color: ${color};"></span>
                        <span class="font-bold text-lg text-gray-800">${faculty.name}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-semibold ${textColorClass}">${reductionValue.toFixed(1)} %</span>
                        <span class="ml-2">${rankDisplay(index)}</span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-teal-600 h-2.5 rounded-full" style="width: ${progress}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>Total Sampah: <strong>${faculty.total.toFixed(1)} kg</strong></span>
                        <span>Progress Pengurangan: <strong>${progress.toFixed(0)}%</strong></span>
                    </div>
                </div>
            </div>
        `;
    });
}

// Fungsi render (SEDIKIT BERUBAH): Menerima data yang sudah jadi
function renderTargetLeaderboard(sortedData) {
    const container = targetLeaderboardContainer;
    container.innerHTML = '';

    if (!sortedData || sortedData.every(f => f.total === 0)) {
        container.innerHTML = '<p class="text-center text-gray-500">Belum ada data timbunan untuk periode ini.</p>';
        return;
    }

    // Data sudah disortir dari backend
    sortedData.forEach((faculty, index) => {
        const color = colors[index % colors.length];
        const progress = faculty.progress; // Ambil progress dari backend
        const rankDisplay = (idx) => idx === 0 ? '🥇' : idx === 1 ? '🥈' : idx === 2 ? '🥉' : `<span class="text-gray-500">${idx + 1}</span>`;
        
        container.innerHTML += `
            <div class="bg-white p-6 rounded-xl shadow-md border-2" style="border-color:${color};">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full mr-4" style="background-color: ${color};"></span>
                        <span class="font-bold text-lg text-gray-800">${faculty.name}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-semibold text-teal-600">${faculty.total.toFixed(1)} / ${faculty.target} kg</span>
                        <span class="ml-2">${rankDisplay(index)}</span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-teal-600 h-2.5 rounded-full" style="width: ${progress.toFixed(0)}%"></div>
                    </div>
                    <div class="flex justify-between text-sm text-gray-500 mt-1">
                        <span>Pencapaian: ${progress.toFixed(0)}%</span>
                    </div>
                </div>
            </div>
        `;
    });
}


/**
 * Fungsi inisialisasi utama untuk halaman ini
 */
export function initFakultasPage() {
    console.log("Fakultas Page Initialized (SQL Mode)");
    
    reductionLeaderboardContainer = document.getElementById('reduction-leaderboard-container');
    targetLeaderboardContainer = document.getElementById('target-leaderboard-container');
    reductionBtnToday = document.getElementById('reduction-btn-today');
    reductionBtnWeekly = document.getElementById('reduction-btn-weekly');
    reductionBtnMonthly = document.getElementById('reduction-btn-monthly');
    targetBtnToday = document.getElementById('target-btn-today');
    targetBtnWeekly = document.getElementById('target-btn-weekly');
    targetBtnMonthly = document.getElementById('target-btn-monthly');

    if (!reductionLeaderboardContainer || !targetLeaderboardContainer || !reductionBtnToday || !targetBtnToday) {
        console.error("Missing required DOM elements for Fakultas page."); return;
    }

    updateCurrentDate('current-date');
    updateGlobalStatCards(); // Panggil data global
    
    applyFilterButtonStyles('reduction');
    applyFilterButtonStyles('target');

    // Setup listener baru: panggil API saat filter diubah
    reductionBtnToday.addEventListener('click', () => { reductionFilter = 'today'; applyFilterButtonStyles('reduction'); fetchAndRenderLeaderboards(); });
    reductionBtnWeekly.addEventListener('click', () => { reductionFilter = 'weekly'; applyFilterButtonStyles('reduction'); fetchAndRenderLeaderboards(); });
    reductionBtnMonthly.addEventListener('click', () => { reductionFilter = 'monthly'; applyFilterButtonStyles('reduction'); fetchAndRenderLeaderboards(); });

    targetBtnToday.addEventListener('click', () => { targetFilter = 'today'; applyFilterButtonStyles('target'); fetchAndRenderLeaderboards(); });
    targetBtnWeekly.addEventListener('click', () => { targetFilter = 'weekly'; applyFilterButtonStyles('target'); fetchAndRenderLeaderboards(); });
    targetBtnMonthly.addEventListener('click', () => { targetFilter = 'monthly'; applyFilterButtonStyles('target'); fetchAndRenderLeaderboards(); });

    // Panggil data awal saat halaman dimuat
    fetchAndRenderLeaderboards();
}
