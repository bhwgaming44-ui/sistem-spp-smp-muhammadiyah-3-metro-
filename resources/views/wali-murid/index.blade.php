<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Orang Tua - E-SPP SMP Muhammadiyah 3 Metro</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #0f172a; }
        .container { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .header h1 { font-size: 24px; font-weight: 700; color: #1e40af; }
        .card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .table th { background: #f8fafc; font-weight: 700; color: #64748b; font-size: 13px; text-transform: uppercase; }
        .search-box { margin-bottom: 16px; }
        .search-box input { width: 100%; padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; }
        .kelas-filter { display: inline-block; margin-right: 16px; }
        .kelas-filter select { padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px; }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>👨‍👩‍👧‍👦 Data Orang Tua / Wali Murid</h1>
                <p style="color: #64748b; font-size: 14px;">Data wali murid dan siswa binaan</p>
            </div>
        </div>
        <div class="card">
            <div class="search-box">
                <input type="text" id="searchWali" placeholder="Cari wali murid (Nama, NISN Anak, Kelas)..." onkeyup="filterWali()">
            </div>
            <div class="kelas-filter">
                <select id="filterKelasWali" onchange="filterWali()">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $kelas)
                    <option value="{{ $kelas->nama_kelas }}">{{ $kelas->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Wali</th><th>NISN Anak</th><th>Nama Anak</th><th>Kelas</th><th>Telp</th><th>Email</th>
                    </tr>
                </thead>
                <tbody id="waliTableBody">
                    @foreach($waliMurid as $w)
                    <tr>
                        <td>{{ $w->nama_wali }}</td>
                        <td>{{ $w->siswa->nisn ?? '-' }}</td>
                        <td>{{ $w->siswa->nama_siswa ?? '-' }}</td>
                        <td>{{ $w->siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td>{{ $w->no_telp }}</td>
                        <td>{{ $w->email }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function filterWali() {
            const search = document.getElementById('searchWali').value.toLowerCase();
            const kelasFilter = document.getElementById('filterKelasWali').value;
            const rows = document.querySelectorAll('#waliTableBody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const kelasCell = row.children[3]?.innerText || '';
                const matchSearch = text.includes(search);
                const matchKelas = !kelasFilter || kelasCell === kelasFilter || kelasCell.includes(kelasFilter);
                row.style.display = (matchSearch && matchKelas) ? '' : 'none';
            });
        }
    </script>
</body>
</html>