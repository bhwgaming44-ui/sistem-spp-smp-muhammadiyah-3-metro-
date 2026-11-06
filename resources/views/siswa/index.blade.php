<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - E-SPP SMP Muhammadiyah 3 Metro</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #0f172a; }
        .container { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .header h1 { font-size: 24px; font-weight: 700; color: #1e40af; }
        .btn { padding: 10px 16px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; }
        .btn-primary { background: #1e40af; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .table th { background: #f8fafc; font-weight: 700; color: #64748b; font-size: 13px; text-transform: uppercase; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .badge-aktif { background: #dcfce7; color: #166534; }
        .search-box { margin-bottom: 16px; }
        .search-box input { width: 100%; padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; }
        .kelas-filter { display: inline-block; margin-right: 16px; }
        .kelas-filter select { padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px; }
        .wali-info { background: #f1f5f9; padding: 12px; border-radius: 8px; margin-top: 12px; }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>👥 Data Siswa</h1>
                <p style="color: #64748b; font-size: 14px;">Kelola data siswa dan pantau status SPP</p>
            </div>
            <a href="/register" class="btn btn-primary">+ Daftar Siswa Baru</a>
        </div>
        <div class="card">
            <div class="search-box">
                <input type="text" id="searchSiswa" placeholder="Cari siswa (NISN, Nama, Kelas)..." onkeyup="filterSiswa()">
            </div>
            <div class="kelas-filter">
                <select id="filterKelas" onchange="filterSiswa()">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $kelas)
                    <option value="{{ $kelas->nama_kelas }}">{{ $kelas->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>NISN</th><th>Nama Siswa</th><th>Kelas</th><th>Tahun Ajaran</th><th>Wali Murid</th><th>Status</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="siswaTableBody">
                    @foreach($siswa as $s)
                    <tr>
                        <td><strong>{{ $s->nisn }}</strong></td>
                        <td>{{ $s->nama_siswa }}</td>
                        <td>{{ $s->kelas->nama_kelas ?? '-' }}</td>
                        <td>{{ $s->tahunAjaran->tahun_ajaran ?? '-' }}</td>
                        <td>
                            @if($s->waliMurid)
                                @if($s->waliMurid->nama_wali)
                                    {{ $s->waliMurid->nama_wali }}
                                @else
                                    -
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td><span class="badge badge-aktif">{{ $s->status_siswa }}</span></td>
                        <td><button class="btn" style="background:#e2e8f0;color:#0f172a;padding:6px 10px;font-size:12px;" onclick="viewSiswaDetail('{{ $s->nisn }}')">Detail</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function filterSiswa() {
            const search = document.getElementById('searchSiswa').value.toLowerCase();
            const kelasFilter = document.getElementById('filterKelas').value;
            const rows = document.querySelectorAll('#siswaTableBody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const kelasCell = row.children[2]?.innerText || '';
                const matchSearch = text.includes(search);
                const matchKelas = !kelasFilter || kelasCell === kelasFilter || kelasCell.includes(kelasFilter);
                row.style.display = (matchSearch && matchKelas) ? '' : 'none';
            });
        }
        function viewSiswaDetail(nisn) {
            window.location.href = '/siswa/' + nisn;
        }
    </script>
</body>
</html>