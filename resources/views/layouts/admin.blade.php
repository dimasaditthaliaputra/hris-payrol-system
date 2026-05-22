<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HRIS Payroll | @yield('title', 'Dashboard')</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AdminLTE Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Select2 & Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    @stack('styles')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="button" class="btn btn-link text-danger text-decoration-none" id="btn-logout">
                        <i class="fas fa-sign-out-alt me-1"></i> Keluar
                    </button>
                </form>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="#" class="brand-link">
            <span class="brand-text font-weight-light ms-3"><b>HRIS</b> Payroll</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="info">
                    <a href="#" class="d-block text-decoration-none">{{ auth()->user()->name ?? 'User' }}</a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard', '*.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isHrd()))
                        @php
                            $isMasterDataActive = request()->routeIs('roles.*', 'users.*', 'departments.*', 'positions.*', 'employees.*', 'allowance_types.*', 'deduction_types.*');
                        @endphp
                        <li class="nav-header">ADMINISTRATION</li>
                        <li class="nav-item {{ $isMasterDataActive ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $isMasterDataActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-database"></i>
                                <p>
                                    Master Data
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @if(auth()->user()->isSuperAdmin())
                                <li class="nav-item">
                                    <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Manajemen Role</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Manajemen User</p>
                                    </a>
                                </li>
                                @endif
                                <li class="nav-item">
                                    <a href="{{ route('departments.index') }}" class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Departemen</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('positions.index') }}" class="nav-link {{ request()->routeIs('positions.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Posisi / Jabatan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Data Karyawan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('allowance_types.index') }}" class="nav-link {{ request()->routeIs('allowance_types.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Tipe Tunjangan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('deduction_types.index') }}" class="nav-link {{ request()->routeIs('deduction_types.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Tipe Potongan</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        @php
                            $isTransaksiActive = request()->routeIs('attendances.*', 'overtimes.*', 'incentives.*', 'thr_payrolls.*', 'cash_advances.*');
                            $isPayrollActive = request()->routeIs('payrolls.*');
                        @endphp
                        <li class="nav-header">TRANSAKSI</li>
                        <li class="nav-item {{ $isTransaksiActive ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $isTransaksiActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-exchange-alt"></i>
                                <p>
                                    Transaksi & Kehadiran
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('attendances.index') }}" class="nav-link {{ request()->routeIs('attendances.index', 'attendances.create', 'attendances.edit') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Absensi</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('overtimes.index') }}" class="nav-link {{ request()->routeIs('overtimes.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Lembur</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('attendances.report') }}" class="nav-link {{ request()->routeIs('attendances.report') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Rekap Bulanan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('incentives.index') }}" class="nav-link {{ request()->routeIs('incentives.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Insentif</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('thr_payrolls.index') }}" class="nav-link {{ request()->routeIs('thr_payrolls.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>THR</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('cash_advances.index') }}" class="nav-link {{ request()->routeIs('cash_advances.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Kasbon (Advance)</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- PAYROLL SECTION --}}
                        <li class="nav-header">PAYROLL</li>
                        <li class="nav-item {{ $isPayrollActive ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $isPayrollActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-money-check-alt"></i>
                                <p>
                                    Penggajian
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('payrolls.index') }}" class="nav-link {{ request()->routeIs('payrolls.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Manajemen Payroll</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('reports.payroll') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-success"></i>
                                        <p>Laporan / Excel</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if(auth()->user()->isKaryawan())
                        <li class="nav-header">SELF SERVICE</li>
                        <li class="nav-item">
                            <a href="{{ route('karyawan.payrolls.index') }}" class="nav-link {{ request()->routeIs('karyawan.payrolls.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                <p>Slip Gaji Saya</p>
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>@yield('title')</h1>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
        <strong>Copyright &copy; 2026 HRIS Payroll.</strong> All rights reserved.
    </footer>

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 5 Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Setup CSRF untuk semua AJAX Request
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Global Select2 Helper
    window.initSelect2 = function() {
        $('.select2').each(function() {
            var $this = $(this);
            var parent = $this.closest('.modal');
            $this.select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: parent.length ? parent : $(document.body)
            });
        });
    };

    // Auto-init Select2 on page load
    $(function() {
        initSelect2();
    });

    // SweetAlert2 Konfirmasi Logout
    $('#btn-logout').on('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Apakah Anda yakin ingin keluar?',
            text: "Sesi kerja aktif Anda akan diakhiri.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Keluar',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading spinner sebelum submit
                Swal.fire({
                    title: 'Mengakhiri Sesi...',
                    text: 'Mohon tunggu sebentar.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $('#logout-form').submit();
            }
        });
    });
</script>

@stack('scripts')
</body>
</html>
