@extends('layouts.admin')

@section('title', 'Manajemen User')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Daftar User</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal"
                    id="btn-add">
                    <i class="fas fa-plus"></i> Tambah User
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="users-table" class="table table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>No. HP</th>
                            <th>Status</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form User -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="userForm">
                    @csrf
                    <input type="hidden" id="user_id" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">Tambah User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="error-name"></div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback" id="error-email"></div>
                        </div>

                        <div class="mb-3">
                            <label for="role_id" class="form-label">Peran (Role)</label>
                            <select class="form-select select2" id="role_id" name="role_id" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="error-role_id"></div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">No. HP</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                            <div class="invalid-feedback" id="error-phone"></div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password <small class="text-muted"
                                    id="password-hint"></small></label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="invalid-feedback" id="error-password"></div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation">
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select select2" id="is_active" name="is_active" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                            <div class="invalid-feedback" id="error-is_active"></div>
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
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('users.data') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'role_name',
                        name: 'role_name'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // Reset Form
            $('#btn-add').click(function() {
                $('#userForm')[0].reset();
                $('#user_id').val('');
                $('#role_id').val('').trigger('change');
                $('#is_active').val('1').trigger('change');
                $('#userModalLabel').text('Tambah User');
                $('#password').prop('required', true);
                $('#password-hint').text('');
                $('.is-invalid').removeClass('is-invalid');
            });

            // Edit Modal
            $('body').on('click', '.edit-btn', function() {
                $('#userForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid');

                var id = $(this).data('id');
                var name = $(this).data('name');
                var email = $(this).data('email');
                var role_id = $(this).data('role_id');
                var phone = $(this).data('phone');
                var is_active = $(this).data('is_active');

                $('#user_id').val(id);
                $('#name').val(name);
                $('#email').val(email);
                $('#role_id').val(role_id).trigger('change');
                $('#phone').val(phone);
                $('#is_active').val(is_active).trigger('change');

                $('#password').prop('required', false);
                $('#password-hint').text('(Kosongkan jika tidak ingin mengubah password)');
                $('#userModalLabel').text('Edit User');
                $('#userModal').modal('show');
            });

            // Submit Form (Create / Update)
            $('#userForm').submit(function(e) {
                e.preventDefault();
                var id = $('#user_id').val();
                var url = id ? "{{ url('users') }}/" + id : "{{ route('users.store') }}";
                var type = id ? "PUT" : "POST";

                // Setup Button Loading State
                var btn = $('#btn-save');
                var originalText = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                $('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: url,
                    type: type,
                    data: $(this).serialize(),
                    success: function(response) {
                        btn.prop('disabled', false).html(originalText);
                        if (response.success) {
                            $('#userModal').modal('hide');
                            table.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(originalText);
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#' + key).addClass('is-invalid');
                                $('#error-' + key).text(value[0]);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Terjadi kesalahan pada server.'
                            });
                        }
                    }
                });
            });

            // Delete User
            $('body').on('click', '.delete-btn', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "User yang dihapus akan dinonaktifkan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('users') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
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
