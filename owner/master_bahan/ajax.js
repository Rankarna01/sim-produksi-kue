document.addEventListener("DOMContentLoaded", () => {
    loadUnits(); 
    loadData();
});

async function loadUnits() {
    const select = document.getElementById('unit');
    const response = await fetchAjax('logic.php?action=get_units', 'GET');
    if (response.status === 'success') {
        let options = '<option value="">-- Pilih --</option>';
        response.data.forEach(u => {
            options += `<option value="${u.name}">${u.name}</option>`;
        });
        select.innerHTML = options;
    } else {
        select.innerHTML = '<option value="">Gagal muat</option>';
    }
}

function resetForm() {
    document.getElementById('formBahan').reset();
    document.getElementById('material_id').value = '';
    document.getElementById('modal-title').innerText = 'Tambah Bahan Baku';
}

function formatDesimal(angka) {
    const num = parseFloat(angka);
    return num % 1 !== 0 ? num.toFixed(2) : num;
}

async function loadData() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="7" class="p-8 text-center text-secondary">Belum ada data bahan baku.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const currentStock = parseFloat(item.stock);
                const minStock = parseFloat(item.min_stock);
                const displayStock = formatDesimal(currentStock);
                const displayMin = formatDesimal(minStock);
                
                let stockClass = "text-slate-800"; 
                let warningIcon = "";
                if (currentStock <= minStock) {
                    stockClass = "text-danger font-bold";
                    warningIcon = `<i class="fa-solid fa-triangle-exclamation text-danger ml-2" title="Stok Menipis!"></i>`;
                }

                // SUNTIKAN: Tombol aksi dinamis RBAC
                let btnAksi = '';
                if (canEdit) {
                    btnAksi += `<button onclick='editData(${JSON.stringify(item)})' class="w-8 h-8 rounded-lg bg-accent/10 text-accent hover:bg-accent hover:text-surface transition-colors flex items-center justify-center shadow-sm" title="Edit"><i class="fa-solid fa-pen text-xs"></i></button>&nbsp;`;
                }
                if (canDelete) {
                    btnAksi += `<button onclick="deleteData(${item.id})" class="w-8 h-8 rounded-lg bg-danger/10 text-danger hover:bg-danger hover:text-surface transition-colors flex items-center justify-center shadow-sm" title="Hapus"><i class="fa-solid fa-trash text-xs"></i></button>`;
                }
                if (btnAksi === '') {
                    btnAksi = '<span class="text-[10px] font-bold text-slate-400">Akses Dibatasi</span>';
                }

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4 font-bold text-slate-700">${item.code}</td>
                        <td class="p-4 font-semibold text-slate-800">${item.name}</td>
                        <td class="p-4 text-center"><span class="bg-slate-100 border border-slate-200 text-slate-600 px-3 py-1 rounded-lg text-xs font-semibold">${item.unit}</span></td>
                        <td class="p-4 text-right ${stockClass} text-base font-black">${displayStock} ${warningIcon}</td>
                        <td class="p-4 text-right text-slate-500 font-semibold">${displayMin}</td>
                        <td class="p-4 text-center print:hidden">
                            <div class="flex items-center justify-center gap-1">
                                ${btnAksi}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

document.getElementById('formBahan').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save', 'POST', formData);
    if (response.status === 'success') {
        closeModal('modal-bahan');
        loadData(); 
    } else {
        alert('Gagal: ' + response.message);
    }
});

function editData(item) {
    document.getElementById('material_id').value = item.id;
    document.getElementById('code').value = item.code;
    document.getElementById('name').value = item.name;
    document.getElementById('unit').value = item.unit;
    document.getElementById('stock').value = parseFloat(item.stock);
    document.getElementById('min_stock').value = parseFloat(item.min_stock);
    document.getElementById('modal-title').innerText = 'Edit Bahan Baku';
    openModal('modal-bahan');
}

function deleteData(id) {
    customConfirm('Yakin ingin menghapus bahan baku ini? Pastikan bahan ini belum terpakai di resep manapun.', async () => {
        const formData = new FormData();
        formData.append('id', id);
        const response = await fetchAjax('logic.php?action=delete', 'POST', formData);
        if (response.status === 'success') {
            loadData();
            alert('Berhasil menghapus bahan baku!'); 
        } else {
            alert('Gagal menghapus: ' + response.message); 
        }
    });
}

function downloadTemplate() {
    window.location.href = 'logic.php?action=download_template';
}

document.getElementById('formImport').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btnImport = document.getElementById('btn-import');
    btnImport.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses...';
    btnImport.disabled = true;
    const formData = new FormData(this);
    try {
        const response = await fetchAjax('logic.php?action=import_csv', 'POST', formData);
        if (response.status === 'success') {
            alert(response.message);
            closeModal('modal-import');
            loadData();
        } else {
            alert('Gagal Import: ' + response.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan saat mengunggah file.');
    } finally {
        btnImport.innerHTML = '<i class="fa-solid fa-upload"></i> Proses Import';
        btnImport.disabled = false;
    }
});