<?php
require_once '../../config/auth.php';
checkPermission('pesanan_custom');
$page_title = "Pesanan Dapur - Produksi";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50" x-data="dapurApp()" x-cloak>

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 w-full relative">
            
            <div x-show="isLoading" class="absolute inset-0 z-50 bg-white/60 backdrop-blur-sm flex items-center justify-center">
                <i class="fa-solid fa-circle-notch fa-spin text-4xl text-orange-500"></i>
            </div>

            <div class="mb-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-fire-burner text-orange-500"></i> Layar Pesanan Dapur
                    </h2>
                    <p class="text-xs text-slate-500 mt-1 font-bold">Pantau, edit, dan kerjakan pesanan Custom & PO secara real-time.</p>
                </div>
                <button @click="loadOrders()" class="bg-white border border-slate-200 text-slate-600 hover:text-orange-500 hover:border-orange-500 px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-rotate-right" :class="isSilentLoading ? 'fa-spin' : ''"></i> Refresh Data
                </button>
            </div>

            <!-- GRID KOTAK LEBIH KECIL (grid-cols-4 untuk layar besar) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                
                <template x-for="order in orders" :key="order.id">
                    <div class="bg-white rounded-[1.5rem] p-4 shadow-sm border flex flex-col h-full transition-all hover:shadow-md"
                         :class="order.production_status === 'pending' ? 'border-rose-200 shadow-rose-100/50' : 'border-orange-300 border-2 bg-orange-50/10'">
                        
                        <!-- Header Card -->
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="font-black text-base text-slate-800 leading-none mb-1" x-text="'#' + order.invoice_no.split('-')[1]"></h4>
                                <div class="text-[9px] font-bold text-slate-400 mt-1 flex items-center gap-1.5">
                                    <span><i class="fa-regular fa-clock"></i> <span x-text="order.time"></span></span>
                                    <span class="bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded uppercase" x-text="order.channel || 'TOKO'"></span>
                                </div>
                            </div>
                            <div class="flex gap-1.5">
                                <!-- TOMBOL PRINT -->
                                <a :href="'print_receipt.php?invoice=' + order.invoice_no" target="_blank" class="text-slate-500 bg-slate-50 hover:bg-slate-500 hover:text-white transition-colors w-7 h-7 rounded-lg flex items-center justify-center shadow-sm" title="Print Struk">
                                    <i class="fa-solid fa-print text-[10px]"></i>
                                </a>
                                <!-- TOMBOL EDIT PO -->
                                <button @click="openEditModal(order)" class="text-blue-500 bg-blue-50 hover:bg-blue-500 hover:text-white transition-colors w-7 h-7 rounded-lg flex items-center justify-center shadow-sm" title="Edit PO">
                                    <i class="fa-solid fa-pen text-[10px]"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Info Pelanggan & Pengambilan -->
                        <div class="text-[11px] font-bold text-slate-600 mb-3 bg-slate-50 p-2.5 rounded-xl border border-slate-100 space-y-1.5">
                            <div class="flex items-center gap-2"><i class="fa-solid fa-user text-slate-400 w-3"></i> <span x-text="order.customer_name || 'Pelanggan Umum'"></span></div>
                            <div class="flex items-center gap-2 text-orange-600" x-show="order.pickup_date"><i class="fa-solid fa-calendar text-orange-400 w-3"></i> <span>Ambil: <span x-text="order.pickup_date"></span> (<span x-text="order.pickup_time"></span>)</span></div>
                            <div x-show="order.notes" class="flex items-start gap-2 pt-1 border-t border-slate-200 mt-1">
                                <i class="fa-solid fa-note-sticky text-slate-400 w-3 mt-0.5"></i>
                                <span class="italic text-slate-500 leading-tight" x-text="order.notes"></span>
                            </div>
                        </div>

                        <!-- Daftar Masakan -->
                        <div class="flex-1 bg-white border border-slate-200 p-2.5 rounded-xl custom-scrollbar overflow-y-auto mb-3 max-h-[130px]">
                            <div class="text-[9px] font-black text-slate-400 mb-2 uppercase tracking-widest border-b border-slate-100 pb-1">Daftar Masakan</div>
                            <div class="space-y-2">
                                <template x-for="item in order.custom_items">
                                    <div class="flex justify-between items-start border-b border-slate-50 pb-2 last:border-0 last:pb-0">
                                        <div class="font-bold text-slate-800 text-xs flex-1 pr-2">
                                            <span class="text-orange-500 mr-1">🛠️</span> <span x-text="item.custom_name"></span>
                                            <!-- TOMBOL SIMPAN KE KATALOG -->
                                            <button @click="saveToCatalog(item)" class="block mt-1 text-[9px] bg-emerald-50 text-emerald-600 border border-emerald-200 px-2 py-0.5 rounded hover:bg-emerald-500 hover:text-white transition-colors">
                                                <i class="fa-solid fa-bookmark mr-1"></i>Simpan ke Produk
                                            </button>
                                        </div>
                                        <div class="font-black text-orange-700 bg-orange-100 px-2 py-0.5 rounded text-xs" x-text="item.qty + 'x'"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Status / Aksi -->
                        <div class="mt-auto">
                            <button x-show="order.production_status === 'pending'" @click="updateStatus(order.id, 'diproses')" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-black py-2.5 rounded-xl text-xs transition-all shadow-sm flex justify-center items-center gap-2">
                                <i class="fa-solid fa-fire"></i> Mulai Masak
                            </button>
                            <button x-show="order.production_status === 'diproses'" @click="updateStatus(order.id, 'selesai')" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-2.5 rounded-xl text-xs transition-all shadow-sm flex justify-center items-center gap-2 animate-pulse">
                                <i class="fa-solid fa-check-double"></i> Tandai Selesai
                            </button>
                        </div>
                    </div>
                </template>

                <!-- JIKA KOSONG -->
                <div x-show="orders.length === 0" class="col-span-full flex flex-col items-center justify-center py-16 bg-white rounded-[2rem] border border-slate-200 border-dashed">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-3 text-4xl text-slate-300"><i class="fa-solid fa-mug-hot"></i></div>
                    <h3 class="text-lg font-black text-slate-700">Dapur Sedang Kosong</h3>
                    <p class="text-xs font-bold text-slate-400 mt-1">Belum ada pesanan custom masuk.</p>
                </div>
            </div>

            <!-- PAGINATION -->
            <div x-show="totalPages > 1" class="flex justify-between items-center mt-5 bg-white p-3.5 rounded-2xl shadow-sm border border-slate-200">
                <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-xl text-xs font-black disabled:opacity-50 transition-colors"><i class="fa-solid fa-chevron-left mr-1"></i> Sebelumnya</button>
                <div class="font-black text-slate-600 text-xs">Halaman <span class="text-primary" x-text="currentPage"></span> dari <span x-text="totalPages"></span></div>
                <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages" class="px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-xl text-xs font-black disabled:opacity-50 transition-colors">Selanjutnya <i class="fa-solid fa-chevron-right ml-1"></i></button>
            </div>

        </main>
    </div>

    <!-- MODAL EDIT DATA PO -->
    <div x-show="editModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;" x-cloak>
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="editModalOpen = false"></div>
        <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl relative z-10 p-6 m-4 flex flex-col overflow-hidden">
            <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
                <h3 class="font-black text-lg text-slate-800"><i class="fa-solid fa-pen-to-square text-blue-500 mr-2"></i> Edit Data PO</h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form @submit.prevent="submitEditPO()" class="space-y-3">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Pelanggan</label>
                    <select x-model="editForm.customer_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-blue-500/20 font-bold text-sm text-slate-700">
                        <option value="">-- Pelanggan Umum --</option>
                        <template x-for="c in customers" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Channel Penjualan</label>
                    <select x-model="editForm.channel" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-blue-500/20 font-bold text-sm text-slate-700">
                        <option value="toko">Toko / Takeaway</option>
                        <option value="grab">GrabFood</option>
                        <option value="gojek">GoFood</option>
                        <option value="delivery">Delivery Kurir Toko</option>
                        <option value="online">Pesanan dari Online</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Tgl Diambil</label>
                        <input type="date" x-model="editForm.pickup_date" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 outline-none font-bold text-sm text-slate-700">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Jam Diambil</label>
                        <input type="time" x-model="editForm.pickup_time" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 outline-none font-bold text-sm text-slate-700">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Catatan / Note</label>
                    <textarea x-model="editForm.notes" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 outline-none focus:ring-2 focus:ring-blue-500/20 font-bold text-sm text-slate-700 placeholder-slate-400" placeholder="Tulis catatan pesanan di sini..."></textarea>
                </div>
                
                <div class="pt-3 mt-2 border-t border-slate-100">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-3 rounded-xl shadow-md transition-colors flex items-center justify-center gap-2">
                        <i class="fa-solid fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>