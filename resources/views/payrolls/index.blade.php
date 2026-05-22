@extends('layouts.admin')

@section('title', 'Manajemen Payroll')

@section('content')
    {{-- Info Cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="info-box bg-gradient-primary shadow-sm">
                <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Payroll Dibuat</span>
                    <span class="info-box-number" id="stat-total">-</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-gradient-success shadow-sm">
                <span class="info-box-icon"><i class="fas fa-lock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Payroll Terkunci</span>
                    <span class="info-box-number" id="stat-locked">-</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-gradient-warning shadow-sm">
                <span class="info-box-icon"><i class="fas fa-edit"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Draft Belum Dikunci</span>
                    <span class="info-box-number" id="stat-draft">-</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title me-auto"><i class="fas fa-receipt me-2"></i>Riwayat Payroll</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="btn-generate">
                    <i class="fas fa-cogs"></i> Generate Payroll Baru
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="payrolls-table" class="table table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Periode</th>
                            <th>Jml Karyawan</th>
                            <th>Total Pengeluaran</th>
                            <th>Status</th>
                            <th>Di-generate Oleh</th>
                            <th>Tgl Generate</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Generate Payroll --}}
    <div class="modal fade" id="generateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-cogs me-2"></i>Generate Payroll Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="generateForm">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Sistem akan menghitung otomatis gaji seluruh karyawan aktif berdasarkan data absensi, lembur, insentif, dan kasbon yang tersedia.
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="gen_month" class="form-label fw-bold">Bulan <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="gen_month" name="month" required>
                                    <option value="">-- Pilih Bulan --</option>
                                    @php
                                        $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                    @endphp
                                    @foreach ($monthNames as $i => $name)
                                        <option value="{{ $i + 1 }}" {{ ($i + 1) == date('n') ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gen_year" class="form-label fw-bold">Tahun <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="gen_year" name="year" required>
                                    @for ($y = date('Y'); $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Perhatian!</strong> Pastikan data absensi, lembur, insentif, dan kasbon untuk periode ini sudah lengkap sebelum generate.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn-submit-generate">
                            <i class="fas fa-play-circle me-1"></i> Generate Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var table = $('#payrolls-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('payrolls.data') }}",
        order: [[6, 'desc']],
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'period', name: 'period', orderable: false },
            { data: 'employee_count', name: 'employee_count' },
            { data: 'total_expenditure', name: 'total_expenditure' },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'generated_by_name', name: 'generatedBy.name' },
            { data: 'created_at', name: 'created_at', render: function(data) { return data ? data.substring(0, 10) : '-'; } },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        drawCallback: function(settings) {
            // Update summary stats
            var json = this.api().ajax.json();
            if (json) {
                updateStats();
            }
        }
    });

    function updateStats() {
        $.get("{{ route('payrolls.data') }}", function(data) {
            var total = data.recordsTotal || 0;
            $('#stat-total').text(total);
        });
        // You can extend this with a dedicated stats endpoint if needed
    }

    // GENERATE Modal
    $('#btn-generate').click(function() {
        $('#generateModal').modal('show');
    });

    $('#generateForm').submit(function(e) {
        e.preventDefault();
        var btn = $('#btn-submit-generate');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Memproses...');

        $.ajax({
            url: "{{ route('payrolls.store') }}",
            type: 'POST',
            data: { month: $('#gen_month').val(), year: $('#gen_year').val() },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-play-circle me-1"></i> Generate Sekarang');
                if (res.success) {
                    $('#generateModal').modal('hide');
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, confirmButtonColor: '#3085d6' });
                } else {
                    toastr.error(res.message);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-play-circle me-1"></i> Generate Sekarang');
                toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan.');
            }
        });
    });

    // APPROVE (Finance)
    $('body').on('click', '.approve-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Setujui Payroll?',
            text: 'Setelah disetujui, Super Admin dapat mengunci data ini secara permanen.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                $.post("{{ url('payrolls') }}/" + id + "/approve", { _token: '{{ csrf_token() }}' }, function(res) {
                    if (res.success) { table.ajax.reload(); toastr.success(res.message); }
                    else toastr.error(res.message);
                }).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error.'));
            }
        });
    });

    // LOCK
    $('body').on('click', '.lock-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Kunci Payroll?',
            html: 'Setelah dikunci:<br>✅ Cicilan kasbon otomatis diproses<br>🔒 Data absensi/lembur/insentif periode ini tidak dapat diubah<br>⚠️ Hanya Super Admin yang bisa membuka kunci.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Kunci Sekarang',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                $.post("{{ url('payrolls') }}/" + id + "/lock", { _token: '{{ csrf_token() }}' }, function(res) {
                    if (res.success) { table.ajax.reload(); toastr.success(res.message); }
                    else toastr.error(res.message);
                }).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error.'));
            }
        });
    });

    // UNLOCK (Super Admin)
    $('body').on('click', '.unlock-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Buka Kunci Payroll?',
            text: 'Tindakan ini akan mengembalikan cicilan kasbon ke status unpaid dan mengizinkan perubahan data.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Buka Kunci',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                $.post("{{ url('payrolls') }}/" + id + "/unlock", { _token: '{{ csrf_token() }}' }, function(res) {
                    if (res.success) { table.ajax.reload(); toastr.success(res.message); }
                    else toastr.error(res.message);
                }).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Error.'));
            }
        });
    });

    // DELETE
    $('body').on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Draft Payroll?',
            text: 'Seluruh rincian gaji karyawan pada periode ini akan ikut terhapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('payrolls') }}/" + id,
                    type: 'DELETE',
                    success: function(res) {
                        if (res.success) { table.ajax.reload(); Swal.fire('Terhapus!', res.message, 'success'); }
                        else Swal.fire('Gagal!', res.message, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
