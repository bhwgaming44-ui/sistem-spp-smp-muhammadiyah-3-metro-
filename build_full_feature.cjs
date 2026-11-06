const fs = require('fs');
const vm = require('vm');

let content = fs.readFileSync('resources/views/welcome.blade.php', 'utf8');

// ==========================================
// 1. ADD CSS FOR 1920x1080 INVOICE DASHBOARD & MIDTRANS SNAP POPUP
// ==========================================
const modernCss = `
        /* ================= 1920x1080 MODERN INVOICE CREATION DASHBOARD ================= */
        .invoice-dashboard-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            border-radius: 16px;
            padding: 26px 32px;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(30, 58, 138, 0.15);
            position: relative;
            overflow: hidden;
        }

        .invoice-dashboard-hero::after {
            content: 'INVOICE';
            position: absolute;
            right: 20px;
            bottom: -25px;
            font-size: 110px;
            font-weight: 900;
            color: rgba(255, 255, 255, 0.04);
            pointer-events: none;
        }

        .inv-hero-left h2 {
            font-size: 24px;
            font-weight: 800;
            margin: 6px 0;
            letter-spacing: -0.3px;
        }

        .inv-hero-left p {
            font-size: 13.5px;
            color: #93c5fd;
            max-width: 720px;
            line-height: 1.5;
        }

        .inv-badge-role {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(245, 158, 11, 0.2);
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: #fef08a;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .inv-hero-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
            z-index: 2;
        }

        .inv-status-pill {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
        }

        .btn-refresh-inv {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-refresh-inv:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }

        /* 4 Metrics Summary Cards */
        .inv-stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .inv-stat-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s;
        }

        .inv-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .inv-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .icon-blue { background: #eff6ff; color: #1e40af; }
        .icon-red { background: #fef2f2; color: #b91c1c; }
        .icon-green { background: #ecfdf5; color: #047857; }
        .icon-purple { background: #faf5ff; color: #6b21a8; }

        .inv-stat-label {
            font-size: 11px;
            font-weight: 800;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            display: block;
        }

        .inv-stat-value {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1.2;
            margin: 2px 0;
        }

        .inv-stat-desc {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
        }

        /* Dual Workspace Grid (1920x1080 Layout: Form 36%, Table 64%) */
        .inv-workspace-grid {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 24px;
            align-items: start;
        }

        @media (min-width: 1600px) {
            .inv-workspace-grid {
                grid-template-columns: 420px 1fr;
            }
        }

        .inv-form-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 24px;
            box-shadow: var(--shadow-sm);
        }

        .inv-card-header {
            margin-bottom: 18px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 12px;
        }

        .inv-card-header h3 {
            font-size: 16px;
            font-weight: 800;
            color: var(--text-dark);
        }

        .inv-card-header p {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Mode Tabs */
        .inv-mode-tabs {
            display: flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 10px;
            margin-bottom: 18px;
            gap: 4px;
        }

        .inv-mode-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            background: transparent;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            border-radius: 7px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .inv-mode-btn.active {
            background: #ffffff;
            color: var(--primary-blue);
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }

        .inv-sync-notice {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            gap: 10px;
            font-size: 12px;
            color: #1e3a8a;
            margin-top: 14px;
            line-height: 1.4;
        }

        .btn-create-inv {
            flex: 1;
            padding: 12px 18px;
            background: var(--primary-blue);
            color: #ffffff;
            border: none;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-create-inv:hover {
            background: var(--primary-blue-hover);
            transform: translateY(-1px);
        }

        /* Right Panel: Invoice Table Card */
        .inv-table-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 24px;
            box-shadow: var(--shadow-sm);
        }

        .inv-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 16px;
        }

        .inv-table-header h3 {
            font-size: 16px;
            font-weight: 800;
            color: var(--text-dark);
        }

        .inv-table-header p {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .inv-filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .inv-filter-select {
            padding: 8px 12px;
            font-size: 12.5px;
            font-weight: 600;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            background: #ffffff;
            color: var(--text-dark);
            cursor: pointer;
        }

        .inv-search-input {
            padding: 8px 14px;
            font-size: 12.5px;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            width: 220px;
            color: var(--text-dark);
        }

        .inv-table-container {
            overflow-x: auto;
        }

        .inv-table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
        }

        .inv-table-modern th {
            background: #f8fafc;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
        }

        .inv-table-modern td {
            padding: 13px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-dark);
            vertical-align: middle;
        }

        .inv-table-modern tr:hover td {
            background: #f8fafc;
        }

        .inv-badge-paid {
            background: #dcfce7;
            color: #15803d;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .inv-badge-unpaid {
            background: #fee2e2;
            color: #b91c1c;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* ================= MIDTRANS SNAP POPUP MODAL (IDENTICAL TO IMAGE 2) ================= */
        .snap-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            animation: snapFadeIn 0.2s ease-out;
        }

        @keyframes snapFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .snap-modal-box {
            background: #ffffff;
            width: 100%;
            max-width: 440px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            animation: snapScaleUp 0.25s ease-out;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #212529;
            position: relative;
        }

        @keyframes snapScaleUp {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Header like Image 2: "SMP MUHAMMADIYAH 3 METRO" / merchant title & X */
        .snap-header {
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .snap-merchant-title {
            font-size: 17px;
            font-weight: 700;
            color: #1e293b;
        }

        .snap-btn-close {
            background: none;
            border: none;
            font-size: 20px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            line-height: 1;
        }

        .snap-btn-close:hover {
            color: #0f172a;
        }

        /* Amount & Order ID like Image 2 */
        .snap-amount-row {
            padding: 16px 20px 8px;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }

        .snap-amount-val {
            font-size: 28px;
            font-weight: 800;
            color: #1e293b;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .snap-copy-icon {
            font-size: 15px;
            color: #3b82f6;
            cursor: pointer;
        }

        .snap-order-row {
            padding: 0 20px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #64748b;
        }

        .snap-order-id {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .snap-order-details-link {
            color: #2563eb;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
        }

        /* Countdown strip like Image 2: "Choose within 23:59:36 ▲" */
        .snap-countdown-strip {
            background: #e2e8f0;
            padding: 8px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            color: #334155;
            font-weight: 600;
        }

        .snap-body-scroll {
            max-height: 380px;
            overflow-y: auto;
            padding-bottom: 16px;
        }

        .snap-section-title {
            padding: 14px 20px 6px;
            font-size: 13.5px;
            color: #94a3b8;
            font-weight: 600;
        }

        /* Payment Method Row like Image 2 */
        .snap-method-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: background 0.15s;
        }

        .snap-method-item:hover {
            background: #f8fafc;
        }

        .snap-method-name {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .snap-method-logos {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .snap-logo-badge {
            font-size: 11px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .badge-gopay { background: #00a5cf; color: #ffffff; }
        .badge-gopay-later { background: #0081a7; color: #ffffff; font-size: 10px; }
        .badge-qris { background: #dc2626; color: #ffffff; }
        .badge-visa { background: #1a1f71; color: #ffffff; }
        .badge-mc { background: #eb001b; color: #ffffff; }
        .badge-shopee { background: #ee4d2d; color: #ffffff; }
        .badge-ovo { background: #4c2a86; color: #ffffff; }
        .badge-va { background: #0284c7; color: #ffffff; }

        .snap-method-arrow {
            font-size: 16px;
            color: #94a3b8;
            font-weight: 700;
        }

        /* QRIS / Sub-Payment Detail Screen inside Snap */
        .snap-detail-view {
            display: none;
            padding: 20px;
            text-align: center;
        }

        .snap-qr-box {
            background: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 16px;
            display: inline-block;
            margin: 14px 0;
        }

        .btn-snap-simulate-pay {
            width: 100%;
            padding: 13px;
            background: #10b981;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.2s;
        }

        .btn-snap-simulate-pay:hover {
            background: #059669;
        }

        .btn-snap-back {
            background: none;
            border: none;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            padding: 6px;
        }

        .btn-snap-back:hover {
            color: #0f172a;
        }
`;

