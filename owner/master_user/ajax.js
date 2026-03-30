document.addEventListener("DOMContentLoaded", () => {
    loadData();
});

function resetForm() {
    document.getElementById('formUser').reset();
    document.getElementById('user_id').value = '';
    document.getElementById('modal-title').innerText = 'Tambah Akun Baru';
    document.getElementById('password_input').required = true;
    document.getElementById('password_help').innerText = 'Wajib diisi untuk akun baru.';
}

async function loadData() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="5" class="p-8 text-center text-secondary">Belum ada data user.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                // Konfigurasi Badge Warna sesuai Role
                let roleBadge = '';
                if(item.role === 'owner') roleBadge = `<span class="bg-primary/10 text-primary border border-primary/20 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Owner</span>`;
                else if(item.role === 'produksi') roleBadge = `<span class="bg-accent/10 text-accent border border-accent/20 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Produksi</span>`;
                else if(item.role === 'admin') roleBadge = `<span class="bg-success/10 text-success border border-success/20 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Admin</span>`;

                html += `
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4 font-bold text-slate-800">${item.name}</td>
                        <td class="p-4 text-secondary font-mono">${item.username}</td>
                        <td class="p-4 text-center">${roleBadge}</td>
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

document.getElementById('formUser').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save', 'POST', formData);
    
    if (response.status === 'success') {
        closeModal('modal-user');
        loadData(); 
    } else {
        alert('Gagal: ' + response.message);
    }
});

function editData(item) {
    document.getElementById('user_id').value = item.id;
    document.getElementById('name').value = item.name;
    document.getElementById('username_input').value = item.username;
    document.getElementById('role_input').value = item.role;
    
    // Konfigurasi khusus saat edit (password tidak wajib diisi)
    document.getElementById('password_input').value = '';
    document.getElementById('password_input').required = false;
    document.getElementById('password_help').innerText = 'Kosongkan jika tidak ingin mengubah password lama.';
    
    document.getElementById('modal-title').innerText = 'Edit Akun User';
    openModal('modal-user');
}

async function deleteData(id) {
    if (confirm('Yakin ingin menghapus akun ini secara permanen?')) {
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