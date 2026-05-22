@extends('layouts.admin')

@section('title', 'Manajemen Karyawan')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title me-auto">Daftar Karyawan</h3>
            <div class="card-tools d-flex gap-2">
                {{-- Download Template --}}
                <a href="{{ route('employees.download-template') }}" class="btn btn-success btn-sm"
                    title="Download template Excel kosong">
                    <i class="fas fa-file-excel"></i> Download Template
                </a>
                {{-- Import Excel --}}
                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fas fa-file-upload"></i> Import Excel
                </button>
                {{-- Tambah Manual --}}
                <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Karyawan
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="employees-table" class="table table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Departemen</th>
                            <th>Posisi</th>
                            <th>Status</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL IMPORT EXCEL ===================== --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-upload me-2"></i> Import Data Karyawan dari Excel
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Petunjuk Penggunaan --}}
                    <div class="alert alert-warning mb-3">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-1"></i> Petunjuk Penggunaan</h6>
                        <ol class="mb-0 ps-3">
                            <li>Download <strong>Template Excel</strong> terlebih dahulu menggunakan tombol <em>"Download
                                    Template"</em> di atas.</li>
                            <li>Isi data karyawan di <strong>sheet pertama</strong>. Lihat sheet <em>"Referensi"</em> untuk
                                daftar kode departemen & posisi yang valid.</li>
                            <li>Kolom yang bertanda <strong class="text-danger">*</strong> wajib diisi.</li>
                            <li>Format tanggal: <strong>YYYY-MM-DD</strong> (contoh: <code>2024-01-15</code>).</li>
                            <li>Jenis Kelamin diisi dengan <strong>L</strong> (Laki-laki) atau <strong>P</strong>
                                (Perempuan).</li>
                            <li>Unggah file dan klik <strong>"Mulai Import"</strong>.</li>
                        </ol>
                    </div>

                    {{-- Form Upload --}}
                    <form id="importForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="import_file" class="form-label fw-bold">
                                Pilih File Excel <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control" id="import_file" name="file" accept=".xls,.xlsx"
                                required>
                            <div class="form-text text-muted">
                                <i class="fas fa-info-circle"></i>
                                Format yang diterima: <strong>.xls</strong> atau <strong>.xlsx</strong>. Maksimal ukuran
                                file: <strong>10MB</strong>.
                            </div>
                            <div class="invalid-feedback" id="error-import-file"></div>
                        </div>

                        {{-- Progress bar (hidden default) --}}
                        <div id="import-progress-wrap" class="d-none">
                            <label class="form-label fw-bold">Progres Upload</label>
                            <div class="progress mb-2" style="height: 22px;">
                                <div id="import-progress-bar"
                                    class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                                    role="progressbar" style="width: 0%">0%</div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Tutup
                    </button>
                    <button type="button" class="btn btn-info" id="btn-import">
                        <i class="fas fa-file-upload me-1"></i> Mulai Import
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL HASIL IMPORT ===================== --}}
    <div class="modal fade" id="importResultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" id="result-modal-header">
                    <h5 class="modal-title" id="result-modal-title">Hasil Import</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Statistik ringkasan --}}
                    <div class="row mb-3" id="import-stats">
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-table"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Data</span>
                                    <span class="info-box-number" id="stat-total">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success elevation-1"><i
                                        class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Berhasil Diimport</span>
                                    <span class="info-box-number text-success" id="stat-success">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-danger elevation-1"><i
                                        class="fas fa-times-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Gagal / Error</span>
                                    <span class="info-box-number text-danger" id="stat-failed">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel baris yang gagal --}}
                    <div id="error-table-wrap" class="d-none">
                        <h6 class="fw-bold text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Detail Baris yang
                            Gagal</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Baris Excel</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Penyebab Kegagalan</th>
                                    </tr>
                                </thead>
                                <tbody id="error-table-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btn-reload-after-import">
                        <i class="fas fa-sync me-1"></i> Refresh Tabel Karyawan
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            // ---- DataTable ----
            var table = $('#employees-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('employees.data') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'position',
                        name: 'position'
                    },
                    {
                        data: 'status_kerja',
                        name: 'status_kerja'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // ---- Hapus Karyawan ----
            $('body').on('click', '.delete-btn', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Karyawan yang dihapus akan dinonaktifkan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('employees') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    table.ajax.reload();
                                    Swal.fire('Terhapus!', response.message, 'success');
                                } else {
                                    Swal.fire('Gagal!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                var msg = (xhr.responseJSON && xhr.responseJSON
                                    .message) ? xhr.responseJSON.message :
                                    'Terjadi kesalahan pada server.';
                                Swal.fire('Error!', msg, 'error');
                            }
                        });
                    }
                });
            });

            // ---- Reset form saat modal Import ditutup ----
            $('#importModal').on('hidden.bs.modal', function() {
                $('#importForm')[0].reset();
                $('#import-progress-wrap').addClass('d-none');
                $('#import-progress-bar').css('width', '0%').text('0%');
                $('#import_file').removeClass('is-invalid');
                $('#btn-import').prop('disabled', false).html(
                    '<i class="fas fa-file-upload me-1"></i> Mulai Import');
            });

            // ---- Proses Import ----
            $('#btn-import').on('click', function() {
                var file = $('#import_file')[0].files[0];
                if (!file) {
                    $('#import_file').addClass('is-invalid');
                    $('#error-import-file').text('File Excel wajib diunggah.');
                    return;
                }
                $('#import_file').removeClass('is-invalid');

                var formData = new FormData();
                formData.append('file', file);
                formData.append('_token', "{{ csrf_token() }}");

                // Tampilkan progress bar
                $('#import-progress-wrap').removeClass('d-none');
                $('#btn-import').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Mengimport...');

                $.ajax({
                    url: "{{ route('employees.import') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                var pct = Math.round((e.loaded / e.total) * 100);
                                $('#import-progress-bar').css('width', pct + '%').text(
                                    pct + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        $('#importModal').modal('hide');
                        showImportResult(response);
                    },
                    error: function(xhr) {
                        $('#btn-import').prop('disabled', false).html(
                            '<i class="fas fa-file-upload me-1"></i> Mulai Import');
                        var msg = 'Terjadi kesalahan pada server.';
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal!', msg, 'error');
                    }
                });
            });

            // ---- Tampilkan Modal Hasil Import ----
            function showImportResult(response) {
                // Set header warna sesuai status
                var headerClass = 'bg-success text-white';
                var titleText = '<i class="fas fa-check-circle me-2"></i> Import Selesai — Semua Data Berhasil!';

                if (response.status === 'failed') {
                    headerClass = 'bg-danger text-white';
                    titleText =
                        '<i class="fas fa-times-circle me-2"></i> Import Gagal — Tidak Ada Data yang Berhasil';
                } else if (response.status === 'partial') {
                    headerClass = 'bg-warning text-dark';
                    titleText =
                        '<i class="fas fa-exclamation-triangle me-2"></i> Import Selesai — Sebagian Data Berhasil';
                }

                $('#result-modal-header').attr('class', 'modal-header ' + headerClass);
                $('#result-modal-title').html(titleText);

                // Statistik
                $('#stat-total').text(response.total_rows);
                $('#stat-success').text(response.success_rows);
                $('#stat-failed').text(response.failed_rows);

                // Tabel error
                var $tbody = $('#error-table-body').empty();
                if (response.errors && response.errors.length > 0) {
                    $('#error-table-wrap').removeClass('d-none');
                    $.each(response.errors, function(i, err) {
                        var errorList = '';
                        $.each(err.errors, function(j, errMsg) {
                            errorList += '<li>' + $('<span>').text(errMsg).html() + '</li>';
                        });
                        $tbody.append(
                            '<tr class="table-danger">' +
                            '<td class="text-center fw-bold">' + err.row + '</td>' +
                            '<td>' + $('<span>').text(err.nik).html() + '</td>' +
                            '<td>' + $('<span>').text(err.name).html() + '</td>' +
                            '<td><ul class="mb-0 ps-3">' + errorList + '</ul></td>' +
                            '</tr>'
                        );
                    });
                } else {
                    $('#error-table-wrap').addClass('d-none');
                }

                $('#importResultModal').modal('show');
            }

            // ---- Reload DataTable setelah melihat hasil ----
            $('#btn-reload-after-import').on('click', function() {
                $('#importResultModal').modal('hide');
                table.ajax.reload();
            });
            $('#importResultModal').on('hidden.bs.modal', function() {
                table.ajax.reload();
            });

        });
    </script>
@endpush
