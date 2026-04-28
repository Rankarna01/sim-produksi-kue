document.addEventListener("DOMContentLoaded", () => {
    // Set default tanggal hari ini
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    initFilter();
});

async function initFilter() {
    const res = await fetchAjax('logic.php?action=init', 'GET');
    if (res.status === 'success') {
        const select = document.getElementById('kitchen_id');
        let opt = '<option value="semua">Semua Dapur</option>';
        res.kitchens.forEach(k => { opt += `<option value="${k.id}">${k.name}</option>`; });
        select.innerHTML = opt;
        
        loadData(); // Load data pertama kali
    }
}

async function loadData() {
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="8" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const kitchen = document.getElementById('kitchen_id').value;

    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&kitchen_id=${kitchen}`;
    const res = await fetchAjax(url, 'GET');

    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="8" class="p-10 text-center text-slate-400 italic font-bold">Tidak ada data rencana produksi pada filter ini.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                
                // Kalkulasi Persentase Pencapaian
                let target = parseFloat(item.target_qty);
                let actual = parseFloat(item.actual_qty);
                let persentase = (actual / target) * 100;
                let colorClass = 'bg-rose-500'; // Default Merah (Jauh dari target)
                
                if (persentase >= 100) { colorClass = 'bg-emerald-500'; persentase = 100; } // Tercapai / Over
                else if (persentase >= 80) { colorClass = 'bg-amber-500'; } // Hampir tercapai
                
                let sisaBadge = '';
                if (actual < target) {
                    sisaBadge = `<span class="text-[10px] text-rose-500 font-bold block mt-1">Kurang ${target - actual} Pcs</span>`;
                } else if (actual > target) {
                    sisaBadge = `<span class="text-[10px] text-emerald-500 font-bold block mt-1">Over ${actual - target} Pcs</span>`;
                } else {
                    sisaBadge = `<span class="text-[10px] text-blue-500 font-bold block mt-1">Sesuai Target</span>`;
                }

                const d = new Date(item.plan_date);
                const tgl = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const dapurName = item.kitchen_name ? item.kitchen_name : 'Dapur Tidak Terdaftar';

                html += `
                    <tr class="hover:bg-slate-50 transition-colors border-b border-slate-50">
                        <td class="p-5 text-center text-xs font-bold text-slate-400">${idx + 1}</td>
                        <td class="p-5 text-xs font-black text-slate-600">${tgl}</td>
                        <td class="p-5">
                            <div class="font-black text-blue-600 text-sm">${item.employee_name}</div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5"><i class="fa-solid fa-store mr-1 text-slate-400"></i> ${dapurName}</div>
                        </td>
                        <td class="p-5 font-black text-slate-800 uppercase text-xs">
                            ${item.product_name}<br>
                            <span class="text-[9px] text-slate-400 font-mono">[${item.product_code}]</span>
                        </td>
                        <td class="p-5 text-center font-bold text-slate-600 text-xs">
                            ${parseFloat(item.est_adonan_kg) > 0 ? parseFloat(item.est_adonan_kg) + ' Kg' : '-'}
                        </td>
                        <td class="p-5 text-center font-black text-amber-600 text-base">
                            ${item.target_qty}
                        </td>
                        <td class="p-5 text-center font-black text-emerald-600 text-base">
                            ${item.actual_qty}
                        </td>
                        <td class="p-5 w-48">
                            <div class="flex items-center justify-between text-[10px] font-black mb-1">
                                <span class="text-slate-500">Progress</span>
                                <span class="text-slate-700">${Math.round(persentase)}%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                <div class="${colorClass} h-2 rounded-full transition-all duration-1000" style="width: ${persentase}%"></div>
                            </div>
                            ${sisaBadge}
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}