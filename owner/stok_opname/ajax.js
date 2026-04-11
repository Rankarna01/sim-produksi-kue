let materialsData = [];
let selectedMaterials = []; 

document.addEventListener("DOMContentLoaded", () => {
    loadHistory();
    loadMaterials();
    setupSearch();
    setupPinInputs(); // Panggil setup input PIN
});

// LOGIKA INPUT PIN (Auto Focus & Pindah Kotak)
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

// LOGIKA SEBELUMNYA (JANGAN DIHAPUS)
function formatDesimal(angka) {
    const num = parseFloat(angka);
    return num % 1 !== 0 ? num.toFixed(2) : num;
}

async function loadMaterials() {
    const response = await fetchAjax('logic.php?action=get_materials', 'GET');
    if (response.status === 'success') { materialsData = response.data; }
}

function setupSearch() {
    const input = document.getElementById('search_material');
    const suggestBox = document.getElementById('suggest_box');
    input.addEventListener('input', function() {
        const keyword = this.value.toLowerCase().trim();
        suggestBox.innerHTML = '';
        if (keyword.length === 0) { suggestBox.classList.add('hidden'); return; }
        const filtered = materialsData.filter(m => 
            (m.name.toLowerCase().includes(keyword) || m.code.toLowerCase().includes(keyword)) &&
            !selectedMaterials.includes(m.id.toString())
        );
        if (filtered.length === 0) {
            suggestBox.innerHTML = `<li class="p-3 text-sm text-slate-500 italic text-center">Bahan tidak ditemukan</li>`;
        } else {
            filtered.forEach(m => {
                const li = document.createElement('li');
                li.className = "p-3 hover:bg-emerald-50 cursor-pointer flex justify-between items-center";
                li.innerHTML = `<div><div class="font-bold text-sm">${m.name}</div><div class="text-xs text-slate-500">${m.code}</div></div>`;
                li.onclick = () => addMaterialToOpname(m);
                suggestBox.appendChild(li);
            });
        }
        suggestBox.classList.remove('hidden');
    });
}

function addMaterialToOpname(material) {
    const list = document.getElementById('opname_list');
    const emptyState = document.getElementById('empty_state');
    if (emptyState) emptyState.remove();
    selectedMaterials.push(material.id.toString());
    updateItemCount();
    const html = `
        <div id="item_row_${material.id}" class="flex flex-col sm:flex-row items-center gap-4 bg-white p-3 rounded-lg border border-slate-200 shadow-sm relative group">
            <input type="hidden" name="material_id[]" value="${material.id}">
            <input type="hidden" name="system_stock[]" value="${material.stock}">
            <div class="flex-1 w-full">
                <h5 class="font-bold text-sm text-slate-800">${material.name} (${material.code})</h5>
                <p class="text-xs text-slate-500">Sistem: ${formatDesimal(material.stock)} ${material.unit}</p>
            </div>
            <div class="flex items-center gap-4 w-full sm:w-auto justify-end">
                <div class="flex flex-col w-28">
                    <label class="text-[10px] font-bold text-slate-400 uppercase mb-1">Stok Fisik</label>
                    <input type="number" step="0.01" name="actual_stock[]" required class="w-full text-center font-black text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg py-1.5 focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="button" onclick="removeMaterial('${material.id}')" class="w-9 h-9 rounded-lg bg-red-50 text-danger hover:bg-danger hover:text-white transition-colors flex items-center justify-center shrink-0 mt-4"><i class="fa-solid fa-trash-can text-sm"></i></button>
            </div>
        </div>`;
    list.insertAdjacentHTML('beforeend', html);
    document.getElementById('search_material').value = '';
    document.getElementById('suggest_box').classList.add('hidden');
}

function removeMaterial(id) {
    document.getElementById(`item_row_${id}`).remove();
    selectedMaterials = selectedMaterials.filter(mId => mId !== id.toString());
    updateItemCount();
}

function updateItemCount() { document.getElementById('item_count').innerText = `${selectedMaterials.length} Item`; }

async function loadHistory() {
    const tbody = document.getElementById('table-body');
    const response = await fetchAjax('logic.php?action=read_history', 'GET');
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="9" class="p-8 text-center text-secondary font-medium">Belum ada riwayat opname.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const dateObj = new Date(item.created_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const diff = parseFloat(item.difference);
                let diffBadge = diff > 0 ? `<span class="text-emerald-600 font-black">+${formatDesimal(diff)}</span>` : `<span class="text-danger font-black">${formatDesimal(diff)}</span>`;
                html += `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 transition-colors">
                        <td class="p-3 text-center text-slate-400 text-xs">${index + 1}</td>
                        <td class="p-3">
                            <div class="font-bold text-slate-700">${tgl}</div>
                            <div class="text-[10px] text-slate-500">${waktu} WIB</div>
                        </td>
                        <td class="p-3 font-mono text-sm text-emerald-600 font-bold">${item.opname_no}</td>
                        <td class="p-3 font-bold text-slate-800 text-sm">${item.material_name}</td>
                        <td class="p-3 text-right font-medium text-slate-500">${formatDesimal(item.system_stock)}</td>
                        <td class="p-3 text-right font-black text-indigo-600">${formatDesimal(item.actual_stock)}</td>
                        <td class="p-3 text-center text-base">${diffBadge}</td>
                        <td class="p-3 text-xs font-medium italic text-slate-600">"${item.reason}"</td>
                        <td class="p-3 text-center text-xs"><div class="bg-slate-100 px-2 py-1 rounded inline-block">${item.petugas}</div></td>
                    </tr>`;
            });
        }
        tbody.innerHTML = html;
    }
}

document.getElementById('formOpname').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (selectedMaterials.length === 0) { Swal.fire('Error', 'Pilih minimal 1 bahan!', 'error'); return; }
    
    const result = await Swal.fire({ title: 'Post Opname?', text: 'Stok akan langsung diperbarui.', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Post!' });
    if (result.isConfirmed) {
        const formData = new FormData(this);
        const response = await fetchAjax('logic.php?action=save', 'POST', formData);
        if (response.status === 'success') {
            closeModal('modal-opname');
            loadHistory();
            Swal.fire('Berhasil', response.message, 'success');
        } else {
            Swal.fire('Gagal', response.message, 'error');
        }
    }
});