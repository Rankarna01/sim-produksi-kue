let currentPage = 1;
let searchTimeout = null;

document.addEventListener("DOMContentLoaded", () => {
    loadComparison(); // Load Analitik Atas
    loadData(1);      // Load Grid Bawah
});

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function resetForm() {
    document.getElementById('formSupplier').reset();
    document.getElementById('id').value = '';
    document.getElementById('modal-title').innerText = 'Tambah Supplier';
}

function formatTgl(dateStr) {
    if(!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(parseFloat(angka) || 0);
}

// ==========================================
// 1. RENDER ANALITIK PERBANDINGAN HARGA
// ==========================================
async function loadComparison() {
    const tbody = document.getElementById('comparison-list');
    const res = await fetchAjax('logic.php?action=read_comparison', 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="3" class="p-10 text-center text-slate-400 font-bold italic">Belum ada data riwayat pembelian untuk dianalisis.</td></tr>';
        } else {
            res.data.forEach(item => {
                
                // Render List Penawaran Box
                let supplierBoxes = '';
                item.suppliers.forEach(sup => {
                    // Beri highlight hijau muda jika dia adalah harga terbaik
                    let isBest = (sup.price === item.best_price);
                    let boxClass = isBest ? 'bg-emerald-50 border-emerald-200' : 'bg-slate-50 border-slate-200';
                    
                    supplierBoxes += `
                        <div class="border ${boxClass} rounded-xl p-3 min-w-[140px] shadow-sm">
                            <p class="text-xs font-bold text-slate-700 truncate w-32" title="${sup.supplier_name}">${sup.supplier_name}</p>
                            <p class="text-sm font-black text-slate-800 my-1">${formatRupiah(sup.price)}</p>
                            <p class="text-[9px] text-slate-400 font-medium">Riwayat • ${formatTgl(sup.date)}</p>
                        </div>
                    `;
                });

                html += `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-5 align-top">
                            <h4 class="font-black text-slate-800 text-sm uppercase">${item.material_name}</h4>
                        </td>
                        <td class="p-5 align-top">
                            <p class="text-lg font-black text-emerald-600">${formatRupiah(item.best_price)}</p>
                            <p class="text-[10px] text-slate-500 font-bold mt-1">via ${item.best_supplier}</p>
                            <p class="text-[9px] text-slate-400 mt-1">Riwayat (${formatTgl(item.best_date)})</p>
                        </td>
                        <td class="p-5 align-top">
                            <div class="flex gap-3 flex-wrap">
                                ${supplierBoxes}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

// ==========================================
// 2. RENDER GRID MASTER SUPPLIER
// ==========================================
function cariData() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { loadData(1); }, 500);
}

async function loadData(page = 1) {
    currentPage = page;
    const container = document.getElementById('grid-supplier');
    container.innerHTML = '<div class="col-span-full p-12 text-center text-slate-400"><i class="fa-solid fa-circle-notch fa-spin text-3xl mb-3"></i><p>Memuat data...</p></div>';
    
    const search = document.getElementById('search').value;
    const response = await fetchAjax(`logic.php?action=read&search=${search}&page=${currentPage}`, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<div class="col-span-full p-10 text-center bg-white rounded-3xl border border-slate-200 text-slate-400 font-bold">Belum ada data supplier.</div>';
        } else {
            response.data.forEach((item) => {
                html += `
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col hover:border-blue-300 transition-all overflow-hidden">
                    <div class="p-5 flex-1">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-black text-slate-800 text-lg truncate pr-4" title="${item.name}">${item.name}</h4>
                            <div class="flex gap-1">
                                <button onclick='editData(${JSON.stringify(item).replace(/'/g, "&apos;")})' class="text-blue-500 hover:text-blue-700 p-1"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button onclick="deleteData(${item.id})" class="text-rose-400 hover:text-rose-600 p-1"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 font-medium mb-1"><i class="fa-solid fa-phone mr-2 text-slate-400"></i> ${item.phone}</p>
                        ${item.contact_person ? `<p class="text-xs text-slate-500 font-medium truncate"><i class="fa-solid fa-user-tie mr-2 text-slate-400"></i> ${item.contact_person}</p>` : ''}
                    </div>
                    <div class="bg-slate-50 border-t border-slate-100 p-3 flex justify-between items-center cursor-pointer hover:bg-slate-100 transition-colors">
                        <span class="text-xs font-bold text-slate-600">${item.items_supplied || 0} Barang disupply</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                    </div>
                </div>
                `;
            });
        }
        container.innerHTML = html;
        renderPagination(response.total_pages, response.current_page);
    }
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination');
    let html = '';
    if (totalPages <= 1) { container.innerHTML = ''; return; }

    html += `<button type="button" ${current > 1 ? `onclick="loadData(${current - 1})"` : 'disabled'} class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-500 text-xs font-bold shadow-sm hover:bg-slate-50 disabled:opacity-50">Prev</button>`;
    for (let i = 1; i <= totalPages; i++) {
        if (i === current) html += `<button class="w-8 h-8 rounded-xl bg-blue-600 text-white text-xs font-black shadow-md">${i}</button>`;
        else html += `<button onclick="loadData(${i})" class="w-8 h-8 rounded-xl bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50">${i}</button>`;
    }
    html += `<button type="button" ${current < totalPages ? `onclick="loadData(${current + 1})"` : 'disabled'} class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-500 text-xs font-bold shadow-sm hover:bg-slate-50 disabled:opacity-50">Next</button>`;
    container.innerHTML = html;
}

// ==========================================
// 3. FUNGSI CRUD SUPPLIER
// ==========================================
document.getElementById('formSupplier').addEventListener('submit', async function(e) {
    e.preventDefault();
    Swal.fire({ title: 'Menyimpan...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save', 'POST', formData);
    
    if (response.status === 'success') {
        closeModal('modal-supplier');
        loadData(currentPage); 
        Swal.fire({ title: 'Berhasil!', text: response.message, icon: 'success', timer: 1500, showConfirmButton: false });
    } else {
        Swal.fire('Gagal!', response.message, 'error');
    }
});

function editData(item) {
    document.getElementById('id').value = item.id;
    document.getElementById('name').value = item.name;
    document.getElementById('contact_person').value = item.contact_person;
    document.getElementById('phone').value = item.phone;
    document.getElementById('email').value = item.email;
    document.getElementById('address').value = item.address;
    document.getElementById('description').value = item.description;
    
    document.getElementById('modal-title').innerText = 'Edit Supplier';
    openModal('modal-supplier');
}

async function deleteData(id) {
    const result = await Swal.fire({
        title: 'Hapus Supplier?',
        text: "Supplier dengan riwayat transaksi PO tidak dapat dihapus.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        confirmButtonText: 'Ya, Hapus!'
    });

    if (result.isConfirmed) {
        Swal.fire({ title: 'Menghapus...', icon: 'info', showConfirmButton: false });
        const formData = new FormData(); formData.append('id', id);
        
        const response = await fetchAjax('logic.php?action=delete', 'POST', formData);
        if (response.status === 'success') {
            loadData(currentPage);
            Swal.fire({ title: 'Terhapus!', text: response.message, icon: 'success', timer: 1500, showConfirmButton: false });
        } else {
            Swal.fire('Gagal!', response.message, 'error');
        }
    }
}