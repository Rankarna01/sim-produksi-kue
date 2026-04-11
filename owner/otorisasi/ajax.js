document.addEventListener("DOMContentLoaded", () => {
    loadRiwayat();
});

async function loadRiwayat() {
    const tbody = document.getElementById('table-otorisasi');
    const response = await fetchAjax('logic.php?action=read', 'GET');

    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="5" class="p-8 text-center text-secondary font-medium">Belum ada riwayat kode akses.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const now = new Date();
                const validUntil = new Date(item.valid_until);
                const isExpired = now > validUntil;
                
                let badge = '';
                if (item.is_used == 1) {
                    badge = '<span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-full text-[10px] font-black uppercase">Terpakai</span>';
                } else if (isExpired) {
                    badge = '<span class="bg-red-50 text-red-500 px-3 py-1 rounded-full text-[10px] font-black uppercase">Expired</span>';
                } else {
                    badge = '<span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">Aktif</span>';
                }

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-slate-400 font-mono text-xs">${index + 1}</td>
                        <td class="p-4 font-black text-indigo-600 tracking-widest text-base">${item.auth_code}</td>
                        <td class="p-4 text-slate-500 text-xs">${formatTanggal(item.created_at)}</td>
                        <td class="p-4 text-slate-500 text-xs">${formatTanggal(item.valid_until)}</td>
                        <td class="p-4 text-center">${badge}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

async function generateAccessCode() {
    Swal.fire({ title: 'Tunggu sebentar...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

    const response = await fetchAjax('logic.php?action=generate', 'GET');
    Swal.close();

    if (response.status === 'success') {
        document.getElementById('display-code').innerText = response.code;
        document.getElementById('display-expiry').innerText = response.valid_until;

        const modal = document.getElementById('modal-auth-success');
        const card = document.getElementById('auth-card');
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }, 10);
        
        loadRiwayat();
    }
}

function closeAuthModal() {
    const modal = document.getElementById('modal-auth-success');
    const card = document.getElementById('auth-card');
    card.classList.add('scale-95', 'opacity-0');
    setTimeout(() => { modal.classList.add('hidden'); }, 200);
}

function formatTanggal(tglString) {
    const d = new Date(tglString);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ', ' + 
           d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
}