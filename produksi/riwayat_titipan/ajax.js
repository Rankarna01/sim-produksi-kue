let masterProducts = [];

document.addEventListener("DOMContentLoaded", () => {
    initFilter();
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    loadRiwayat();
});

async function initFilter() {
    const res = await fetchAjax('logic.php?action=init_filter', 'GET');
    if (res.status === 'success') {
        let optWarehouse = '<option value="">Semua Store</option>';
        res.warehouses.forEach(w => optWarehouse += `<option value="${w.id}">${w.name}</option>`);
        document.getElementById('filter_store').innerHTML = optWarehouse;
    }
}

document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault();
    loadRiwayat();
});

function resetFilter() {
    document.getElementById('formFilter').reset();
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    loadRiwayat();
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    const day = String(date.getDate()).padStart(2, '0');
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"];
    return `${day} ${monthNames[date.getMonth()]} ${date.getFullYear()}<br><span class="text-[10px] text-slate-400 font-bold">${String(date.getHours()).padStart(2, '0')}.${String(date.getMinutes()).padStart(2, '0')} WIB</span>`;
}

async function loadRiwayat() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="8" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';

    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    const warehouse_id = document.getElementById('filter_store').value;
    const status = document.getElementById('filter_status').value;

    const url = `logic.php?action=read&start_date=${start_date}&end_date=${end_date}&warehouse_id=${warehouse_id}&status=${status}`;
    
    try {
        const res = await fetchAjax(url, 'GET');
        if (res.status === 'success') {
            let html = '';
            if (res.data.length === 0) {
                html = '<tr><td colspan="8" class="p-10 text-center text-slate-400 italic">Tidak ada data riwayat titipan ditemukan.</td></tr>';
            } else {
                res.data.forEach((item, index) => {
                    let badgeStatus = '';
                    if(item.status === 'pending') badgeStatus = '<span class="bg-amber-50 text-amber-600 border border-amber-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-clock mr-1"></i> Pending</span>';
                    else if(item.status === 'received') badgeStatus = '<span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-check mr-1"></i> Valid</span>';
                    else if(item.status === 'ditolak') badgeStatus = '<span class="bg-rose-50 text-rose-600 border border-rose-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-rotate-left mr-1"></i> Ditolak</span>';
                    else badgeStatus = '<span class="bg-slate-100 text-slate-500 border border-slate-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-ban mr-1"></i> Dibatalkan</span>';

                    let btnAction = '';
                    if(item.status === 'pending') {
                        btnAction = `<button onclick="batalkanData(${item.id})" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center transition-all shadow-sm" title="Batalkan"><i class="fa-solid fa-trash text-xs"></i></button>`;
                    } else if (item.status === 'ditolak') {
                        btnAction = `<button onclick="bukaRevisi(${item.id})" class="w-auto px-3 h-8 rounded-lg bg-amber-500 text-white hover:bg-amber-600 flex items-center justify-center transition-all shadow-sm font-bold text-[10px] uppercase tracking-widest" title="Edit/Revisi"><i class="fa-solid fa-pen mr-1"></i> Revisi</button>`;
                    }

                    html += `
                        <tr class="hover:bg-slate-50 transition-colors ${item.status === 'cancelled' ? 'opacity-60' : ''}">
                            <td class="p-4 sm:p-5 text-center font-mono text-slate-400 text-xs">${index + 1}</td>
                            <td class="p-4 sm:p-5">${formatDate(item.created_at)}</td>
                            <td class="p-4 sm:p-5 font-black text-slate-800">${item.invoice_no}</td>
                            <td class="p-4 sm:p-5"><span class="font-bold text-blue-700">${item.karyawan}</span><br><span class="text-[10px] font-bold text-slate-400"><i class="fa-solid fa-shop mr-1"></i> ${item.dapur || 'Pusat'}</span></td>
                            <td class="p-4 sm:p-5 text-xs text-slate-600 leading-relaxed max-w-xs truncate" title="${item.product_list}">${item.product_list}</td>
                            <td class="p-4 sm:p-5 text-center font-black text-blue-600 text-lg">${item.total_pcs}</td>
                            <td class="p-4 sm:p-5 text-center">${badgeStatus}</td>
                            <td class="p-4 sm:p-5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="cetakStruk(${item.id})" class="w-8 h-8 rounded-lg bg-slate-800 text-white hover:bg-slate-700 flex items-center justify-center transition-all shadow-sm" title="Cetak"><i class="fa-solid fa-print text-xs"></i></button>
                                    ${btnAction}
                                </div>
                            </td>
                        </tr>
                    `;
                });
            }
            tbody.innerHTML = html;
        }
    } catch (e) { console.error(e); }
}

