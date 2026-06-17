<?php
session_start();

// Deteksi Routing Dinamis
$is_localhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
$base_url = $is_localhost ? '/sim-produksi-kue/' : '/';

// Cek sesi aktif & Cegah Blank Page
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role === 'owner')        { header("Location: " . $base_url . "owner/dashboard/"); exit; }
    elseif ($role === 'produksi') { header("Location: " . $base_url . "produksi/input_produksi/"); exit; }
    elseif ($role === 'admin')    { header("Location: " . $base_url . "admin/scan_barcode/"); exit; }
    elseif ($role === 'auditor')  { header("Location: " . $base_url . "owner/dashboard/"); exit; }
    else { session_destroy(); }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistem ERP Produksi</title>
    <meta name="description" content="Login ke Sistem ERP Produksi untuk mengelola resep, stok bahan baku, dan produksi harian.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Lottie Player -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue-50:  #eff6ff;
            --blue-100: #dbeafe;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
            --blue-700: #1d4ed8;
            --blue-800: #1e40af;
            --blue-900: #1e3a8a;
            --slate-50:  #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --emerald-500: #10b981;
            --red-500: #ef4444;
            --red-50:  #fef2f2;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--slate-50);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* ── Background mesh gradient ── */
        .bg-mesh {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(37,99,235,.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 90%, rgba(99,102,241,.10) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 60% 40%, rgba(14,165,233,.06) 0%, transparent 50%),
                linear-gradient(135deg, #f0f4ff 0%, #f8fafc 50%, #eef2ff 100%);
        }

        /* ── Floating orbs ── */
        .orb {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(60px);
            animation: float 8s ease-in-out infinite;
            z-index: 0;
        }
        .orb-1 { width: 500px; height: 500px; top: -15%; left: -10%; background: rgba(37,99,235,.10); animation-delay: 0s; }
        .orb-2 { width: 400px; height: 400px; bottom: -15%; right: -10%; background: rgba(99,102,241,.10); animation-delay: 3s; }
        .orb-3 { width: 300px; height: 300px; top: 40%; left: 40%; background: rgba(14,165,233,.06); animation-delay: 1.5s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-20px) scale(1.04); }
        }

        /* ── Card wrapper ── */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 1020px;
            min-height: 580px;
            display: flex;
            background: #fff;
            border-radius: 32px;
            box-shadow:
                0 4px 6px rgba(0,0,0,.04),
                0 20px 60px rgba(37,99,235,.10),
                0 60px 120px rgba(0,0,0,.06);
            overflow: hidden;
            margin: 16px;
            animation: cardIn .6s cubic-bezier(.22,.68,0,1.2) both;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(24px) scale(.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── LEFT PANEL ── */
        .left-panel {
            width: 52%;
            background: linear-gradient(145deg, var(--blue-800) 0%, var(--blue-600) 50%, #3b82f6 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -30%; left: -20%;
            width: 120%; height: 70%;
            background: radial-gradient(ellipse, rgba(255,255,255,.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .left-panel::after {
            content: '';
            position: absolute;
            bottom: -20%; right: -10%;
            width: 70%; height: 60%;
            background: radial-gradient(ellipse, rgba(99,102,241,.25) 0%, transparent 65%);
            pointer-events: none;
        }

        /* Grid dot pattern */
        .dot-grid {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,.12) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
        }

        /* Lottie container */
        .lottie-wrap {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 380px;
            aspect-ratio: 1;
            margin-bottom: 28px;
            filter: drop-shadow(0 20px 40px rgba(0,0,0,.2));
        }

        #lottie-anim {
            width: 100%;
            height: 100%;
        }

        .left-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #fff;
        }

        .left-content h1 {
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: -.02em;
            margin-bottom: 10px;
        }

        .left-content p {
            font-size: .83rem;
            font-weight: 400;
            color: rgba(255,255,255,.78);
            line-height: 1.7;
            max-width: 300px;
            margin: 0 auto 24px;
        }

        .feature-badges {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 50px;
            padding: 6px 14px;
            font-size: .72rem;
            font-weight: 600;
            color: rgba(255,255,255,.90);
            letter-spacing: .03em;
        }
        .badge i { font-size: .7rem; color: rgba(255,255,255,.7); }

        /* ── RIGHT PANEL ── */
        .right-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 52px 56px;
            background: #fff;
            position: relative;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--blue-600), var(--blue-800));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #fff;
            box-shadow: 0 8px 20px rgba(37,99,235,.3);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }
        .brand-text span:first-child {
            font-size: .65rem;
            font-weight: 600;
            letter-spacing: .12em;
            color: var(--blue-500);
            text-transform: uppercase;
        }
        .brand-text span:last-child {
            font-size: .92rem;
            font-weight: 800;
            color: var(--slate-800);
            letter-spacing: -.01em;
            line-height: 1.1;
        }

        .login-heading h2 {
            font-size: 1.9rem;
            font-weight: 800;
            color: var(--slate-900);
            letter-spacing: -.03em;
            line-height: 1.2;
        }
        .login-heading p {
            margin-top: 6px;
            font-size: .85rem;
            color: var(--slate-400);
            font-weight: 400;
        }

        /* ── Form ── */
        .form-group { margin-top: 24px; }

        .input-label {
            display: block;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            color: var(--slate-500);
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%; left: 18px;
            transform: translateY(-50%);
            color: var(--slate-400);
            font-size: .9rem;
            pointer-events: none;
            transition: color .2s;
        }

        .form-input {
            width: 100%;
            padding: 14px 18px 14px 48px;
            background: var(--slate-50);
            border: 2px solid var(--slate-100);
            border-radius: 14px;
            font-family: 'Inter', sans-serif;
            font-size: .9rem;
            font-weight: 500;
            color: var(--slate-800);
            outline: none;
            transition: all .25s;
        }
        .form-input::placeholder { color: var(--slate-300); font-weight: 400; }
        .form-input:focus {
            background: #fff;
            border-color: var(--blue-500);
            box-shadow: 0 0 0 4px rgba(59,130,246,.12);
        }
        .form-input:focus + .input-icon,
        .input-wrap:focus-within .input-icon { color: var(--blue-500); }

        .input-icon { left: 18px; }
        .input-wrap .input-icon { pointer-events: none; transition: color .2s; z-index: 1; }

        /* ── Submit button ── */
        .btn-submit {
            width: 100%;
            margin-top: 28px;
            padding: 15px 24px;
            background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%);
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: .88rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(37,99,235,.30);
            transition: all .25s;
            position: relative;
            overflow: hidden;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 16px 40px rgba(37,99,235,.38);
        }
        .btn-submit:active:not(:disabled) { transform: translateY(0); }
        .btn-submit:disabled { opacity: .7; cursor: not-allowed; }
        .btn-submit.success {
            background: linear-gradient(135deg, #059669, #10b981) !important;
            box-shadow: 0 10px 30px rgba(16,185,129,.35) !important;
        }

        /* ── Alert ── */
        .alert-error {
            display: none;
            margin-top: 20px;
            padding: 12px 16px;
            background: var(--red-50);
            border: 1.5px solid #fecaca;
            border-left: 4px solid var(--red-500);
            border-radius: 12px;
            color: #dc2626;
            font-size: .83rem;
            font-weight: 600;
            animation: shake .4s cubic-bezier(.36,.07,.19,.97);
        }
        .alert-error.visible { display: flex; align-items: center; gap: 8px; }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%  { transform: translateX(-6px); }
            40%  { transform: translateX(6px); }
            60%  { transform: translateX(-4px); }
            80%  { transform: translateX(4px); }
        }

        /* ── Footer ── */
        .login-footer {
            margin-top: 36px;
            text-align: center;
            font-size: .72rem;
            color: var(--slate-300);
            font-weight: 500;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .left-panel { display: none; }
            .right-panel { padding: 40px 28px; }
            .login-card { min-height: auto; border-radius: 24px; }
        }

        /* ── Pulse ring animation on icon ── */
        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(37,99,235,.4); }
            70%  { box-shadow: 0 0 0 10px rgba(37,99,235,0); }
            100% { box-shadow: 0 0 0 0 rgba(37,99,235,0); }
        }
        .brand-icon { animation: pulse-ring 3s ease-out infinite; }
    </style>
