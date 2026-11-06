<!-- viewStaffCreate -->
<section id="viewStaffCreate" class="view-section" style="display:none;">
    <div class="page-header">
        <div class="header-left">
            <h2>Tambah Petugas Baru</h2>
            <p class="subtitle">Isi form berikut untuk menambah data petugas ke database</p>
        </div>
        <div class="header-right">
            <button class="btn btn-secondary" onclick="hideCreateStaff()">&larr; Kembali</button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Form Data Petugas</h3>
        </div>
        <div class="card-body">
            <form id="createStaffForm" onsubmit="event.preventDefault(); storeStaff();">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Username Siswa (dari daftar user)</label>
                        <select id="staffUserId" class="form-input" required>
                            <option value="">-- Pilih Username --</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id_user }}">
                                {{ $user->username }} ({{ $user->nama_petugas ?? 'N/A' }}) - Role: {{ $user->role ?? 'N/A' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jabatan</label>
                        <select id="staffJabatan" class="form-input" required>
                            <option value="">-- Pilih Jabatan --</option>
                            <option value="petugas">Petugas</option>
                            <option value="admin">Admin</option>
                            <option value="bendahara">Bendahara</option>
                            <option value="kepala_sekolah">Kepala Sekolah</option>
                            <option value="guru">Guru Wali Kelas</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" id="staffNoTelp" class="form-input" placeholder="Contoh: 081234567890">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" id="staffEmail" class="form-input" placeholder="nama@email.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status Aktif</label>
                        <select id="staffStatus" class="form-input">
                            <option value="aktif">Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        &#10003; Simpan Petugas
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetStaffForm()">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
<!-- End viewStaffCreate -->