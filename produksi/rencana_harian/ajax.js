let cartPlan = [];
let productsData = [];

document.addEventListener('DOMContentLoaded', () => {
    initForm();
    loadTodayPlans();
});

async function initForm() {
    const res = await fetchAjax('logic.php?action=init', 'GET');
    if (res.status === 'success') {
        productsData = res.produk;

        // Isi Dropdown Karyawan
        const selKar = document.getElementById('karyawan_id');
        let optKar = '<option value="">-- Pilih Karyawan (Petugas) --</option>';
        res.karyawan.forEach(k => { 
            const kitchenName = k.kitchen_name ? `[${k.kitchen_name}]` : '';
            optKar += `<option value="${k.id}">${kitchenName} ${k.name}</option>`; 
        });
        selKar.innerHTML = optKar;
    }
}

// ===============================================
// LOGIKA AUTOCOMPLETE PRODUK
// ===============================================
function renderDropdownList(products) {
    const ul = document.getElementById('product_list');
    ul.innerHTML = ''; 
    
    if(products.length === 0) {
        ul.innerHTML = '<li class="p-3 text-sm text-slate-500 text-center">Produk tidak ditemukan</li>';
    } else {
        products.forEach(p => {
            const li = document.createElement('li');
            li.className = "p-3 hover:bg-blue-50 cursor-pointer border-b border-slate-50 text-sm font-semibold text-slate-700 transition-colors";
            li.innerHTML = `<span class="text-blue-600 font-mono text-xs mr-2">[${p.code}]</span> ${p.name}`;
            
            li.onclick = function() {
                document.getElementById('search_product').value = `[${p.code}] ${p.name}`;
                document.getElementById('item_product_id').value = p.id;
                ul.classList.add('hidden'); 
            };
            ul.appendChild(li);
        });
    }
}

function showDropdown() {
    const ul = document.getElementById('product_list');
    renderDropdownList(productsData);
    ul.classList.remove('hidden');
}

function filterDropdown() {
    const keyword = document.getElementById('search_product').value.toLowerCase();
    const ul = document.getElementById('product_list');
    
    // Reset ID tersembunyi saat user mulai mengetik manual lagi
    document.getElementById('item_product_id').value = '';
    
    const filteredProducts = productsData.filter(p => 
        p.name.toLowerCase().includes(keyword) || 
        p.code.toLowerCase().includes(keyword)
    );
    
    renderDropdownList(filteredProducts);
    ul.classList.remove('hidden');
}

function closeAllDropdowns(event) {
    if (!event.target.matches('.search-input')) {
        const ul = document.getElementById('product_list');
        if(ul) ul.classList.add('hidden');
    }
}

// ===============================================
// LOGIKA KERANJANG RENCANA (PLAN)
// ===============================================
function tambahItem() {
    const product_id = document.getElementById('item_product_id').value;
    const search_input = document.getElementById('search_product').value;
    const qty = parseInt(document.getElementById('item_qty').value);
    const adonan = parseFloat(document.getElementById('item_adonan').value) || 0;

    if (!product_id || search_input === '') {
        Swal.fire('Peringatan', 'Silakan cari dan pilih produk dari daftar yang muncul (jangan ketik manual)!', 'warning');
        return;
    }

    if (isNaN(qty) || qty < 1) {
        Swal.fire('Peringatan', 'Masukkan target qty yang benar!', 'warning');
        return;
    }

    const prod = productsData.find(p => p.id == product_id);
    const existIdx = cartPlan.findIndex(c => c.product_id == product_id);

    if (existIdx !== -1) {
        cartPlan[existIdx].qty += qty;
        cartPlan[existIdx].adonan += adonan;
    } else {
        cartPlan.push({ product_id: product_id, product_name: prod.name, qty: qty, adonan: adonan });
    }

    // Reset Input
    document.getElementById('item_product_id').value = '';
    document.getElementById('search_product').value = '';
    document.getElementById('item_qty').value = 1;
    document.getElementById('item_adonan').value = '';
    
    renderCart();
}

function hapusItem(idx) {
    cartPlan.splice(idx, 1);
    renderCart();
}

function renderCart() {
    const tbody = document.getElementById('cart-plan');
    let html = '';
    if (cartPlan.length === 0) {
        html = '<tr><td colspan="4" class="p-6 text-center text-slate-400 italic text-xs">Belum ada target yang ditambahkan.</td></tr>';
    } else {
        cartPlan.forEach((item, idx) => {
            let badgeAdonan = item.adonan > 0 ? `${item.adonan} Kg` : '-';
            html += `
                <tr class="hover:bg-slate-50 border-b border-slate-50 last:border-0">
                    <td class="p-3 text-xs uppercase font-black">${item.product_name}</td>
                    <td class="p-3 text-center text-blue-600">${item.qty} Pcs</td>
                    <td class="p-3 text-center text-amber-600">${badgeAdonan}</td>
                    <td class="p-3 text-center">
                        <button type="button" onclick="hapusItem(${idx})" class="w-6 h-6 rounded bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center mx-auto transition-colors"><i class="fa-solid fa-xmark text-[10px]"></i></button>
                    </td>
                </tr>
            `;
        });
    }
    tbody.innerHTML = html;
}

// ===============================================
// SUBMIT RENCANA KE DATABASE
// ===============================================
async function simpanRencana() {
    const karyawan_id = document.getElementById('karyawan_id').value;
    const notes = document.getElementById('notes').value;

    if (!karyawan_id) { Swal.fire('Data Belum Lengkap', 'Pilih Karyawan terlebih dahulu!', 'warning'); return; }
    if (cartPlan.length === 0) { Swal.fire('Target Kosong', 'Tambahkan minimal 1 target produk!', 'warning'); return; }

    const confirm = await Swal.fire({ title: 'Simpan Rencana?', text: 'Setelah disimpan, karyawan ini akan diizinkan untuk melakukan Input Produksi aktual hari ini.', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Simpan', confirmButtonColor: '#2563EB' });
    
    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Menyimpan...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });
        
        const formData = new FormData();
        formData.append('action', 'save_plan');
        formData.append('karyawan_id', karyawan_id);
        formData.append('notes', notes);
        formData.append('cart', JSON.stringify(cartPlan));

        const res = await fetchAjax('logic.php', 'POST', formData);
        
        if (res.status === 'success') {
            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false });
            document.getElementById('form-plan').reset();
            cartPlan = [];
            renderCart();
            loadTodayPlans();
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}

async function loadTodayPlans() {
    const container = document.getElementById('list-today');
    container.innerHTML = '<p class="text-center text-xs text-slate-400 py-4"><i class="fa-solid fa-circle-notch fa-spin"></i> Memuat...</p>';

    const res = await fetchAjax('logic.php?action=read_today', 'GET');
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<p class="text-center text-xs text-rose-400 font-bold italic py-4">Belum ada karyawan yang membuat target hari ini.</p>';
        } else {
            res.data.forEach(item => {
                const time = new Date(item.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                html += `
                    <div class="flex items-center justify-between p-3 border border-slate-100 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-user-check text-xs"></i></div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">${item.karyawan_name}</h4>
                                <p class="text-[10px] text-slate-500 font-medium">Telah submit target (${item.total_item} Item)</p>
                            </div>
                        </div>
                        <div class="text-[10px] font-black text-slate-400 bg-slate-100 px-2 py-1 rounded">${time}</div>
                    </div>
                `;
            });
        }
        container.innerHTML = html;
    }
}