function cetakStruk(id) { window.open(`../input_titipan/print.php?id=${id}`, 'CetakStrukTitipan', 'width=400,height=600'); }

function batalkanData(id) {
    Swal.fire({
        title: 'Batalkan Transaksi?',
        text: "Stok barang titipan akan dikembalikan ke UMKM.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Batalkan!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
            const formData = new FormData();
            formData.append('action', 'cancel');
            formData.append('id', id);
            const res = await fetchAjax('logic.php', 'POST', formData);
            if (res.status === 'success') { Swal.fire('Dibatalkan!', res.message, 'success'); loadRiwayat(); } 
            else { Swal.fire('Gagal!', res.message, 'error'); }
        }
    });
}

// ==========================================
// LOGIKA REVISI
// ==========================================
async function bukaRevisi(id) {
    Swal.fire({ title: 'Memuat Data...', didOpen: () => Swal.showLoading() });
    
    const res = await fetchAjax(`logic.php?action=get_revisi_data&id=${id}`, 'GET');
    if (res.status === 'success') {
        masterProducts = res.master;
        
        document.getElementById('rev_prod_id').value = res.header.id;
        document.getElementById('rev_invoice').innerText = res.header.invoice_no;
        document.getElementById('rev_emp_name').value = res.header.emp_name;
        
        const container = document.getElementById('rev-product-container');
        container.innerHTML = '';
        
        res.details.forEach(d => {
            // Karena ini refund, stok yg tersedia = stok sekarang + qty yang dibooking sblmnya
            const prodMaster = masterProducts.find(p => p.id == d.id);
            const maxStok = prodMaster ? parseInt(prodMaster.stok) + parseInt(d.quantity) : d.quantity;
            addRevRow(d.id, d.name, d.code, d.quantity, maxStok);
        });
        
        Swal.close();
        document.getElementById('modal-revisi').classList.remove('hidden');
    }
}

function tutupModalRevisi() {
    document.getElementById('modal-revisi').classList.add('hidden');
}

