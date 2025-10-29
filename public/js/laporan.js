// public/js/laporan.js (REFACTORED for Laravel API)

// Hapus impor Firebase
import * as XLSX from 'https://cdn.sheetjs.com/xlsx-0.20.2/package/xlsx.mjs'; // For Excel export

let currentReportData = []; // Tetap simpan data di JS untuk sorting
let currentSortKey = 'timestamp'; // Default sort key
let currentSortDirection = 'desc'; // Default sort direction

let startDateInput;
let endDateInput;
let facultyFilterSelect;
let generateReportBtn;
let reportResultsDiv;
let loadingReportText;
let noDataReportText;
let reportTable;
let reportTableBody;
let exportReportBtn;

let co2ReductionSpan;
let monthlyReductionSpan;
let monthlyTotalSpan;
let achievementsListUl;

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


// ==============================
// === SUMMARY & ACHIEVEMENTS ===
// (Diambil dari data laporan)
// ==============================

function displaySummaryStatistics(summaryData) {
    if (!summaryData) {
        co2ReductionSpan.textContent = 'N/A';
        monthlyReductionSpan.textContent = 'N/A';
        monthlyTotalSpan.textContent = 'N/A';
        return;
    }
    co2ReductionSpan.textContent = summaryData.co2_reduction.toFixed(1);
    monthlyReductionSpan.textContent = summaryData.monthly_reduction_kg.toFixed(1);
    monthlyTotalSpan.textContent = summaryData.monthly_total_kg.toFixed(1);
}

function displayAchievements(achievementsData) {
    achievementsListUl.innerHTML = '';
    if (!achievementsData) {
        achievementsListUl.innerHTML = '<li class="text-center text-gray-500 py-2">Gagal memuat data pencapaian.</li>';
        return;
    }

    const {
        active_faculty_count,
        last_month_total_kg,
        monthly_reduction_kg, // Ambil dari summary
        consistency_count,
        consistency_total_days
    } = achievementsData;

    const monthlyReductionKg = achievementsData.monthly_reduction_kg;

    const achievementsItems = [
        { text: `Jumlah fakultas aktif bulan ini: <strong>${active_faculty_count}</strong>`, status: active_faculty_count > 0 ? 'checked' : 'hourglass' },
        { text: `Total pengurangan <strong>${monthlyReductionKg.toFixed(1)} kg</strong> dari ${last_month_total_kg.toFixed(1)} kg`, status: 'checked' },
        { text: 'Target berat sampah tercapai', status: monthlyReductionKg >= 0 ? 'checked' : 'hourglass' },
        { text: `Konsistensi input data: <strong>${consistency_count} dari ${consistency_total_days} hari</strong>`, status: (consistency_total_days > 0 && (consistency_count / consistency_total_days) >= 0.7) ? 'checked' : 'hourglass' }
    ];

    achievementsItems.forEach(achievement => {
        let icon = achievement.status === 'checked'
            ? `<svg class="text-green-500 w-4 h-4" ... (copy icon check) ... ></svg>`
            : `<svg class="w-4 h-4" ... (copy icon hourglass) ... ></svg>`; // Salin SVG icon dari file lama
        achievementsListUl.innerHTML += `<li class="flex items-center gap-2 mb-1">${icon}<span class="text-sm">${achievement.text}</span></li>`;
    });
}

// ==============================
// === LAPORAN TABLE SECTION ===
// (Fungsi-fungsi ini tidak berubah)
// ==============================

function renderTable(dataToRender) {
    reportTableBody.innerHTML = '';
    if (dataToRender.length === 0) {
        return; 
    }
    let tableHTML = '';
    dataToRender.forEach(rowData => {
        tableHTML += `
            <tr class="text-center">
                <td class="px-4 py-2">${rowData.Tanggal}</td>
                <td class="px-4 py-2">${rowData.Hari}</td>
                <td class="px-4 py-2">${rowData.Waktu}</td>
                <td class="px-4 py-2">${rowData.Fakultas}</td>
                <td class="px-4 py-2">${rowData['Jenis Sampah']}</td>
                <td class="px-4 py-2">${rowData['Berat (kg)']}</td>
            </tr>`;
    });
    reportTableBody.innerHTML = tableHTML;
}

function updateSortIcons() {
    document.querySelectorAll('.sortable-header').forEach(header => {
        const key = header.getAttribute('data-sort-key');
        const iconSpan = header.querySelector('.sort-icon');
        if (key === currentSortKey) {
            iconSpan.classList.add('active');
            iconSpan.innerHTML = currentSortDirection === 'asc' ? '▲' : '▼';
        } else {
            iconSpan.classList.remove('active');
            iconSpan.innerHTML = '';
        }
    });
}

function sortAndRenderData() {
    const sortedData = [...currentReportData].sort((a, b) => {
        let valA, valB;

        // Sorting berdasarkan timestamp (ISO string) atau Berat (angka)
        if (currentSortKey === 'timestamp') {
            valA = a.timestamp;
            valB = b.timestamp;
        } else if (currentSortKey === 'Berat (kg)') {
            valA = parseFloat(a['Berat (kg)']) || 0;
            valB = parseFloat(b['Berat (kg)']) || 0;
        } else {
            valA = a[currentSortKey] || '';
            valB = b[currentSortKey] || '';
        }
        
        if (valA < valB) return currentSortDirection === 'asc' ? -1 : 1;
        if (valA > valB) return currentSortDirection === 'asc' ? 1 : -1;
        return 0;
    });

    renderTable(sortedData);
    updateSortIcons();
}