// Insert modern CSS right before </style>
if (!content.includes('.invoice-dashboard-hero')) {
    content = content.replace('</style>', modernCss + '\n</style>');
    console.log('Appended modern CSS rules');
}

// ==========================================
// 2. OVERHAUL VIEW 3: BAYAR SPP ONLINE (MIDTRANS ONLY)
// ==========================================
const viewBayarStart = content.indexOf('<!-- ================= VIEW 3: BAYAR SPP ONLINE (MIDTRANS) ================= -->');
const viewRiwayatStart = content.indexOf('<!-- ================= VIEW 4: RIWAYAT & KUITANSI ================= -->');

if (viewBayarStart !== -1 && viewRiwayatStart !== -1) {
    const modernViewBayarHtml = `<!-- ================= VIEW 3: BAYAR SPP ONLINE (MIDTRANS) ================= -->
            <section id="viewBayar" class="view-section">
                <div class="content-card" style="max-width:820px;margin:0 auto;">
                    <div class="content-card-head">
                        <div>
                            <div class="content-card-title">Pembayaran SPP Online via Midtrans</div>
                            <div class="content-card-sub">Pilih tagihan SPP dan selesaikan langsung melalui pop-up resmi Midtrans Gateway</div>
                        </div>
                        <span class="badge-status badge-lunas">⚡ Midtrans 1-Click Gateway</span>
                    </div>

                    <!-- Selected Months List -->
                    <div style="margin-bottom:24px;">
                        <h4 style="font-size:14px;font-weight:800;margin-bottom:12px;color:var(--text-dark);">Pilih Bulan Tagihan yang Ingin Dibayar:</h4>
                        <div id="paymentMonthList" style="display:flex;flex-direction:column;gap:10px;">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>

                    <!-- Total Summary Box -->
                    <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;padding:22px;margin-bottom:24px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <div style="font-size:13px;color:#1e40af;font-weight:700;">TOTAL PEMBAYARAN:</div>
                                <div style="font-size:28px;font-weight:900;color:#1e3a8a;" id="paySubtotal">Rp 175.000</div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:12px;color:#64748b;">Biaya Layanan Gateway:</div>
                                <div style="font-size:13px;font-weight:800;color:#15803d;">GRATIS (Ditanggung Sekolah)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Exclusive Midtrans Gateway Banner (No manual cash / loket) -->
                    <div style="background:#f8fafc;border:1.5px solid var(--border-color);border-radius:12px;padding:20px;margin-bottom:24px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;background:#1e40af;color:#fff;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;">⚡</div>
                                <div>
                                    <div style="font-size:14px;font-weight:800;color:var(--text-dark);">Metode Pembayaran Resmi: Midtrans Gateway</div>
                                    <div style="font-size:12px;color:var(--text-muted);">Pop-up Snap otomatis terbuka dengan metode QRIS & Virtual Account lengkap</div>
                                </div>
                            </div>
                            <span style="font-size:11px;font-weight:800;background:#dbeafe;color:#1e40af;padding:4px 10px;border-radius:20px;">100% ONLINE</span>
                        </div>
                        
                        <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:10px;margin-top:14px;">
                            <div style="background:#fff;border:1px solid var(--border-color);border-radius:8px;padding:10px;text-align:center;">
                                <div style="font-size:18px;">📱</div>
                                <div style="font-size:11.5px;font-weight:700;">GoPay QRIS</div>
                            </div>
                            <div style="background:#fff;border:1px solid var(--border-color);border-radius:8px;padding:10px;text-align:center;">
                                <div style="font-size:18px;">🛍️</div>
                                <div style="font-size:11.5px;font-weight:700;">ShopeePay QRIS</div>
                            </div>
                            <div style="background:#fff;border:1px solid var(--border-color);border-radius:8px;padding:10px;text-align:center;">
                                <div style="font-size:18px;">🏦</div>
                                <div style="font-size:11.5px;font-weight:700;">Virtual Account</div>
                            </div>
                            <div style="background:#fff;border:1px solid var(--border-color);border-radius:8px;padding:10px;text-align:center;">
                                <div style="font-size:18px;">💳</div>
                                <div style="font-size:11.5px;font-weight:700;">Kartu Debit/Kredit</div>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Midtrans Snap Action Button -->
                    <button type="button" class="btn-action-primary" style="width:100%;padding:15px;font-size:16px;justify-content:center;box-shadow:0 6px 20px rgba(30,64,175,0.25);" onclick="triggerMidtransPayment()">
                        <span>🔒</span> Bayar Sekarang via Midtrans Snap Pop-up
                    </button>
                    <p style="text-align:center;font-size:12px;color:var(--text-muted);margin-top:14px;">
                        Transaksi diproteksi oleh 256-bit SSL Enkripsi Midtrans Payment Gateway. Bukti kuitansi resmi langsung terbit seketika.
                    </p>
                </div>
            </section>
`;

    content = content.substring(0, viewBayarStart) + modernViewBayarHtml + '\n            ' + content.substring(viewRiwayatStart);
    console.log('Overhauled viewBayar to Midtrans Only successfully!');
}

