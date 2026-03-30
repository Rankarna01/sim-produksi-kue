document.addEventListener("DOMContentLoaded", () => {
    loadDashboard();
});

let trendChart = null;

async function loadDashboard() {
    const response = await fetchAjax('logic.php', 'GET');
    
    if (response && response.status === 'success') {
        // 1. Tampilkan Angka KPI
        document.getElementById('stat-produksi').innerHTML = response.stats.produksi + ' <span class="text-sm font-semibold text-slate-400">Pcs</span>';
        document.getElementById('stat-bahan').innerHTML = response.stats.bahan_kritis + ' <span class="text-sm font-semibold text-slate-400">Item</span>';
        document.getElementById('stat-produk').innerHTML = response.stats.produk + ' <span class="text-sm font-semibold text-slate-400">Macam</span>';
        document.getElementById('stat-user').innerHTML = response.stats.user + ' <span class="text-sm font-semibold text-slate-400">Akun</span>';

        // 2. Render Grafik Tren 7 Hari
        renderChart(response.chart);

        // 3. Render Aktivitas Terakhir
        renderRecent(response.recent);
    }
}

// Fungsi Render Bar Chart
function renderChart(chartData) {
    const ctx = document.getElementById('trendChart').getContext('2d');
    
    if (trendChart != null) {
        trendChart.destroy();
    }

    // Siapkan wadah array kosong
    const labels = [];
    const dataPoints = [];

    // Jika ada data, masukkan ke array
    if (chartData.length > 0) {
        chartData.forEach(item => {
            // Ubah format tanggal SQL ke format singkat (misal: 15 Aug)
            const dateObj = new Date(item.tgl);
            const shortDate = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            
            labels.push(shortDate);
            dataPoints.push(item.total);
        });
    } else {
        // Jika kosong sama sekali (belum ada produksi)
        labels.push('Belum ada data');
        dataPoints.push(0);
    }

    trendChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Produksi (Pcs)',
                data: dataPoints,
                backgroundColor: '#3b82f6', // Warna Primary (Biru Tailwind)
                borderRadius: 6, // Ujung bar tumpul biar elegan
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            },
            plugins: {
                legend: { display: false } // Sembunyikan legenda atas karena sudah jelas
            }
        }
    });
}

// Fungsi Render Daftar Aktivitas
function renderRecent(data) {
    const container = document.getElementById('recent-activities');
    let html = '';
    
    if (data.length === 0) {
        html = '<p class="text-center text-sm text-secondary py-4">Belum ada aktivitas.</p>';
    } else {
        data.forEach(item => {
            const timeObj = new Date(item.created_at);
            const waktu = timeObj.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
            const tgl = timeObj.toLocaleDateString('id-ID', {day: '2-digit', month: 'short'});
            
            html += `
                <div class="flex items-start gap-3 p-3 border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors rounded-xl">
                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-1">
                        <i class="fa-solid fa-cake-candles"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-slate-800">${item.quantity} Pcs ${item.name}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Oleh: <span class="font-semibold text-slate-700">${item.karyawan}</span></p>
                    </div>
                    <div class="text-[10px] font-bold text-slate-400 text-right">
                        <div>${waktu}</div>
                        <div>${tgl}</div>
                    </div>
                </div>
            `;
        });
    }
    container.innerHTML = html;
}