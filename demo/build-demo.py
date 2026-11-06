#!/usr/bin/env python3
"""
Build demo/spp-offline.html dari resources/views/welcome.blade.php.

Hasil build adalah SATU file HTML mandiri yang bisa dibuka langsung di HP
(file://) tanpa Laravel, MySQL, XAMPP, atau server apa pun.

Jalankan dari folder mana saja:
    python demo/build-demo.py
"""

from pathlib import Path
import re
import urllib.parse

ROOT = Path(__file__).resolve().parent.parent
BLADE = ROOT / "resources" / "views" / "welcome.blade.php"
MOCK = ROOT / "demo" / "mock-backend.js"
OUTPUT = ROOT / "demo" / "spp-offline.html"

# JavaScript kecil yang menggantikan Snap SDK asli.
# Saat window.snap.pay() dipanggil, alihkan ke simulasi bawaan aplikasi.
FAKE_SNAP_JS = (
    "window.snap={pay:function(t,o){return window.__demoSnapPay(t,o);}};"
)
FAKE_SNAP_SRC = (
    "data:text/javascript;charset=utf-8,"
    + urllib.parse.quote(FAKE_SNAP_JS, safe="")
)

REGISTER_HANDLER = (
    'href="#" onclick="event.preventDefault();'
    "if(window.showToast){showToast('Registrasi dinonaktifkan pada demo offline.');}"
    'return false;"'
)


def main() -> None:
    html = BLADE.read_text(encoding="utf-8")
    mock_js = MOCK.read_text(encoding="utf-8")

    # 1. Token CSRF dummy (mode demo tidak memakai server).
    html = html.replace("{{ csrf_token() }}", "demo-csrf-token-offline")

    # 2. Ganti Snap SDK asli dengan script data-URL yang mengarah ke simulasi.
    snap_pattern = re.compile(
        r'<script id="midtransSnapScript"\s+src="[^"]*"\s+'
        r'data-client-key="([^"]*)"\s*></script>'
    )
    if not snap_pattern.search(html):
        raise SystemExit("Tag midtransSnapScript tidak ditemukan di welcome.blade.php")

    html = snap_pattern.sub(
        lambda m: (
            '<script id="midtransSnapScript" '
            f'data-client-key="{m.group(1)}" src="{FAKE_SNAP_SRC}"></script>'
        ),
        html,
        count=1,
    )

    # 3. Netralkan tautan registrasi (tidak ada route /register di file://).
    html = html.replace('href="/register"', REGISTER_HANDLER, 1)

    # 4. Sesuaikan label status pada halaman Publikasi Online agar jujur
    #    menunjukkan mode demo (bukan "Lokal (localhost)").
    html = html.replace(
        "badge.innerText = isOnline ? 'Online' : 'Lokal (localhost)';",
        "badge.innerText = isOnline ? 'Online' : 'Demo Offline';",
    )

    # 5. Sisipkan mock backend tepat setelah <body> agar fetch sudah siap
    #    sebelum script utama aplikasi berjalan.
    marker = "<body>"
    if marker not in html:
        raise SystemExit("Tag <body> tidak ditemukan")

    injected = (
        marker
        + "\n\n"
        + "    <!-- ==================================================================\n"
        + "         DEMO OFFLINE: mock backend (tidak ada request ke server)\n"
        + "         File ini di-generate oleh demo/build-demo.py\n"
        + "         ================================================================== -->\n"
        + "    <script>\n"
        + mock_js
        + "\n    </script>\n"
    )
    html = html.replace(marker, injected, 1)

    # 6. Tambahkan komentar header.
    html = (
        "<!--\n"
        "  E-SPP SMP Muhammadiyah 3 Metro - DEMO OFFLINE\n"
        "  Satu file HTML mandiri. Buka langsung di browser HP/laptop.\n"
        "  Tidak memerlukan Laravel, MySQL, XAMPP, atau koneksi internet.\n"
        "  Pembayaran Midtrans disimulasikan (bukan transaksi sungguhan).\n"
        "-->\n"
        + html
    )

    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT.write_text(html, encoding="utf-8")

    size_kb = OUTPUT.stat().st_size / 1024
    print(f"OK  {OUTPUT}")
    print(f"    {size_kb:.1f} KB")
    print(f"    Snap payload: {len(FAKE_SNAP_SRC)} chars")


if __name__ == "__main__":
    main()
