@extends('layouts.admin')

@section('title', 'Manajemen THR')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title me-auto">Daftar THR Karyawan</h3>
            <div class="card-tools d-flex gap-2">
                <select id="filter_year" class="form-select form-select-sm" style="width: 120px;">
                    <option value="">Semua Tahun</option>
                    @for ($i = date('Y') - 5; $i <= date('Y') + 1; $i++)
                        <option value="{{ $i }}" {{ date('Y') == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
                <button type="button" class="btn btn-primary btn-sm" id="btn-generate">
                    <i class="fas fa-cogs"></i> Generate THR
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="thr-table" class="table table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Tahun</th>
                            <th>NIK</th>
                            <th>Nama Karyawan</th>
                            <th>Gaji Pokok</th>
                            <th>Masa Kerja</th>
                            <th>Nominal THR</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#thr-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('thr_payrolls.data') }}",
                    data: function(d) {
                        d.year = $('#filter_year').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'period_year',
                        name: 'period_year'
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
                        data: 'basic_salary',
                        name: 'basic_salary'
                    },
                    {
                        data: 'length_of_service',
                        name: 'length_of_service'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#filter_year').change(function() {
                table.ajax.reload();
            });

            $('#btn-generate').click(function() {
                var year = $('#filter_year').val() || new Date().getFullYear();

                Swal.fire({
                    title: 'Generate THR Tahun ' + year + '?',
                    text: "Sistem akan menghitung THR untuk semua karyawan secara otomatis.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Generate!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ route('thr_payrolls.generate') }}",
                            type: 'POST',
                            data: {
                                year: year,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    table.ajax.reload();
                                    Swal.fire(
                                        'Berhasil!',
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
                                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? 
                                    xhr.responseJSON.message : 'Terjadi kesalahan pada server.';
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
