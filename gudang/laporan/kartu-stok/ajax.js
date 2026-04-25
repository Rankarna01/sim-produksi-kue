let currentPage = 1;
let searchTimeout = null;

document.addEventListener("DOMContentLoaded", async () => {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    await initFilter();
    loadData(1);
});

async function initFilter() {
    const res = await fetchAjax('logic.php?action=init', 'GET');
    if(res.status === 'success') {
        let opt = '<option value="">-- Ketik nama barang atau biarkan kosong untuk semua --</option>';
        res.materials.forEach(m => { opt += `<option value="${m.id}">[${m.sku_code}] ${m.material_name}</option>`; });
        document.getElementById('filter_material').innerHTML = opt;
    }
}

function updateTitleDanLoad() {
    const select = document.getElementById('filter_material');
    const title = document.getElementById('title_barang');
    if(select.value) {
        title.innerText = select.options[select.selectedIndex].text;
        title.className = "font-black text-blue-600";
    } else {
        title.innerText = "Semua Barang";
        title.className = "font-black text-slate-800";
    }
    loadData(1);
}

function formatTglTime(datetime) {
    const d = new Date(datetime);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'numeric', year: 'numeric' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function setFilterDate(type) {
    currentFilterDate = type;
    
    document.getElementById('btn-harian').className = "px-4 py-2 hover:bg-slate-50 transition-colors border-r border-slate-300";
    document.getElementById('btn-periode').className = "px-4 py-2 hover:bg-slate-50 transition-colors border-r border-slate-300";
    document.getElementById('btn-semua').className = "px-4 py-2 hover:bg-slate-50 transition-colors";
    
    document.getElementById(`btn-${type}`).className = "px-4 py-2 bg-blue-50 text-blue-600 font-black transition-colors border-r border-slate-300";

    const datePicker = document.getElementById('custom-date-filter');
    if (type === 'periode') {
        datePicker.classList.remove('hidden');
        datePicker.classList.add('flex');
    } else {
        datePicker.classList.add('hidden');
        datePicker.classList.remove('flex');
    }

    loadData(1);
}

function cariData() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { loadData(1); }, 500);
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="9" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const material_id = document.getElementById('filter_material').value;
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const url = `logic.php?action=read&page=${currentPage}&material_id=${material_id}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    const res = await fetchAjax(url, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="9" class="p-10 text-center text-slate-400 italic font-medium">Tidak ada pergerakan barang ditemukan.</td></tr>';
        } else {
            res.data.forEach((item) => {
                let badgeTipe = '';
                if(item.tipe === 'IN') badgeTipe = '<span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded font-black text-[10px]">IN</span>';
                else if(item.tipe === 'OUT') badgeTipe = '<span class="bg-rose-100 text-rose-700 px-2 py-1 rounded font-black text-[10px]">OUT</span>';
                else if(item.tipe === 'IN (Opname)') badgeTipe = '<span class="bg-purple-100 text-purple-700 px-2 py-1 rounded font-black text-[10px] tracking-tighter">IN (OPN)</span>';
                else if(item.tipe === 'OUT (Opname)') badgeTipe = '<span class="bg-fuchsia-100 text-fuchsia-700 px-2 py-1 rounded font-black text-[10px] tracking-tighter">OUT (OPN)</span>';

                let valMasuk = item.masuk > 0 ? `<span class="text-emerald-600 font-bold">+${parseFloat(item.masuk)}</span>` : '<span class="text-slate-300">-</span>';
                let valKeluar = item.keluar > 0 ? `<span class="text-rose-600 font-bold">-${parseFloat(item.keluar)}</span>` : '<span class="text-slate-300">-</span>';

                let notesInfo = item.notes || '-';
                if(item.ref && item.ref !== 'Manual') {
                    notesInfo += ` <span class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded text-[10px] ml-1">${item.ref}</span>`;
                }

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-xs font-bold text-slate-500">${formatTglTime(item.created_at)}</td>
                        <td class="p-4 text-xs font-black text-slate-800">${item.material_name}</td>
                        <td class="p-4 text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest">${item.unit}</td>
                        <td class="p-4 text-center">${badgeTipe}</td>
                        <td class="p-4 text-xs text-slate-600">${notesInfo}</td>
                        <td class="p-4 text-center">${valMasuk}</td>
                        <td class="p-4 text-center">${valKeluar}</td>
                        <td class="p-4 text-center font-black text-slate-800 text-sm">${parseFloat(item.saldo)}</td>
                        <td class="p-4 text-xs font-bold text-slate-500">${item.admin_name}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(res.total_pages, res.current_page);
    }
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination');
    let html = '';
    if (totalPages <= 1) { container.innerHTML = ''; return; }

    for (let i = 1; i <= totalPages; i++) {
        const active = i === current ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200';
        html += `<button onclick="loadData(${i})" class="w-8 h-8 rounded-lg font-black text-xs transition-all ${active}">${i}</button>`;
    }
    container.innerHTML = html;
}

function exportData(type) {
    const material_id = document.getElementById('filter_material').value;
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    let url = (type === 'pdf') ? 'export_pdf.php' : 'export_excel.php';
    url += `?material_id=${material_id}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    
    window.open(url, '_blank');
}