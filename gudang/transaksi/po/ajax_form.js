let cartPO = []; 
let materialsData = []; 
let receiveItems = [];
let activeReceivePoId = null;

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

// ===============================================
// FORM BUAT PO & KERANJANG
// ===============================================
async function loadDraftPO() {
    cartPO = []; 
    document.getElementById('form-po').reset();
    renderCart();

    const res = await fetchAjax('logic.php?action=init_form', 'GET');
    if (res.status === 'success') {
        materialsData = res.materials;

        let optSupp = '<option value="">-- Pilih Supplier --</option>';
        res.suppliers.forEach(s => { optSupp += `<option value="${s.id}">${s.name}</option>`; });
        document.getElementById('supplier_id').innerHTML = optSupp;

        const tbodyPR = document.getElementById('list-pr-pending');
        let htmlPR = '';
        if (res.pr_pending.length === 0) {
            htmlPR = '<tr><td colspan="6" class="py-6 text-center italic opacity-70">Tidak ada permintaan barang yang tertunda.</td></tr>';
        } else {
            res.pr_pending.forEach(pr => {
                const itemData = encodeURIComponent(JSON.stringify({
                    pr_id: pr.id,
                    material_id: pr.material_id,
                    material_name: pr.material_name,
                    qty: parseFloat(pr.qty),
                    unit: pr.unit
                }));

                const d = new Date(pr.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'numeric', month:'numeric', year:'numeric'});

                htmlPR += `
                    <tr class="hover:bg-amber-50/50 transition-colors border-b border-amber-200/40 last:border-0">
                        <td class="py-4 pr-2 text-xs font-medium text-amber-800">${tgl}</td>
                        <td class="py-4 pr-2 font-black text-amber-900">${pr.material_name}</td>
                        <td class="py-4 pr-2 font-black text-amber-900">${parseFloat(pr.qty)} <span class="text-[10px] font-bold uppercase text-amber-700/70">${pr.unit}</span></td>
                        <td class="py-4 pr-2 text-xs font-bold text-amber-800">${pr.requested_by_name}</td>
                        <td class="py-4 pr-2 text-xs italic text-amber-700/70 truncate max-w-[150px]">${pr.notes || '-'}</td>
                        <td class="py-4 text-right">
                            <button type="button" onclick="masukkanKePO(this, '${itemData}')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all shadow-md shadow-blue-200">
                                Masukkan ke PO
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        tbodyPR.innerHTML = htmlPR;
    }
}

function masukkanKePO(btn, encodedData) {
    const item = JSON.parse(decodeURIComponent(encodedData));
    const exist = cartPO.findIndex(c => c.material_id === item.material_id);
    
    if (exist !== -1) cartPO[exist].qty += item.qty;
    else cartPO.push(item); 
    
    renderCart();
    btn.className = "bg-amber-100 text-amber-500 px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest cursor-not-allowed";
    btn.innerHTML = "<i class='fa-solid fa-check'></i> Ditambahkan";
    btn.disabled = true;
}

// ===============================================
// AUTOCOMPLETE INPUT ITEM MANUAL (DRAFT PO)
// ===============================================
function filterMaterialList() {
    const keyword = document.getElementById('search_material').value.toLowerCase();
    const listContainer = document.getElementById('material_list');
    
    listContainer.innerHTML = '';
    if (keyword.length < 1) {
        listContainer.classList.add('hidden');
        document.getElementById('item_material_id').value = '';
        return;
    }

    const filtered = materialsData.filter(m => 
        m.material_name.toLowerCase().includes(keyword) || 
        m.sku_code.toLowerCase().includes(keyword)
    );

    if (filtered.length === 0) {
        listContainer.innerHTML = `<div class="p-3 text-xs text-slate-400 italic font-bold">Barang tidak ditemukan.</div>`;
        listContainer.classList.remove('hidden');
        return;
    }

    filtered.forEach(m => {
        const div = document.createElement('div');
        div.className = "p-3 border-b border-slate-50 hover:bg-blue-50 cursor-pointer transition-colors";
        div.innerHTML = `
            <div class="font-black text-slate-800 text-xs">${m.material_name}</div>
            <div class="text-[10px] text-slate-500 font-mono font-bold mt-0.5">[${m.sku_code}] • Satuan: ${m.unit}</div>
        `;
        div.onclick = () => {
            document.getElementById('item_material_id').value = m.id;
            document.getElementById('search_material').value = m.material_name;
            listContainer.classList.add('hidden');
        };
        listContainer.appendChild(div);
    });
    listContainer.classList.remove('hidden');
}

document.addEventListener('click', function(e) {
    const searchInput = document.getElementById('search_material');
    const listContainer = document.getElementById('material_list');
    if (searchInput && listContainer && e.target !== searchInput && !listContainer.contains(e.target)) {
        listContainer.classList.add('hidden');
    }
    
    const searchTerima = document.getElementById('search_extra_terima');
    const listTerima = document.getElementById('extra_terima_list');
    if (searchTerima && listTerima && e.target !== searchTerima && !listTerima.contains(e.target)) {
        listTerima.classList.add('hidden');
    }
});

function tambahItemManual() {
    const material_id = document.getElementById('item_material_id').value;
    const qty = parseFloat(document.getElementById('item_qty').value);
    
    if (!material_id || isNaN(qty) || qty <= 0) {
        Swal.fire('Ups!', 'Pilih barang dari daftar pencarian dan masukkan jumlah yang benar.', 'warning'); 
        return;
    }

    const mat = materialsData.find(m => m.id == material_id);
    const exist = cartPO.findIndex(c => c.material_id == material_id);
    
    if (exist !== -1) {
        Swal.fire('Sudah Ada!', 'Barang ini sudah ada di daftar PO. Silakan edit jumlah (QTY)-nya langsung di tabel bawah.', 'warning');
    } else {
        cartPO.push({ pr_id: null, material_id: mat.id, material_name: mat.material_name, qty: qty, unit: mat.unit });
        document.getElementById('item_material_id').value = '';
        document.getElementById('search_material').value = '';
        document.getElementById('item_qty').value = 1;
        renderCart();
    }
}

function hapusCart(index) {
    cartPO.splice(index, 1);
    renderCart();
}

function updateCartQty(index, newQty) {
    let val = parseFloat(newQty);
    if(isNaN(val) || val <= 0) {
        Swal.fire('Ups!', 'Jumlah tidak valid!', 'error');
        renderCart(); 
        return;
    }
    cartPO[index].qty = val;
}

function renderCart() {
    const tbody = document.getElementById('cart-po');
    let html = '';
    if (cartPO.length === 0) {
        html = '<tr><td colspan="3" class="p-8 text-center text-slate-400 italic text-xs">Belum ada item yang ditambahkan.</td></tr>';
    } else {
        cartPO.forEach((item, index) => {
            html += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-5 font-black text-slate-700 uppercase text-xs">${item.material_name}</td>
                    <td class="p-5 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <input type="number" step="any" value="${item.qty}" min="0.1" onchange="updateCartQty(${index}, this.value)" class="w-20 px-2 py-1.5 border border-slate-300 rounded-lg text-center font-black text-blue-600 outline-none focus:border-blue-600 shadow-sm transition-all">
                            <span class="text-[10px] font-black uppercase text-slate-400">${item.unit}</span>
                        </div>
                    </td>
                    <td class="p-5 text-center">
                        <button type="button" onclick="hapusCart(${index})" class="w-7 h-7 rounded bg-rose-50 hover:bg-rose-500 text-rose-500 hover:text-white transition-all flex items-center justify-center mx-auto shadow-sm"><i class="fa-solid fa-xmark text-xs"></i></button>
                    </td>
                </tr>
            `;
        });
    }
    tbody.innerHTML = html;
}

