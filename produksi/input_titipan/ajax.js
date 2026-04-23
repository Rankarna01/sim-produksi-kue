let masterProducts = [];

document.addEventListener("DOMContentLoaded", () => {
    initForm();
});

async function initForm() {
    const res = await fetchAjax('logic.php?action=init_form', 'GET');
    
    if (res.status === 'success') {
        masterProducts = res.products;

        let optWarehouse = '<option value="">-- Pilih Store Tujuan --</option>';
        res.warehouses.forEach(w => optWarehouse += `<option value="${w.id}">Store: ${w.name}</option>`);
        document.getElementById('warehouse_id').innerHTML = optWarehouse;

        let optEmployee = '<option value="">-- Pilih Nama Anda --</option>';
        res.employees.forEach(e => {
            const labelLoc = e.kitchen_name ? e.kitchen_name : "Belum diatur";
            optEmployee += `<option value="${e.id}">${e.emp_name} (${labelLoc})</option>`;
        });
        document.getElementById('employee_id').innerHTML = optEmployee;

        addProductRow();
    }
}

function renderDropdownList(wrapper, productList) {
    const ul = wrapper.querySelector('.custom-dropdown');
    ul.innerHTML = ''; 
    
    if(productList.length === 0) {
        ul.innerHTML = '<li class="p-3 text-sm text-slate-500 text-center">Data tidak ditemukan/Stok Habis</li>';
    } else {
        productList.forEach(p => {
            const li = document.createElement('li');
            li.className = "p-3 hover:bg-amber-50 cursor-pointer border-b border-slate-50 text-sm font-semibold text-slate-700 transition-colors";
            li.innerHTML = `<span class="text-amber-600 font-bold text-xs mr-2">[${p.code}]</span> ${p.name} <span class="text-xs text-slate-400 float-right">Stok: ${p.stok}</span>`;
            
            li.onclick = function() {
                const textInput = wrapper.querySelector('.search-input');
                const hiddenInput = wrapper.querySelector('.hidden-id');
                const qtyInput = wrapper.parentElement.querySelector('input[name="quantity[]"]');
                
                const allHidden = document.querySelectorAll('.hidden-id');
                let isDuplicate = false;
                allHidden.forEach(inp => {
                    if (inp !== hiddenInput && inp.value == p.id) { isDuplicate = true; }
                });

                if (isDuplicate) {
                    alert(`⚠️ Produk "${p.name}" sudah ada di daftar!\nUbah jumlah (Qty) pada baris yang sudah ada.`);
                    return; 
                }

                textInput.value = `[${p.code}] ${p.name}`;
                hiddenInput.value = p.id;
                qtyInput.max = p.stok; // Set max input to available stock
                qtyInput.placeholder = `Maks: ${p.stok}`;
                
                textInput.classList.add('border-amber-500', 'bg-amber-50');
                ul.classList.add('hidden'); 
            };
            ul.appendChild(li);
        });
    }
}

