<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Siswa Baru - E-SPP SMP Muhammadiyah 3 Metro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary-blue: #1e40af; 
            --primary-blue-hover: #1d4ed8; 
            --bg-page: #f8fafc; 
            --text-dark: #0f172a; 
            --text-muted: #64748b; 
            --border-color: #e2e8f0; 
            --white: #ffffff; 
            --success: #10b981; 
            --danger: #ef4444; 
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.06); 
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-page); color: var(--text-dark); line-height: 1.5; min-height: 100vh; }
        .register-wrapper { display: flex; min-height: 100vh; background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); padding: 30px 20px; }
        .register-panel { margin: auto; background: var(--white); border-radius: 16px; box-shadow: var(--shadow); width: 100%; max-width: 580px; padding: 40px; }
        .register-header { text-align: center; margin-bottom: 28px; }
        .register-header h1 { color: var(--primary-blue); font-size: 26px; font-weight: 800; margin-bottom: 6px; }
        .register-header p { color: var(--text-muted); font-size: 14px; }
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; margin-bottom: 6px; font-weight: 700; color: var(--text-dark); font-size: 13px; }
        .form-input { width: 100%; padding: 11px 14px; border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 14px; font-family: inherit; color: var(--text-dark); transition: border-color 0.2s, box-shadow 0.2s; }
        .form-input:focus { outline: none; border-color: var(--primary-blue); box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.12); }
        .form-hint { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
        .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .btn { width: 100%; padding: 13px; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; font-family: inherit; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block; text-align: center; }
        .btn-primary { background: var(--primary-blue); color: var(--white); }
        .btn-primary:hover { background: var(--primary-blue-hover); }
        .btn-secondary { background: #f1f5f9; color: var(--text-dark); margin-top: 12px; border: 1px solid var(--border-color); }
        .btn-secondary:hover { background: #e2e8f0; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: var(--primary-blue); text-decoration: none; font-size: 14px; font-weight: 600; }
        .back-link a:hover { text-decoration: underline; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 580px) { 
            .form-row { grid-template-columns: 1fr; } 
            .register-panel { padding: 24px; } 
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <div class="register-panel">
            <div class="register-header">
                <h1>📝 Daftar Siswa Baru</h1>
                <p>Portal Pendaftaran Siswa E-SPP SMP Muhammadiyah 3 Metro</p>
            </div>

            @if(session('success'))
            <div class="alert alert-success">
                ✅ {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-error">
                <strong>Terjadi kesalahan:</strong>
                <ul style="margin-left: 20px; margin-top: 6px;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="/register">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">NISN <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="nisn" value="{{ old('nisn') }}" class="form-input" placeholder="Contoh: 0081234567" maxlength="10" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIS <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="nis" value="{{ old('nis') }}" class="form-input" placeholder="Contoh: 2024001" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Lengkap Siswa <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="nama_siswa" value="{{ old('nama_siswa') }}" class="form-input" placeholder="Contoh: Ahmad Dahlan Putra" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin <span style="color: var(--danger);">*</span></label>
                        <select name="jenis_kelamin" class="form-input" required>
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="Laki-Laki" {{ old('jenis_kelamin') == 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kelas <span style="color: var(--danger);">*</span></label>
                        <select name="id_kelas" class="form-input" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id_kelas }}" {{ old('id_kelas') == $kelas->id_kelas ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }} (Tingkat {{ $kelas->tingkat }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tahun Ajaran <span style="color: var(--danger);">*</span></label>
                    <select name="id_tahun_ajaran" class="form-input" required>
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($tahunAjaranList as $tahun)
                        <option value="{{ $tahun->id_tahun_ajaran }}" {{ old('id_tahun_ajaran') == $tahun->id_tahun_ajaran ? 'selected' : '' }}>
                            {{ $tahun->tahun_ajaran }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Orang Tua / Wali <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="nama_ortu" value="{{ old('nama_ortu') }}" class="form-input" placeholder="Contoh: Budi Santoso" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">No. Telepon Orang Tua / WhatsApp <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="no_telp_ortu" value="{{ old('no_telp_ortu') }}" class="form-input" placeholder="Contoh: 081234567890" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Orang Tua <span style="color: var(--danger);">*</span></label>
                        <input type="email" name="email_ortu" value="{{ old('email_ortu') }}" class="form-input" placeholder="Contoh: ortu@email.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Lengkap <span style="color: var(--danger);">*</span></label>
                    <textarea name="alamat" class="form-input" rows="3" placeholder="Contoh: Jl. Hadimulyo Barat No. 15, Metro Pusat, Kota Metro" required>{{ old('alamat') }}</textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password <span style="color: var(--danger);">*</span></label>
                        <input type="password" name="password" class="form-input" placeholder="Minimal 6 karakter" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password <span style="color: var(--danger);">*</span></label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">📝 Daftar Sekarang</button>
                <a href="/" class="btn btn-secondary">Sudah punya akun? Masuk</a>

                <div class="back-link">
                    <a href="/">← Kembali ke Halaman Masuk</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>