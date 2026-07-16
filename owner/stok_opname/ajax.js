let materialsData = []; // Data mentah semua bahan baku dari server
let opnameItems = {};   // Bahan baku yang dimasukkan ke list opname utama: { material_id: { data } }
let selectedInModal = {}; // Sementara dipilih di modal: { material_id: { actual_stock, checked } }

// Paginasi modal
let modalPage = 1;
const modalLimit = 8;
let filteredMaterials = [];

document.addEventListener("DOMContentLoaded", () => {
    loadHistory();
    loadMaterials();
    setupPinInputs();
    
    // Set Jam Default di filter modal
    setInterval(() => {
        const jamInput = document.getElementById('filter-jam');
        if (jamInput && !jamInput.value) {
            updateJamDefault();
        }
    }, 1000);
});

// LOGIKA INPUT PIN (Tetap dipertahankan untuk keamanan akses owner)
function setupPinInputs() {
    const inputs = document.querySelectorAll('.pin-input');
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });
}

async function verifyPin() {
    const inputs = document.querySelectorAll('.pin-input');
    let pin = '';
    inputs.forEach(input => pin += input.value);

    if (pin.length < 6) {
        Swal.fire('Peringatan', 'Masukkan 6 digit kode akses!', 'warning');
        return;
    }

    const btn = document.getElementById('btn-unlock');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Verifikasi...';

    const formData = new FormData();
    formData.append('pin', pin);

    const response = await fetchAjax('logic.php?action=verify_pin', 'POST', formData);

    if (response.status === 'success') {
        document.getElementById('lock-screen').classList.add('hidden');
        Swal.fire({ title: 'Akses Dibuka!', icon: 'success', timer: 1000, showConfirmButton: false });
    } else {
        Swal.fire('Gagal', response.message, 'error');
        inputs.forEach(input => input.value = '');
        inputs[0].focus();
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-unlock-keyhole"></i> Buka Akses';
    }
}

// FORMAT DESIMAL
function formatDesimal(angka) {
    const num = parseFloat(angka);
    if (isNaN(num)) return 0;
    return num % 1 !== 0 ? num.toFixed(2) : num;
}

// AMBIL SEMUA BAHAN BAKU
async function loadMaterials() {
    const response = await fetchAjax('logic.php?action=get_materials', 'GET');
    if (response.status === 'success') {
        materialsData = response.data;
        populateGudangFilter();
    }
}

// POPULASI FILTER GUDANG DI MODAL
function populateGudangFilter() {
    const filter = document.getElementById('filter-gudang');
    if (!filter) return;
    
    // Ambil nama gudang yang unik
    const gudangs = [...new Set(materialsData.map(m => m.warehouse_name).filter(Boolean))];
    filter.innerHTML = '<option value="">Semua Gudang</option>';
    gudangs.forEach(g => {
        filter.innerHTML += `<option value="${g}">${g}</option>`;
    });
}

// AUTO UPDATE JAM DEFAULT
function updateJamDefault() {
    const now = new Date();
    const jam = String(now.getHours()).padStart(2, '0');
    const menit = String(now.getMinutes()).padStart(2, '0');
    const detik = String(now.getSeconds()).padStart(2, '0');
    const jamInput = document.getElementById('filter-jam');
    if (jamInput) jamInput.value = `${jam}.${menit}.${detik}`;
}

// BUKA MODAL TAMBAH BAHAN BAKU
function bukaModalTambah() {
    updateJamDefault();
    selectedInModal = {}; // Reset data pilihan sementara
    
    // Sinkronkan data modal dengan item yang sudah ada di tabel utama
    Object.keys(opnameItems).forEach(id => {
        selectedInModal[id] = {
            actual_stock: opnameItems[id].actual_stock,
            checked: true
        };
    });
    
    document.getElementById('modal-tambah-bahan').classList.remove('hidden');
    document.getElementById('search-modal').value = '';
    document.getElementById('filter-gudang').value = '';
    modalPage = 1;
    filterBahan();
}

// TUTUP MODAL
function tutupModalTambah() {
    document.getElementById('modal-tambah-bahan').classList.add('hidden');
}

// FILTER BAHAN BAKU DI MODAL
function filterBahan() {
    const search = document.getElementById('search-modal').value.toLowerCase().trim();
    const gudang = document.getElementById('filter-gudang').value;

    filteredMaterials = materialsData.filter(m => {
        const matchesSearch = m.name.toLowerCase().includes(search) || m.code.toLowerCase().includes(search);
        const matchesGudang = !gudang || m.warehouse_name === gudang;
        return matchesSearch && matchesGudang;
    });

    renderModalTable();
}

