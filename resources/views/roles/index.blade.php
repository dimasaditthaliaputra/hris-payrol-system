@extends('layouts.admin')

@section('title', 'Manajemen Role')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Daftar Role</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                <i class="fas fa-plus"></i> Tambah Role
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="roles-table" class="table table-bordered table-hover w-100">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Kode Role (Slug)</th>
                        <th>Nama Tampilan</th>
                        <th>Deskripsi</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="roleForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="role_id" name="id">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Kode Role (Huruf kecil & underscore)</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback" id="error-name"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="display_name" class="form-label">Nama Tampilan</label>
                        <input type="text" class="form-control" id="display_name" name="display_name" required>
                        <div class="invalid-feedback" id="error-display_name"></div>
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
        // Initialize DataTable
        var table = $('#roles-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('roles.data') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'display_name', name: 'display_name'},
                {data: 'description', name: 'description'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        // Open Modal for Add
        $('#btn-add').click(function() {
            $('#roleForm')[0].reset();
            $('#role_id').val('');
            $('#modalTitle').text('Tambah Role');
            $('.is-invalid').removeClass('is-invalid');
            $('#roleModal').modal('show');
        });

        // Open Modal for Edit
        $('body').on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var display_name = $(this).data('display_name');
            var description = $(this).data('description');

            $('#roleForm')[0].reset();
            $('.is-invalid').removeClass('is-invalid');
            
            $('#role_id').val(id);
            $('#name').val(name);
            $('#display_name').val(display_name);
            $('#description').val(description);
            
            $('#modalTitle').text('Edit Role');
            $('#roleModal').modal('show');
        });

        // Handle Form Submit
        $('#roleForm').submit(function(e) {
            e.preventDefault();
            $('#btn-save').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
            $('.is-invalid').removeClass('is-invalid');

            var id = $('#role_id').val();
            var url = id ? "{{ url('roles') }}/" + id : "{{ route('roles.store') }}";
            var method = id ? 'PUT' : 'POST';
            
            var data = {
                name: $('#name').val(),
                display_name: $('#display_name').val(),
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
                        $('#roleModal').modal('hide');
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

        // Handle Delete
        $('body').on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Role yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('roles') }}/" + id,
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
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan pada server.';
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
