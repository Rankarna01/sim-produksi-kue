document.addEventListener("DOMContentLoaded", () => {
    loadDashboard();
});

let myChart = null; // Simpan instance chart

async function loadDashboard() {
    // Beri efek loading di kotak aktivitas terakhir saat mengambil data
    document.getElementById('recent-activities').innerHTML = '<p class="text-center text-sm text-secondary py-4"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat aktivitas...</p>';

    const res = await fetchAjax('logic.php?action=dashboard_data', 'GET');
    
    if (res.status === 'success') {
        // 1. Isi Angka Ringkasan (KPI)
        document.getElementById('stat-total').innerText = res.stats.total;
        document.getElementById('stat-pending').innerText = res.stats.pending;
        document.getElementById('stat-valid').innerText = res.stats.valid;

        // 2. Render Chart Lingkaran (Doughnut)
        renderChart(res.stats.pending, res.stats.valid);

        // 3. Render 5 Log Aktivitas Terakhir
        renderRecent(res.recent);
    } else {
        document.getElementById('recent-activities').innerHTML = '<p class="text-center text-sm text-danger py-4">Gagal memuat data aktivitas.</p>';
    }
}

function renderChart(pending, valid) {
    const ctx = document.getElementById('kpiChart').getContext('2d');
    
    // Hancurkan chart lama jika ada (agar tidak error tumpang tindih saat refresh)
    if(myChart != null){
        myChart.destroy();
    }

    // Jika tidak ada data sama sekali, tampilkan chart abu-abu (kosong)
    let dataValues = [pending, valid];
    let bgColors = ['#F59E0B', '#10B981']; // Warna Accent(Kuning/Pending) & Success(Hijau/Valid)
    
    if(pending == 0 && valid == 0) {
        dataValues = [1];
        bgColors = ['#E2E8F0']; // Slate 200 (Abu-abu)
    }

    myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: (pending == 0 && valid == 0) ? ['Belum ada Produksi'] : ['Pending', 'Masuk Gudang (Valid)'],
            datasets: [{
                data: dataValues,
                backgroundColor: bgColors,
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%', // Ketebalan donat agar terlihat modern
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

function renderRecent(data) {
    const container = document.getElementById('recent-activities');
    let html = '';
    
    if (data.length === 0) {
        html = '<div class="text-center py-6 text-secondary"><i class="fa-solid fa-box-open text-3xl text-slate-200 mb-2 block"></i><span class="text-sm font-medium">Belum ada aktivitas hari ini.</span></div>';
    } else {
        data.forEach(item => {
            // Format waktu menjadi 10:45
            const time = new Date(item.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
            
            let icon = '';
            let statusText = '';
            
            // Tentukan Ikon dan Warna berdasarkan Status Produksi
            if (item.status === 'pending') {
                icon = '<div class="w-10 h-10 rounded-full bg-accent/20 text-accent flex items-center justify-center shrink-0"><i class="fa-solid fa-clock"></i></div>';
                statusText = 'Menunggu Scan Gudang';
            } else if (item.status === 'ditolak') {
                icon = '<div class="w-10 h-10 rounded-full bg-danger/20 text-danger flex items-center justify-center shrink-0"><i class="fa-solid fa-xmark"></i></div>';
                statusText = 'Ditolak / Revisi';
            } else if (item.status === 'expired') {
                icon = '<div class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center shrink-0"><i class="fa-solid fa-ban"></i></div>';
                statusText = 'Expired / Rusak';
            } else {
                icon = '<div class="w-10 h-10 rounded-full bg-success/20 text-success flex items-center justify-center shrink-0"><i class="fa-solid fa-check"></i></div>';
                statusText = 'Selesai Validasi';
            }
            
            // Render HTML per baris
            html += `
                <div class="flex items-center gap-4 p-3 border border-slate-100 rounded-xl hover:bg-slate-50 transition-colors">
                    ${icon}
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-slate-800">${item.quantity} Pcs ${item.name}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">${statusText}</p>
                    </div>
                    <div class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded">${time}</div>
                </div>
            `;
        });
    }
    container.innerHTML = html;
}