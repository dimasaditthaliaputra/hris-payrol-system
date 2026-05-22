@extends('layouts.admin')

@section('title', 'Manajemen Kasbon (Cash Advance)')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title me-auto">Daftar Pengajuan Kasbon</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                    <i class="fas fa-plus"></i> Ajukan Kasbon
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="cash-advances-table" class="table table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>NIK</th>
                            <th>Nama Karyawan</th>
                            <th>Nominal</th>
                            <th>Tenor</th>
                            <th>Status</th>
                            <th>Tgl Approval</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form (Create/Edit) -->
    <div class="modal fade" id="cashAdvanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="cashAdvanceForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Ajukan Kasbon</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="cash_advance_id" name="id">

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

                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label for="amount" class="form-label">Nominal Kasbon (Rp)</label>
                                <input type="number" class="form-control" id="amount" name="amount" min="1000" required>
                                <div class="invalid-feedback" id="error-amount"></div>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="tenor_months" class="form-label">Tenor (Bulan)</label>
                                <input type="number" class="form-control" id="tenor_months" name="tenor_months" min="1" max="60" required>
                                <div class="invalid-feedback" id="error-tenor_months"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="purpose" class="form-label">Keperluan</label>
                            <textarea class="form-control" id="purpose" name="purpose" rows="3" required></textarea>
                            <div class="invalid-feedback" id="error-purpose"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="btn-save">Simpan Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail & Approval -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Detail Kasbon & Jadwal Cicilan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><th width="40%">Nama Karyawan</th><td>: <span id="detail-name"></span></td></tr>
                                <tr><th>Nominal Pengajuan</th><td>: <span id="detail-amount" class="fw-bold"></span></td></tr>
                                <tr><th>Tenor</th><td>: <span id="detail-tenor"></span> Bulan</td></tr>
                                <tr><th>Status</th><td>: <span id="detail-status"></span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><th width="30%">Keperluan</th><td>: <span id="detail-purpose"></span></td></tr>
                                <tr><th>Tgl Approval</th><td>: <span id="detail-approval-date"></span></td></tr>
                            </table>
                        </div>
                    </div>

                    <h6 class="fw-bold"><i class="fas fa-list"></i> Simulasi / Jadwal Cicilan</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped" id="installments-table">
                            <thead class="table-dark">
                                <tr>
                                    <th width="10%">Bulan Ke</th>
                                    <th>Periode (Bulan - Tahun)</th>
                                    <th>Nominal Cicilan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="installments-body">
                                <!-- Dynamic content -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" id="approval-actions">
                    <!-- Actions will be injected here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#cash-advances-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('cash_advances.data') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
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
                        data: 'tenor_months',
                        name: 'tenor_months'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'approval_date',
                        name: 'approval_date',
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // CREATE / EDIT
            $('#btn-add').click(function() {
                $('#cashAdvanceForm')[0].reset();
                $('#cash_advance_id').val('');
                $('#employee_id').val('').trigger('change').prop('disabled', false);
                $('#modalTitle').text('Ajukan Kasbon Baru');
                $('.is-invalid').removeClass('is-invalid');
                $('#cashAdvanceModal').modal('show');
            });

            $('body').on('click', '.edit-btn', function() {
                $('#cashAdvanceForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid');

                $('#cash_advance_id').val($(this).data('id'));
                $('#employee_id').val($(this).data('employee_id')).trigger('change').prop('disabled', true);
                $('#amount').val($(this).data('amount'));
                $('#tenor_months').val($(this).data('tenor_months'));
                $('#purpose').val($(this).data('purpose'));

                $('#modalTitle').text('Edit Pengajuan Kasbon');
                $('#cashAdvanceModal').modal('show');
            });

            $('#cashAdvanceForm').submit(function(e) {
                e.preventDefault();
                $('#btn-save').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                $('.is-invalid').removeClass('is-invalid');

                var id = $('#cash_advance_id').val();
                var url = id ? "{{ url('cash-advances') }}/" + id : "{{ route('cash_advances.store') }}";
                var method = id ? 'PUT' : 'POST';

                var data = {
                    employee_id: $('#employee_id').val(),
                    amount: $('#amount').val(),
                    tenor_months: $('#tenor_months').val(),
                    purpose: $('#purpose').val(),
                    _method: method
                };

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        $('#btn-save').prop('disabled', false).html('Simpan Pengajuan');
                        if (response.success) {
                            $('#cashAdvanceModal').modal('hide');
                            table.ajax.reload();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        $('#btn-save').prop('disabled', false).html('Simpan Pengajuan');
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, val) {
                                $('#' + key).addClass('is-invalid');
                                $('#error-' + key).text(val[0]);
                            });
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Terjadi kesalahan pada server.');
                        }
                    }
                });
            });

            // DETAIL & APPROVAL
            $('body').on('click', '.detail-btn', function() {
                var id = $(this).data('id');
                
                $.get("{{ url('cash-advances') }}/" + id, function(data) {
                    $('#detail-name').text(data.employee.nik + ' - ' + data.employee.name);
                    $('#detail-amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.amount));
                    $('#detail-tenor').text(data.tenor_months);
                    $('#detail-purpose').text(data.purpose);
                    $('#detail-approval-date').text(data.approval_date ? data.approval_date : '-');
                    
                    var statusBadge = '';
                    if(data.status == 'pending') statusBadge = '<span class="badge bg-warning">Pending</span>';
                    else if(data.status == 'approved') statusBadge = '<span class="badge bg-primary">Approved</span>';
                    else if(data.status == 'paid_off') statusBadge = '<span class="badge bg-success">Lunas</span>';
                    else if(data.status == 'rejected') statusBadge = '<span class="badge bg-danger">Ditolak</span>';
                    
                    $('#detail-status').html(statusBadge);

                    var tbody = $('#installments-body');
                    tbody.empty();

                    if (data.status == 'pending') {
                        // PREVIEW Simulasi
                        var previewAmount = data.amount / data.tenor_months;
                        var startMonth = new Date().getMonth() + 2; // Next month (1-based index)
                        var startYear = new Date().getFullYear();
                        if (startMonth > 12) {
                            startMonth -= 12;
                            startYear++;
                        }
                        
                        for (var i = 0; i < data.tenor_months; i++) {
                            var monthDisplay = (startMonth < 10 ? '0' : '') + startMonth + ' - ' + startYear;
                            tbody.append(
                                '<tr>' +
                                '<td class="text-center">' + (i+1) + '</td>' +
                                '<td>' + monthDisplay + ' (Estimasi)</td>' +
                                '<td>Rp ' + new Intl.NumberFormat('id-ID').format(previewAmount) + '</td>' +
                                '<td><span class="badge bg-secondary">Simulasi</span></td>' +
                                '</tr>'
                            );
                            startMonth++;
                            if (startMonth > 12) {
                                startMonth = 1;
                                startYear++;
                            }
                        }

                        // Approval buttons
                        var approveBtn = '<button type="button" class="btn btn-success" onclick="approveKasbon('+data.id+')"><i class="fas fa-check"></i> Setujui Kasbon</button>';
                        var rejectBtn = '<button type="button" class="btn btn-danger" onclick="rejectKasbon('+data.id+')"><i class="fas fa-times"></i> Tolak</button>';
                        $('#approval-actions').html(rejectBtn + approveBtn + '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>');

                    } else if (data.installments && data.installments.length > 0) {
                        // REAL Installments
                        $.each(data.installments, function(index, inst) {
                            var monthDisplay = (inst.month < 10 ? '0' : '') + inst.month + ' - ' + inst.year;
                            var badge = inst.status == 'paid' ? '<span class="badge bg-success">Lunas (Dipotong Gaji)</span>' : '<span class="badge bg-warning text-dark">Belum Lunas</span>';
                            
                            tbody.append(
                                '<tr>' +
                                '<td class="text-center">' + (index+1) + '</td>' +
                                '<td>' + monthDisplay + '</td>' +
                                '<td>Rp ' + new Intl.NumberFormat('id-ID').format(inst.amount) + '</td>' +
                                '<td>' + badge + '</td>' +
                                '</tr>'
                            );
                        });
                        
                        $('#approval-actions').html('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>');
                    } else {
                        tbody.append('<tr><td colspan="4" class="text-center text-muted">Jadwal cicilan tidak tersedia.</td></tr>');
                        $('#approval-actions').html('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>');
                    }

                    $('#detailModal').modal('show');
                });
            });

            // DELETE
            $('body').on('click', '.delete-btn', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Kasbon?',
                    text: "Data pengajuan kasbon akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('cash-advances') }}/" + id,
                            type: 'DELETE',
                            success: function(response) {
                                if (response.success) {
                                    table.ajax.reload();
                                    Swal.fire('Terhapus!', response.message, 'success');
                                } else {
                                    Swal.fire('Gagal!', response.message, 'error');
                                }
                            }
                        });
                    }
                });
            });
            
            // Approval functions
            window.approveKasbon = function(id) {
                Swal.fire({
                    title: 'Setujui Kasbon?',
                    text: "Jadwal cicilan akan digenerate otomatis mulai bulan depan.",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Setujui',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post("{{ url('cash-advances') }}/" + id + "/approve", { _token: '{{ csrf_token() }}' }, function(res) {
                            if(res.success) {
                                $('#detailModal').modal('hide');
                                table.ajax.reload();
                                toastr.success(res.message);
                            } else {
                                toastr.error(res.message);
                            }
                        }).fail(function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Error server.');
                        });
                    }
                });
            };

            window.rejectKasbon = function(id) {
                Swal.fire({
                    title: 'Tolak Kasbon?',
                    text: "Pengajuan ini akan ditandai sebagai ditolak.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Tolak',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post("{{ url('cash-advances') }}/" + id + "/reject", { _token: '{{ csrf_token() }}' }, function(res) {
                            if(res.success) {
                                $('#detailModal').modal('hide');
                                table.ajax.reload();
                                toastr.success(res.message);
                            } else {
                                toastr.error(res.message);
                            }
                        }).fail(function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Error server.');
                        });
                    }
                });
            };

        });
    </script>
@endpush
