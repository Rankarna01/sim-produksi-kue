let currentPage = 1;
let searchTimeout = null;

document.addEventListener("DOMContentLoaded", async () => {
    await initRoles();
    loadData(1);
});

function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }

async function initRoles() {
    const res = await fetchAjax('logic.php?action=get_roles', 'GET');
    if (res.status === 'success') {
        let opt = '<option value="">-- Pilih Jabatan --</option>';
        res.data.forEach(r => { opt += `<option value="${r.role_slug}">${r.role_name}</option>`; });
        document.getElementById('role').innerHTML = opt;
    }
}

function bukaModalTambah() {
    document.getElementById('form-user').reset();
    document.getElementById('user_id').value = '';
    document.getElementById('modal-title').innerHTML = '<i class="fa-solid fa-user-plus text-blue-600"></i> Tambah User Baru';
    
    // Wajibkan password
    document.getElementById('password').required = true;
    document.getElementById('req-pass').classList.remove('hidden');
    document.getElementById('help-pass').innerText = 'Minimal 6 karakter.';
    
    openModal('modal-user');
}

function cariData() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { loadData(1); }, 500);
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const search = document.getElementById('search').value;
    const res = await fetchAjax(`logic.php?action=read&page=${currentPage}&search=${search}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="6" class="p-10 text-center text-slate-400 font-bold italic">Tidak ada user gudang yang ditemukan.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                const no = (currentPage - 1) * 10 + idx + 1;
                
                // Badge Status
                let statusBadge = item.status === 'active' 
                    ? '<span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-check mr-1"></i> Aktif</span>' 
                    : '<span class="bg-rose-50 text-rose-500 border border-rose-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-ban mr-1"></i> Non-Aktif</span>';

                // Inisial Avatar
                let inisial = item.name.substring(0, 2).toUpperCase();

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-5 text-center text-xs font-bold text-slate-400">${no}</td>
                        <td class="p-5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-black text-xs shrink-0">${inisial}</div>
                                <span class="font-black text-slate-800 text-sm">${item.name}</span>
                            </div>
                        </td>
                        <td class="p-5 font-mono text-xs font-bold text-slate-500">${item.username}</td>
                        <td class="p-5 font-bold text-slate-700 text-xs">${item.role_name}</td>
                        <td class="p-5 text-center">${statusBadge}</td>
                        <td class="p-5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick='editData(${JSON.stringify(item).replace(/'/g, "&apos;")})' class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Edit">
                                    <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                </button>
                                <button onclick="hapusData(${item.id})" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Hapus">
                                    <i class="fa-solid fa-trash-can text-[10px]"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(res.total_pages, res.current_page);
    }
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination');
    let html = '';
    if (totalPages <= 1) { container.innerHTML = ''; return; }

    for (let i = 1; i <= totalPages; i++) {
        const active = i === current ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200';
        html += `<button onclick="loadData(${i})" class="w-8 h-8 rounded-lg font-black text-xs transition-all ${active}">${i}</button>`;
    }
    container.innerHTML = html;
}

function editData(item) {
    document.getElementById('form-user').reset();
    document.getElementById('modal-title').innerHTML = '<i class="fa-solid fa-user-pen text-blue-600"></i> Edit Data User';
    
    document.getElementById('user_id').value = item.id;
    document.getElementById('name').value = item.name;
    document.getElementById('username').value = item.username;
    document.getElementById('role').value = item.role;
    document.getElementById('status').value = item.status;
    
    // Password opsional saat edit
    document.getElementById('password').required = false;
    document.getElementById('req-pass').classList.add('hidden');
    document.getElementById('help-pass').innerText = 'Kosongkan jika tidak ingin mengubah password.';
    
    openModal('modal-user');
}

document.getElementById('form-user').addEventListener('submit', async function(e) {
    e.preventDefault();
    Swal.fire({ title: 'Menyimpan...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

    const formData = new FormData(this);
    
    // 👇 INI OBATNYA TEMAN: Tambahkan aksi 'save' ke dalam form 👇
    formData.append('action', 'save');

    const res = await fetchAjax('logic.php', 'POST', formData);
    
    if (res.status === 'success') {
        closeModal('modal-user');
        loadData(currentPage);
        Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
    } else {
        Swal.fire('Gagal!', res.message, 'error');
    }
});

async function hapusData(id) {
    const result = await Swal.fire({
        title: 'Hapus Akun User?',
        text: "User ini tidak akan bisa login lagi ke dalam sistem Gudang.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        confirmButtonText: 'Ya, Hapus!'
    });

    if (result.isConfirmed) {
        Swal.fire({ title: 'Menghapus...', icon: 'info', showConfirmButton: false });
        const formData = new FormData(); 
        formData.append('action', 'delete'); 
        formData.append('id', id);
        
        const res = await fetchAjax('logic.php', 'POST', formData);
        if (res.status === 'success') {
            loadData(currentPage);
            Swal.fire({ title: 'Terhapus!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}