// ==========================================
// 3. ADD MIDTRANS SNAP POPUP HTML (IMAGE 2) BEFORE </body>
// ==========================================
const snapModalHtml = `
    <!-- ==========================================
         MIDTRANS SNAP POPUP MODAL (IDENTICAL TO IMAGE 2)
         ========================================== -->
    <div id="modalSnapMidtrans" class="snap-modal-overlay">
        <div class="snap-modal-box">
            <!-- Header: Merchant Name & Close -->
            <div class="snap-header">
                <div class="snap-merchant-title" id="snapMerchantTitle">SMP MUHAMMADIYAH 3 METRO</div>
                <button class="snap-btn-close" onclick="closeMidtransSnapModal()">&times;</button>
            </div>

            <!-- Main Method Selection Screen -->
            <div id="snapMainView">
                <!-- Amount Row -->
                <div class="snap-amount-row">
                    <div class="snap-amount-val" id="snapAmountText">
                        Rp175.000 <span class="snap-copy-icon" onclick="copySnapText('snapAmountRaw')">⧉</span>
                    </div>
                </div>

                <!-- Order ID Row -->
                <div class="snap-order-row">
                    <div class="snap-order-id">
                        Order ID <span id="snapOrderIdText">#SPP-202501-8123</span>
                        <span class="snap-copy-icon" onclick="copySnapText('snapOrderIdVal')">⧉</span>
                    </div>
                    <a href="javascript:void(0)" class="snap-order-details-link" onclick="toggleSnapDetails()">Details ▼</a>
                </div>

                <div id="snapOrderDetailsBox" style="display:none;background:#f8fafc;padding:10px 20px;font-size:12px;border-bottom:1px solid #e2e8f0;">
                    <div>Item: <strong id="snapItemDesc">SPP Siswa SMP Muhammadiyah 3 Metro</strong></div>
                    <div>Nama Siswa: <strong id="snapStudentDesc">Ahmad Dahlan Putra</strong></div>
                </div>

                <!-- Countdown Strip like Image 2 -->
                <div class="snap-countdown-strip">
                    <span>Choose within</span> <strong id="snapCountdownTimer">23:59:36</strong> <span>▲</span>
                </div>

                <!-- Scrollable Methods List -->
                <div class="snap-body-scroll">
                    <div class="snap-section-title">Last payment method</div>
                    <div class="snap-method-item" onclick="openSnapDetail('gopay')">
                        <div>
                            <div class="snap-method-name">GoPay QRIS</div>
                            <div class="snap-method-logos">
                                <span class="snap-logo-badge badge-gopay">gopay</span>
                                <span class="snap-logo-badge badge-gopay-later">gopay later</span>
                                <span class="snap-logo-badge badge-qris">QRIS</span>
                            </div>
                        </div>
                        <div class="snap-method-arrow">&rsaquo;</div>
                    </div>

                    <div class="snap-section-title">All payment methods</div>
                    
                    <!-- GoPay QRIS -->
                    <div class="snap-method-item" onclick="openSnapDetail('gopay')">
                        <div>
                            <div class="snap-method-name">GoPay QRIS</div>
                            <div class="snap-method-logos">
                                <span class="snap-logo-badge badge-gopay">gopay</span>
                                <span class="snap-logo-badge badge-gopay-later">gopay later</span>
                                <span class="snap-logo-badge badge-qris">QRIS</span>
                            </div>
                        </div>
                        <div class="snap-method-arrow">&rsaquo;</div>
                    </div>

                    <!-- Card Payment -->
                    <div class="snap-method-item" onclick="openSnapDetail('card')">
                        <div>
                            <div class="snap-method-name">Card Payment</div>
                            <div class="snap-method-logos">
                                <span class="snap-logo-badge badge-visa">VISA</span>
                                <span class="snap-logo-badge badge-mc">Mastercard</span>
                                <span class="snap-logo-badge" style="background:#0079bf;color:#fff;">JCB</span>
                                <span class="snap-logo-badge" style="background:#002663;color:#fff;">AMEX</span>
                            </div>
                        </div>
                        <div class="snap-method-arrow">&rsaquo;</div>
                    </div>

                    <!-- ShopeePay QRIS -->
                    <div class="snap-method-item" onclick="openSnapDetail('shopee')">
                        <div>
                            <div class="snap-method-name">ShopeePay QRIS</div>
                            <div class="snap-method-logos">
                                <span class="snap-logo-badge badge-shopee">ShopeePay</span>
                                <span class="snap-logo-badge badge-shopee">SPayLater</span>
                                <span class="snap-logo-badge badge-qris">QRIS</span>
                            </div>
                        </div>
                        <div class="snap-method-arrow">&rsaquo;</div>
                    </div>

                    <!-- OVO -->
                    <div class="snap-method-item" onclick="openSnapDetail('ovo')">
                        <div>
                            <div class="snap-method-name">OVO</div>
                            <div class="snap-method-logos">
                                <span class="snap-logo-badge badge-ovo">OVO</span>
                            </div>
                        </div>
                        <div class="snap-method-arrow">&rsaquo;</div>
                    </div>

                    <!-- Bank Virtual Account -->
                    <div class="snap-method-item" onclick="openSnapDetail('va')">
                        <div>
                            <div class="snap-method-name">Bank Virtual Account</div>
                            <div class="snap-method-logos">
                                <span class="snap-logo-badge badge-va">BSI</span>
                                <span class="snap-logo-badge badge-va">Mandiri</span>
                                <span class="snap-logo-badge badge-va">BRI</span>
                                <span class="snap-logo-badge badge-va">BCA</span>
                            </div>
                        </div>
                        <div class="snap-method-arrow">&rsaquo;</div>
                    </div>
                </div>
            </div>

            <!-- Sub Detail View (QR / VA / Card Simulation) -->
            <div id="snapDetailView" class="snap-detail-view">
                <h4 id="snapDetailTitle" style="font-size:16px;font-weight:800;color:#1e293b;">Scan QRIS untuk Bayar</h4>
                <p id="snapDetailDesc" style="font-size:12px;color:#64748b;margin-top:4px;">Buka aplikasi GoPay / BCA / Mandiri / Shopee / OVO lalu scan kode di bawah:</p>
                
                <div class="snap-qr-box" id="snapQrBox">
                    <!-- SVG QR Code Mockup -->
                    <svg width="180" height="180" viewBox="0 0 100 100" style="display:block;margin:0 auto;">
                        <rect width="100" height="100" fill="#ffffff"/>
                        <!-- QR Corner 1 -->
                        <rect x="5" y="5" width="28" height="28" fill="#000000"/>
                        <rect x="9" y="9" width="20" height="20" fill="#ffffff"/>
                        <rect x="13" y="13" width="12" height="12" fill="#000000"/>
                        <!-- QR Corner 2 -->
                        <rect x="67" y="5" width="28" height="28" fill="#000000"/>
                        <rect x="71" y="9" width="20" height="20" fill="#ffffff"/>
                        <rect x="75" y="13" width="12" height="12" fill="#000000"/>
                        <!-- QR Corner 3 -->
                        <rect x="5" y="67" width="28" height="28" fill="#000000"/>
                        <rect x="9" y="71" width="20" height="20" fill="#ffffff"/>
                        <rect x="13" y="75" width="12" height="12" fill="#000000"/>
                        <!-- QR Pattern Blocks -->
                        <rect x="40" y="8" width="6" height="6" fill="#000000"/>
                        <rect x="50" y="8" width="10" height="6" fill="#000000"/>
                        <rect x="40" y="20" width="8" height="6" fill="#000000"/>
                        <rect x="54" y="20" width="6" height="12" fill="#000000"/>
                        <rect x="8" y="40" width="6" height="10" fill="#000000"/>
                        <rect x="20" y="40" width="14" height="6" fill="#000000"/>
                        <rect x="40" y="40" width="20" height="20" fill="#000000"/>
                        <rect x="68" y="40" width="10" height="14" fill="#000000"/>
                        <rect x="84" y="40" width="8" height="8" fill="#000000"/>
                        <rect x="40" y="68" width="14" height="8" fill="#000000"/>
                        <rect x="60" y="68" width="8" height="18" fill="#000000"/>
                        <rect x="74" y="68" width="18" height="6" fill="#000000"/>
                        <rect x="40" y="84" width="8" height="8" fill="#000000"/>
                        <rect x="76" y="82" width="16" height="10" fill="#000000"/>
                    </svg>
                    <div style="font-size:12px;font-weight:700;color:#0f172a;margin-top:8px;">NMID: ID1020261024070</div>
                </div>

                <div id="snapVaBox" style="display:none;background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:10px;padding:16px;margin:14px 0;">
                    <div style="font-size:12px;color:#64748b;">Nomor Virtual Account (BSI / Mandiri):</div>
                    <div style="font-size:22px;font-weight:800;color:#1e40af;letter-spacing:1px;margin:6px 0;" id="snapVaNumber">
                        7268 0100 8123 4567
                    </div>
                    <div style="font-size:12px;color:#15803d;font-weight:600;">Otomatis terverifikasi dalam 1 detik</div>
                </div>

                <button type="button" class="btn-snap-simulate-pay" onclick="completeSnapPaymentSuccess()">
                    ✅ Simulasi Pembayaran Berhasil (Snap)
                </button>
                <div>
                    <button type="button" class="btn-snap-back" onclick="backToSnapMain()">&larr; Pilih Metode Lain</button>
                </div>
            </div>
        </div>
    </div>
`;

if (!content.includes('id="modalSnapMidtrans"')) {
    content = content.replace('</body>', snapModalHtml + '\n</body>');
    console.log('Appended Midtrans Snap modal HTML');
}

fs.writeFileSync('resources/views/welcome.blade.php', content, 'utf8');
console.log('Step 1 and 2 completed.');
