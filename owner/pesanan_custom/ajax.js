document.addEventListener('alpine:init', () => {
    Alpine.data('dapurApp', () => ({
        orders: [], customers: [],
        currentPage: 1, totalPages: 1,
        isLoading: false, isSilentLoading: false, autoRefreshInterval: null,
        editModalOpen: false, editForm: { id: '', customer_id: '', channel: '', pickup_date: '', pickup_time: '' },

        async init() {
            this.isLoading = true;
            await this.loadCustomers();
            await this.loadOrders();
            this.isLoading = false;
            this.autoRefreshInterval = setInterval(() => { this.loadOrders(true); }, 30000);
        },

        async loadCustomers() {
            try {
                const res = await fetch(`logic.php?action=get_customers`);
                const rawText = await res.text(); // Ambil mentahan
                try {
                    const result = JSON.parse(rawText);
                    if(result.status === 'success') this.customers = result.data;
                    else console.error("❌ ERROR PHP (Customers):", result.message);
                } catch(parseErr) {
                    console.error("❌ BUKAN JSON (Customers):", rawText);
                }
            } catch(e) { console.error("Koneksi gagal", e); }
        },

        async loadOrders(isSilent = false) {
            if(!isSilent) this.isLoading = true; else this.isSilentLoading = true;
            try {
                const res = await fetch(`logic.php?action=read&page=${this.currentPage}&nocache=${Date.now()}`);
                const rawText = await res.text(); // Ambil mentahan
                try {
                    const result = JSON.parse(rawText);
                    if (result.status === 'success') {
                        result.data.forEach(o => { o.time = new Date(o.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}); });
                        this.orders = result.data; this.currentPage = result.pagination.current_page; this.totalPages = result.pagination.total_pages;
                    } else {
                        console.error("❌ ERROR PHP (Orders):", result.message);
                    }
                } catch(parseErr) {
                    console.error("❌ BUKAN JSON (Orders):", rawText);
                }
            } catch (e) { console.error("Jaringan error", e); }
            finally { this.isLoading = false; this.isSilentLoading = false; }
        },

        changePage(page) {
            if(page < 1 || page > this.totalPages) return;
            this.currentPage = page; this.loadOrders();
        },

        async updateStatus(id, newStatus) {
            let txt = newStatus === 'diproses' ? "Mulai kerjakan pesanan ini?" : "Tandai pesanan selesai dan siap diambil?";
            if(!confirm(txt)) return;
            try {
                const fd = new FormData(); fd.append('id', id); fd.append('status', newStatus);
                const res = await fetch('logic.php?action=update_status', { method: 'POST', body: fd });
                const result = await res.json();
                if(result.status === 'success') this.loadOrders();
                else Swal.fire('Gagal', result.message, 'error');
            } catch(e) { Swal.fire('Error', 'Kesalahan koneksi', 'error'); }
        },

        openEditModal(order) {
            this.editForm = { id: order.id, customer_id: order.customer_id || '', channel: order.channel || 'toko', pickup_date: order.pickup_date || '', pickup_time: order.pickup_time || '' };
            this.editModalOpen = true;
        },

        async submitEditPO() {
            try {
                const fd = new FormData();
                for (let key in this.editForm) { fd.append(key, this.editForm[key]); }
                const res = await fetch('logic.php?action=update_po', { method: 'POST', body: fd });
                const result = await res.json();
                if(result.status === 'success') {
                    this.editModalOpen = false;
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: result.message, showConfirmButton: false, timer: 1500 });
                    this.loadOrders();
                } else { Swal.fire('Gagal', result.message, 'error'); }
            } catch(e) { Swal.fire('Error', 'Kesalahan koneksi', 'error'); }
        },

        async saveToCatalog(item) {
            if(!confirm(`Simpan "${item.custom_name}" sebagai produk permanen di Kasir?`)) return;
            try {
                const fd = new FormData(); fd.append('name', item.custom_name); fd.append('price', item.price);
                const res = await fetch('logic.php?action=save_to_catalog', { method: 'POST', body: fd });
                const result = await res.json();
                if(result.status === 'success') { Swal.fire('Tersimpan!', result.message, 'success'); } 
                else { Swal.fire('Gagal', result.message, 'error'); }
            } catch(e) { Swal.fire('Error', 'Kesalahan koneksi', 'error'); }
        }
    }));
});