function addRevRow(id = '', name = '', code = '', qty = '', max = '') {
    const container = document.getElementById('rev-product-container');
    const displayValue = name ? `[${code}] ${name}` : '';
    
    const rowHTML = `
        <div class="product-row bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 items-start relative">
            <div class="flex-1 w-full relative dropdown-wrapper">
                <label class="block text-[11px] font-bold text-slate-400 mb-1 uppercase">Cari & Pilih Barang <span class="text-danger">*</span></label>
                <div class="relative">
                    <input type="text" value="${displayValue}" class="search-input w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-amber-500 outline-none text-sm font-semibold text-slate-700" placeholder="Ketik nama UMKM atau barang..." onfocus="showDropdown(this)" oninput="filterDropdown(this)" autocomplete="off" required>
                    <input type="hidden" name="product_id[]" value="${id}" class="hidden-id" required>
                    <ul class="custom-dropdown custom-scrollbar absolute z-50 w-full bg-white border border-slate-200 shadow-xl rounded-xl mt-1 max-h-48 overflow-y-auto hidden"></ul>
                </div>
            </div>
            <div class="w-full md:w-32">
                <label class="block text-[11px] font-bold text-slate-400 mb-1 uppercase">Jumlah <span class="text-danger">*</span></label>
                <input type="number" name="quantity[]" value="${qty}" required min="1" max="${max}" class="w-full px-3 py-3 border-2 border-slate-200 rounded-xl focus:border-amber-500 outline-none font-black text-amber-600 text-center text-lg" placeholder="${max ? 'Maks: '+max : '0'}">
            </div>
            <div class="w-full md:w-auto md:self-end">
                <button type="button" onclick="removeRevRow(this)" title="Hapus Baris" class="w-full md:w-12 h-[52px] bg-danger/10 hover:bg-danger text-danger hover:text-white rounded-xl flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', rowHTML);
}

function removeRevRow(button) {
    const container = document.getElementById('rev-product-container');
    if (container.children.length > 1) { button.closest('.product-row').remove(); } 
    else { alert("Minimal harus ada 1 barang untuk direvisi."); }
}

// DROPDOWN LOGIC KHUSUS REVISI
function renderDropdownList(wrapper, productList) {
    const ul = wrapper.querySelector('.custom-dropdown');
    ul.innerHTML = ''; 
    if(productList.length === 0) {
        ul.innerHTML = '<li class="p-3 text-sm text-slate-500 text-center">Stok Habis / Tidak ditemukan</li>';
    } else {
        productList.forEach(p => {
            const li = document.createElement('li');
            li.className = "p-3 hover:bg-amber-50 cursor-pointer border-b border-slate-50 text-sm font-semibold text-slate-700";
            li.innerHTML = `<span class="text-amber-600 font-bold text-xs mr-2">[${p.code}]</span> ${p.name} <span class="text-xs text-slate-400 float-right">Stok: ${p.stok}</span>`;
            
            li.onclick = function() {
                const hiddenInput = wrapper.querySelector('.hidden-id');
                const isDuplicate = Array.from(document.querySelectorAll('.hidden-id')).some(inp => inp !== hiddenInput && inp.value == p.id);
                if (isDuplicate) { alert("Produk ini sudah ada di daftar!"); return; }

                wrapper.querySelector('.search-input').value = `[${p.code}] ${p.name}`;
                hiddenInput.value = p.id;
                
                const qtyInput = wrapper.parentElement.querySelector('input[name="quantity[]"]');
                qtyInput.max = p.stok;
                qtyInput.placeholder = `Maks: ${p.stok}`;
                
                ul.classList.add('hidden'); 
            };
            ul.appendChild(li);
        });
    }
}

function showDropdown(inputElement) {
    const wrapper = inputElement.closest('.dropdown-wrapper');
    renderDropdownList(wrapper, masterProducts);
    wrapper.querySelector('.custom-dropdown').classList.remove('hidden');
}

function filterDropdown(inputElement) {
    const wrapper = inputElement.closest('.dropdown-wrapper');
    wrapper.querySelector('.hidden-id').value = '';
    const keyword = inputElement.value.toLowerCase();
    const filtered = masterProducts.filter(p => p.name.toLowerCase().includes(keyword) || p.code.toLowerCase().includes(keyword));
    renderDropdownList(wrapper, filtered);
    wrapper.querySelector('.custom-dropdown').classList.remove('hidden');
}

function closeAllDropdowns(event) {
    if (!event.target.matches('.search-input')) {
        document.querySelectorAll('.custom-dropdown').forEach(ul => ul.classList.add('hidden'));
    }
}

async function submitRevisi() {
    const form = document.getElementById('formRevisi');
    const hiddenInputs = form.querySelectorAll('.hidden-id');
    for (let input of hiddenInputs) {
        if (!input.value) { alert("Pilih barang dari daftar dropdown!"); return; }
    }

    const employeeName = document.getElementById('rev_emp_name').value;
    
    const { value: pin } = await Swal.fire({
        title: 'Otorisasi Revisi',
        html: `Masukkan <b>PIN Rahasia</b> untuk otorisasi oleh:<br><span class="text-amber-600 font-bold mt-2 inline-block">${employeeName}</span>`,
        input: 'password',
        inputAttributes: { maxlength: 4, autocapitalize: 'off' },
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        confirmButtonText: 'Kirim Revisi',
        inputValidator: (value) => { if (!value || !/^\d{4}$/.test(value)) return 'PIN harus 4 Angka!'; }
    });

    if (pin) {
        Swal.fire({ title: 'Menyimpan Revisi...', didOpen: () => Swal.showLoading() });
        const formData = new FormData(form);
        formData.append('action', 'revisi');
        formData.append('pin', pin);

        try {
            const res = await fetchAjax('logic.php', 'POST', formData);
            if (res.status === 'success') {
                tutupModalRevisi();
                Swal.fire('Berhasil!', res.message, 'success');
                loadRiwayat();
            } else { Swal.fire('Gagal!', res.message, 'error'); }
        } catch(e) { Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error'); }
    }
}