/**
 * Fungsi utama untuk mengambil data laporan
 */
async function fetchAndDisplayReportData() {
    console.log("DEBUG: Fetching Report Data from Laravel API.");

    // Hapus listener lama
    // if (unsubscribeReportListener) unsubscribeReportListener();

    loadingReportText.classList.remove('hidden');
    noDataReportText.classList.add('hidden');
    reportTable.classList.add('hidden');
    reportTableBody.innerHTML = '';
    exportReportBtn.classList.add('hidden');

    const startDate = startDateInput.value;
    const endDate = endDateInput.value;
    const selectedFaculty = facultyFilterSelect.value;

    if (!startDate || !endDate) {
        alert("Mohon pilih tanggal mulai dan tanggal akhir.");
        loadingReportText.classList.add('hidden');
        return;
    }

    // Bangun URL
    const params = new URLSearchParams({
        start_date: startDate,
        end_date: endDate,
        fakultas: selectedFaculty,
    });
    const url = `/api/data/laporan?${params.toString()}`;

    const data = await fetchData(url);

    if (!data) {
        loadingReportText.classList.add('hidden');
        noDataReportText.classList.remove('hidden');
        reportTable.classList.add('hidden');
        exportReportBtn.classList.add('hidden');
        return;
    }

    // 1. Tampilkan Summary & Achievements
    displaySummaryStatistics(data.summary);
    // Gabungkan data untuk achievements
    displayAchievements({
        ...data.achievements,
        monthly_reduction_kg: data.summary.monthly_reduction_kg 
    });

    // 2. Tampilkan data tabel
    currentReportData = data.report_data; // Simpan data di global
    
    if (currentReportData.length === 0) {
        loadingReportText.classList.add('hidden');
        noDataReportText.classList.remove('hidden');
        reportTable.classList.add('hidden');
        exportReportBtn.classList.add('hidden');
    } else {
        loadingReportText.classList.add('hidden');
        noDataReportText.classList.remove('hidden');
        reportTable.classList.remove('hidden');
        exportReportBtn.classList.remove('hidden');
        // Panggil fungsi sort & render untuk menampilkan data
        sortAndRenderData();
    }
}

// ==============================
// === EXPORT EXCEL (Tidak berubah) ===
// ==============================
async function exportReport() {
    if (currentReportData.length === 0) {
        alert("Tidak ada data untuk diexport.");
        return;
    }

    // Ambil data yang sudah disortir saat ini
    const sortedData = [...currentReportData].sort((a, b) => {
        let valA, valB;
        if (currentSortKey === 'timestamp') { valA = a.timestamp; valB = b.timestamp; }
        else if (currentSortKey === 'Berat (kg)') { valA = parseFloat(a['Berat (kg)']) || 0; valB = parseFloat(b['Berat (kg)']) || 0; }
        else { valA = a[currentSortKey] || ''; valB = b[currentSortKey] || ''; }
        if (valA < valB) return currentSortDirection === 'asc' ? -1 : 1;
        if (valA > valB) return currentSortDirection === 'asc' ? 1 : -1;
        return 0;
    });
    
    const dataToExport = sortedData.map(item => ({
        Tanggal: item.Tanggal,
        Hari: item.Hari,
        Waktu: item.Waktu,
        Fakultas: item.Fakultas,
        'Jenis Sampah': item['Jenis Sampah'],
        'Berat (kg)': item['Berat (kg)'],
    }));

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.json_to_sheet(dataToExport);
    XLSX.utils.book_append_sheet(wb, ws, "Laporan Sampah");
    XLSX.writeFile(wb, "Laporan_Sampah.xlsx");
}

// ==============================
// === INIT PAGE ===
// ==============================
export function initLaporanPage() {
    console.log("Laporan Page Initialized (SQL Mode)");

    updateCurrentDate('current-date');
    updateGlobalStatCards(); // Panggil data global

    startDateInput = document.getElementById('start-date');
    endDateInput = document.getElementById('end-date');
    facultyFilterSelect = document.getElementById('faculty-filter');
    generateReportBtn = document.getElementById('generate-report-btn');
    reportResultsDiv = document.getElementById('report-results');
    loadingReportText = document.getElementById('loading-report');
    noDataReportText = document.getElementById('no-data-report');
    reportTable = reportResultsDiv.querySelector('table');
    reportTableBody = document.getElementById('report-table-body');
    exportReportBtn = document.getElementById('export-report-btn');
    
    co2ReductionSpan = document.getElementById('co2-reduction');
    monthlyReductionSpan = document.getElementById('monthly-reduction');
    monthlyTotalSpan = document.getElementById('monthly-total');
    achievementsListUl = document.getElementById('achievements-list');

    // Default date range
    if (!startDateInput.value) {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        startDateInput.valueAsDate = firstDay;
    }
    if (!endDateInput.value) endDateInput.valueAsDate = new Date();

    generateReportBtn.addEventListener('click', fetchAndDisplayReportData);
    exportReportBtn.addEventListener('click', exportReport);
    
    // Listener untuk sorting (tidak berubah)
    document.querySelectorAll('.sortable-header').forEach(header => {
        header.addEventListener('click', () => {
            const sortKey = header.getAttribute('data-sort-key');
            if (currentSortKey === sortKey) {
                currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortKey = sortKey;
                currentSortDirection = (sortKey === 'timestamp') ? 'desc' : 'asc';
            }
            sortAndRenderData();
        });
    });

    // Panggil data saat halaman dimuat
    fetchAndDisplayReportData();
    
    // Hapus setupGlobalSampahListener
}