// RENDER TABEL DI MODAL
function renderModalTable() {
    const tbody = document.getElementById('modal-materials-tbody');
    const pagination = document.getElementById('modal-pagination');
    
    if (filteredMaterials.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-slate-400 italic">Bahan baku tidak ditemukan.</td></tr>';
        pagination.innerHTML = '';
        document.getElementById('modal-total-records').innerText = '0';
        updateSelectedCount();
        return;
    }

    document.getElementById('modal-total-records').innerText = filteredMaterials.length;

    // Kalkulasi pagination
    const totalPages = Math.ceil(filteredMaterials.length / modalLimit);
    if (modalPage > totalPages) modalPage = totalPages;
    if (modalPage < 1) modalPage = 1;

    const startIdx = (modalPage - 1) * modalLimit;
    const endIdx = Math.min(startIdx + modalLimit, filteredMaterials.length);
    const pageData = filteredMaterials.slice(startIdx, endIdx);

    let html = '';
    pageData.forEach(m => {
        // Cek status checkbox & qty
        const isChecked = selectedInModal[m.id] ? selectedInModal[m.id].checked : false;
        const currentQty = selectedInModal[m.id] ? selectedInModal[m.id].actual_stock : 0;

        html += `
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="p-3 text-center">
                    <input type="checkbox" onchange="toggleSelectMaterial(this, ${m.id})" ${isChecked ? 'checked' : ''} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer material-chk">
                </td>
                <td class="p-3">
                    <div class="font-bold text-slate-800">${m.name}</div>
                    <div class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">${m.unit}</div>
                </td>
                <td class="p-3 text-center font-mono font-semibold text-slate-500">${m.code}</td>
                <td class="p-3 font-semibold text-slate-600">${m.warehouse_name || '-'}</td>
                <td class="p-3 text-right font-medium text-slate-500">${formatDesimal(m.stock)}</td>
                <td class="p-3">
                    <div class="flex items-center justify-center gap-1.5">
                        <button type="button" onclick="adjustModalQty(${m.id}, -1)" class="qty-btn bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-90">-</button>
                        <input type="number" step="0.01" value="${currentQty}" onchange="changeModalQty(${m.id}, this.value)" class="w-20 text-center font-bold border border-slate-200 rounded-lg py-1 text-xs focus:ring-2 focus:ring-indigo-500 outline-none" id="qty-input-${m.id}">
                        <button type="button" onclick="adjustModalQty(${m.id}, 1)" class="qty-btn bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-90">+</button>
                    </div>
                </td>
            </tr>`;
    });
    tbody.innerHTML = html;

    // Render Pagination
    let pagHtml = '';
    if (totalPages > 1) {
        // Prev button
        pagHtml += `<button type="button" onclick="setModalPage(${modalPage - 1})" class="px-2.5 py-1 text-[10px] font-bold border border-slate-200 rounded-lg hover:bg-slate-50 ${modalPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}">Prev</button>`;
        
        for (let i = 1; i <= totalPages; i++) {
            pagHtml += `<button type="button" onclick="setModalPage(${i})" class="px-3 py-1 text-[10px] font-bold rounded-lg ${modalPage === i ? 'bg-indigo-600 text-white' : 'border border-slate-200 hover:bg-slate-50'}">${i}</button>`;
        }

        // Next button
        pagHtml += `<button type="button" onclick="setModalPage(${modalPage + 1})" class="px-2.5 py-1 text-[10px] font-bold border border-slate-200 rounded-lg hover:bg-slate-50 ${modalPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''}">Next</button>`;
    }
    pagination.innerHTML = pagHtml;

    updateSelectedCount();
}

function setModalPage(page) {
    modalPage = page;
    renderModalTable();
}

// LOGIKA PILIH DI MODAL
function toggleSelectMaterial(chk, id) {
    if (!selectedInModal[id]) {
        selectedInModal[id] = { actual_stock: 0, checked: false };
    }
    selectedInModal[id].checked = chk.checked;
    
    // Auto set qty = system stock saat pertama kali dicentang agar memudahkan
    if (chk.checked && selectedInModal[id].actual_stock === 0) {
        const material = materialsData.find(m => m.id === id);
        if (material) {
            selectedInModal[id].actual_stock = parseFloat(material.stock);
            const input = document.getElementById(`qty-input-${id}`);
            if (input) input.value = formatDesimal(material.stock);
        }
    }
    
    updateSelectedCount();
}

function toggleSelectAllModal(chk) {
    const checkboxes = document.querySelectorAll('.material-chk');
    checkboxes.forEach(c => {
        c.checked = chk.checked;
        c.dispatchEvent(new Event('change'));
    });
}