function addProductRow() {
    const container = document.getElementById('product-container');
    const rowHTML = `
        <div class="product-row bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 items-start relative">
            <div class="flex-1 w-full relative dropdown-wrapper">
                <label class="block text-[11px] font-bold text-slate-400 mb-1 uppercase tracking-wider">Cari & Pilih Barang <span class="text-danger">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-box text-slate-300"></i>
                    </div>
                    <input type="text" class="search-input w-full pl-9 pr-3 py-3 border-2 border-slate-200 rounded-xl focus:border-amber-500 focus:bg-white bg-slate-50 outline-none transition-all text-sm font-semibold text-slate-700 placeholder:text-slate-400" placeholder="Ketik nama UMKM atau barang..." onfocus="showDropdown(this)" oninput="filterDropdown(this)" autocomplete="off" required>
                    <input type="hidden" name="product_id[]" class="hidden-id" required>
                    <ul class="custom-dropdown custom-scrollbar absolute z-[60] w-full bg-white border border-slate-200 shadow-xl rounded-xl mt-1 max-h-48 overflow-y-auto hidden"></ul>
                </div>
            </div>
            <div class="w-full md:w-32">
                <label class="block text-[11px] font-bold text-slate-400 mb-1 uppercase tracking-wider">Jumlah <span class="text-danger">*</span></label>
                <input type="number" name="quantity[]" required min="1" class="w-full px-3 py-3 border-2 border-slate-200 rounded-xl focus:border-amber-500 outline-none transition-all font-black text-amber-600 text-center text-lg" placeholder="0">
            </div>
            <div class="w-full md:w-auto md:self-end">
                <button type="button" onclick="removeRow(this)" title="Hapus Baris" class="w-full md:w-12 h-[52px] bg-danger/10 hover:bg-danger text-danger hover:text-white rounded-xl flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', rowHTML);
}

function showDropdown(inputElement) {
    const wrapper = inputElement.closest('.dropdown-wrapper');
    const ul = wrapper.querySelector('.custom-dropdown');
    renderDropdownList(wrapper, masterProducts);
    ul.classList.remove('hidden');
}

function filterDropdown(inputElement) {
    const wrapper = inputElement.closest('.dropdown-wrapper');
    const ul = wrapper.querySelector('.custom-dropdown');
    const hiddenInput = wrapper.querySelector('.hidden-id');
    
    hiddenInput.value = '';
    inputElement.classList.remove('border-amber-500', 'bg-amber-50');
    
    const keyword = inputElement.value.toLowerCase();
    const filteredProducts = masterProducts.filter(p => 
        p.name.toLowerCase().includes(keyword) || p.code.toLowerCase().includes(keyword)
    );
    
    renderDropdownList(wrapper, filteredProducts);
    ul.classList.remove('hidden');
}

function closeAllDropdowns(event) {
    if (!event.target.matches('.search-input')) {
        document.querySelectorAll('.custom-dropdown').forEach(ul => ul.classList.add('hidden'));
    }
}

function removeRow(button) {
    const container = document.getElementById('product-container');
    if (container.children.length > 1) { button.closest('.product-row').remove(); } 
    else { alert("Minimal harus ada 1 barang."); }
}

document.getElementById('formProduksi').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const hiddenInputs = document.querySelectorAll('.hidden-id');
    for (let input of hiddenInputs) {
        if (!input.value) {
            alert("Pilih barang dari daftar yang muncul!"); return;
        }
    }

    const employeeName = document.getElementById('employee_id').options[document.getElementById('employee_id').selectedIndex].text;
    
    const { value: pin } = await Swal.fire({
        title: 'Otorisasi Keamanan',
        html: `Masukkan <b>PIN Rahasia</b> untuk otorisasi oleh:<br><span class="text-amber-600 font-bold mt-2 inline-block">${employeeName}</span>`,
        input: 'password',
        inputAttributes: { maxlength: 4, autocapitalize: 'off', autocorrect: 'off' },
        showCancelButton: true,
        confirmButtonColor: '#f59e0b', 
        confirmButtonText: 'Otorisasi & Kirim',
        inputValidator: (value) => {
            if (!value) return 'PIN tidak boleh kosong!';
            if (!/^\d{4}$/.test(value)) return 'PIN harus 4 Angka!';
        }
    });

    if (pin) {
        Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
        
        const formData = new FormData(this);
        formData.append('pin', pin); 

        try {
            const response = await fetch('logic.php?action=save', { method: 'POST', body: formData });
            const res = await response.json();
            
            if (res.status === 'success') {
                Swal.close();
                document.getElementById('btnCetak').setAttribute('onclick', `cetakStruk(${res.production_id})`);
                document.getElementById('modal-sukses').classList.remove('hidden');
            } else {
                Swal.fire('Gagal!', res.message, 'error');
            }
        } catch(e) {
            Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        }
    }
});

function cetakStruk(id) {
    window.open(`print.php?id=${id}`, 'CetakStruk', 'width=400,height=600');
}

function selesaiProduksi() {
    document.getElementById('modal-sukses').classList.add('hidden');
    document.getElementById('formProduksi').reset();
    document.getElementById('product-container').innerHTML = '';
    addProductRow();
    initForm(); // Refresh data supaya stok terupdate
}