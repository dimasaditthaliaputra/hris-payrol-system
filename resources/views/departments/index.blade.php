@extends('layouts.admin')

@section('title', 'Manajemen Departemen')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Daftar Departemen</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                    <i class="fas fa-plus"></i> Tambah Departemen
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="departments-table" class="table table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Kode</th>
                            <th>Nama Departemen</th>
                            <th>Deskripsi</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="departmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="departmentForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Tambah Departemen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="department_id" name="id">

                        <div class="mb-3">
                            <label for="code" class="form-label">Kode Departemen</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                            <div class="invalid-feedback" id="error-code"></div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Departemen</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="error-name"></div>
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
            var table = $('#departments-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('departments.data') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
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
                $('#departmentForm')[0].reset();
                $('#department_id').val('');
                $('#modalTitle').text('Tambah Departemen');
                $('.is-invalid').removeClass('is-invalid');
                $('#departmentModal').modal('show');
            });

            $('body').on('click', '.edit-btn', function() {
                $('#departmentForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid');

                $('#department_id').val($(this).data('id'));
                $('#code').val($(this).data('code'));
                $('#name').val($(this).data('name'));
                $('#description').val($(this).data('description'));

                $('#modalTitle').text('Edit Departemen');
                $('#departmentModal').modal('show');
            });

            $('#departmentForm').submit(function(e) {
                e.preventDefault();
                $('#btn-save').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                $('.is-invalid').removeClass('is-invalid');

                var id = $('#department_id').val();
                var url = id ? "{{ url('departments') }}/" + id : "{{ route('departments.store') }}";
                var method = id ? 'PUT' : 'POST';

                var data = {
                    code: $('#code').val(),
                    name: $('#name').val(),
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
                            $('#departmentModal').modal('hide');
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
                    text: "Departemen yang dihapus akan dinonaktifkan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('departments') }}/" + id,
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