</head>
<body>

    <!-- Background -->
    <div class="bg-mesh"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <!-- Card -->
    <div class="login-card">

        <!-- ════ LEFT PANEL ════ -->
        <div class="left-panel">
            <div class="dot-grid"></div>

            <!-- Lottie Animation -->
            <div class="lottie-wrap">
                <div id="lottie-anim"></div>
            </div>

            <!-- Text -->
            <div class="left-content">
                <h1>Sistem ERP Produksi</h1>
                <p>Kelola resep, stok bahan baku, dan produksi harian secara terintegrasi & real-time.</p>
                <div class="feature-badges">
                    <span class="badge"><i class="fa-solid fa-bolt"></i> Real-Time</span>
                    <span class="badge"><i class="fa-solid fa-shield-halved"></i> Aman</span>
                    <span class="badge"><i class="fa-solid fa-chart-line"></i> Analitik</span>
                </div>
            </div>
        </div>

        <!-- ════ RIGHT PANEL ════ -->
        <div class="right-panel">

            <!-- Brand -->
            <div class="brand-logo">
                <div class="brand-icon">
                    <i class="fa-solid fa-cake-candles"></i>
                </div>
                <div class="brand-text">
                    <span>Platform</span>
                    <span>RotiKu ERP</span>
                </div>
            </div>

            <!-- Heading -->
            <div class="login-heading">
                <h2>Selamat Datang 👋</h2>
                <p>Masukkan kredensial akun Anda untuk melanjutkan.</p>
            </div>

            <!-- Alert -->
            <div id="alert-error" class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span id="error-message">Error</span>
            </div>

            <!-- Form -->
            <form id="loginForm" autocomplete="off">

                <div class="form-group">
                    <label class="input-label" for="username">Username</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-user input-icon"></i>
                        <input type="text" id="username" name="username" class="form-input" placeholder="Masukkan username" required autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label class="input-label" for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" id="btn-login" class="btn-submit">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                &copy; <?= date('Y') ?> RotiKu ERP &mdash; All rights reserved.
            </div>

        </div>
    </div>

    <script>
        // ── Init Lottie ──────────────────────────────────────
        lottie.loadAnimation({
            container: document.getElementById('lottie-anim'),
            renderer:  'svg',
            loop:      true,
            autoplay:  true,
            path:      'assets/img/Isometric Production Chain Industry.json'
        });

        // ── Login Form ───────────────────────────────────────
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData    = new FormData(this);
            const btnLogin    = document.getElementById('btn-login');
            const alertError  = document.getElementById('alert-error');
            const errorMsg    = document.getElementById('error-message');

            // Reset state
            alertError.classList.remove('visible');
            btnLogin.innerHTML  = '<i class="fa-solid fa-circle-notch fa-spin"></i><span>Memproses...</span>';
            btnLogin.disabled   = true;

            try {
                const response = await fetch('login_logic.php', { method: 'POST', body: formData });
                const result   = await response.json();

                if (result.status === 'success') {
                    btnLogin.classList.add('success');
                    btnLogin.innerHTML = '<i class="fa-solid fa-check"></i><span>Berhasil!</span>';
                    setTimeout(() => { window.location.href = result.redirect; }, 800);
                } else {
                    errorMsg.textContent = result.message;
                    alertError.classList.add('visible');
                    btnLogin.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket"></i><span>Masuk ke Sistem</span>';
                    btnLogin.disabled  = false;
                }
            } catch (err) {
                errorMsg.textContent = 'Terjadi kesalahan koneksi server.';
                alertError.classList.add('visible');
                btnLogin.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket"></i><span>Masuk ke Sistem</span>';
                btnLogin.disabled  = false;
            }
        });
    </script>
</body>
</html>