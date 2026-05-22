@extends('layouts.admin')

@section('title', 'Riwayat Penggajian Saya')

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info shadow-sm">
                <i class="fas fa-info-circle me-2"></i>
                Halaman ini menampilkan riwayat penggajian Anda. Anda dapat mengunduh Slip Gaji (PDF) untuk setiap periode penggajian yang telah disahkan (locked).
            </div>
        </div>
    </div>

    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-money-check-alt me-2"></i>Daftar Gaji</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="my-payrolls-table" class="table table-bordered table-hover w-100">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th>Periode</th>
                            <th>Total Pendapatan</th>
                            <th>Total Potongan</th>
                            <th>Gaji Bersih</th>
                            <th width="15%">Aksi</th>
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
    $('#my-payrolls-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('karyawan.payrolls.data') }}",
        order: [[1, 'desc']],
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'period', name: 'period', orderable: false, searchable: false },
            { data: 'gross_salary', name: 'gross_salary' },
            { data: 'total_cuts', name: 'total_cuts' },
            { data: 'net_salary', name: 'net_salary' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });
});
</script>
@endpush
