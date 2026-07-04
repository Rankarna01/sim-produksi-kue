// ──────────────────────────────────────────────
// UTILITAS: Toast Notification (fix showToast)
// ──────────────────────────────────────────────
function showToast(type, message) {
    // Buat container jika belum ada
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
        document.body.appendChild(container);
    }

    const colors = {
        success: { bg: '#ecfdf5', border: '#6ee7b7', text: '#065f46', icon: '✔' },
        error:   { bg: '#fef2f2', border: '#fca5a5', text: '#991b1b', icon: '✖' },
        warning: { bg: '#fffbeb', border: '#fcd34d', text: '#92400e', icon: '⚠' },
        info:    { bg: '#eff6ff', border: '#93c5fd', text: '#1e40af', icon: 'ℹ' },
    };
    const c = colors[type] || colors.info;

    const toast = document.createElement('div');
    toast.style.cssText = `
        background:${c.bg}; border:1.5px solid ${c.border}; color:${c.text};
        border-radius:12px; padding:12px 18px; font-size:13px; font-weight:600;
        display:flex; align-items:center; gap:10px; min-width:260px; max-width:360px;
        box-shadow:0 8px 24px rgba(0,0,0,.10); pointer-events:auto;
        animation:toastIn .3s cubic-bezier(.22,.68,0,1.2) both;
    `;
    toast.innerHTML = `<span style="font-size:16px">${c.icon}</span><span>${message}</span>`;

    // Injeksi keyframe sekali saja
    if (!document.getElementById('toast-style')) {
        const style = document.createElement('style');
        style.id = 'toast-style';
        style.textContent = `
            @keyframes toastIn  { from{opacity:0;transform:translateX(40px)} to{opacity:1;transform:translateX(0)} }
            @keyframes toastOut { from{opacity:1;transform:translateX(0)} to{opacity:0;transform:translateX(40px)} }
        `;
        document.head.appendChild(style);
    }

    container.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'toastOut .3s ease forwards';
        setTimeout(() => toast.remove(), 320);
    }, 3000);
}

document.addEventListener("DOMContentLoaded", () => {
    initDropdowns();
    loadData();
});

let storeOptions = [];
let roleOptions = [];

async function initDropdowns() {
    const response = await fetchAjax('logic.php?action=init_data', 'GET');
    if (response.status === 'success') {
        storeOptions = response.stores || [];
        roleOptions = response.roles || [];

        const selectStore = document.getElementById('warehouse_id');
        selectStore.innerHTML = '<option value="">🌐 Semua Store (Global Akses)</option>';
        storeOptions.forEach(s => {
            selectStore.innerHTML += `<option value="${s.id}">🏪 [${s.code}] ${s.name}</option>`;
        });

        const selectRole = document.getElementById('role_id');
        selectRole.innerHTML = '';
        roleOptions.forEach(r => {
            selectRole.innerHTML += `<option value="${r.id}">${r.role_name}</option>`;
        });
    }
}

function resetForm() {
    document.getElementById('formKasir').reset();
    document.getElementById('user_id').value = '';
    document.getElementById('modal-title').innerText = 'Tambah Kasir Outlet';
    document.getElementById('pass-hint').classList.add('hidden');
    document.getElementById('password').required = true;
}

async function loadData() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data kasir...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="6" class="p-8 text-center text-secondary">Belum ada akun kasir outlet.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                let badgeStore = item.store_name 
                    ? `<span class="px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold"><i class="fa-solid fa-store mr-1"></i> [${item.store_code}] ${item.store_name}</span>`
                    : `<span class="px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-bold"><i class="fa-solid fa-globe mr-1"></i> Global Akses</span>`;

                let badgeRole = `<span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-xs font-semibold">${item.role_name || 'Kasir'}</span>`;

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4 font-bold text-slate-800">${item.name}</td>
                        <td class="p-4 font-mono text-slate-600 text-sm">@${item.username}</td>
                        <td class="p-4">${badgeRole}</td>
                        <td class="p-4">${badgeStore}</td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick='editData(${JSON.stringify(item)})' class="w-8 h-8 rounded-lg bg-accent/10 text-accent hover:bg-accent hover:text-surface transition-colors flex items-center justify-center" title="Edit"><i class="fa-solid fa-pen text-xs"></i></button>
                                <button onclick="deleteData(${item.id})" class="w-8 h-8 rounded-lg bg-danger/10 text-danger hover:bg-danger hover:text-surface transition-colors flex items-center justify-center" title="Hapus"><i class="fa-solid fa-trash text-xs"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

function editData(item) {
    resetForm();
    document.getElementById('modal-title').innerText = 'Edit Kasir Outlet';
    document.getElementById('user_id').value = item.id;
    document.getElementById('name').value = item.name;
    document.getElementById('username').value = item.username;
    document.getElementById('role_id').value = item.role_id || 2;
    document.getElementById('warehouse_id').value = item.warehouse_id || '';
    
    document.getElementById('password').required = false;
    document.getElementById('pass-hint').classList.remove('hidden');
    openModal('modal-kasir');
}

document.getElementById('formKasir').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'save');

    const btnSubmit = this.querySelector('button[type="submit"]');
    const originalText = btnSubmit.innerHTML;
    btnSubmit.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan...';
    btnSubmit.disabled = true;

    const response = await fetchAjax('logic.php', 'POST', formData);
    
    btnSubmit.innerHTML = originalText;
    btnSubmit.disabled = false;

    if (response.status === 'success') {
        showToast('success', response.message);
        closeModal('modal-kasir');
        loadData();
    } else {
        showToast('error', response.message);
    }
});

async function deleteData(id) {
    const ok = await new Promise(resolve => {
        const confirmed = window.confirm('Yakin ingin menghapus akun kasir ini?\nAkses login ke POS akan terputus.');
        resolve(confirmed);
    });
    if (!ok) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    const response = await fetchAjax('logic.php', 'POST', formData);
    if (response.status === 'success') {
        showToast('success', response.message);
        loadData();
    } else {
        showToast('error', response.message);
    }
}
