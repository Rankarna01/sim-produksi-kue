document.addEventListener("DOMContentLoaded", () => {
    loadData();
});

function resetForm() {
    document.getElementById('formSatuan').reset();
    document.getElementById('unit_id').value = '';
    document.getElementById('modal-title').innerText = 'Tambah Satuan';
}

async function loadData() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="3" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="3" class="p-8 text-center text-secondary">Belum ada data satuan.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
    <td class="p-4 text-center text-secondary">${index + 1}</td>
    <td class="p-4 font-bold text-slate-800">${item.name}</td>
    <td class="p-4 text-center">
        <div class="flex items-center justify-center gap-2">
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

document.getElementById('formSatuan').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save', 'POST', formData);
    
    if (response.status === 'success') {
        closeModal('modal-satuan');
        loadData(); 
    } else {
        alert('Gagal: ' + response.message);
    }
});

function editData(item) {
    document.getElementById('unit_id').value = item.id;
    document.getElementById('name').value = item.name;
    
    document.getElementById('modal-title').innerText = 'Edit Satuan';
    openModal('modal-satuan');
}

// async function deleteData(id) {
//     if (confirm('Yakin ingin menghapus satuan ini?')) {
//         const formData = new FormData();
//         formData.append('id', id);
        
//         const response = await fetchAjax('logic.php?action=delete', 'POST', formData);
//         if (response.status === 'success') {
//             loadData();
//         } else {
//             alert('Gagal menghapus: ' + response.message);
//         }
//     }
// }


async function deleteData(id) {
    // Memanggil customConfirm yang sudah kita buat di head.php
    customConfirm('Yakin ingin menghapus data satuan ini?', async () => {
        
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetchAjax('logic.php?action=delete', 'POST', formData);
        
        if (response.status === 'success') {
            loadData();
            // Tambahkan alert sukses agar Toast hijau muncul di layar!
            alert('Berhasil menghapus Data Satuan!'); 
        } else {
            // Memunculkan popup error merah
            alert('Gagal menghapus: ' + response.message); 
        }

    });
}