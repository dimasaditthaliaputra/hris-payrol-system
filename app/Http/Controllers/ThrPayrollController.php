<?php

namespace App\Http\Controllers;

use App\Models\ThrPayroll;
use App\Services\ThrService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class ThrPayrollController extends Controller
{
    public function __construct(
        protected ThrService $thrService
    ) {}

    public function index()
    {
        return view('thr_payrolls.index');
    }

    public function data(Request $request)
    {
        $query = ThrPayroll::with('employee')->select('thr_payrolls.*');

        if ($request->has('year') && $request->year != '') {
            $query->where('period_year', $request->year);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_nik', function ($row) {
                return $row->employee ? $row->employee->nik : '-';
            })
            ->addColumn('employee_name', function ($row) {
                return $row->employee ? $row->employee->name : '-';
            })
            ->editColumn('basic_salary', function ($row) {
                return 'Rp ' . number_format($row->basic_salary, 0, ',', '.');
            })
            ->editColumn('amount', function ($row) {
                return 'Rp ' . number_format($row->amount, 0, ',', '.');
            })
            ->editColumn('length_of_service', function ($row) {
                return $row->length_of_service . ' Bulan';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->status == 'paid') return '<span class="badge bg-success">Paid</span>';
                if ($row->status == 'approved') return '<span class="badge bg-primary">Approved</span>';
                return '<span class="badge bg-warning">Pending</span>';
            })
            ->rawColumns(['status_badge'])
            ->make(true);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        try {
            $result = $this->thrService->generateThr($request->year);
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal generate THR: ' . $e->getMessage()], 500);
        }
    }
}
