<!-- viewTagihan -->
<section id="viewTagihan" class="view-section">
    <div class="page-header">
        <div class="header-left">
            <h2>Tagihan SPP</h2>
            <p class="subtitle">Kelola dan lihat tagihan SPP yang telah dibuat</p>
        </div>
        <div class="header-right">
            <div class="badge badge-belum">
                <span class="badge-count" id="totalBelumLunas">0</span>
                <div class="badge-label">Belum Lunas</div>
            </div>
            <div class="badge badge-lunas">
                <span class="badge-count" id="totalLunas">0</span>
                <div class="badge-label">Lunas</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Daftar Tagihan</h3>
            <div class="card-actions">
                <button class="btn btn-primary" onclick="showCreateInvoice()">
                    <span class="btn-icon">&#9998;</span> Buat Tagihan Baru
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="tagihanTableContainer">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Bulan</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tagihanTableBody">
                        <tr>
                            <td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">
                                Memuat data tagihan...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Tagihan Yang Dibuat</h3>
        </div>
        <div class="card-body p-0">
            <div id="createdInvoicesInTagihan" style="margin-top:12px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Siswa</th>
                            <th>Bulan</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Tgl Buat</th>
                        </tr>
                    </thead>
                    <tbody id="createdInvoicesTableBody">
                        <tr>
                            <td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">
                                Belum ada tagihan yang dibuat.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<!-- End viewTagihan -->