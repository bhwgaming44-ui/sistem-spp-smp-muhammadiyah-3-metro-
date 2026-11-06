<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kelas - E-SPP SMP Muhammadiyah 3 Metro</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #0f172a; }
        .container { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .header h1 { font-size: 24px; font-weight: 700; color: #1e40af; }
        .btn { padding: 10px 16px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; }
        .btn-primary { background: #1e40af; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .table th { background: #f8fafc; font-weight: 700; color: #64748b; font-size: 13px; text-transform: uppercase; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .badge-aktif { background: #dcfce7; color: #166534; }
        .badge-active { background: #bfdbfe; color: #1e40af; }
        .no-data { text-align: center; padding: 40px; color: #64748b; }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📚 Data Kelas</h1>
            <a href="/staff" class="btn btn-secondary">Kembali ke Petugas</a>
        </div>

        <div class="card">
            <h2>Daftar Kelas dan Jumlah Murid</h2>
            <p class="subtitle">Total murid terdaftar di seluruh kelas</p>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Kelas</th>
                        <th>Tingkat</th>
                        <th>Jumlah Murid</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kelas as $k)
                    <tr>
                        <td><strong>{{ $k->nama_kelas }}</strong></td>
                        <td>{{ $k->tingkat }}</td>
                        <td><span class="badge badge-aktif">{{ $k->siswa_count }}</span></td>
                        <td><a href="/siswa?kelas={{ $k->nama_kelas }}" class="btn btn-sm btn-primary">Lihat Murid</a></td>
                    </tr>
                    @endforeach
                    @if($kelas->isEmpty())
                    <tr>
                        <td colspan="4" class="no-data">Belum ada data kelas</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>