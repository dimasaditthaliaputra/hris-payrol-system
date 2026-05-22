@extends('layouts.admin')

@section('title', 'Dashboard Analytics')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <h4 class="m-0 text-dark">Selamat Datang, {{ auth()->user()->name }}!</h4>
        <p class="text-muted">Role Anda saat ini: <strong>{{ auth()->user()->role->display_name ?? '-' }}</strong></p>
    </div>
</div>

@if(!auth()->user()->isKaryawan())
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info shadow-sm">
            <div class="inner">
                <h3>{{ $totalKaryawan }}</h3>
                <p>Total Karyawan Aktif</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('employees.index') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-4 col-6">
        <div class="small-box bg-success shadow-sm">
            <div class="inner">
                <h3><sup style="font-size: 20px">Rp</sup>{{ number_format($totalPengeluaranBulanIni, 0, ',', '.') }}</h3>
                <p>Pengeluaran Gaji Bulan Ini</p>
            </div>
            <div class="icon">
                <i class="fas fa-wallet"></i>
            </div>
            <a href="{{ route('payrolls.index') }}" class="small-box-footer">Lihat Payroll <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                    <h3 class="card-title"><i class="fas fa-chart-line me-2"></i>Tren Pengeluaran Gaji</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="position-relative mb-4">
                    <canvas id="payrollChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="row">
    <div class="col-lg-4">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title">Akses Cepat</h3>
            </div>
            <div class="card-body text-center">
                <a href="{{ route('karyawan.payrolls.index') }}" class="btn btn-app bg-success">
                    <i class="fas fa-file-invoice-dollar"></i> Slip Gaji
                </a>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
@if(!auth()->user()->isKaryawan())
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById('payrollChart').getContext('2d');
        var months = {!! json_encode($months) !!};
        var expenditures = {!! json_encode($expenditures) !!};

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Total Pengeluaran (Rp)',
                    data: expenditures,
                    backgroundColor: 'rgba(60,141,188,0.2)',
                    borderColor: 'rgba(60,141,188,1)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b8bba',
                    pointBorderColor: 'rgba(60,141,188,1)',
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        ticks: {
                            callback: function(value, index, values) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000) + ' Jt';
                                }
                                return value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endif
@endpush
