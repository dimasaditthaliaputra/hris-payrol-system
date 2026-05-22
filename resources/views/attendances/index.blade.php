@extends('layouts.admin')

@section('title', 'Manajemen Absensi')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
                <h3 class="card-title me-auto">Daftar Absensi</h3>
                <div class="card-tools d-flex gap-2">
                    {{-- Download Template --}}
                    <a href="{{ route('attendances.download-template') }}" class="btn btn-success btn-sm"
                        title="Download template Excel kosong">
                        <i class="fas fa-file-excel"></i> Download Template
                    </a>
                    {{-- Import Excel --}}
                    <button type="button" class="btn btn-info btn-sm" id="btn-import-modal">
                        <i class="fas fa-file-upload"></i> Import Excel
                    </button>
                    {{-- Tambah Manual --}}
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                        <i class="fas fa-plus"></i> Tambah Manual
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="attendances-table" class="table table-bordered table-hover w-100">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Tanggal</th>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Status</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Form Manual -->
        <div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="attendanceForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">Tambah Absensi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="attendance_id" name="id">

                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Karyawan</label>
                                <select class="form-select select2" id="employee_id" name="employee_id" required>
                                    <option value="">-- Pilih Karyawan --</option>
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->nik }} - {{ $emp->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="error-employee_id"></div>
                            </div>

                            <div class="mb-3">
                                <label for="date" class="form-label">Tanggal</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                                <div class="invalid-feedback" id="error-date"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="time_in" class="form-label">Jam Masuk</label>
                                    <input type="time" class="form-control" id="time_in" name="time_in">
                                    <div class="invalid-feedback" id="error-time_in"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="time_out" class="form-label">Jam Keluar</label>
                                    <input type="time" class="form-control" id="time_out" name="time_out">
                                    <div class="invalid-feedback" id="error-time_out"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select select2" id="status" name="status" required>
                                    <option value="hadir">Hadir</option>
                                    <option value="terlambat">Terlambat</option>
                                    <option value="izin">Izin</option>
                                    <option value="sakit">Sakit</option>
                                    <option value="alpha">Alpha</option>
                                    <option value="cuti">Cuti</option>
                                </select>
                                <div class="invalid-feedback" id="error-status"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary" id="btn-save">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Import -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-file-upload me-2"></i> Import Data Absensi dari Excel
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Petunjuk Penggunaan --}}
                        <div class="alert alert-warning mb-3">
                            <h6 class="alert-heading"><i class="fas fa-info-circle me-1"></i> Petunjuk Penggunaan</h6>
                            <ol class="mb-0 ps-3" style="font-size: 14.5px; line-height: 1.6;">
                                <li>Download <strong>Template Excel</strong> terlebih dahulu menggunakan tombol
                                    <em>"Download
                                        Template"</em> di atas.</li>
                                <li>Isi data absensi di <strong>sheet pertama</strong>.</li>
                                <li>Kolom yang bertanda <strong class="text-danger">*</strong> wajib diisi.</li>
                                <li>Format tanggal: <strong>YYYY-MM-DD</strong> (contoh: <code>2024-05-22</code>).</li>
                                <li>Format Jam Masuk & Jam Keluar: <strong>HH:MM</strong> (contoh: <code>08:00</code> atau
                                    <code>17:00</code>).
                                </li>
                                <li>Status diisi dengan: <strong>hadir</strong>, <strong>terlambat</strong>,
                                    <strong>izin</strong>, <strong>sakit</strong>, <strong>alpha</strong>, atau
                                    <strong>cuti</strong> (gunakan huruf kecil semua).
                                </li>
                                <li>Unggah file dan klik <strong>"Mulai Import"</strong>.</li>
                            </ol>
                        </div>

                        <form id="importForm" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="import_file" class="form-label fw-bold">
                                    Pilih File Excel <span class="text-danger">*</span>
                                </label>
                                <input class="form-control" type="file" id="import_file" name="file"
                                    accept=".xls,.xlsx" required>
                                <div class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Format yang diterima: <strong>.xls</strong> atau <strong>.xlsx</strong>. Maksimal ukuran
                                    file: <strong>10MB</strong>.
                                </div>
                                <div class="invalid-feedback" id="error-import-file"></div>
                            </div>
                            <div class="progress d-none" id="import-progress-wrap" style="height: 25px;">
                                <div id="import-progress-bar"
                                    class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                    role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
                                    aria-valuemax="100">0%</div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-success" id="btn-import">
                            <i class="fas fa-file-upload me-1"></i> Mulai Import
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Hasil Import -->
        <div class="modal fade" id="importResultModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
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
                                    <span class="info-box-icon bg-secondary elevation-1"><i
                                            class="fas fa-table"></i></span>
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
                        <div id="error-table-wrap" class="d-none">
                            <h6 class="fw-bold text-danger mb-2">Detail Kegagalan:</h6>
                            <div class="table-responsive" style="max-height: 300px;">
                                <table class="table table-sm table-bordered table-striped" style="font-size: 14px;">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="5%">Baris</th>
                                            <th width="15%">NIK</th>
                                            <th width="25%">Nama</th>
                                            <th>Keterangan Error</th>
                                        </tr>
                                    </thead>
                                    <tbody id="error-table-body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="btn-reload-after-import">Tutup & Muat Ulang
                            Tabel</button>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('scripts')
        <script>
            $(document).ready(function() {
                var table = $('#attendances-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('attendances.data') }}",
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'date',
                            name: 'date'
                        },
                        {
                            data: 'employee_nik',
                            name: 'employee_nik'
                        },
                        {
                            data: 'employee_name',
                            name: 'employee_name'
                        },
                        {
                            data: 'time_in',
                            name: 'time_in'
                        },
                        {
                            data: 'time_out',
                            name: 'time_out'
                        },
                        {
                            data: 'status_badge',
                            name: 'status',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ]
                });

                $('#btn-add').click(function() {
                    $('#attendanceForm')[0].reset();
                    $('#attendance_id').val('');
                    $('#employee_id').val('').trigger('change');
                    $('#status').val('hadir').trigger('change');
                    $('#modalTitle').text('Tambah Absensi');
                    $('.is-invalid').removeClass('is-invalid');
                    $('#attendanceModal').modal('show');
                });

                $('body').on('click', '.edit-btn', function() {
                    $('#attendanceForm')[0].reset();
                    $('.is-invalid').removeClass('is-invalid');

                    $('#attendance_id').val($(this).data('id'));
                    $('#employee_id').val($(this).data('employee_id')).trigger('change');
                    $('#date').val($(this).data('date'));

                    // Format time: "17:00:00" -> "17:00"
                    var timeIn = $(this).data('time_in');
                    var timeOut = $(this).data('time_out');
                    if (timeIn && timeIn.length >= 5) timeIn = timeIn.substring(0, 5);
                    if (timeOut && timeOut.length >= 5) timeOut = timeOut.substring(0, 5);

                    $('#time_in').val(timeIn);
                    $('#time_out').val(timeOut);
                    $('#status').val($(this).data('status')).trigger('change');

                    $('#modalTitle').text('Edit Absensi');
                    $('#attendanceModal').modal('show');
                });

                $('#attendanceForm').submit(function(e) {
                    e.preventDefault();
                    $('#btn-save').prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                    $('.is-invalid').removeClass('is-invalid');

                    var id = $('#attendance_id').val();
                    var url = id ? "{{ url('attendances') }}/" + id : "{{ route('attendances.store') }}";
                    var method = id ? 'PUT' : 'POST';

                    var data = {
                        employee_id: $('#employee_id').val(),
                        date: $('#date').val(),
                        time_in: $('#time_in').val(),
                        time_out: $('#time_out').val(),
                        status: $('#status').val(),
                        _method: method
                    };

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: data,
                        success: function(response) {
                            $('#btn-save').prop('disabled', false).html('Simpan');
                            if (response.success) {
                                $('#attendanceModal').modal('hide');
                                table.ajax.reload();
                                toastr.success(response.message);
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(xhr) {
                            $('#btn-save').prop('disabled', false).html('Simpan');
                            if (xhr.status === 422) {
                                var errors = xhr.responseJSON.errors;
                                $.each(errors, function(key, val) {
                                    $('#' + key).addClass('is-invalid');
                                    $('#error-' + key).text(val[0]);
                                });
                            } else if (xhr.status === 403) {
                                Swal.fire('Terlarang!', xhr.responseJSON.message, 'error');
                            } else {
                                toastr.error('Terjadi kesalahan pada server.');
                            }
                        }
                    });
                });

                $('body').on('click', '.delete-btn', function() {
                    var id = $(this).data('id');
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data absensi akan dihapus!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('attendances') }}/" + id,
                                type: 'DELETE',
                                success: function(response) {
                                    if (response.success) {
                                        table.ajax.reload();
                                        Swal.fire(
                                            'Terhapus!',
                                            response.message,
                                            'success'
                                        );
                                    } else {
                                        Swal.fire(
                                            'Gagal!',
                                            response.message,
                                            'error'
                                        );
                                    }
                                },
                                error: function(xhr) {
                                    var msg = (xhr.responseJSON && xhr.responseJSON
                                            .message) ? xhr.responseJSON.message :
                                        'Terjadi kesalahan pada server.';
                                    Swal.fire(
                                        'Error!',
                                        msg,
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                });

                // Import
                $('#btn-import-modal').click(function() {
                    $('#importForm')[0].reset();
                    $('#import-progress-wrap').addClass('d-none');
                    $('#import-progress-bar').css('width', '0%').text('0%');
                    $('#import_file').removeClass('is-invalid');
                    $('#btn-import').prop('disabled', false).html(
                        '<i class="fas fa-file-upload me-1"></i> Mulai Import');
                    $('#importModal').modal('show');
                });

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

                    $('#import-progress-wrap').removeClass('d-none');
                    $('#btn-import').prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin me-1"></i> Mengimport...');

                    $.ajax({
                        url: "{{ route('attendances.import') }}",
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

                    $('#stat-total').text(response.total_rows);
                    $('#stat-success').text(response.success_rows);
                    $('#stat-failed').text(response.failed_rows);

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
