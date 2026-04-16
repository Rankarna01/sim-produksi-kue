function getTodayLocal() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

document.addEventListener("DOMContentLoaded", async () => {
    await loadFilterGudang();
    
    const today = getTodayLocal();
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    loadHistory();
});

async function loadFilterGudang() {
    try {
        const response = await fetchAjax('logic.php?action=init_filter', 'GET');
        if (response.status === 'success') {
            
            // Render Dropdown Store (Jika Role bukan admin)
            const selectStore = document.getElementById('warehouse_id');
            if(selectStore && selectStore.tagName === 'SELECT') {
                let options = '<option value="">Semua Store</option>';
                response.warehouses.forEach(w => {
                    options += `<option value="${w.id}">Store: ${w.name}</option>`;
                });
                selectStore.innerHTML = options;
            }

            // Render Dropdown Dapur
            const selectKitchen = document.getElementById('kitchen_id');
            if(selectKitchen) {
                let optKitchen = '<option value="">Semua Dapur</option>';
                response.kitchens.forEach(k => {
                    optKitchen += `<option value="${k.id}">${k.name}</option>`;
                });
                selectKitchen.innerHTML = optKitchen;
            }
        }
    } catch (e) {
        console.error("Gagal memuat filter data", e);
    }
}

document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault();
    loadHistory();
});

function resetFilter() {
    document.getElementById('formFilter').reset();
    
    const today = getTodayLocal();
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    const store = document.getElementById('warehouse_id');
    if(store && store.tagName === 'SELECT') store.value = '';
    document.getElementById('kitchen_id').value = '';
    
    loadHistory();
}

async function loadHistory() {
    const tbody = document.getElementById('table-history');
    tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const warehouseId = document.getElementById('warehouse_id').value; 
    const kitchenId = document.getElementById('kitchen_id').value; 
    
    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&warehouse_id=${warehouseId}&kitchen_id=${kitchenId}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="6" class="p-8 text-center text-secondary font-medium">Tidak ada riwayat validasi pada filter tersebut.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const dateObj = new Date(item.updated_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                
                html += `
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4">
                            <div class="font-bold text-slate-700">${tgl}</div>
                            <div class="text-xs text-secondary font-mono">${waktu} WIB</div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800">${item.produk}</div>
                            <div class="text-xs text-slate-500 font-mono tracking-wider">${item.invoice_no}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2 py-1 rounded inline-block">
                                <i class="fa-solid fa-fire-burner mr-1 text-slate-400"></i> ${item.asal_dapur || '-'}
                            </div>
                        </td>
                        <td class="p-4 text-center">
                            <span class="bg-success/10 text-success border border-success/20 px-3 py-1 rounded-full font-bold shadow-sm inline-block transform group-hover:scale-110 transition-transform">
                                + ${new Intl.NumberFormat('id-ID').format(item.quantity)}
                            </span>
                        </td>
                        <td class="p-4 font-semibold text-slate-600">
                            <i class="fa-solid fa-store text-slate-400 mr-2"></i>Store ${item.gudang}
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    } else {
        tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-danger font-bold"><i class="fa-solid fa-triangle-exclamation mr-2"></i> ${response.message}</td></tr>`;
    }
}