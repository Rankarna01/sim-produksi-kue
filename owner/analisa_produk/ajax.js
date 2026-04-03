document.addEventListener("DOMContentLoaded", () => {
    applyQuickFilter(); 
});

document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('quick_filter').value = 'custom'; 
    loadAnalisa();
});

function applyQuickFilter() {
    const filterType = document.getElementById('quick_filter').value;
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    const today = new Date();
    let start = '';
    let end = '';

    if (filterType === 'today') {
        start = end = today.toISOString().split('T')[0];
    } 
    else if (filterType === 'this_week') {
        const first = today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1);
        const last = first + 6;
        const startDay = new Date(today.setDate(first));
        const endDay = new Date(today.setDate(last));
        start = startDay.toISOString().split('T')[0];
        end = endDay.toISOString().split('T')[0];
    } 
    else if (filterType === 'this_month') {
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        start = firstDay.toISOString().split('T')[0];
        end = lastDay.toISOString().split('T')[0];
    } 
    else if (filterType === 'this_year') {
        start = `${today.getFullYear()}-01-01`;
        end = `${today.getFullYear()}-12-31`;
    }

    if (filterType !== 'custom') {
        startDateInput.value = start;
        endDateInput.value = end;
        loadAnalisa(); 
    }
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

async function loadAnalisa() {
    const tbody = document.getElementById('table-analisa');
    tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memproses kalkulasi data...</td></tr>';
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    
    document.getElementById('print-periode').innerText = `Periode: ${start || 'Awal'} s/d ${end || 'Akhir'}`;

    const url = `logic.php?action=read&start_date=${start}&end_date=${end}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        
        // 1. UPDATE KARTU RINGKASAN
        document.getElementById('sum-produksi').innerHTML = `${formatNumber(response.summary.total_produksi)} <span class="text-sm font-semibold text-slate-500">Pcs</span>`;
        document.getElementById('sum-terbuang').innerHTML = `${formatNumber(response.summary.total_terbuang)} <span class="text-sm font-semibold text-danger/70">Pcs</span>`;
        document.getElementById('sum-persen').innerHTML = `${response.summary.loss_rate}%`;

        // 2. RENDER TABEL PERINGKAT
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="6" class="p-8 text-center text-secondary font-medium">Tidak ada data produksi atau penarikan pada periode ini.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                
                // Indikator Warna Berdasarkan Persentase Kerugian (Loss Rate)
                let statusBadge = '';
                let rankTrophy = index < 3 ? `<i class="fa-solid fa-trophy text-danger opacity-50 ml-1"></i>` : ''; // Top 3 paling rugi

                if (item.loss_rate >= 15) {
                    statusBadge = `<span class="bg-danger/10 text-danger border border-danger/20 px-3 py-1 rounded-full text-[11px] font-bold uppercase print:border-none print:text-black print:p-0"><i class="fa-solid fa-skull-crossbones mr-1"></i> Bahaya (Kurangi Prod)</span>`;
                } else if (item.loss_rate >= 5) {
                    statusBadge = `<span class="bg-amber-100 text-amber-600 border border-amber-200 px-3 py-1 rounded-full text-[11px] font-bold uppercase print:border-none print:text-black print:p-0"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Waspada</span>`;
                } else {
                    statusBadge = `<span class="bg-success/10 text-success border border-success/20 px-3 py-1 rounded-full text-[11px] font-bold uppercase print:border-none print:text-black print:p-0"><i class="fa-solid fa-check-circle mr-1"></i> Aman</span>`;
                }

                html += `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 transition-colors text-slate-700">
                        <td class="p-4 text-center font-black text-slate-400">#${index + 1} ${rankTrophy}</td>
                        <td class="p-4 font-bold text-slate-800 text-base">${item.produk}</td>
                        <td class="p-4 text-right font-semibold">${formatNumber(item.produksi)}</td>
                        <td class="p-4 text-right font-black text-danger text-lg print:text-black">${formatNumber(item.terbuang)}</td>
                        <td class="p-4 text-center font-bold ${item.loss_rate > 5 ? 'text-amber-600' : 'text-success'}">${item.loss_rate}%</td>
                        <td class="p-4 text-center">${statusBadge}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    } else {
        alert(response.message);
    }
}