@extends('layouts.admin')

@section('title', 'Manajemen Posisi / Jabatan')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Daftar Posisi</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                    <i class="fas fa-plus"></i> Tambah Posisi
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="positions-table" class="table table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Departemen</th>
                            <th>Kode Posisi</th>
                            <th>Nama Posisi</th>
                            <th>Tarif Lembur</th>
                            <th>Deskripsi</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="positionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="positionForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Posisi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="position_id" name="id">

                        <div class="mb-3">
                            <label for="department_id" class="form-label">Departemen</label>
                            <select class="form-select select2" id="department_id" name="department_id" required>
                                <option value="">-- Pilih Departemen --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="error-department_id"></div>
                        </div>

                        <div class="mb-3">
                            <label for="code" class="form-label">Kode Posisi</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                            <div class="invalid-feedback" id="error-code"></div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Posisi</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="error-name"></div>
                        </div>

                        <div class="mb-3">
                            <label for="overtime_rate" class="form-label">Tarif Lembur per Jam (Rp)</label>
                            <input type="number" class="form-control" id="overtime_rate" name="overtime_rate" min="0" value="0" required>
                            <div class="invalid-feedback" id="error-overtime_rate"></div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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
            var table = $('#positions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('positions.data') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'department_name',
                        name: 'department_name'
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'overtime_rate',
                        name: 'overtime_rate',
                        render: $.fn.dataTable.render.number(',', '.', 0, 'Rp ')
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
                $('#positionForm')[0].reset();
                $('#position_id').val('');
                $('#department_id').val('').trigger('change');
                $('#modalTitle').text('Tambah Posisi');
                $('.is-invalid').removeClass('is-invalid');
                $('#positionModal').modal('show');
            });

            $('body').on('click', '.edit-btn', function() {
                $('#positionForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid');

                $('#position_id').val($(this).data('id'));
                $('#department_id').val($(this).data('department_id')).trigger('change');
                $('#code').val($(this).data('code'));
                $('#name').val($(this).data('name'));
                $('#overtime_rate').val($(this).data('overtime_rate'));
                $('#description').val($(this).data('description'));

                $('#modalTitle').text('Edit Posisi');
                $('#positionModal').modal('show');
            });

            $('#positionForm').submit(function(e) {
                e.preventDefault();
                $('#btn-save').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                $('.is-invalid').removeClass('is-invalid');

                var id = $('#position_id').val();
                var url = id ? "{{ url('positions') }}/" + id : "{{ route('positions.store') }}";
                var method = id ? 'PUT' : 'POST';

                var data = {
                    department_id: $('#department_id').val(),
                    code: $('#code').val(),
                    name: $('#name').val(),
                    overtime_rate: $('#overtime_rate').val(),
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
                            $('#positionModal').modal('hide');
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
                    text: "Posisi yang dihapus akan dinonaktifkan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('positions') }}/" + id,
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
