@extends('layouts.admin')

@section('title', 'Detail Payroll - ' . $payroll->period_label)

@section('content')
    @php
        $months = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    @endphp
    {{-- Header Info --}}
    <div class="card card-primary card-outline shadow-sm mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-receipt me-2"></i>
                Rincian Payroll — {{ $payroll->period_label }}
            </h3>
            <div class="card-tools">
                <a href="{{ route('payrolls.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="description-block border-right">
                        <span class="description-text">STATUS</span>
                        <h5 class="description-header">
                            @if($payroll->status === 'locked')
                                <span class="badge bg-success"><i class="fas fa-lock me-1"></i>Locked</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="fas fa-edit me-1"></i>Draft</span>
                            @endif
                        </h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="description-block border-right">
                        <span class="description-text">TOTAL KARYAWAN</span>
                        <h5 class="description-header">{{ $payroll->employee_count }} Orang</h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="description-block border-right">
                        <span class="description-text">TOTAL PENGELUARAN</span>
                        <h5 class="description-header text-success">Rp {{ number_format($payroll->total_expenditure, 0, ',', '.') }}</h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="description-block">
                        <span class="description-text">DI-GENERATE OLEH</span>
                        <h5 class="description-header">{{ $payroll->generatedBy->name ?? '-' }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Tabel --}}
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table me-2"></i>Rincian Gaji Per Karyawan</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="detail-table" class="table table-bordered table-hover table-sm w-100">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>NIK</th>
                            <th>Nama Karyawan</th>
                            <th>Gaji Pokok</th>
                            <th>Tunjangan</th>
                            <th>Lembur</th>
                            <th>Insentif</th>
                            <th>Total Potongan</th>
                            <th>Gaji Bersih</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Detail Komponen Gaji --}}
    <div class="modal fade" id="componentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-calculator me-2"></i>Rincian Komponen Gaji</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="component-body">
                    {{-- Injected via AJAX --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var payrollId = {{ $payroll->id }};

    var table = $('#detail-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('payrolls.detail.data', $payroll->id) }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nik', name: 'employee.nik' },
            { data: 'employee_name', name: 'employee.name' },
            { data: 'basic_salary', name: 'basic_salary' },
            { data: 'total_allowance', name: 'total_allowance' },
            { data: 'overtime_pay', name: 'overtime_pay' },
            { data: 'incentive', name: 'incentive' },
            { data: 'total_cuts', name: 'total_cuts' },
            { data: 'net_salary', name: 'net_salary' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    var btn = '<button type="button" class="btn btn-info btn-sm detail-comp-btn me-1" data-row=\'' + JSON.stringify(row) + '\' title="Detail"><i class="fas fa-list-alt"></i></button>';
                    if (row.payroll.status === 'locked') {
                        btn += '<a href="{{ url('payrolls/slip') }}/' + data + '" class="btn btn-danger btn-sm" title="Download Slip PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>';
                    }
                    return btn;
                }
            },
        ]
    });

    $('body').on('click', '.detail-comp-btn', function() {
        var row = $(this).data('row');
        var fmt = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(parseFloat(n));
        
        var html = '<div class="row">';
        html += '<div class="col-md-6">';
        html += '<h6 class="fw-bold text-success"><i class="fas fa-plus-circle me-1"></i>Komponen Pendapatan</h6>';
        html += '<table class="table table-sm table-borderless">';
        html += '<tr><td>Gaji Pokok</td><td class="text-end">' + fmt(row.basic_salary || 0) + '</td></tr>';
        html += '<tr><td>Total Tunjangan</td><td class="text-end">' + fmt(row.total_allowance || 0) + '</td></tr>';
        html += '<tr><td>Uang Lembur (' + (parseFloat(row.overtime_hours)||0).toFixed(1) + ' jam)</td><td class="text-end">' + fmt(row.overtime_pay || 0) + '</td></tr>';
        html += '<tr><td>Insentif Bulanan</td><td class="text-end">' + fmt(row.incentive || 0) + '</td></tr>';
        html += '<tr><td>THR</td><td class="text-end">' + fmt(row.thr || 0) + '</td></tr>';
        html += '<tr class="table-success fw-bold"><td>Total Pendapatan Kotor</td><td class="text-end">' + fmt(row.gross_salary || 0) + '</td></tr>';
        html += '</table></div>';
        
        html += '<div class="col-md-6">';
        html += '<h6 class="fw-bold text-danger"><i class="fas fa-minus-circle me-1"></i>Komponen Potongan</h6>';
        html += '<table class="table table-sm table-borderless">';
        html += '<tr><td>Potongan Manual</td><td class="text-end">' + fmt(row.total_deduction || 0) + '</td></tr>';
        html += '<tr><td>BPJS Kesehatan</td><td class="text-end">' + fmt(row.bpjs_kesehatan || 0) + '</td></tr>';
        html += '<tr><td>BPJS Ketenagakerjaan</td><td class="text-end">' + fmt(row.bpjs_ketenagakerjaan || 0) + '</td></tr>';
        html += '<tr><td>PPh 21</td><td class="text-end">' + fmt(row.pph21 || 0) + '</td></tr>';
        html += '<tr><td>Cicilan Kasbon</td><td class="text-end">' + fmt(row.cash_advance_installment || 0) + '</td></tr>';
        html += '<tr class="table-danger fw-bold"><td>Total Potongan</td><td class="text-end">' + fmt(row.total_cuts || 0) + '</td></tr>';
        html += '</table></div>';
        html += '</div>';
        
        html += '<div class="alert alert-success text-center fs-5 fw-bold mt-2">';
        html += '💰 Gaji Bersih: ' + fmt(row.net_salary || 0);
        html += '</div>';
        html += '<small class="text-muted">Hari Hadir: ' + (row.working_days || 0) + ' hari | Jam Lembur: ' + (parseFloat(row.overtime_hours)||0).toFixed(1) + ' jam</small>';
        
        $('#component-body').html(html);
        $('#componentModal').modal('show');
    });
});
</script>
@endpush
