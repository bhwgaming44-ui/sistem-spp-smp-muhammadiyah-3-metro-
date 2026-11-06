<!-- viewStaff -->
<section id="viewStaff" class="view-section">
    <div class="page-header">
        <div class="header-left">
            <h2>Database Petugas</h2>
            <p class="subtitle">Kelola data petugas, admin, bendahara, dan kepala sekolah</p>
        </div>
        <div class="header-right">
            <button class="btn btn-primary" onclick="showCreateStaff()">
                <span class="btn-icon">&#9998;</span> Tambah Petugas
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Daftar Petugas Sekolah</h3>
        </div>
        <div class="card-body p-0">
            <div id="petugasTableContainer">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Petugas</th>
                            <th>Jabatan</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="petugasTableBody">
                        <tr>
                            <td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">
                                Memuat data petugas...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<!-- End viewStaff -->