function adjustModalQty(id, delta) {
    if (!selectedInModal[id]) {
        selectedInModal[id] = { actual_stock: 0, checked: true };
    }
    
    // Otomatis centang jika qty disesuaikan
    selectedInModal[id].checked = true;

    let val = parseFloat(selectedInModal[id].actual_stock) + delta;
    if (val < 0) val = 0;
    selectedInModal[id].actual_stock = val;

    const input = document.getElementById(`qty-input-${id}`);
    if (input) input.value = formatDesimal(val);
    
    // Update checkbox visual di baris tersebut
    const row = input.closest('tr');
    if (row) {
        const chk = row.querySelector('.material-chk');
        if (chk) chk.checked = true;
    }

    updateSelectedCount();
}

function changeModalQty(id, val) {
    if (!selectedInModal[id]) {
        selectedInModal[id] = { actual_stock: 0, checked: true };
    }
    let qty = parseFloat(val);
    if (isNaN(qty) || qty < 0) qty = 0;
    selectedInModal[id].actual_stock = qty;
    selectedInModal[id].checked = true;
    
    const row = document.getElementById(`qty-input-${id}`).closest('tr');
    if (row) {
        const chk = row.querySelector('.material-chk');
        if (chk) chk.checked = true;
    }
    updateSelectedCount();
}

function updateSelectedCount() {
    const count = Object.values(selectedInModal).filter(item => item.checked).length;
    document.getElementById('selected-count').innerText = count;
}

// PINDAHKAN BAHAN BAKU DARI MODAL KE TABEL UTAMA (Gambar 3)
function tambahkanBahanTerpilih() {
    const keys = Object.keys(selectedInModal).filter(id => selectedInModal[id].checked);
    
    if (keys.length === 0) {
        Swal.fire('Info', 'Pilih minimal 1 bahan baku untuk ditambahkan!', 'info');
        return;
    }

    // Bangun ulang opnameItems berdasarkan pilihan modal
    const tempItems = {};
    const jamInput = document.getElementById('filter-jam').value || '00:00:00';

    keys.forEach(id => {
        const material = materialsData.find(m => m.id.toString() === id.toString());
        if (material) {
            // Jika sudah ada sebelumnya, pertahankan waktu input lama, jika baru gunakan jam input modal
            const waktuInput = opnameItems[id] ? opnameItems[id].waktu : jamInput;
            tempItems[id] = {
                id: material.id,
                name: material.name,
                code: material.code,
                unit: material.unit,
                system_stock: parseFloat(material.stock),
                actual_stock: parseFloat(selectedInModal[id].actual_stock),
                waktu: waktuInput
            };
        }
    });

    opnameItems = tempItems;
    tutupModalTambah();
    renderMainTable();
}

// RENDER TABEL UTAMA HALAMAN STOK OPNAME
function renderMainTable() {
    const tbody = document.getElementById('opname_list');
    const keys = Object.keys(opnameItems);
    
    if (keys.length === 0) {
        tbody.innerHTML = `
            <tr id="empty_state">
                <td colspan="7" class="p-12 text-center text-slate-400 italic">
                    <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 border border-dashed border-slate-200">
                        <i class="fa-solid fa-box-open text-2xl"></i>
                    </div>
                    Belum ada bahan baku diopname.<br>Klik tombol <strong class="text-indigo-600 cursor-pointer" onclick="bukaModalTambah()">+ Tambah Bahan</strong> untuk memasukkan data.
                </td>
            </tr>`;
        document.getElementById('item_count').innerText = '0';
        return;
    }

    document.getElementById('item_count').innerText = keys.length;
    let html = '';
    
    keys.forEach((id, index) => {
        const item = opnameItems[id];
        const sys = parseFloat(item.system_stock);
        const act = parseFloat(item.actual_stock);
        const diff = act - sys;

        let diffBadge = '';
        if (diff > 0) {
            diffBadge = `<span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-black text-xs border border-emerald-100">+${formatDesimal(diff)}</span>`;
        } else if (diff < 0) {
            diffBadge = `<span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-black text-xs border border-rose-100">${formatDesimal(diff)}</span>`;
        } else {
            diffBadge = `<span class="px-2.5 py-1 rounded-lg bg-slate-50 text-slate-500 font-bold text-xs border border-slate-100">Pas</span>`;
        }

        html += `
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="p-4 text-center font-mono text-xs text-slate-400">${index + 1}</td>
                <td class="p-4">
                    <div class="font-bold text-slate-800">${item.name}</div>
                    <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">${item.code}</div>
                </td>
                <td class="p-4 text-center font-semibold text-slate-500 text-xs">${item.waktu}</td>
                <td class="p-4 text-right">
                    <input type="hidden" name="material_id[]" value="${item.id}">
                    <input type="hidden" name="system_stock[]" value="${sys}">
                    
                    <div class="flex items-center justify-end gap-1.5">
                        <input type="number" step="0.01" name="actual_stock[]" value="${act}" onchange="changeMainQty(${item.id}, this.value)" class="w-24 text-right font-black text-indigo-700 bg-indigo-50/40 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none text-xs">
                        <span class="text-xs text-slate-400 font-bold w-8 text-left pl-1">${item.unit}</span>
                    </div>
                </td>
                <td class="p-4 text-right font-semibold text-slate-500">${formatDesimal(sys)} ${item.unit}</td>
                <td class="p-4 text-center">${diffBadge}</td>
                <td class="p-4 text-center">
                    <button type="button" onclick="hapusItemUtama(${item.id})" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center mx-auto" title="Hapus dari daftar">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                </td>
            </tr>`;
    });

    tbody.innerHTML = html;
}