async function simpanPO() {
    const supplier_id = document.getElementById('supplier_id').value;
    const shipping_date = document.getElementById('shipping_date').value;
    if (!supplier_id || !shipping_date) { Swal.fire('Data Belum Lengkap', 'Mohon lengkapi Supplier dan Tanggal Pengiriman!', 'warning'); return; }
    if (cartPO.length === 0) { Swal.fire('Keranjang Kosong', 'Tambahkan minimal 1 item barang untuk dibeli!', 'warning'); return; }

    Swal.fire({ title: 'Menerbitkan PO...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });

    const formData = new FormData();
    formData.append('action', 'save_po');
    formData.append('supplier_id', supplier_id);
    formData.append('shipping_date', shipping_date);
    formData.append('cart', JSON.stringify(cartPO));

    const res = await fetchAjax('logic.php', 'POST', formData);
    if (res.status === 'success') {
        Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false }).then(() => {
            document.querySelector('button[x-show="tab === \'buat_po\'"]').click(); 
        });
    } else { Swal.fire('Gagal!', res.message, 'error'); }
}

// ===============================================
// MODAL TERIMA BARANG (REVISI AUTOCOMPLETE)
// ===============================================
let terimaMaterialsData = [];

async function openModalTerima(po_id, po_no) {
    activeReceivePoId = po_id;
    document.getElementById('terima-po-title').innerText = 'Penerimaan Barang PO: ' + po_no;
    
    // Reset autocomplete input
    document.getElementById('search_extra_terima').value = '';
    document.getElementById('terima_extra_item_id').value = '';
    
    openModal('modal-terima-barang');

    const res = await fetchAjax(`logic.php?action=get_po_receive&po_id=${po_id}`, 'GET');
    if (res.status === 'success') {
        receiveItems = res.items.map(item => ({
            material_id: item.material_id, material_name: item.material_name, qty_po: parseFloat(item.qty), qty_terima: parseFloat(item.qty), unit: item.unit, price: '', exp_date: ''
        }));
        
        // Simpan data master bahan baku untuk pencarian
        terimaMaterialsData = res.materials;
        renderTerimaItems();
    }
}

