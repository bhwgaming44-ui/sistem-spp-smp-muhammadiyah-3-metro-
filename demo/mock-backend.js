/* ============================================================================
 * MOCK BACKEND — E-SPP SMP Muhammadiyah 3 Metro (MODE DEMO OFFLINE)
 * ----------------------------------------------------------------------------
 * File ini HANYA dipakai oleh demo/spp-offline.html.
 * Semua panggilan fetch('/api/...') dijawab di dalam browser dengan data contoh,
 * sehingga aplikasi bisa dibuka langsung dari file HTML tanpa server Laravel,
 * MySQL, XAMPP, atau koneksi internet.
 *
 * Pembayaran Midtrans TIDAK memanggil server sungguhan. Alur Snap disimulasikan
 * memakai modal pembayaran bawaan aplikasi.
 * ========================================================================== */
(function () {
    'use strict';

    /* ------------------------------------------------------------------
     * 1. Penyimpanan lokal: fallback bila browser membatasi localStorage
     *    pada file:// (mis. mode incognito).
     * ---------------------------------------------------------------- */
    try {
        window.localStorage.setItem('__demo_probe__', '1');
        window.localStorage.removeItem('__demo_probe__');
    } catch (e) {
        var memoryStore = {};
        var memoryStorage = {
            getItem: function (k) {
                return Object.prototype.hasOwnProperty.call(memoryStore, k) ? memoryStore[k] : null;
            },
            setItem: function (k, v) { memoryStore[k] = String(v); },
            removeItem: function (k) { delete memoryStore[k]; },
            clear: function () { memoryStore = {}; }
        };
        try {
            Object.defineProperty(window, 'localStorage', {
                configurable: true,
                value: memoryStorage
            });
        } catch (e2) {
            window.__demoMemoryStorage = memoryStorage;
        }
    }

    /* ------------------------------------------------------------------
     * 2. Polyfill clipboard agar tombol salin tidak error di file://
     * ---------------------------------------------------------------- */
    if (!navigator.clipboard) {
        try {
            Object.defineProperty(navigator, 'clipboard', {
                configurable: true,
                value: {
                    writeText: function () {
                        return Promise.reject(new Error('Clipboard tidak tersedia pada mode demo.'));
                    }
                }
            });
        } catch (e) { /* abaikan */ }
    }

    var DEMO_MERCHANT = 'SMP MUHAMMADIYAH 3 METRO';

    function pad2(n) { return (n < 10 ? '0' : '') + n; }

    function nowStr() {
        var d = new Date();
        return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate()) +
            ' ' + pad2(d.getHours()) + ':' + pad2(d.getMinutes()) + ':' + pad2(d.getSeconds());
    }

    /* ------------------------------------------------------------------
     * 3. Data contoh (bisa ditambah / diubah / dihapus selama demo)
     * ---------------------------------------------------------------- */
    var demoPetugas = [
        {
            id_petugas: 1,
            id_user: 1,
            user: { id_user: 1, username: 'admin', role: 'petugas' },
            jabatan: 'Administrator SPP',
            no_telp: '0812-3456-7890',
            email: 'admin@smpm3metro.sch.id',
            status_petugas: 'aktif',
            created_at: '2024-07-15T08:00:00'
        },
        {
            id_petugas: 2,
            id_user: 2,
            user: { id_user: 2, username: 'bendahara', role: 'petugas' },
            jabatan: 'Bendahara Sekolah',
            no_telp: '0812-9876-5432',
            email: 'bendahara@smpm3metro.sch.id',
            status_petugas: 'aktif',
            created_at: '2024-07-15T08:05:00'
        },
        {
            id_petugas: 3,
            id_user: 3,
            user: { id_user: 3, username: 'kepsek', role: 'petugas' },
            jabatan: 'Kepala Sekolah',
            no_telp: '0813-1122-3344',
            email: 'kepsek@smpm3metro.sch.id',
            status_petugas: 'aktif',
            created_at: '2024-07-15T08:10:00'
        }
    ];

    var demoTagihan = [
        {
            id_tagihan: 1, id_siswa: 1, id_kelas: 1,
            siswa: { id_siswa: 1, nisn: '0081234567', nama_siswa: 'Ahmad Dahlan Putra' },
            kelas: { id_kelas: 1, nama_kelas: '7A' },
            bulan: 'Januari 2025', nominal: 250000, status: 'belum_lunas',
            keterangan: 'SPP bulan Januari 2025', created_at: '2025-01-05T08:00:00'
        },
        {
            id_tagihan: 2, id_siswa: 2, id_kelas: 2,
            siswa: { id_siswa: 2, nisn: '0087654321', nama_siswa: 'Siti Walidah Putri' },
            kelas: { id_kelas: 2, nama_kelas: '8A' },
            bulan: 'Januari 2025', nominal: 250000, status: 'belum_lunas',
            keterangan: 'SPP bulan Januari 2025', created_at: '2025-01-05T08:01:00'
        },
        {
            id_tagihan: 3, id_siswa: 3, id_kelas: 3,
            siswa: { id_siswa: 3, nisn: '0082345678', nama_siswa: 'Muhammad Fauzan' },
            kelas: { id_kelas: 3, nama_kelas: '7B' },
            bulan: 'Januari 2025', nominal: 250000, status: 'belum_lunas',
            keterangan: 'SPP bulan Januari 2025', created_at: '2025-01-05T08:02:00'
        },
        {
            id_tagihan: 4, id_siswa: 4, id_kelas: 4,
            siswa: { id_siswa: 4, nisn: '0083456789', nama_siswa: 'Aisyah Rahmawati' },
            kelas: { id_kelas: 4, nama_kelas: '8B' },
            bulan: 'Januari 2025', nominal: 250000, status: 'lunas',
            keterangan: 'Sudah dibayar via QRIS', created_at: '2025-01-04T09:30:00'
        },
        {
            id_tagihan: 5, id_siswa: 5, id_kelas: 5,
            siswa: { id_siswa: 5, nisn: '0084567890', nama_siswa: 'Fikri Pratama' },
            kelas: { id_kelas: 5, nama_kelas: '9A' },
            bulan: 'Januari 2025', nominal: 250000, status: 'lunas',
            keterangan: 'Sudah dibayar via Virtual Account', created_at: '2025-01-04T10:15:00'
        },
        {
            id_tagihan: 6, id_siswa: 1, id_kelas: 1,
            siswa: { id_siswa: 1, nisn: '0081234567', nama_siswa: 'Ahmad Dahlan Putra' },
            kelas: { id_kelas: 1, nama_kelas: '7A' },
            bulan: 'Desember 2024', nominal: 250000, status: 'lunas',
            keterangan: 'Sudah dibayar via Midtrans QRIS', created_at: '2024-12-10T09:15:00'
        },
        {
            id_tagihan: 7, id_siswa: 2, id_kelas: 2,
            siswa: { id_siswa: 2, nisn: '0087654321', nama_siswa: 'Siti Walidah Putri' },
            kelas: { id_kelas: 2, nama_kelas: '8A' },
            bulan: 'Desember 2024', nominal: 250000, status: 'lunas',
            keterangan: 'Sudah dibayar via Kasir', created_at: '2024-12-11T14:20:00'
        },
        {
            id_tagihan: 8, id_siswa: 3, id_kelas: 3,
            siswa: { id_siswa: 3, nisn: '0082345678', nama_siswa: 'Muhammad Fauzan' },
            kelas: { id_kelas: 3, nama_kelas: '7B' },
            bulan: 'Desember 2024', nominal: 250000, status: 'lunas',
            keterangan: 'Sudah dibayar via QRIS', created_at: '2024-12-12T11:05:00'
        }
    ];

    /* ------------------------------------------------------------------
     * 4. Registry transaksi Midtrans simulasi
     * ---------------------------------------------------------------- */
    var demoSnapTokens = {};
    var demoPaidOrders = {};
    var demoOrderSeq = 1000;

    /**
     * Dipanggil oleh window.snap.pay() tiruan.
     * Membuka modal pembayaran bawaan aplikasi, lalu menandai order lunas
     * ketika tombol "Simulasi Pembayaran Berhasil" diklik.
     */
    window.__demoSnapPay = function (token, opts) {
        opts = opts || {};
        var payment = demoSnapTokens[token];

        if (!payment) {
            if (typeof opts.onError === 'function') {
                opts.onError({ status_message: 'Token demo tidak ditemukan.' });
            }
            return;
        }

        window.__demoPaidOrder = payment.orderId;

        if (typeof window.openMidtransSnapModal === 'function') {
            window.openMidtransSnapModal(payment.orderId, payment.amount, payment.bulan);
        }

        // Tombol simulasi ada di dalam modal. Daftarkan listener sekali saja.
        var simulateBtn = document.querySelector('.btn-snap-simulate-pay');
        if (simulateBtn && !simulateBtn.getAttribute('data-demo-bound')) {
            simulateBtn.setAttribute('data-demo-bound', '1');
            simulateBtn.addEventListener('click', function () {
                if (window.__demoPaidOrder) {
                    demoPaidOrders[window.__demoPaidOrder] = true;
                }
            });
        }
    };

    /* ------------------------------------------------------------------
     * 5. Helper respons fetch
     * ---------------------------------------------------------------- */
    function ok(data, status) {
        status = status || 200;
        return Promise.resolve({
            ok: status >= 200 && status < 300,
            status: status,
            json: function () { return Promise.resolve(data); },
            text: function () { return Promise.resolve(JSON.stringify(data)); }
        });
    }

    function readBody(init) {
        var body = init && init.body;
        if (!body) return {};

        if (typeof FormData !== 'undefined' && body instanceof FormData) {
            var obj = {};
            body.forEach(function (value, key) { obj[key] = value; });
            return obj;
        }

        if (typeof body === 'string') {
            try { return JSON.parse(body); } catch (e) { return {}; }
        }

        return {};
    }

    function nextId(list, key) {
        var max = 0;
        list.forEach(function (item) {
            var n = Number(item[key]);
            if (!isNaN(n) && n > max) max = n;
        });
        return max + 1;
    }

    function findIndexBy(list, key, value) {
        for (var i = 0; i < list.length; i++) {
            if (Number(list[i][key]) === Number(value)) return i;
        }
        return -1;
    }

    function getDemoStudents() {
        try {
            if (typeof DEMO_USERS !== 'undefined' && DEMO_USERS && Array.isArray(DEMO_USERS.siswa)) {
                return DEMO_USERS.siswa;
            }
        } catch (e) { /* DEMO_USERS belum siap */ }
        return [];
    }

    function buildDemoTagihanSiswa() {
        return getDemoStudents().map(function (s, i) {
            var belum = s.tagihanUnpaid && s.tagihanUnpaid.length > 0;
            return {
                id_tagihan: i + 1,
                id_siswa: i + 1,
                id_kelas: i + 1,
                siswa: { id_siswa: i + 1, nisn: s.nisn, nama_siswa: s.nama },
                kelas: { id_kelas: i + 1, nama_kelas: s.kelas },
                bulan: 'Januari 2025',
                nominal: s.tarif || 250000,
                status: belum ? 'belum_lunas' : 'lunas',
                keterangan: belum ? 'SPP bulan Januari 2025' : 'Sudah dibayar',
                created_at: '2025-01-05T08:00:00'
            };
        });
    }

    /* ------------------------------------------------------------------
     * 6. Router fetch tiruan
     * ---------------------------------------------------------------- */
    window.fetch = function (input, init) {
        init = init || {};

        var raw = typeof input === 'string' ? input : ((input && input.url) || '');
        var path = raw.split('?')[0].split('#')[0];
        var apiIndex = path.indexOf('/api/');
        if (apiIndex >= 0) path = path.slice(apiIndex);

        var method = String(init.method || (input && input.method) || 'GET').toUpperCase();
        var body = readBody(init);

        /* ---------------- System / Publikasi ---------------- */
        if (path === '/api/system/status' && method === 'GET') {
            return ok({
                success: true,
                app_name: 'E-SPP SMP Muhammadiyah 3 Metro',
                app_env: 'demo-offline',
                app_debug: false,
                app_url: window.location.href.split('?')[0].split('#')[0],
                current_url: 'Demo Offline (tanpa server)',
                host: 'demo-offline',
                is_local: true,
                is_https: false,
                php_version: 'Demo (tanpa server)',
                laravel_version: 'Demo (tanpa server)',
                db_connected: true,
                db_error: null,
                storage_writable: true,
                public_url: window.localStorage.getItem('demo_public_url') || null,
                server_time: nowStr()
            });
        }

        if (path === '/api/system/public-url') {
            if (method === 'POST') {
                var publicUrl = String(body.public_url || '').trim();
                if (publicUrl && !/^https?:\/\//i.test(publicUrl)) {
                    return ok({
                        success: false,
                        message: 'URL harus diawali http:// atau https://'
                    }, 422);
                }
                publicUrl = publicUrl.replace(/\/+$/, '');
                window.localStorage.setItem('demo_public_url', publicUrl);
                return ok({
                    success: true,
                    public_url: publicUrl,
                    message: 'URL publikasi berhasil disimpan (mode demo).'
                });
            }
            return ok({
                success: true,
                public_url: window.localStorage.getItem('demo_public_url') || null,
                updated_at: null
            });
        }

        /* ---------------- Midtrans ---------------- */
        if (path === '/api/midtrans/config') {
            if (method === 'POST') {
                return ok({
                    success: true,
                    message: 'Mode demo offline: konfigurasi Midtrans tidak dikirim ke server.'
                });
            }
            var snapTag = document.getElementById('midtransSnapScript');
            return ok({
                success: true,
                enabled: true,
                is_production: false,
                mode: 'sandbox',
                merchant_id: 'YOUR_MERCHANT_ID',
                client_key: snapTag ? (snapTag.getAttribute('data-client-key') || '') : '',
                server_key: 'Mid-server-******** (disembunyikan pada demo)',
                snap_js: snapTag ? snapTag.src : '',
                qris_name: DEMO_MERCHANT
            });
        }

        if (path === '/api/midtrans/snap-token' && method === 'POST') {
            var amount = parseInt(body.amount, 10) || 0;
            var orderId = 'DEMO-' + Date.now().toString().slice(-8);
            var token = 'demo-snap-' + (++demoOrderSeq);

            demoSnapTokens[token] = {
                orderId: orderId,
                amount: amount,
                bulan: body.bulan || 'SPP',
                nisn: body.nisn || '',
                nama: body.student_name || ''
            };

            return ok({
                success: true,
                snap_token: token,
                order_id: orderId,
                redirect_url: '',
                qris_name: DEMO_MERCHANT,
                is_production: false,
                expires_at: new Date(Date.now() + 24 * 3600 * 1000).toISOString(),
                expires_in: 86400
            });
        }

        var cekMatch = path.match(/^\/api\/midtrans\/cek-status\/(.+)$/);
        if (cekMatch && method === 'GET') {
            var cekOrderId = decodeURIComponent(cekMatch[1]);
            var paid = !!demoPaidOrders[cekOrderId];
            return ok({
                success: true,
                paid: paid,
                order_id: cekOrderId,
                payment_type: paid ? 'qris' : null,
                transaction_status: paid ? 'settlement' : 'pending'
            });
        }

        if (path === '/api/midtrans/notification' && method === 'POST') {
            return ok({ success: true, message: 'Notifikasi diabaikan pada mode demo.' });
        }

        /* ---------------- Petugas ---------------- */
        if (path === '/api/petugas/role' && method === 'GET') {
            return ok(demoPetugas.map(function (p) {
                return {
                    id_user: p.id_user,
                    user: {
                        id_user: p.user.id_user,
                        username: p.user.username,
                        role: p.user.role
                    }
                };
            }));
        }

        if (path === '/api/petugas') {
            if (method === 'GET') {
                return ok(demoPetugas.slice());
            }
            if (method === 'POST') {
                var chosenUserId = parseInt(body.id_user, 10) || 0;
                var chosen = demoPetugas.filter(function (p) {
                    return Number(p.id_user) === chosenUserId;
                })[0];

                var newPetugas = {
                    id_petugas: nextId(demoPetugas, 'id_petugas'),
                    id_user: chosenUserId || nextId(demoPetugas, 'id_user'),
                    user: {
                        id_user: chosenUserId || nextId(demoPetugas, 'id_user'),
                        username: chosen ? chosen.user.username : ('petugas' + (demoPetugas.length + 1)),
                        role: 'petugas'
                    },
                    jabatan: body.jabatan || '-',
                    no_telp: body.no_telp || '',
                    email: body.email || '',
                    status_petugas: body.status_petugas || 'aktif',
                    created_at: nowStr()
                };
                demoPetugas.push(newPetugas);
                return ok({
                    status: 'success',
                    message: 'Petugas berhasil ditambahkan!',
                    data: newPetugas
                });
            }
        }

        var petugasMatch = path.match(/^\/api\/petugas\/(\d+)$/);
        if (petugasMatch) {
            var petugasId = Number(petugasMatch[1]);
            var petugasIndex = findIndexBy(demoPetugas, 'id_petugas', petugasId);

            if (petugasIndex < 0) {
                return ok({ status: 'error', message: 'Petugas tidak ditemukan.' }, 404);
            }

            if (method === 'GET') {
                return ok(demoPetugas[petugasIndex]);
            }

            if (method === 'PUT') {
                var edited = demoPetugas[petugasIndex];
                if (body.jabatan != null) edited.jabatan = body.jabatan;
                if (body.no_telp != null) edited.no_telp = body.no_telp;
                if (body.email != null) edited.email = body.email;
                if (body.status_petugas != null) edited.status_petugas = body.status_petugas;
                return ok({
                    status: 'success',
                    message: 'Petugas berhasil diperbarui!',
                    data: edited
                });
            }

            if (method === 'DELETE') {
                demoPetugas.splice(petugasIndex, 1);
                return ok({ status: 'success', message: 'Petugas berhasil dihapus!' });
            }
        }

        /* ---------------- Tahun Ajaran ---------------- */
        if (path === '/api/tahun-ajaran' && method === 'GET') {
            var nowYear = new Date().getFullYear();
            var nowMonth = new Date().getMonth();
            var currentTa = nowMonth >= 6
                ? (nowYear + '/' + (nowYear + 1))
                : ((nowYear - 1) + '/' + nowYear);

            // Rentang tahun ajaran 2000/2001 sampai 2045/2046, sama dengan
            // isi tabel tbl_tahun_ajaran pada aplikasi Laravel.
            var tahunAjaranRows = [];
            var taId = 1;
            for (var taStart = 2000; taStart <= 2045; taStart++) {
                var taName = taStart + '/' + (taStart + 1);
                tahunAjaranRows.push({
                    id_tahun_ajaran: taId++,
                    tahun_ajaran: taName,
                    status_aktif: taName === currentTa ? 'aktif' : 'nonaktif'
                });
            }

            return ok({
                success: true,
                status: 'success',
                count: tahunAjaranRows.length,
                data: tahunAjaranRows
            });
        }

        /* ---------------- Tagihan ---------------- */
        if (path === '/api/tagihan/search' && method === 'GET') {
            var queryString = raw.indexOf('?') >= 0 ? raw.split('?')[1] : '';
            var params = {};
            queryString.split('&').forEach(function (pair) {
                if (!pair) return;
                var parts = pair.split('=');
                var key = decodeURIComponent(parts[0] || '');
                var value = decodeURIComponent((parts[1] || '').replace(/\+/g, ' '));
                params[key] = value;
            });

            var q = String(params.query || params.q || '').toLowerCase().trim();
            var statusFilter = String(params.status || '');

            var searchResult = buildDemoTagihanSiswa().filter(function (t) {
                var haystack = [
                    t.siswa.nisn, t.siswa.nama_siswa, t.kelas.nama_kelas,
                    t.bulan, t.keterangan
                ].join(' ').toLowerCase();

                var cocokQuery = !q || haystack.indexOf(q) >= 0;
                var cocokStatus = !statusFilter || t.status === statusFilter;
                return cocokQuery && cocokStatus;
            });

            var mapped = searchResult.map(function (t) {
                return {
                    id: t.id_tagihan,
                    id_tagihan: t.id_tagihan,
                    nisn: t.siswa.nisn,
                    nis: t.siswa.nisn,
                    nama_siswa: t.siswa.nama_siswa,
                    kelas: t.kelas.nama_kelas,
                    bulan: t.bulan,
                    nominal: t.nominal,
                    status: t.status,
                    status_label: t.status === 'lunas' ? 'Lunas' : 'Belum Lunas',
                    tempo: '2025-01-15',
                    tahun_ajaran: '2024/2025 Genap',
                    keterangan: t.keterangan
                };
            });

            var totalNominal = mapped.reduce(function (sum, t) {
                return sum + (Number(t.nominal) || 0);
            }, 0);

            return ok({
                status: 'success',
                query: q,
                count: mapped.length,
                total_nominal: totalNominal,
                data: mapped
            });
        }

        if (path === '/api/tagihan/unpaid' || path.indexOf('/api/tagihan/unpaid/') === 0) {
            var identifier = path.indexOf('/api/tagihan/unpaid/') === 0
                ? decodeURIComponent(path.replace('/api/tagihan/unpaid/', ''))
                : '';
            var unpaid = demoTagihan.filter(function (t) {
                var matchStudent = !identifier ||
                    String(t.id_siswa) === identifier ||
                    (t.siswa && String(t.siswa.nisn) === identifier);
                return matchStudent && t.status !== 'lunas';
            });
            var totalNominal = unpaid.reduce(function (sum, t) {
                return sum + (parseInt(t.nominal, 10) || 0);
            }, 0);
            return ok({
                status: 'success',
                count: unpaid.length,
                total_nominal: totalNominal,
                data: unpaid
            });
        }

        if (path === '/api/tagihan' && method === 'GET') {
            return ok(demoTagihan.slice());
        }

        var tagihanMatch = path.match(/^\/api\/tagihan\/(\d+)$/);
        if (tagihanMatch) {
            var tagihanId = Number(tagihanMatch[1]);
            var tagihanIndex = findIndexBy(demoTagihan, 'id_tagihan', tagihanId);

            if (tagihanIndex < 0) {
                return ok({ status: 'error', message: 'Tagihan tidak ditemukan.' }, 404);
            }

            if (method === 'GET') {
                return ok(demoTagihan[tagihanIndex]);
            }

            if (method === 'DELETE') {
                demoTagihan.splice(tagihanIndex, 1);
                return ok({ status: 'success', message: 'Tagihan berhasil dihapus!' });
            }
        }

        /* ---------------- Fallback ---------------- */
        console.warn('[DEMO OFFLINE] Endpoint belum dimock:', method, path);
        if (method === 'GET') return ok([]);
        return ok({ success: true, status: 'success' });
    };

    /* ------------------------------------------------------------------
     * 7. Badge penanda mode demo
     * ---------------------------------------------------------------- */
    function addDemoBadge() {
        if (document.getElementById('demoModeBadge')) return;

        var badge = document.createElement('button');
        badge.id = 'demoModeBadge';
        badge.type = 'button';
        badge.textContent = '🔌 Mode Demo Offline';
        badge.style.cssText = 'position:fixed;right:14px;bottom:14px;z-index:99999;' +
            'background:#0f172a;color:#fff;border:1px solid #334155;border-radius:999px;' +
            'padding:8px 14px;font:700 11px/1 "Plus Jakarta Sans",system-ui,sans-serif;' +
            'letter-spacing:.3px;box-shadow:0 8px 24px rgba(15,23,42,.28);cursor:pointer;opacity:.94;';
        badge.onclick = function () {
            var message = 'Mode demo offline: data contoh & pembayaran Midtrans disimulasikan ' +
                'tanpa server Laravel/XAMPP.';
            if (typeof window.showToast === 'function') {
                window.showToast(message);
            } else {
                window.alert(message);
            }
        };
        document.body.appendChild(badge);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addDemoBadge);
    } else {
        addDemoBadge();
    }

    console.log('[DEMO OFFLINE] Mock backend aktif. Tidak ada request ke server sungguhan.');
})();
