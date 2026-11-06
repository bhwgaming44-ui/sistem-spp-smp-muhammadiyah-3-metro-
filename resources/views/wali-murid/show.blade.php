<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Wali Murid - E-SPP SMP Muhammadiyah 3 Metro</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #0f172a; }
        .container { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .header h1 { font-size: 24px; font-weight: 700; color: #1e40af; }
        .card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .detail-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .detail-table th, .detail-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .detail-table th { background: #f8fafc; font-weight: 700; color: #64748b; font-size: 13px; width: 30%; }
        .detail-table td { color: #0f172a; }
        .btn { padding: 8px 16px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; margin-right: 8px; }
        .btn-primary { background: #1e40af; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #64748b; color: white; }
        .btn-secondary:hover { background: #475269; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fef3c7; color: #92400e; }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>👨‍👩‍👧‍👦 Detail Wali Murid</h1>
                <p style="color: #64748b; font-size: 14px;">Siswa: {{ $siswa->nama_siswa }}</p>
            </div>
            <a href="/wali-murid" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="card">
            <h2>Informasi Wali Murid</h2>
            
            @if($waliMurid)
                <table class="detail-table">
                    <tr>
                        <th>Nama Wali</th>
                        <td>{{ $waliMurid->nama_wali }}</td>
                    </tr>
                    <tr>
                        <th>No. Telepon</th>
                        <td>{{ $waliMurid->no_telp ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $waliMurid->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kelas</th>
                        <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                    </tr>
                </table>
            @else
                <div class="alert alert-error">
                    Data wali murid belum tersedia untuk siswa ini.
                </div>
            @endif
        </div>

        <div class="card" style="margin-top: 24px;">
            <h2>Data Siswa</h2>
            <table class="detail-table">
                <tr>
                    <th>NISN</th>
                    <td>{{ $siswa->nisn }}</td>
                </tr>
                <tr>
                    <th>Nama Siswa</th>
                    <td>{{ $siswa->nama_siswa }}</td>
                </tr>
                <tr>
                    <th>Kelas</th>
                    <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Tahun Ajaran</th>
                    <td>{{ $siswa->tahunAjaran->tahun_ajaran ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status Siswa</th>
                    <td><span class="badge badge-aktif">{{ $siswa->status_siswa }}</span></td>
                </tr>
                <tr>
                    <th>Jenis Kelamin</th>
                    <td>{{ $siswa->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan' }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>