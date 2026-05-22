<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\Employee;
use App\Services\CashAdvanceService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Carbon\Carbon;

class CashAdvanceController extends Controller
{
    public function __construct(
        protected CashAdvanceService $cashAdvanceService
    ) {}

    public function index()
    {
        $employees = Employee::whereNull('deleted_at')->get();
        return view('cash_advances.index', compact('employees'));
    }

    public function data(Request $request)
    {
        $query = CashAdvance::with('employee')->select('cash_advances.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_nik', function ($row) {
                return $row->employee ? $row->employee->nik : '-';
            })
            ->addColumn('employee_name', function ($row) {
                return $row->employee ? $row->employee->name : '-';
            })
            ->editColumn('amount', function ($row) {
                return 'Rp ' . number_format($row->amount, 0, ',', '.');
            })
            ->editColumn('tenor_months', function ($row) {
                return $row->tenor_months . ' Bulan';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->status == 'approved') return '<span class="badge bg-primary">Approved</span>';
                if ($row->status == 'paid_off') return '<span class="badge bg-success">Lunas</span>';
                if ($row->status == 'rejected') return '<span class="badge bg-danger">Ditolak</span>';
                return '<span class="badge bg-warning">Pending</span>';
            })
            ->addColumn('action', function ($row) {
                $buttons = '<div class="btn-group btn-group-sm">';
                
                $buttons .= '<button type="button" class="btn btn-info detail-btn" data-id="'.$row->id.'" title="Detail"><i class="fas fa-eye"></i></button>';

                if ($row->status == 'pending') {
                    $buttons .= '
                        <button type="button" class="btn btn-warning edit-btn" 
                            data-id="'.$row->id.'" 
                            data-employee_id="'.$row->employee_id.'"
                            data-amount="'.$row->amount.'"
                            data-tenor_months="'.$row->tenor_months.'"
                            data-purpose="'.$row->purpose.'"
                            title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger delete-btn" data-id="'.$row->id.'" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }
                
                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:1000',
            'tenor_months' => 'required|integer|min:1|max:60',
            'purpose' => 'required|string',
        ]);

        try {
            $data = $request->only(['employee_id', 'amount', 'tenor_months', 'purpose']);
            $this->cashAdvanceService->create($data);
            return response()->json(['success' => true, 'message' => 'Pengajuan kasbon berhasil disimpan.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $cashAdvance = CashAdvance::with(['employee', 'installments' => function($q) {
            $q->orderBy('year')->orderBy('month');
        }])->findOrFail($id);
        
        return response()->json($cashAdvance);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'tenor_months' => 'required|integer|min:1|max:60',
            'purpose' => 'required|string',
        ]);

        try {
            $data = $request->only(['amount', 'tenor_months', 'purpose']);
            $this->cashAdvanceService->update($id, $data);
            return response()->json(['success' => true, 'message' => 'Pengajuan kasbon berhasil diperbarui.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        try {
            $this->cashAdvanceService->delete($id);
            return response()->json(['success' => true, 'message' => 'Pengajuan kasbon berhasil dihapus.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function approve(Request $request, $id)
    {
        try {
            $date = $request->input('approval_date', now()->format('Y-m-d'));
            $this->cashAdvanceService->approve($id, $date);
            return response()->json(['success' => true, 'message' => 'Kasbon berhasil disetujui.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function reject($id)
    {
        try {
            $this->cashAdvanceService->reject($id);
            return response()->json(['success' => true, 'message' => 'Kasbon ditolak.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
