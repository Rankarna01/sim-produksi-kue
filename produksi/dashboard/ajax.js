document.addEventListener("DOMContentLoaded", () => {
    loadDashboard();
});

let myChart = null; // Simpan instance chart

async function loadDashboard() {
    const res = await fetchAjax('logic.php?action=dashboard_data', 'GET');
    
    if (res.status === 'success') {
        // 1. Isi Angka Stat
        document.getElementById('stat-total').innerText = res.stats.total;
        document.getElementById('stat-pending').innerText = res.stats.pending;
        document.getElementById('stat-valid').innerText = res.stats.valid;

        // 2. Render Chart Lingkaran (Doughnut)
        renderChart(res.stats.pending, res.stats.valid);

        // 3. Render Log Terakhir
        renderRecent(res.recent);
    }
}

function renderChart(pending, valid) {
    const ctx = document.getElementById('kpiChart').getContext('2d');
    
    // Hancurkan chart lama jika ada (agar tidak tumpang tindih saat refresh)
    if(myChart != null){
        myChart.destroy();
    }

    // Jika tidak ada data sama sekali, tampilkan abu-abu
    let dataValues = [pending, valid];
    let bgColors = ['#F59E0B', '#10B981']; // Warna Accent(Kuning) & Success(Hijau) sesuai tema kita
    
    if(pending == 0 && valid == 0) {
        dataValues = [1];
        bgColors = ['#E2E8F0']; // Slate 200 (Abu-abu kosong)
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
            cutout: '75%', // Ketebalan donat
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
        html = '<p class="text-center text-sm text-secondary py-4">Belum ada aktivitas hari ini.</p>';
    } else {
        data.forEach(item => {
            const time = new Date(item.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
            const icon = item.status === 'pending' 
                ? '<div class="w-10 h-10 rounded-full bg-accent/20 text-accent flex items-center justify-center"><i class="fa-solid fa-clock"></i></div>'
                : '<div class="w-10 h-10 rounded-full bg-success/20 text-success flex items-center justify-center"><i class="fa-solid fa-check"></i></div>';
            
            html += `
                <div class="flex items-center gap-4 p-3 border border-slate-100 rounded-xl hover:bg-slate-50 transition-colors">
                    ${icon}
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-slate-800">${item.quantity} Pcs ${item.name}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">${item.status === 'pending' ? 'Menunggu Scan' : 'Selesai Validasi'}</p>
                    </div>
                    <div class="text-xs font-bold text-slate-400">${time}</div>
                </div>
            `;
        });
    }
    container.innerHTML = html;
}