document.addEventListener("DOMContentLoaded", () => {
    loadUsers();
    loadEmployees();
});

// FUNGSI SWITCH TAB
function switchTab(tabId) {
    document.getElementById('tab-akun').classList.add('hidden');
    document.getElementById('tab-karyawan').classList.add('hidden');
    
    document.getElementById('btn-tab-akun').className = "pb-3 text-sm font-bold border-b-2 border-transparent text-secondary hover:text-slate-700 transition-colors";
    document.getElementById('btn-tab-karyawan').className = "pb-3 text-sm font-bold border-b-2 border-transparent text-secondary hover:text-slate-700 transition-colors";

    document.getElementById(tabId).classList.remove('hidden');
    document.getElementById(`btn-${tabId}`).className = "pb-3 text-sm font-bold border-b-2 border-primary text-primary transition-colors";
}

// FUNGSI CETAK LAPORAN 
function cetakLaporan(activeTabId) {
    document.querySelectorAll('.printable-area').forEach(el => el.classList.add('hidden'));
    document.getElementById(activeTabId).classList.remove('hidden');
    
    window.print();
    
    setTimeout(() => {
        switchTab(activeTabId);
    }, 500);
}


// ==============================================================
// LOGIKA TAB 1: AKUN LOGIN SISTEM (USERS)
// ==============================================================
function resetFormUser() {
    document.getElementById('formUser').reset();
    document.getElementById('user_id').value = '';
    document.getElementById('modal-title-user').innerText = 'Tambah Akun Sistem';
    document.getElementById('password_input').required = true;
    document.getElementById('password_help').innerText = 'Wajib diisi untuk akun baru.';
}

async function loadUsers() {
    const tbody = document.getElementById('table-user');
    tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read_users', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="5" class="p-8 text-center text-secondary">Belum ada data user.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                let roleBadge = '';
                if(item.role === 'owner') roleBadge = `<span class="bg-primary/10 text-primary border border-primary/20 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider print:border-none print:text-black print:p-0">Owner</span>`;
                else if(item.role === 'produksi') roleBadge = `<span class="bg-accent/10 text-accent border border-accent/20 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider print:border-none print:text-black print:p-0">T. Produksi</span>`;
                else if(item.role === 'admin') roleBadge = `<span class="bg-success/10 text-success border border-success/20 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider print:border-none print:text-black print:p-0">Admin</span>`;
                else if(item.role === 'auditor') roleBadge = `<span class="bg-indigo-100 text-indigo-600 border border-indigo-200 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider print:border-none print:text-black print:p-0">Auditor</span>`;

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4 font-bold text-slate-800">${item.name}</td>
                        <td class="p-4 text-secondary font-mono text-sm">${item.username}</td>
                        <td class="p-4 text-center">${roleBadge}</td>
                        <td class="p-4 text-center btn-aksi">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick='editUser(${JSON.stringify(item).replace(/'/g, "&apos;")})' class="w-8 h-8 rounded-lg bg-accent/10 text-accent hover:bg-accent hover:text-surface transition-colors flex items-center justify-center"><i class="fa-solid fa-pen text-xs"></i></button>
                                <button onclick="deleteUser(${item.id})" class="w-8 h-8 rounded-lg bg-danger/10 text-danger hover:bg-danger hover:text-surface transition-colors flex items-center justify-center"><i class="fa-solid fa-trash text-xs"></i></button>
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
    
    // Tampilkan loading SweetAlert
    Swal.fire({ title: 'Menyimpan...', text: 'Mohon tunggu', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save_user', 'POST', formData);
    
    if (response.status === 'success') {
        closeModal('modal-user');
        loadUsers(); 
        Swal.fire({ title: 'Berhasil!', text: response.message, icon: 'success', timer: 1500, showConfirmButton: false });
    } else {
        Swal.fire('Gagal!', response.message, 'error');
    }
});

function editUser(item) {
    document.getElementById('user_id').value = item.id;
    document.getElementById('name').value = item.name;
    document.getElementById('username_input').value = item.username;
    document.getElementById('role_input').value = item.role;
    
    document.getElementById('password_input').value = '';
    document.getElementById('password_input').required = false;
    document.getElementById('password_help').innerText = 'Kosongkan jika tidak ingin mengubah password lama.';
    
    document.getElementById('modal-title-user').innerText = 'Edit Akun Sistem';
    openModal('modal-user');
}

