<div id="global-loader" class="fixed inset-0 z-[999] flex flex-col items-center justify-center bg-slate-900/40">
        <i class="fa-solid fa-circle-notch fa-spin text-4xl text-white mb-3"></i>
        <p class="text-white font-medium text-sm tracking-wide">Memproses data...</p>
    </div>

    <script>
        // Handler Modal Global
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Handler Loading Global untuk proses AJAX
        function showLoading() {
            document.getElementById('global-loader').style.display = 'flex';
        }
        function hideLoading() {
            document.getElementById('global-loader').style.display = 'none';
        }

        // Setup dasar untuk AJAX Fetch agar gampang dipakai di file spesifik
        async function fetchAjax(url, method = 'GET', data = null) {
            showLoading();
            try {
                let options = { method: method };
                if (data && method !== 'GET') {
                    options.body = data; // FormData
                }
                const response = await fetch(url, options);
                const result = await response.json();
                hideLoading();
                return result;
            } catch (error) {
                hideLoading();
                console.error("AJAX Error:", error);
                alert("Terjadi kesalahan pada server!");
                return { status: 'error', message: 'Koneksi gagal' };
            }
        }
    </script>
</body>
</html>