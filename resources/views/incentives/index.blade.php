@extends('layouts.admin')

@section('title', 'Manajemen Insentif')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title me-auto">Daftar Insentif Karyawan</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                    <i class="fas fa-plus"></i> Tambah Insentif
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="incentives-table" class="table table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Tanggal</th>
                            <th>NIK</th>
                            <th>Nama Karyawan</th>
                            <th>Nominal</th>
                            <th>Keterangan</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="incentiveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="incentiveForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Insentif</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="incentive_id" name="id">

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
                            <label for="amount" class="form-label">Nominal (Rp)</label>
                            <input type="number" class="form-control" id="amount" name="amount" min="0" required>
                            <div class="invalid-feedback" id="error-amount"></div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                            <div class="invalid-feedback" id="error-description"></div>
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
            var table = $('#incentives-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('incentives.data') }}",
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
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'description',
                        name: 'description'
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
                $('#incentiveForm')[0].reset();
                $('#incentive_id').val('');
                $('#employee_id').val('').trigger('change');
                $('#modalTitle').text('Tambah Insentif');
                $('.is-invalid').removeClass('is-invalid');
                $('#incentiveModal').modal('show');
            });

            $('body').on('click', '.edit-btn', function() {
                $('#incentiveForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid');

                $('#incentive_id').val($(this).data('id'));
                $('#employee_id').val($(this).data('employee_id')).trigger('change');
                $('#date').val($(this).data('date'));
                $('#amount').val($(this).data('amount'));
                $('#description').val($(this).data('description'));

                $('#modalTitle').text('Edit Insentif');
                $('#incentiveModal').modal('show');
            });

            $('#incentiveForm').submit(function(e) {
                e.preventDefault();
                $('#btn-save').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                $('.is-invalid').removeClass('is-invalid');

                var id = $('#incentive_id').val();
                var url = id ? "{{ url('incentives') }}/" + id : "{{ route('incentives.store') }}";
                var method = id ? 'PUT' : 'POST';

                var data = {
                    employee_id: $('#employee_id').val(),
                    date: $('#date').val(),
                    amount: $('#amount').val(),
                    description: $('#description').val(),
                    _method: method
                };

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        $('#btn-save').prop('disabled', false).html('Simpan');
                        if (response.success) {
                            $('#incentiveModal').modal('hide');
                            table.ajax.reload();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        $('#btn-save').prop('disabled', false).html('Simpan');
                        if (xhr.status === 422) {
                            if (xhr.responseJSON.errors) {
                                var errors = xhr.responseJSON.errors;
                                $.each(errors, function(key, val) {
                                    $('#' + key).addClass('is-invalid');
                                    $('#error-' + key).text(val[0]);
                                });
                            } else {
                                toastr.error(xhr.responseJSON.message);
                            }
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
                    text: "Data insentif akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('incentives') }}/" + id,
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
