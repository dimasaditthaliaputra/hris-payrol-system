@extends('layouts.admin')

@section('title', 'Laporan Payroll')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-excel me-2"></i>Export Laporan Payroll (Excel)</h3>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('reports.payroll.export') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="month" class="form-label fw-bold">Bulan <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="month" name="month" required>
                                <option value="">-- Pilih Bulan --</option>
                                @php
                                    $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                @endphp
                                @foreach ($monthNames as $i => $name)
                                    <option value="{{ $i + 1 }}" {{ ($i + 1) == date('n') ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="year" class="form-label fw-bold">Tahun <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="year" name="year" required>
                                @for ($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-download me-2"></i> Download Excel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
