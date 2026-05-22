@extends('layouts.admin')

@section('title', 'Tipe Tunjangan')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Daftar Tipe Tunjangan</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                <i class="fas fa-plus"></i> Tambah Tipe Tunjangan
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="allowances-table" class="table table-bordered table-hover w-100">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Tunjangan</th>
                        <th>Deskripsi</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="allowanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="allowanceForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Tipe Tunjangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="allowance_id" name="id">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Tunjangan</label>
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
        var table = $('#allowances-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('allowance_types.data') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'description', name: 'description'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        $('#btn-add').click(function() {
            $('#allowanceForm')[0].reset();
            $('#allowance_id').val('');
            $('#modalTitle').text('Tambah Tipe Tunjangan');
            $('.is-invalid').removeClass('is-invalid');
            $('#allowanceModal').modal('show');
        });

        $('body').on('click', '.edit-btn', function() {
            $('#allowanceForm')[0].reset();
            $('.is-invalid').removeClass('is-invalid');
            
            $('#allowance_id').val($(this).data('id'));
            $('#name').val($(this).data('name'));
            $('#description').val($(this).data('description'));
            
            $('#modalTitle').text('Edit Tipe Tunjangan');
            $('#allowanceModal').modal('show');
        });

        $('#allowanceForm').submit(function(e) {
            e.preventDefault();
            $('#btn-save').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
            $('.is-invalid').removeClass('is-invalid');

            var id = $('#allowance_id').val();
            var url = id ? "{{ url('allowance_types') }}/" + id : "{{ route('allowance_types.store') }}";
            var method = id ? 'PUT' : 'POST';
            
            var data = {
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
                        $('#allowanceModal').modal('hide');
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
                text: "Tipe tunjangan yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('allowance_types') }}/" + id,
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