function changeMainQty(id, val) {
    let qty = parseFloat(val);
    if (isNaN(qty) || qty < 0) qty = 0;
    
    if (opnameItems[id]) {
        opnameItems[id].actual_stock = qty;
        renderMainTable(); // Re-render untuk mengupdate selisih
    }
}

function hapusItemUtama(id) {
    delete opnameItems[id];
    renderMainTable();
}

// RIWAYAT TRANS-COLLAPSIBLE (Gambar 3)
function toggleRiwayat() {
    const panel = document.getElementById('riwayat-panel');
    const chevron = document.getElementById('riwayat-chevron');
    if (panel.classList.contains('hidden')) {
        panel.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
        loadHistory();
    } else {
        panel.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

async function loadHistory() {
    const tbody = document.getElementById('table-body');
    const response = await fetchAjax('logic.php?action=read_history', 'GET');
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="9" class="p-8 text-center text-slate-400 italic">Belum ada riwayat opname.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const dateObj = new Date(item.created_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const diff = parseFloat(item.difference);
                
                let diffBadge = diff > 0 
                    ? `<span class="text-emerald-600 font-bold">+${formatDesimal(diff)}</span>` 
                    : `<span class="text-rose-600 font-bold">${formatDesimal(diff)}</span>`;
                
                html += `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 transition-colors">
                        <td class="p-3 text-center text-slate-400 font-mono">${index + 1}</td>
                        <td class="p-3">
                            <div class="font-bold text-slate-700">${tgl}</div>
                            <div class="text-[9px] text-slate-400 font-medium">${waktu} WIB</div>
                        </td>
                        <td class="p-3 font-mono text-emerald-600 font-black">${item.opname_no}</td>
                        <td class="p-3">
                            <div class="font-bold text-slate-800">${item.material_name}</div>
                            <div class="text-[9px] text-slate-400 font-medium">${item.code}</div>
                        </td>
                        <td class="p-3 text-right font-medium text-slate-500">${formatDesimal(item.system_stock)} ${item.unit}</td>
                        <td class="p-3 text-right font-black text-indigo-600">${formatDesimal(item.actual_stock)} ${item.unit}</td>
                        <td class="p-3 text-center">${diffBadge}</td>
                        <td class="p-3 text-slate-600 font-medium italic">"${item.reason || 'Tanpa catatan'}"</td>
                        <td class="p-3 text-center"><span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-bold text-[10px]">${item.petugas}</span></td>
                    </tr>`;
            });
        }
        tbody.innerHTML = html;
    }
}

// POST DATA OPNAME KE SERVER
document.getElementById('formOpname').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const keys = Object.keys(opnameItems);
    if (keys.length === 0) {
        Swal.fire('Gagal', 'Pilih minimal 1 bahan baku terlebih dahulu!', 'error');
        return;
    }

    const result = await Swal.fire({
        title: 'Post Dokumen Opname?',
        text: 'Stok fisik bahan baku akan langsung diperbarui ke dalam sistem.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        confirmButtonText: '<i class="fa-solid fa-cloud-arrow-up mr-1"></i> Ya, Posting!'
    });

    if (!result.isConfirmed) return;

    Swal.fire({ title: 'Menyimpan data...', allowOutsideClick: false, showConfirmButton: false, icon: 'info' });

    const formData = new FormData(this);
    formData.append('action', 'save');

    const response = await fetchAjax('logic.php', 'POST', formData);

    if (response.status === 'success') {
        opnameItems = {};
        selectedInModal = {};
        renderMainTable();
        
        // Buka riwayat panel untuk melihat penyesuaian yang baru dibuat
        const panel = document.getElementById('riwayat-panel');
        if (panel.classList.contains('hidden')) {
            toggleRiwayat();
        } else {
            loadHistory();
        }

        // Reload data material
        loadMaterials();

        Swal.fire({ title: 'Sukses!', text: response.message, icon: 'success' });
    } else {
        Swal.fire('Gagal', response.message, 'error');
    }
});