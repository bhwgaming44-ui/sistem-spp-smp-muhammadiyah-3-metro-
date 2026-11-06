<!-- viewStaffShow -->
<section id="viewStaffShow" class="view-section" style="display:none;">
    <div class="page-header">
        <div class="header-left">
            <h2>Detail Petugas</h2>
            <p class="subtitle">Rincian data petugas</p>
        </div>
        <div class="header-right">
            <button class="btn btn-secondary" onclick="hideShowStaff()">&larr; Kembali</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <div class="staff-detail-card">
                <div class="staff-avatar" id="staffAvatar">
                    <span id="staffAvatarLetter">-</span>
                </div>
                <div class="staff-info">
                    <h2><span id="staffName">-</span></h2>
                    <p id="staffJabatan" class="text-muted">-</p>
                </div>
            </div>

            <div class="staff-detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Username</span>
                    <span class="detail-value" id="staffUsername">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Role</span>
                    <span class="detail-value" id="staffRole">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">No. Telepon</span>
                    <span class="detail-value" id="staffNoTelp">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email</span>
                    <span class="detail-value" id="staffEmail">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="detail-value status-badge" id="staffStatus">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Dibuat</span>
                    <span class="detail-value" id="staffCreated">-</span>
                </div>
            </div>

            <div class="form-actions mt-3">
                <button class="btn btn-primary" onclick="hideShowStaff(); showEditStaff();">
                    &#9998; Edit Data
                </button>
            </div>
        </div>
    </div>
</section>
<!-- End viewStaffShow -->