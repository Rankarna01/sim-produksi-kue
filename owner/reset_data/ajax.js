document.addEventListener("DOMContentLoaded", () => {
    loadStats();
});

function togglePasswordVisibility() {
    const pwd = document.getElementById('ownerPassword');
    const icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

async function loadStats() {
    const grid = document.getElementById('statsGrid');
    grid.innerHTML = `
        <div class="col-span-full p-8 text-center bg-white rounded-3xl border border-slate-200">
            <i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i>
            <p class="text-xs font-bold text-slate-400 mt-2">Menghitung data transaksi...</p>
        </div>
    `;

    try {
        const res = await fetch('logic.php?action=get_stats');
        const json = await res.json();

        if (json.status === 'success' && json.data) {
            const d = json.data;
            grid.innerHTML = `
                <!-- 1. Transaksi Produksi -->
                <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-slate-400">Produksi</span>
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-fire-burner"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-800">${d.transaksi_produksi.toLocaleString()}</div>
                        <p class="text-[11px] font-medium text-slate-500 mt-0.5">Riwayat Batch & Rencana</p>
                    </div>
                </div>

                <!-- 2. PO & PR -->
                <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-slate-400">Pengadaan</span>
                        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-800">${d.transaksi_po_pr.toLocaleString()}</div>
                        <p class="text-[11px] font-medium text-slate-500 mt-0.5">PO Vendor & PR Barang</p>
                    </div>
                </div>

                <!-- 3. Logistik & Mutasi -->
                <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-slate-400">Logistik</span>
                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-boxes-packing"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-800">${d.transaksi_logistik.toLocaleString()}</div>
                        <p class="text-[11px] font-medium text-slate-500 mt-0.5">Barang Masuk & Keluar</p>
                    </div>
                </div>

                <!-- 4. Riwayat Opname -->
                <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-slate-400">Opname</span>
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-800">${d.riwayat_opname.toLocaleString()}</div>
                        <p class="text-[11px] font-medium text-slate-500 mt-0.5">Riwayat Hitung Stok</p>
                    </div>
                </div>

                <!-- 5. Akun Login (Aman) -->
                <div class="p-5 rounded-3xl bg-emerald-50/50 border border-emerald-200 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-emerald-700">Akun Login</span>
                        <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-emerald-800">${d.user_login_aman.toLocaleString()}</div>
                        <p class="text-[11px] font-bold text-emerald-600 mt-0.5">Akun Aman & Terlindungi</p>
                    </div>
                </div>
            `;
        }
    } catch (e) {
        grid.innerHTML = `
            <div class="col-span-full p-4 bg-red-50 text-red-600 text-xs font-bold rounded-2xl">
                Gagal memuat statistik database.
            </div>
        `;
    }
}

// Form Submission & Reset Action
document.getElementById('formReset').addEventListener('submit', async function(e) {
    e.preventDefault();

    const confirmText = document.getElementById('confirmText').value.trim();
    const password = document.getElementById('ownerPassword').value;
    const mode = document.querySelector('input[name="resetMode"]:checked').value;

    if (confirmText !== 'RESET DATA LIVE') {
        Swal.fire({
            title: 'Frasa Salah',
            text: 'Silakan ketik teks "RESET DATA LIVE" dengan huruf kapital persis.',
            icon: 'warning'
        });
        return;
    }

    if (!password) {
        Swal.fire('Password Wajib Diisi', 'Masukkan password login Owner Anda.', 'warning');
        return;
    }

    const modeText = (mode === 'total') 
        ? 'Mode: FACTORY RESET TOTAL (Seluruh transaksi + Master Produk/Resep/Bahan akan dibersihkan).' 
        : 'Mode: STANDAR GO-LIVE (Seluruh transaksi dihapus, semua stok jadi 0, Master Produk & Resep dipertahankan).';

    // Konfirmasi Tingkat Tinggi
    const result = await Swal.fire({
        title: 'Konfirmasi Reset Database',
        html: `
            <div class="text-left text-xs space-y-3 text-slate-600">
                <p class="font-bold text-rose-600 text-sm">PERINGATAN: Aksi ini tidak dapat dibatalkan!</p>
                <p>${modeText}</p>
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 font-medium">
                    Apakah Anda sudah men-download backup data melalui tombol di atas?
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Eksekusi Reset Sekarang',
        cancelButtonText: 'Batal'
    });

    if (!result.isConfirmed) return;

    Swal.fire({
        title: 'Mereset Database...',
        text: 'Mengosongkan transaksi dan menginisialisasi ulang sistem.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => { Swal.showLoading(); }
    });

    try {
        const formData = new FormData();
        formData.append('action', 'execute_reset');
        formData.append('confirm_text', confirmText);
        formData.append('password', password);
        formData.append('mode', mode);

        const res = await fetch('logic.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (data.status === 'success') {
            await Swal.fire({
                title: 'Reset Berhasil!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#2563eb'
            });

            // Reset form
            document.getElementById('formReset').reset();
            loadStats();
        } else {
            Swal.fire({
                title: 'Gagal Mereset',
                text: data.message,
                icon: 'error'
            });
        }
    } catch (err) {
        Swal.fire('Terjadi Kesalahan', err.message, 'error');
    }
});