function renderTerimaItems() {
    const tbody = document.getElementById('terima-po-items');
    let html = '';
    receiveItems.forEach((item, idx) => {
        html += `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4 font-bold text-slate-700 text-xs">${item.material_name}</td>
                <td class="p-4 text-center font-bold text-slate-500">${item.qty_po || '-'}</td>
                <td class="p-4"><input type="number" step="any" class="w-full px-2 py-1.5 border border-slate-300 rounded font-black text-blue-600 text-center outline-none" value="${item.qty_terima}" onchange="receiveItems[${idx}].qty_terima = this.value"></td>
                <td class="p-4 text-center text-xs font-bold text-slate-500">${item.unit}</td>
                <td class="p-4"><input type="number" step="any" placeholder="Harga Satuan..." class="w-full px-2 py-1.5 border border-slate-300 rounded font-bold text-emerald-600 outline-none" value="${item.price}" onchange="receiveItems[${idx}].price = this.value"></td>
                <td class="p-4"><input type="date" class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs font-bold text-slate-700 outline-none" value="${item.exp_date}" onchange="receiveItems[${idx}].exp_date = this.value"></td>
                <td class="p-4 text-center"><button type="button" onclick="removeTerimaItem(${idx})" class="w-6 h-6 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center mx-auto"><i class="fa-solid fa-xmark text-[10px]"></i></button></td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

// LOGIKA AUTOCOMPLETE TAMBAH BARANG LAIN (TERIMA PO)
function filterExtraTerimaList() {
    const keyword = document.getElementById('search_extra_terima').value.toLowerCase();
    const listContainer = document.getElementById('extra_terima_list');
    
    listContainer.innerHTML = '';
    if (keyword.length < 1) {
        listContainer.classList.add('hidden');
        document.getElementById('terima_extra_item_id').value = '';
        return;
    }

    const filtered = terimaMaterialsData.filter(m => 
        m.material_name.toLowerCase().includes(keyword) || 
        m.sku_code.toLowerCase().includes(keyword)
    );

    if (filtered.length === 0) {
        listContainer.innerHTML = `<div class="p-3 text-xs text-slate-400 italic font-bold">Barang tidak ditemukan.</div>`;
        listContainer.classList.remove('hidden');
        return;
    }

    filtered.forEach(m => {
        const div = document.createElement('div');
        div.className = "p-3 border-b border-slate-50 hover:bg-blue-50 cursor-pointer transition-colors";
        div.innerHTML = `
            <div class="font-black text-slate-800 text-xs">${m.material_name}</div>
            <div class="text-[10px] text-slate-500 font-mono font-bold mt-0.5">[${m.sku_code}] • Satuan: ${m.unit}</div>
        `;
        div.onclick = () => {
            document.getElementById('terima_extra_item_id').value = m.id;
            document.getElementById('terima_extra_item_name').value = m.material_name;
            document.getElementById('terima_extra_item_unit').value = m.unit;
            document.getElementById('search_extra_terima').value = m.material_name;
            listContainer.classList.add('hidden');
        };
        listContainer.appendChild(div);
    });
    listContainer.classList.remove('hidden');
}

async function addExtraTerimaItem() {
    const mat_id = document.getElementById('terima_extra_item_id').value;
    const mat_name = document.getElementById('terima_extra_item_name').value;
    const mat_unit = document.getElementById('terima_extra_item_unit').value;

    if (!mat_id) {
        Swal.fire('Ups!', 'Silakan cari dan pilih barang terlebih dahulu.', 'warning');
        return;
    }

    const { value: code } = await Swal.fire({ title: 'Otorisasi Dibutuhkan', text: 'Masukkan kode Manager/Admin.', input: 'text', inputPlaceholder: 'Kode Otorisasi', showCancelButton: true, confirmButtonColor: '#2563EB' });
    if (code) {
        // Cek jika barang sudah ada di list terima
        const exist = receiveItems.findIndex(i => i.material_id == mat_id);
        if (exist !== -1) {
            Swal.fire('Info', 'Barang tersebut sudah ada di daftar penerimaan.', 'info');
        } else {
            receiveItems.push({ material_id: mat_id, material_name: mat_name, qty_po: 0, qty_terima: 1, unit: mat_unit, price: '', exp_date: '' });
            renderTerimaItems(); 
            
            // Reset input
            document.getElementById('search_extra_terima').value = '';
            document.getElementById('terima_extra_item_id').value = '';
            
            Swal.fire({ title: 'Otorisasi Berhasil', text: 'Barang tambahan dimasukkan ke daftar.', icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
        }
    }
}

function removeTerimaItem(idx) { receiveItems.splice(idx, 1); renderTerimaItems(); }

async function submitTerimaBarang() {
    if(receiveItems.length === 0) { Swal.fire('Ups!', 'Daftar penerimaan kosong.', 'warning'); return; }
    const hasEmptyPrice = receiveItems.some(item => item.price === '' || item.price === null);
    if(hasEmptyPrice) { Swal.fire('Harga Kosong', 'Mohon isi Harga Satuan sesuai dengan nota dari Supplier.', 'error'); return; }

    const confirm = await Swal.fire({ title: 'Simpan Penerimaan?', text: 'Stok barang akan otomatis ditambahkan ke Inventory Gudang.', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Terima Barang', confirmButtonColor: '#10B981' });
    
    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', showConfirmButton: false });
        const formData = new FormData();
        formData.append('action', 'save_receive_po'); formData.append('po_id', activeReceivePoId); formData.append('items', JSON.stringify(receiveItems));
        const res = await fetchAjax('logic.php', 'POST', formData);
        if (res.status === 'success') {
            closeModal('modal-terima-barang'); Swal.fire('Berhasil!', res.message, 'success'); loadDataPO(); 
        } else { Swal.fire('Gagal!', res.message, 'error'); }
    }
}