let materialsData = [];

document.addEventListener("DOMContentLoaded", () => {
    loadHistory();
    loadMaterials();
});

// Fungsi Format Angka Desimal
function formatDesimal(angka) {
    const num = parseFloat(angka);
    return num % 1 !== 0 ? num.toFixed(2) : num;
}

// 1. Tarik Data Master Bahan Baku
async function loadMaterials() {
    const response = await fetchAjax('logic.php?action=get_materials', 'GET');
    if (response.status === 'success') {
        materialsData = response.data;
        let opt = '<option value="">-- Pilih Bahan Baku --</option>';
        materialsData.forEach(m => {
            opt += `<option value="${m.id}">${m.code} - ${m.name}</option>`;
        });
        document.getElementById('material_id').innerHTML = opt;
    }
}

// 2. Saat Dropdown Bahan Dipilih, Munculkan Stok Sistemnya
function handleMaterialChange() {
    const matId = document.getElementById('material_id').value;
    const systemStockDisplay = document.getElementById('info_system_stock');
    const systemStockInput = document.getElementById('system_stock');
    const unitLabels = document.querySelectorAll('.unit-label');
    const actualInput = document.getElementById('actual_stock');

    actualInput.value = ''; // Reset inputan fisik
    document.getElementById('info_difference').innerText = '-';
    document.getElementById('diff-container').className = "text-center p-3 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50";

    if (!matId) {
        systemStockDisplay.innerText = '0';
        systemStockInput.value = '0';
        unitLabels.forEach(el => el.innerText = 'Satuan');
        return;
    }

    // Cari data bahan dari array
    const mat = materialsData.find(m => m.id == matId);
    if (mat) {
        systemStockDisplay.innerText = formatDesimal(mat.stock);
        systemStockInput.value = mat.stock;
        unitLabels.forEach(el => el.innerText = mat.unit);
    }
}

// 3. Hitung Selisih Real Time Saat Mengetik
function calculateDifference() {
    const system = parseFloat(document.getElementById('system_stock').value) || 0;
    const actual = parseFloat(document.getElementById('actual_stock').value);
    const diffDisplay = document.getElementById('info_difference');
    const diffContainer = document.getElementById('diff-container');
    const unit = document.querySelector('.unit-label').innerText;

    if (isNaN(actual)) {
        diffDisplay.innerText = '-';
        diffContainer.className = "text-center p-3 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50";
        return;
    }

    const difference = actual - system;
    const formattedDiff = formatDesimal(Math.abs(difference));

    if (difference > 0) {
        // Surplus
        diffDisplay.innerHTML = `+ ${formattedDiff} <span class="text-xs">${unit}</span> (SURPLUS)`;
        diffDisplay.className = "text-lg font-black text-emerald-600";
        diffContainer.className = "text-center p-3 rounded-xl border-2 border-emerald-200 bg-emerald-50";
    } else if (difference < 0) {
        // Defisit / Minus
        diffDisplay.innerHTML = `- ${formattedDiff} <span class="text-xs">${unit}</span> (MINUS)`;
        diffDisplay.className = "text-lg font-black text-danger";
        diffContainer.className = "text-center p-3 rounded-xl border-2 border-red-200 bg-danger/10";
    } else {
        // Sesuai
        diffDisplay.innerHTML = `Sesuai / Cocok`;
        diffDisplay.className = "text-lg font-bold text-slate-500";
        diffContainer.className = "text-center p-3 rounded-xl border-2 border-slate-200 bg-slate-50";
    }
}

function resetForm() {
    document.getElementById('formOpname').reset();
    handleMaterialChange(); // Reset UI
}

// 4. Render Tabel Riwayat
async function loadHistory() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read_history', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="8" class="p-8 text-center text-secondary font-medium">Belum ada riwayat penyesuaian stok.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const dateObj = new Date(item.created_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                const diff = parseFloat(item.difference);
                let diffBadge = '';
                
                if (diff > 0) {
                    diffBadge = `<span class="text-emerald-600 font-black">+${formatDesimal(diff)}</span>`;
                } else if (diff < 0) {
                    diffBadge = `<span class="text-danger font-black">${formatDesimal(diff)}</span>`;
                }

                html += `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 transition-colors text-slate-700">
                        <td class="p-3 text-center text-slate-400 text-xs">${index + 1}</td>
                        <td class="p-3">
                            <div class="font-bold text-slate-700">${tgl}</div>
                            <div class="text-[10px] text-slate-500">${waktu} WIB</div>
                        </td>
                        <td class="p-3 font-bold text-slate-800 text-sm">${item.code} - ${item.material_name}</td>
                        <td class="p-3 text-right font-medium text-slate-500">${formatDesimal(item.system_stock)} <span class="text-[10px]">${item.unit}</span></td>
                        <td class="p-3 text-right font-black text-indigo-600">${formatDesimal(item.actual_stock)} <span class="text-[10px]">${item.unit}</span></td>
                        <td class="p-3 text-center text-base">${diffBadge}</td>
                        <td class="p-3 text-xs font-medium text-slate-600 italic">"${item.reason}"</td>
                        <td class="p-3 text-center text-xs font-semibold text-slate-600">
                            <div class="bg-slate-100 px-2 py-1 rounded inline-block">${item.petugas}</div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

// 5. Proses Simpan
document.getElementById('formOpname').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (confirm("Apakah Anda yakin ingin memperbarui stok bahan ini secara permanen?")) {
        const btnSave = document.getElementById('btn-save');
        btnSave.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan...';
        btnSave.disabled = true;

        const formData = new FormData(this);
        const response = await fetchAjax('logic.php?action=save', 'POST', formData);
        
        btnSave.innerHTML = '<i class="fa-solid fa-save"></i> Simpan Penyesuaian';
        btnSave.disabled = false;

        if (response.status === 'success') {
            closeModal('modal-opname');
            loadHistory();
            loadMaterials(); // Refresh stok sistem di array dropdown
            alert("Berhasil! Stok berhasil diperbarui.");
        } else {
            alert('Gagal: ' + response.message);
        }
    }
});