async function deleteUser(id) {
    // Gunakan Konfirmasi Custom (SweetAlert)
    const result = await Swal.fire({
        title: 'Hapus Akun?',
        text: "Akun ini akan dihapus secara permanen dan tidak dapat login lagi!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#94A3B8',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    });

    if (result.isConfirmed) {
        Swal.fire({ title: 'Menghapus...', text: 'Mohon tunggu', icon: 'info', allowOutsideClick: false, showConfirmButton: false });
        
        const formData = new FormData();
        formData.append('id', id);
        const response = await fetchAjax('logic.php?action=delete_user', 'POST', formData);
        
        if (response.status === 'success') {
            loadUsers();
            Swal.fire({ title: 'Terhapus!', text: 'Akun berhasil dihapus.', icon: 'success', timer: 1500, showConfirmButton: false });
        } else {
            Swal.fire('Gagal!', response.message, 'error');
        }
    }
}


// ==============================================================
// LOGIKA TAB 2: DAFTAR KARYAWAN (EMPLOYEES)
// ==============================================================
function resetFormKaryawan() {
    document.getElementById('formKaryawan').reset();
    document.getElementById('emp_id').value = '';
    document.getElementById('modal-title-karyawan').innerText = 'Tambah Karyawan Dapur';
}

async function loadEmployees() {
    const tbody = document.getElementById('table-karyawan');
    tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read_employees', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="4" class="p-8 text-center text-secondary">Belum ada daftar karyawan. Silakan tambah.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const dateObj = new Date(item.created_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4 font-black text-indigo-900 text-lg">${item.name}</td>
                        <td class="p-4 text-secondary text-sm">${tgl}</td>
                        <td class="p-4 text-center btn-aksi">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick='editEmployee(${JSON.stringify(item).replace(/'/g, "&apos;")})' class="w-8 h-8 rounded-lg bg-accent/10 text-accent hover:bg-accent hover:text-surface transition-colors flex items-center justify-center"><i class="fa-solid fa-pen text-xs"></i></button>
                                <button onclick="deleteEmployee(${item.id})" class="w-8 h-8 rounded-lg bg-danger/10 text-danger hover:bg-danger hover:text-surface transition-colors flex items-center justify-center"><i class="fa-solid fa-trash text-xs"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

document.getElementById('formKaryawan').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    Swal.fire({ title: 'Menyimpan...', text: 'Mohon tunggu', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save_employee', 'POST', formData);
    
    if (response.status === 'success') {
        closeModal('modal-karyawan');
        loadEmployees(); 
        Swal.fire({ title: 'Berhasil!', text: response.message, icon: 'success', timer: 1500, showConfirmButton: false });
    } else {
        Swal.fire('Gagal!', response.message, 'error');
    }
});

function editEmployee(item) {
    document.getElementById('emp_id').value = item.id;
    document.getElementById('emp_name').value = item.name;
    document.getElementById('modal-title-karyawan').innerText = 'Edit Nama Karyawan';
    openModal('modal-karyawan');
}

async function deleteEmployee(id) {
    // Gunakan Konfirmasi Custom (SweetAlert)
    const result = await Swal.fire({
        title: 'Hapus Karyawan?',
        text: "Karyawan ini tidak akan bisa dipilih lagi di form Dapur!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#94A3B8',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    });

    if (result.isConfirmed) {
        Swal.fire({ title: 'Menghapus...', text: 'Mohon tunggu', icon: 'info', allowOutsideClick: false, showConfirmButton: false });
        
        const formData = new FormData();
        formData.append('id', id);
        const response = await fetchAjax('logic.php?action=delete_employee', 'POST', formData);
        
        if (response.status === 'success') {
            loadEmployees();
            Swal.fire({ title: 'Terhapus!', text: 'Karyawan berhasil dihapus.', icon: 'success', timer: 1500, showConfirmButton: false });
        } else {
            Swal.fire('Gagal!', response.message, 'error');
        }
    }
}