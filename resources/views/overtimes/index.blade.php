@extends('layouts.admin')

@section('title', 'Manajemen Lembur')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Daftar Lembur</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                    <i class="fas fa-plus"></i> Tambah Lembur
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="overtimes-table" class="table table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Tanggal</th>
                            <th>NIK</th>
                            <th>Nama Karyawan</th>
                            <th>Durasi (Jam)</th>
                            <th>Nominal Lembur</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="overtimeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="overtimeForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Lembur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="overtime_id" name="id">

                        <div class="mb-3">
                            <label for="employee_id" class="form-label">Karyawan</label>
                            <select class="form-select select2" id="employee_id" name="employee_id" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->nik }} - {{ $emp->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="error-employee_id"></div>
                        </div>

                        <div class="mb-3">
                            <label for="date" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                            <div class="invalid-feedback" id="error-date"></div>
                        </div>

                        <div class="mb-3">
                            <label for="duration_hours" class="form-label">Durasi (Jam)</label>
                            <input type="number" class="form-control" id="duration_hours" name="duration_hours" step="0.1" min="0.1" required>
                            <div class="invalid-feedback" id="error-duration_hours"></div>
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
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#overtimes-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('overtimes.data') }}",
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
                        data: 'duration_hours',
                        name: 'duration_hours'
                    },
                    {
                        data: 'amount_formatted',
                        name: 'amount'
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
                $('#overtimeForm')[0].reset();
                $('#overtime_id').val('');
                $('#employee_id').val('').trigger('change');
                $('#modalTitle').text('Tambah Lembur');
                $('.is-invalid').removeClass('is-invalid');
                $('#overtimeModal').modal('show');
            });

            $('body').on('click', '.edit-btn', function() {
                $('#overtimeForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid');

                $('#overtime_id').val($(this).data('id'));
                $('#employee_id').val($(this).data('employee_id')).trigger('change');
                $('#date').val($(this).data('date'));
                $('#duration_hours').val($(this).data('duration_hours'));

                $('#modalTitle').text('Edit Lembur');
                $('#overtimeModal').modal('show');
            });

            $('#overtimeForm').submit(function(e) {
                e.preventDefault();
                $('#btn-save').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                $('.is-invalid').removeClass('is-invalid');

                var id = $('#overtime_id').val();
                var url = id ? "{{ url('overtimes') }}/" + id : "{{ route('overtimes.store') }}";
                var method = id ? 'PUT' : 'POST';

                var data = {
                    employee_id: $('#employee_id').val(),
                    date: $('#date').val(),
                    duration_hours: $('#duration_hours').val(),
                    _method: method
                };

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        $('#btn-save').prop('disabled', false).html('Simpan');
                        if (response.success) {
                            $('#overtimeModal').modal('hide');
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
                        } else if(xhr.status === 403) {
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
                    text: "Data lembur akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('overtimes') }}/" + id,
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
        });
    </script>
@endpush
