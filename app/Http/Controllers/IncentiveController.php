<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Incentive;
use App\Services\IncentiveService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class IncentiveController extends Controller
{
    public function __construct(
        protected IncentiveService $incentiveService
    ) {}

    public function index()
    {
        $employees = Employee::whereNull('deleted_at')->get();
        return view('incentives.index', compact('employees'));
    }

    public function data(Request $request)
    {
        $query = Incentive::with('employee')->select('incentives.*');

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
            ->addColumn('action', function ($row) {
                return '
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-warning edit-btn" 
                            data-id="'.$row->id.'" 
                            data-employee_id="'.$row->employee_id.'"
                            data-date="'.$row->date.'"
                            data-amount="'.$row->amount.'"
                            data-description="'.$row->description.'"
                            title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger delete-btn" data-id="'.$row->id.'" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $this->incentiveService->createIncentive($request->all());
            return response()->json(['success' => true, 'message' => 'Insentif berhasil ditambahkan.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $this->incentiveService->updateIncentive($id, $request->all());
            return response()->json(['success' => true, 'message' => 'Insentif berhasil diperbarui.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        try {
            $this->incentiveService->deleteIncentive($id);
            return response()->json(['success' => true, 'message' => 'Insentif berhasil dihapus.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
