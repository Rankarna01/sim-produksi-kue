let searchTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
    loadDataRak();
    // Otomatis fokus ke kotak pencarian saat halaman dimuat
    document.getElementById('search-rak').focus();
});

function cariRak() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadDataRak();
    }, 300);
}

// FUNGSI BARU UNTUK SCANNER BARCODE
async function scanBarcode(e) {
    // Scanner barcode akan otomatis menekan tombol "Enter" setelah selesai membaca kode
    if (e.key === 'Enter') {
        e.preventDefault();
        const val = document.getElementById('search-rak').value.trim();
        if(!val) return;

        // Loading indicator
        Swal.fire({ title: 'Mencari Rak...', showConfirmButton: false, allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const res = await fetchAjax(`logic.php?action=scan_rack&code=${val}`, 'GET');
        
        if (res.status === 'success' && res.data) {
            Swal.close();
            // Langsung buka detail rak jika ketemu!
            bukaDetail(res.data.id, res.data.name);
            document.getElementById('search-rak').value = ''; // Bersihkan input untuk scan berikutnya
        } else {
            Swal.fire('Tidak Ditemukan!', 'Kode/Barcode rak tersebut belum terdaftar di sistem.', 'error');
            document.getElementById('search-rak').value = ''; 
        }
    }
}

async function loadDataRak() {
    const grid = document.getElementById('grid-rak');
    const search = document.getElementById('search-rak').value;
    
    grid.innerHTML = '<div class="col-span-full p-10 text-center text-slate-400"><i class="fa-solid fa-circle-notch fa-spin text-2xl"></i> Memuat data rak...</div>';
    
    const res = await fetchAjax(`logic.php?action=read_racks&search=${search}`, 'GET');
    
    if (res.status === 'success') {
        document.getElementById('total-rak').innerText = res.data.length;
        let html = '';
        
        if(res.data.length === 0){
            html = '<div class="col-span-full p-10 bg-white rounded-3xl border border-slate-200 text-center font-bold text-slate-400 italic">Rak tidak ditemukan.</div>';
        } else {
            res.data.forEach(item => {
                const totalStok = parseFloat(item.total_stock);
                html += `
                <div onclick="bukaDetail(${item.id}, '${item.name}')" class="bg-white border border-slate-200 rounded-3xl p-5 hover:shadow-lg hover:border-blue-300 transition-all cursor-pointer group relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fa-solid fa-server text-6xl text-blue-600"></i>
                    </div>
                    <div class="flex justify-between items-start mb-4">
                        <h4 class="text-xl font-black text-blue-600 uppercase tracking-widest bg-blue-50 px-3 py-1 rounded-lg">${item.name}</h4>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-slate-500">Total Barang: <span class="text-slate-800">${item.total_items} Jenis</span></p>
                        <p class="text-xs font-bold text-slate-500">Total Stok: <span class="text-slate-800">${totalStok}</span></p>
                    </div>
                </div>
                `;
            });
        }
        grid.innerHTML = html;
    }
}

async function bukaDetail(id, name) {
    window.dispatchEvent(new CustomEvent('open-detail', {
        detail: { id: id, name: name }
    }));

    const tbody = document.getElementById('detail-barang');
    tbody.innerHTML = '<tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';

    const res = await fetchAjax(`logic.php?action=read_detail&rack_id=${id}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        let countItem = res.data.length;
        let countStok = 0;

        if (countItem === 0) {
            html = '<tr><td colspan="7" class="p-10 text-center text-slate-400 italic">Rak ini masih kosong. Belum ada barang yang ditugaskan ke sini.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                const stokVal = parseFloat(item.stock);
                countStok += stokVal; 
                let expText = item.expiry_date ? item.expiry_date : '-';
                
                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-5 text-center text-slate-400 font-mono text-xs">${idx + 1}</td>
                        <td class="p-5 font-mono text-blue-600 text-xs">${item.sku_code}</td>
                        <td class="p-5 font-black text-slate-700 uppercase">${item.material_name}</td>
                        <td class="p-5 text-center text-slate-500 text-xs">${item.category_name || '-'}</td>
                        <td class="p-5 text-center font-black text-slate-800 text-lg">${stokVal}</td>
                        <td class="p-5 text-center text-slate-500 font-semibold">${item.unit}</td>
                        <td class="p-5 text-center text-slate-500 text-xs">${expText}</td>
                    </tr>
                `;
            });
        }
        
        document.getElementById('detail-total-item').innerText = countItem;
        document.getElementById('detail-total-stok').innerText = countStok;
        tbody.innerHTML = html;
    }
}