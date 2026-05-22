@extends('layouts.admin')

@section('title', 'Rekap Absensi Bulanan')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Rekap Absensi Bulanan</h3>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="month">Bulan</label>
                    <select id="month" class="form-select select2">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ sprintf('%02d', $i) }}" {{ date('m') == $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year">Tahun</label>
                    <select id="year" class="form-select select2">
                        @for ($i = date('Y') - 5; $i <= date('Y') + 1; $i++)
                            <option value="{{ $i }}" {{ date('Y') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary" id="btn-filter"><i class="fas fa-search"></i> Filter</button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="report-table" class="table table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>NIK</th>
                            <th>Nama Karyawan</th>
                            <th>Hadir</th>
                            <th>Terlambat</th>
                            <th>Izin</th>
                            <th>Sakit</th>
                            <th>Alpha</th>
                            <th>Cuti</th>
                            <th>Lembur (Jam)</th>
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
            var table = $('#report-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('attendances.report.data') }}",
                    data: function(d) {
                        d.month = $('#month').val();
                        d.year = $('#year').val();
                    }
                },
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'hadir',
                        name: 'hadir',
                        searchable: false
                    },
                    {
                        data: 'terlambat',
                        name: 'terlambat',
                        searchable: false
                    },
                    {
                        data: 'izin',
                        name: 'izin',
                        searchable: false
                    },
                    {
                        data: 'sakit',
                        name: 'sakit',
                        searchable: false
                    },
                    {
                        data: 'alpha',
                        name: 'alpha',
                        searchable: false
                    },
                    {
                        data: 'cuti',
                        name: 'cuti',
                        searchable: false
                    },
                    {
                        data: 'lembur',
                        name: 'lembur',
                        searchable: false
                    }
                ]
            });

            $('#btn-filter').click(function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush
