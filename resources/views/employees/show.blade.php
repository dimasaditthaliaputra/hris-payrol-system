@extends('layouts.admin')

@section('title', 'Detail Profil Karyawan')

@section('content')
<div class="row">
    <!-- Kolom Kiri: Ringkasan & Foto Profil -->
    <div class="col-md-4">
        <div class="card card-primary card-outline card-profile shadow-sm">
            <div class="card-body box-profile text-center">
                <div class="position-relative d-inline-block mb-3">
                    @if($employee->profile_photo)
                        <img class="profile-user-img img-fluid img-circle img-thumbnail border-primary shadow-sm"
                             src="{{ asset('storage/' . $employee->profile_photo) }}"
                             alt="Foto Profil"
                             style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <img class="profile-user-img img-fluid img-circle img-thumbnail border-secondary shadow-sm"
                             src="https://ui-avatars.com/api/?name={{ urlencode($employee->name) }}&size=150&background=0D8ABC&color=fff"
                             alt="Avatar Default"
                             style="width: 150px; height: 150px; object-fit: cover;">
                    @endif
                </div>

                <h3 class="profile-username text-center fw-bold text-dark mb-1">{{ $employee->name }}</h3>
                <p class="text-muted text-center mb-2"><i class="fas fa-id-card me-1"></i> NIK: <strong class="text-primary">{{ $employee->nik }}</strong></p>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge bg-primary px-3 py-2"><i class="fas fa-building me-1"></i> {{ $employee->department->name ?? '-' }}</span>
                    <span class="badge bg-info px-3 py-2"><i class="fas fa-briefcase me-1"></i> {{ $employee->position->name ?? '-' }}</span>
                </div>

                <hr class="my-3">

                <!-- Informasi Kontak Cepat -->
                <ul class="list-group list-group-unbordered mb-3 text-start">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted"><i class="fas fa-phone me-1"></i> No. HP</span>
                        <span class="fw-bold">{{ $employee->phone ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted"><i class="fas fa-envelope me-1"></i> Email</span>
                        <a href="mailto:{{ $employee->email }}" class="fw-bold text-decoration-none text-truncate" style="max-width: 200px;">
                            {{ $employee->email ?? '-' }}
                        </a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-bottom-0">
                        <span class="text-muted"><i class="fas fa-calendar-alt me-1"></i> Bergabung Sejak</span>
                        <span class="fw-bold">{{ $employee->join_date ? \Carbon\Carbon::parse($employee->join_date)->translatedFormat('d F Y') : '-' }}</span>
                    </li>
                </ul>

                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning shadow-sm fw-bold">
                        <i class="fas fa-edit me-1"></i> Edit Data Karyawan
                    </a>
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary shadow-sm fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Rincian Lengkap Data Karyawan -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header p-2 bg-light">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active fw-bold" href="#personal-tab" data-bs-toggle="tab">
                            <i class="fas fa-user-circle me-1"></i> Data Pribadi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold" href="#payroll-tab" data-bs-toggle="tab">
                            <i class="fas fa-wallet me-1"></i> Keuangan & BPJS
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold" href="#allowances-tab" data-bs-toggle="tab">
                            <i class="fas fa-tags me-1"></i> Tunjangan & Potongan
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    
                    <!-- TAB 1: DATA PRIBADI -->
                    <div class="tab-pane active" id="personal-tab">
                        <h5 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="fas fa-user me-1"></i> Detail Identitas Personal</h5>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="text-muted mb-0">Tempat Lahir</label>
                                <p class="fw-bold text-dark mb-2">{{ $employee->place_of_birth ?? '-' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted mb-0">Tanggal Lahir</label>
                                <p class="fw-bold text-dark mb-2">
                                    {{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->translatedFormat('d F Y') : '-' }}
                                </p>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted mb-0">Jenis Kelamin</label>
                                <p class="fw-bold text-dark mb-2">
                                    @if($employee->gender == 'L')
                                        <i class="fas fa-mars text-primary me-1"></i> Laki-laki
                                    @elseif($employee->gender == 'P')
                                        <i class="fas fa-venus text-danger me-1"></i> Perempuan
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted mb-0">Alamat Lengkap</label>
                                <p class="fw-bold text-dark mb-2">{{ $employee->address ?? '-' }}</p>
                            </div>
                        </div>

                        <h5 class="fw-bold mt-4 mb-3 text-primary border-bottom pb-2"><i class="fas fa-briefcase me-1"></i> Informasi Pekerjaan</h5>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="text-muted mb-0">Status Kepegawaian</label>
                                <p class="mb-2">
                                    @if($employee->status_kerja == 'Tetap')
                                        <span class="badge bg-success px-2 py-1"><i class="fas fa-check me-1"></i> Karyawan Tetap</span>
                                    @elseif($employee->status_kerja == 'Kontrak')
                                        <span class="badge bg-warning px-2 py-1 text-dark"><i class="fas fa-clock me-1"></i> Kontrak</span>
                                    @elseif($employee->status_kerja == 'Freelance')
                                        <span class="badge bg-info px-2 py-1"><i class="fas fa-project-diagram me-1"></i> Freelance</span>
                                    @else
                                        <span class="badge bg-secondary px-2 py-1">{{ $employee->status_kerja }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted mb-0">Masa Kerja</label>
                                <p class="fw-bold text-dark mb-2">
                                    @if($employee->join_date)
                                        {{ \Carbon\Carbon::parse($employee->join_date)->diffForHumans(null, true) }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: KEUANGAN & BPJS -->
                    <div class="tab-pane" id="payroll-tab">
                        <h5 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="fas fa-money-bill-wave me-1"></i> Skema Remunerasi</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <label class="text-muted mb-0">Gaji Pokok bulanan</label>
                                <h4 class="fw-bold text-success mb-1">Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</h4>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted mb-0">Status Golongan Pajak (PTKP)</label>
                                <p class="fw-bold text-dark mb-1">
                                    <span class="badge bg-secondary px-2 py-1">{{ $employee->status_pajak }}</span>
                                </p>
                                <small class="text-muted">
                                    @if($employee->status_pajak == 'TK/0') Tidak Kawin (0 Tanggungan)
                                    @elseif($employee->status_pajak == 'K/0') Kawin (0 Tanggungan)
                                    @elseif($employee->status_pajak == 'K/1') Kawin (1 Tanggungan)
                                    @elseif($employee->status_pajak == 'K/2') Kawin (2 Tanggungan)
                                    @elseif($employee->status_pajak == 'K/3') Kawin (3 Tanggungan)
                                    @endif
                                </small>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="fas fa-university me-1"></i> Akun Perbankan</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <label class="text-muted mb-0">Nama Bank Penerima</label>
                                <p class="fw-bold text-dark mb-2">{{ $employee->nama_bank ?? '-' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted mb-0">Nomor Rekening Bank</label>
                                <p class="fw-bold text-dark mb-2">{{ $employee->rekening_bank ?? '-' }}</p>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="fas fa-shield-alt me-1"></i> Asuransi Sosial & Pajak</h5>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="text-muted mb-0">Nomor NPWP</label>
                                <p class="fw-bold text-dark mb-2">{{ $employee->npwp ?? '-' }}</p>
                            </div>
                            <div class="col-sm-4">
                                <label class="text-muted mb-0">Nomor BPJS Kesehatan</label>
                                <p class="fw-bold text-dark mb-2">{{ $employee->bpjs_kesehatan ?? '-' }}</p>
                            </div>
                            <div class="col-sm-4">
                                <label class="text-muted mb-0">Nomor BPJS Ketenagakerjaan</label>
                                <p class="fw-bold text-dark mb-2">{{ $employee->bpjs_ketenagakerjaan ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: TUNJANGAN & POTONGAN -->
                    <div class="tab-pane" id="allowances-tab">
                        <div class="row">
                            <!-- Kolom Tunjangan Kustom -->
                            <div class="col-md-6 mb-3">
                                <h5 class="fw-bold mb-3 text-success border-bottom pb-2">
                                    <i class="fas fa-plus-circle me-1"></i> Tunjangan Rutin
                                </h5>
                                @if($employee->allowances && $employee->allowances->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm table-hover">
                                            <thead class="table-success">
                                                <tr>
                                                    <th>Nama Tunjangan</th>
                                                    <th class="text-end">Nominal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($employee->allowances as $allowance)
                                                    <tr>
                                                        <td>{{ $allowance->allowanceType->name ?? '-' }}</td>
                                                        <td class="text-end fw-bold text-success">
                                                            Rp {{ number_format($allowance->amount, 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-light border text-center text-muted">
                                        <i class="fas fa-info-circle me-1"></i> Tidak memiliki tunjangan rutin khusus.
                                    </div>
                                @endif
                            </div>

                            <!-- Kolom Potongan Kustom -->
                            <div class="col-md-6 mb-3">
                                <h5 class="fw-bold mb-3 text-danger border-bottom pb-2">
                                    <i class="fas fa-minus-circle me-1"></i> Potongan Rutin
                                </h5>
                                @if($employee->deductions && $employee->deductions->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm table-hover">
                                            <thead class="table-danger">
                                                <tr>
                                                    <th>Nama Potongan</th>
                                                    <th class="text-end">Nominal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($employee->deductions as $deduction)
                                                    <tr>
                                                        <td>{{ $deduction->deductionType->name ?? '-' }}</td>
                                                        <td class="text-end fw-bold text-danger">
                                                            Rp {{ number_format($deduction->amount, 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-light border text-center text-muted">
                                        <i class="fas fa-info-circle me-1"></i> Tidak memiliki potongan rutin khusus.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
