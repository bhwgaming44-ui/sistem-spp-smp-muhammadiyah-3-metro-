<!-- viewCreateInvoice -->
<section id="viewCreateInvoice" class="view-section">
    <div class="page-header">
        <div class="header-left">
            <h2>Buat Tagihan SPP</h2>
            <p class="subtitle">Isi form di bawah untuk membuat tagihan SPP</p>
        </div>
        <div class="header-right">
            <button class="btn btn-secondary" onclick="hideCreateInvoice()">&larr; Kembali</button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Form Pembuatan Tagihan</h3>
        </div>
        <div class="card-body">
            <form id="createInvoiceForm" onsubmit="event.preventDefault(); createInvoice();">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Siswa</label>
                        <select id="invoiceSiswa" class="form-input" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswaList as $siswa)
                            <option value="{{ $siswa->id_siswa }}">
                                {{ $siswa->nama_siswa }} (NISN: {{ $siswa->nisn }}, NIS: {{ $siswa->nis }}, Kelas: {{ $siswa->kelas->nama_kelas ?? 'N/A' }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kelas</label>
                        <select id="invoiceKelas" class="form-input" onchange="autoFillNominal()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id_kelas }}">{{ $kelas->nama_kelas }} (Tingkat {{ $kelas->tingkat }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tahun Ajaran</label>
                        <select id="invoiceTahun" class="form-input">
                            <option value="">-- Pilih Tahun --</option>
                            @foreach($tahunAjaranList as $tahun)
                            <option value="{{ $tahun->id_tahun_ajaran }}">{{ $tahun->tahun_ajaran }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Bulan SPP</label>
                        @php
                            $bulanNama = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                        @endphp
                        <select id="invoiceBulan" class="form-input">
                            <option value="">-- Pilih Bulan --</option>
                            @for($i = 1; $i <= 12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ $bulanNama[$i] }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nominal SPP</label>
                        <input type="number" id="invoiceNominal" class="form-input" readonly placeholder="Rp 0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status Awal</label>
                        <select id="invoiceStatus" class="form-input">
                            <option value="belum_lunas">Belum Bayar</option>
                            <option value="lunas">Lunas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Keterangan (Opsional)</label>
                        <textarea id="invoiceKeterangan" class="form-input" rows="3" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        &#10003; Simpan Tagihan
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetInvoiceForm()">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Daftar Tagihan yang Telah Dibuat</h3>
        </div>
        <div class="card-body p-0">
            <div id="createdInvoiceList">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Siswa</th>
                            <th>Bulan</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Tgl Buat</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<!-- End viewCreateInvoice -->