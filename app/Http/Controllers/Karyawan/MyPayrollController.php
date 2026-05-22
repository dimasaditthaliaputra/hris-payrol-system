<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollDetail;
use Yajra\DataTables\Facades\DataTables;

class MyPayrollController extends Controller
{
    /**
     * Ambil employee yang terhubung dengan user yang sedang login.
     * Prioritas: via user_id (kolom resmi), fallback via email.
     */
    private function resolveEmployee(): ?Employee
    {
        $user = auth()->user();

        // Cara resmi: cari employee via relasi user_id
        $employee = $user->employee()->first();

        // Fallback untuk data lama yang belum punya user_id
        if (!$employee) {
            $employee = Employee::where('email', $user->email)->first();
        }

        return $employee;
    }

    public function index()
    {
        return view('karyawan.payrolls.index');
    }

    public function data()
    {
        $employee = $this->resolveEmployee();

        if (!$employee) {
            // Kembalikan DataTables kosong jika user tidak terhubung ke employee manapun
            return DataTables::of(collect([]))->make(true);
        }

        $employeeId = $employee->id;

        // Anti N+1: eager load 'payroll' sekaligus
        // Hanya tampilkan payroll yang sudah dilock (published)
        $query = PayrollDetail::with('payroll')
            ->where('employee_id', $employeeId)
            ->whereHas('payroll', fn($q) => $q->where('status', 'locked'))
            ->select('payroll_details.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('period', fn($row) => $row->payroll->period_label)
            ->editColumn('gross_salary', fn($row) => 'Rp ' . number_format($row->gross_salary, 0, ',', '.'))
            ->editColumn('total_cuts', fn($row) => 'Rp ' . number_format($row->total_cuts, 0, ',', '.'))
            ->editColumn('net_salary', fn($row) => '<strong class="text-success">Rp ' . number_format($row->net_salary, 0, ',', '.') . '</strong>')
            ->addColumn('action', fn($row) => '<a href="' . route('payrolls.slip', $row->id) . '" class="btn btn-danger btn-sm" title="Download Slip PDF" target="_blank"><i class="fas fa-file-pdf me-1"></i> Slip</a>')
            ->rawColumns(['net_salary', 'action'])
            ->make(true);
    }
}
