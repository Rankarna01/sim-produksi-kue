<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistem Produksi Kue</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Poppins', 'sans-serif'] },
                colors: {
                    surface: '#FFFFFF',
                    background: '#F8FAFC',
                    primary: '#2563EB',
                    secondary: '#94A3B8',
                    accent: '#F59E0B',
                    danger: '#EF4444',
                    success: '#10B981'
                }
            }
        }
    }
</script>

<style>
    body { font-family: 'Poppins', sans-serif; background-color: theme('colors.background'); }
    #global-loader { display: none; backdrop-filter: blur(4px); }
    
    /* Modifikasi SweetAlert agar fontnya ikut Poppins */
    div:where(.swal2-container) { font-family: 'Poppins', sans-serif; }
</style> 

<script>
    // 1. FUNGSI OVERRIDE ALERT()
    window.alert = function(message) {
        let type = 'info';
        let msgStr = String(message).toLowerCase();
        
        // Deteksi cerdas jenis pesan berdasarkan kata kunci
        if(msgStr.includes('berhasil') || msgStr.includes('success') || msgStr.includes('dicatat') || msgStr.includes('disimpan')) type = 'success';
        if(msgStr.includes('gagal') || msgStr.includes('error') || msgStr.includes('maaf')) type = 'error';
        if(msgStr.includes('pilih') || msgStr.includes('wajib') || msgStr.includes('harap')) type = 'warning';

        // Tampilan Success (Toast di pojok kanan atas)
        if (type === 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                customClass: { popup: 'rounded-xl shadow-lg border border-slate-100 mt-4 mr-4' },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({ icon: 'success', title: message });
        } 
        // Tampilan Gagal / Warning (Popup di tengah)
        else {
            Swal.fire({
                title: type === 'error' ? 'Oops! Ada Masalah' : (type === 'warning' ? 'Perhatian' : 'Informasi'),
                html: `<p style="color: #475569; font-weight: 500; font-size: 14px;">${message}</p>`,
                icon: type,
                confirmButtonText: 'Mengerti',
                confirmButtonColor: type === 'error' ? '#EF4444' : (type === 'warning' ? '#F59E0B' : '#2563EB'),
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border border-slate-100',
                    title: 'text-xl font-extrabold text-slate-800'
                }
            });
        }
    };

    // 2. FUNGSI CUSTOM CONFIRM ()
    window.customConfirm = function(message, callback) {
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            html: `<p style="color: #475569; font-weight: 500; font-size: 14px;">${message}</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444', // Warna Merah untuk tombol aksi
            cancelButtonColor: '#94A3B8',  // Warna Abu-abu untuk tombol batal
            confirmButtonText: '<i class="fa-solid fa-check mr-1"></i> Ya, Lanjutkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true, // Posisi tombol batal di kiri, ok di kanan
            customClass: { 
                popup: 'rounded-3xl shadow-2xl border border-slate-100', 
                title: 'text-xl font-extrabold text-slate-800' 
            }
        }).then((result) => {
            if (result.isConfirmed) {
                callback(); // Jalankan perintah jika user klik Ya
            }
        });
    };
</script>