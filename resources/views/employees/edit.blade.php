@extends('layouts.admin')

@section('title', 'Edit Karyawan')

@section('content')
<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title">Form Edit Karyawan: {{ $employee->name }}</h3>
    </div>
    
    <form action="{{ route('employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h5 class="text-primary border-bottom pb-2 mb-3">Informasi Pribadi</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>NIK <span class="text-danger">*</span></label>
                    <input type="text" name="nik" class="form-control" value="{{ old('nik', $employee->nik) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $employee->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Tempat Lahir</label>
                    <input type="text" name="place_of_birth" class="form-control" value="{{ old('place_of_birth', $employee->place_of_birth) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $employee->date_of_birth) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Jenis Kelamin <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select select2" required>
                        <option value="L" {{ old('gender', $employee->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender', $employee->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>No. HP</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
                </div>
                <div class="col-md-12 mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}">
                </div>
                <div class="col-md-12 mb-3">
                    <label>Alamat</label>
                    <textarea name="address" class="form-control" rows="3">{{ old('address', $employee->address) }}</textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Foto Profil (Kosongkan jika tidak ingin mengubah. Maks 2MB)</label>
                    @if($employee->profile_photo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $employee->profile_photo) }}" alt="Profile" class="img-thumbnail" width="100">
                        </div>
                    @endif
                    <input type="file" name="profile_photo" class="form-control" accept="image/jpeg,image/png,image/jpg">
                </div>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Informasi Pekerjaan</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Departemen <span class="text-danger">*</span></label>
                    <select name="department_id" class="form-select select2" required>
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Posisi <span class="text-danger">*</span></label>
                    <select name="position_id" class="form-select select2" required>
                        <option value="">-- Pilih Posisi --</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" {{ old('position_id', $employee->position_id) == $pos->id ? 'selected' : '' }}>{{ $pos->name }} ({{ $pos->department->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Status Kerja <span class="text-danger">*</span></label>
                    <select name="status_kerja" class="form-select select2" required>
                        <option value="Tetap" {{ old('status_kerja', $employee->status_kerja) == 'Tetap' ? 'selected' : '' }}>Tetap</option>
                        <option value="Kontrak" {{ old('status_kerja', $employee->status_kerja) == 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                        <option value="Freelance" {{ old('status_kerja', $employee->status_kerja) == 'Freelance' ? 'selected' : '' }}>Freelance</option>
                        <option value="Nonaktif" {{ old('status_kerja', $employee->status_kerja) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Tanggal Bergabung <span class="text-danger">*</span></label>
                    <input type="date" name="join_date" class="form-control" value="{{ old('join_date', $employee->join_date) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Gaji Pokok (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="basic_salary" class="form-control" value="{{ old('basic_salary', $employee->basic_salary) }}" min="0" required>
                </div>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Informasi Pajak, BPJS, & Bank</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Status Pajak <span class="text-danger">*</span></label>
                    <select name="status_pajak" class="form-select select2" required>
                        <option value="TK/0" {{ old('status_pajak', $employee->status_pajak) == 'TK/0' ? 'selected' : '' }}>TK/0 (Tidak Kawin, 0 Tanggungan)</option>
                        <option value="K/0" {{ old('status_pajak', $employee->status_pajak) == 'K/0' ? 'selected' : '' }}>K/0 (Kawin, 0 Tanggungan)</option>
                        <option value="K/1" {{ old('status_pajak', $employee->status_pajak) == 'K/1' ? 'selected' : '' }}>K/1 (Kawin, 1 Tanggungan)</option>
                        <option value="K/2" {{ old('status_pajak', $employee->status_pajak) == 'K/2' ? 'selected' : '' }}>K/2 (Kawin, 2 Tanggungan)</option>
                        <option value="K/3" {{ old('status_pajak', $employee->status_pajak) == 'K/3' ? 'selected' : '' }}>K/3 (Kawin, 3 Tanggungan)</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Nomor NPWP</label>
                    <input type="text" name="npwp" class="form-control" placeholder="00.000.000.0-000.000" value="{{ old('npwp', $employee->npwp) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>BPJS Kesehatan</label>
                    <input type="text" name="bpjs_kesehatan" class="form-control" placeholder="Nomor BPJS Kesehatan" value="{{ old('bpjs_kesehatan', $employee->bpjs_kesehatan) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>BPJS Ketenagakerjaan</label>
                    <input type="text" name="bpjs_ketenagakerjaan" class="form-control" placeholder="Nomor BPJS Ketenagakerjaan" value="{{ old('bpjs_ketenagakerjaan', $employee->bpjs_ketenagakerjaan) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Nama Bank</label>
                    <input type="text" name="nama_bank" class="form-control" placeholder="BCA, Mandiri, BNI, dll" value="{{ old('nama_bank', $employee->nama_bank) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Nomor Rekening</label>
                    <input type="text" name="rekening_bank" class="form-control" placeholder="Nomor Rekening Bank" value="{{ old('rekening_bank', $employee->rekening_bank) }}">
                </div>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Tunjangan Rutin</h5>
            <div id="allowances-container">
                @php
                    $oldAllowances = old('allowances', $employee->allowances->toArray());
                @endphp
                @if(!empty($oldAllowances))
                    @foreach($oldAllowances as $index => $allowance)
                    <div class="row mb-2 allowance-item">
                        <div class="col-md-5">
                             <select name="allowances[{{ $index }}][allowance_type_id]" class="form-select select2">
                                <option value="">-- Pilih Tunjangan --</option>
                                @foreach($allowanceTypes as $type)
                                    <option value="{{ $type->id }}" {{ $allowance['allowance_type_id'] == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="number" name="allowances[{{ $index }}][amount]" class="form-control" placeholder="Nominal" value="{{ $allowance['amount'] }}" min="0">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-remove-allowance"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    @endforeach
                @else
                <div class="row mb-2 allowance-item">
                    <div class="col-md-5">
                        <select name="allowances[0][allowance_type_id]" class="form-select select2">
                            <option value="">-- Pilih Tunjangan --</option>
                            @foreach($allowanceTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="number" name="allowances[0][amount]" class="form-control" placeholder="Nominal" min="0">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-remove-allowance"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                @endif
            </div>
            <button type="button" class="btn btn-success btn-sm mt-2" id="btn-add-allowance"><i class="fas fa-plus"></i> Tambah Tunjangan</button>

            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">Potongan Rutin</h5>
            <div id="deductions-container">
                @php
                    $oldDeductions = old('deductions', $employee->deductions->toArray());
                @endphp
                @if(!empty($oldDeductions))
                    @foreach($oldDeductions as $index => $deduction)
                    <div class="row mb-2 deduction-item">
                        <div class="col-md-5">
                             <select name="deductions[{{ $index }}][deduction_type_id]" class="form-select select2">
                                <option value="">-- Pilih Potongan --</option>
                                @foreach($deductionTypes as $type)
                                    <option value="{{ $type->id }}" {{ $deduction['deduction_type_id'] == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="number" name="deductions[{{ $index }}][amount]" class="form-control" placeholder="Nominal" value="{{ $deduction['amount'] }}" min="0">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-remove-deduction"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    @endforeach
                @else
                <div class="row mb-2 deduction-item">
                    <div class="col-md-5">
                        <select name="deductions[0][deduction_type_id]" class="form-select select2">
                            <option value="">-- Pilih Potongan --</option>
                            @foreach($deductionTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="number" name="deductions[0][amount]" class="form-control" placeholder="Nominal" min="0">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-remove-deduction"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                @endif
            </div>
            <button type="button" class="btn btn-warning btn-sm mt-2" id="btn-add-deduction"><i class="fas fa-plus"></i> Tambah Potongan</button>

        </div>
        <div class="card-footer text-end">
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle loading state saat form disubmit
        $('form').submit(function() {
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        });

        let allowanceIndex = {{ count($oldAllowances) > 0 ? count($oldAllowances) : 1 }};
        $('#btn-add-allowance').click(function() {
            let html = `
                <div class="row mb-2 allowance-item">
                    <div class="col-md-5">
                        <select name="allowances[\${allowanceIndex}][allowance_type_id]" class="form-select select2">
                            <option value="">-- Pilih Tunjangan --</option>
                            @foreach($allowanceTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="number" name="allowances[\${allowanceIndex}][amount]" class="form-control" placeholder="Nominal" min="0">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-remove-allowance"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            `;
            $('#allowances-container').append(html);
            initSelect2();
            allowanceIndex++;
        });

        $(document).on('click', '.btn-remove-allowance', function() {
            $(this).closest('.allowance-item').remove();
        });

        let deductionIndex = {{ count($oldDeductions) > 0 ? count($oldDeductions) : 1 }};
        $('#btn-add-deduction').click(function() {
            let html = `
                <div class="row mb-2 deduction-item">
                    <div class="col-md-5">
                        <select name="deductions[\${deductionIndex}][deduction_type_id]" class="form-select select2">
                            <option value="">-- Pilih Potongan --</option>
                            @foreach($deductionTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="number" name="deductions[\${deductionIndex}][amount]" class="form-control" placeholder="Nominal" min="0">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-remove-deduction"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            `;
            $('#deductions-container').append(html);
            initSelect2();
            deductionIndex++;
        });

        $(document).on('click', '.btn-remove-deduction', function() {
            $(this).closest('.deduction-item').remove();
        });
    });
</script>
@endpush
