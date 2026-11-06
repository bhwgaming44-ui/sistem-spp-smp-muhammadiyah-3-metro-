<!-- viewStaffEdit -->
<section id="viewStaffEdit" class="view-section" style="display:none;">
    <div class="page-header">
        <div class="header-left">
            <h2>Edit Data Petugas</h2>
            <p class="subtitle">Perbarui data petugas di database</p>
        </div>
        <div class="header-right">
            <button class="btn btn-secondary" onclick="hideEditStaff()">&larr; Kembali</button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Form Update Petugas</h3>
        </div>
        <div class="card-body">
            <form id="editStaffForm" onsubmit="event.preventDefault(); updateStaff();">
                <div class="form-grid">
                    <input type="hidden" id="editStaffId">
                    <div class="form-group">
                        <label class="form-label">Nama Petugas</label>
                        <input type="text" id="editStaffNama" class="form-input" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jabatan</label>
                        <select id="editStaffJabatan" class="form-input" required>
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
                        <input type="text" id="editStaffNoTelp" class="form-input" placeholder="Contoh: 081234567890">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" id="editStaffEmail" class="form-input" placeholder="nama@email.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status Aktif</label>
                        <select id="editStaffStatus" class="form-input">
                            <option value="aktif">Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        &#10003; Update Petugas
                    </button>
                    <button type="button" class="btn btn-danger" onclick="deleteStaff()">
                        &#10005; Hapus Petugas
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
<!-- End viewStaffEdit -->