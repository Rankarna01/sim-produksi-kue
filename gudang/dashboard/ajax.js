let trendChart = null;
let stockChart = null;

document.addEventListener("DOMContentLoaded", () => {
    loadDashboard();
});

async function loadDashboard() {
    try {
        const response = await fetch('logic.php?action=get_dashboard_data');
        const res = await response.json();
        if (res.status === 'success') {
            // Stats
            document.getElementById('stat-po').innerText = res.stats.po;
            document.getElementById('stat-req').innerText = res.stats.req;
            document.getElementById('stat-kritis').innerText = res.stats.kritis;
            document.getElementById('stat-hutang').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(res.stats.hutang);

            // Pengumuman
            if (res.pengumuman) {
                document.getElementById('text-pengumuman').innerText = res.pengumuman;
                document.getElementById('pengumuman-container').classList.remove('hidden');
                window.currentMsg = res.pengumuman;
            }

            // Tabel Permintaan
            let htmlReq = '';
            res.tables.reqs.forEach(item => {
                htmlReq += `<tr><td class='py-3'>${item.created_at}</td><td class='font-bold'>${item.material_name}</td><td class='text-center'>${parseFloat(item.qty)} ${item.unit}</td><td class='text-center'>${item.status}</td></tr>`;
            });
            document.getElementById('table-permintaan').innerHTML = htmlReq || '<tr><td colspan="4" class="text-center">Kosong</td></tr>';

            // Supplier
            let htmlSupp = '';
            res.tables.supps.forEach(s => {
                htmlSupp += `<div class='flex gap-3 p-3 bg-slate-50 rounded-xl'><div class='w-8 h-8 bg-blue-600 rounded-full text-white flex items-center justify-center font-bold'>${s.name[0]}</div><div><p class='text-xs font-bold'>${s.name}</p><p class='text-[10px] text-slate-400'>${s.phone}</p></div></div>`;
            });
            document.getElementById('list-supplier').innerHTML = htmlSupp;

            // Charts
            renderCharts(res.charts);
        }
    } catch (e) { console.error(e); }
}

function renderCharts(data) {
    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    if(trendChart) trendChart.destroy();
    trendChart = new Chart(ctxTrend, {
        type: 'bar',
        data: {
            labels: data.trend.labels,
            datasets: [
                { label: 'Masuk', data: data.trend.in, backgroundColor: '#2563eb', borderRadius: 5 },
                { label: 'Keluar', data: data.trend.out, backgroundColor: '#f43f5e', borderRadius: 5 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    const ctxStock = document.getElementById('stockChart').getContext('2d');
    if(stockChart) stockChart.destroy();
    stockChart = new Chart(ctxStock, {
        type: 'doughnut',
        data: {
            labels: ['Aman', 'Kritis', 'Habis'],
            datasets: [{ data: [data.stock.aman, data.stock.kritis, data.stock.habis], backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'] }]
        },
        options: { cutout: '70%', plugins: { legend: { position: 'bottom' } } }
    });
}

function bukaModalPengumuman() {
    document.getElementById('input_pengumuman').value = window.currentMsg || '';
    document.getElementById('modal-pengumuman').classList.remove('hidden');
}

function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

document.getElementById('formPengumuman')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('action', 'update_pengumuman');
    formData.append('pesan', document.getElementById('input_pengumuman').value);
    const response = await fetch('logic.php', { method: 'POST', body: formData });
    const res = await response.json();
    if(res.status === 'success') {
        Swal.fire('Berhasil', '', 'success');
        closeModal('modal-pengumuman');
        loadDashboard();
    }
});