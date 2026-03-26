document.addEventListener("DOMContentLoaded", () => {
    loadData();
});

function resetForm() {
    document.getElementById('formBahan').reset();
    document.getElementById('material_id').value = '';
    document.getElementById('modal-title').innerText = 'Tambah Bahan Baku';
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
                // Logika Highlight Peringatan Stok
                const currentStock = parseFloat(item.stock);
                const minStock = parseFloat(item.min_stock);
                
                let stockClass = "text-slate-700"; // Default warna
                let warningIcon = "";
                
                // Jika stok aktual <= minimum stok, jadikan merah dan beri ikon peringatan
                if (currentStock <= minStock) {
                    stockClass = "text-danger font-bold";
                    warningIcon = `<i class="fa-solid fa-triangle-exclamation text-danger ml-2" title="Stok Menipis!"></i>`;
                }

                html += `
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4 font-semibold text-slate-700">${item.code}</td>
                        <td class="p-4 text-slate-800">${item.name}</td>
                        <td class="p-4 text-center"><span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs">${item.unit}</span></td>
                        <td class="p-4 text-right ${stockClass} text-base">${currentStock} ${warningIcon}</td>
                        <td class="p-4 text-right text-secondary">${minStock}</td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button onclick='editData(${JSON.stringify(item)})' class="w-8 h-8 rounded-lg bg-accent/10 text-accent hover:bg-accent hover:text-surface transition-colors flex items-center justify-center" title="Edit">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                <button onclick="deleteData(${item.id})" class="w-8 h-8 rounded-lg bg-danger/10 text-danger hover:bg-danger hover:text-surface transition-colors flex items-center justify-center" title="Hapus">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
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
    document.getElementById('stock').value = item.stock;
    document.getElementById('min_stock').value = item.min_stock;
    
    document.getElementById('modal-title').innerText = 'Edit Bahan Baku';
    openModal('modal-bahan');
}

async function deleteData(id) {
    if (confirm('Yakin ingin menghapus bahan baku ini? Pastikan bahan ini belum terpakai di resep manapun.')) {
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetchAjax('logic.php?action=delete', 'POST', formData);
        if (response.status === 'success') {
            loadData();
        } else {
            alert('Gagal menghapus: ' + response.message);
        }
    }
}