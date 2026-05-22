<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class PayrollController extends Controller
{
    public function __construct(
        protected PayrollService $payrollService
    ) {}

    public function index()
    {
        return view('payrolls.index');
    }

    public function data()
    {
        $query = Payroll::with(['generatedBy', 'approvedBy'])->select('payrolls.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('period', function ($row) {
                $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ];
                return ($months[$row->month] ?? $row->month) . ' ' . $row->year;
            })
            ->editColumn('total_expenditure', function ($row) {
                return 'Rp ' . number_format($row->total_expenditure, 0, ',', '.');
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->status === 'locked') {
                    return '<span class="badge bg-success"><i class="fas fa-lock me-1"></i>Locked</span>';
                }
                return '<span class="badge bg-warning text-dark"><i class="fas fa-edit me-1"></i>Draft</span>';
            })
            ->addColumn('generated_by_name', function ($row) {
                return $row->generatedBy ? $row->generatedBy->name : '-';
            })
            ->addColumn('action', function ($row) {
                $buttons = '<div class="btn-group btn-group-sm">';
                $buttons .= '<a href="' . route('payrolls.show', $row->id) . '" class="btn btn-info" title="Detail Rincian"><i class="fas fa-eye"></i></a>';

                if ($row->status === 'draft') {
                    // Tombol Approve untuk Finance atau Super Admin
                    if ((auth()->user()->isFinance() || auth()->user()->isSuperAdmin()) && $row->approved_by === null) {
                        $buttons .= '<button type="button" class="btn btn-primary approve-btn" data-id="' . $row->id . '" title="Approve Payroll (Finance)"><i class="fas fa-check-circle"></i></button>';
                    }

                    // Tombol Lock (Super Admin) -> Sebaiknya hanya bisa dilock kalau sudah di-approve
                    if (auth()->user()->isSuperAdmin()) {
                        if ($row->approved_by !== null) {
                             $buttons .= '<button type="button" class="btn btn-success lock-btn" data-id="' . $row->id . '" title="Kunci Payroll"><i class="fas fa-lock"></i></button>';
                        }
                    }

                    if (auth()->user()->isSuperAdmin() || auth()->user()->isHrd()) {
                        $buttons .= '<button type="button" class="btn btn-danger delete-btn" data-id="' . $row->id . '" title="Hapus Draft"><i class="fas fa-trash"></i></button>';
                    }
                }

                if ($row->status === 'locked' && auth()->user()->isSuperAdmin()) {
                    $buttons .= '<button type="button" class="btn btn-warning unlock-btn" data-id="' . $row->id . '" title="Buka Kunci (Super Admin)"><i class="fas fa-unlock"></i></button>';
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
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020|max:2099',
        ]);

        try {
            $result = $this->payrollService->generateBatch(
                (int) $request->month,
                (int) $request->year
            );
            return response()->json([
                'success' => true,
                'message' => "Payroll berhasil digenerate! Total {$result['employee_count']} karyawan, total pengeluaran: Rp " . number_format($result['total_expenditure'], 0, ',', '.'),
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $payroll = Payroll::with(['generatedBy', 'approvedBy', 'details.employee'])->findOrFail($id);
        return view('payrolls.show', compact('payroll'));
    }

    public function destroy($id)
    {
        try {
            $this->payrollService->deletePayroll((int) $id);
            return response()->json(['success' => true, 'message' => 'Draft payroll berhasil dihapus.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function lock($id)
    {
        try {
            $payroll = Payroll::findOrFail($id);
            if ($payroll->approved_by === null) {
                 return response()->json(['success' => false, 'message' => 'Payroll belum di-approve oleh Finance.'], 422);
            }

            $this->payrollService->lockPayroll((int) $id);
            return response()->json(['success' => true, 'message' => 'Payroll berhasil dikunci. Cicilan kasbon terkait otomatis diproses.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function approve($id)
    {
        try {
            $this->payrollService->approvePayroll((int) $id);
            return response()->json(['success' => true, 'message' => 'Payroll berhasil disetujui. Sekarang Super Admin dapat mengunci data ini.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function unlock($id)
    {
        try {
            $this->payrollService->unlockPayroll((int) $id);
            return response()->json(['success' => true, 'message' => 'Payroll berhasil dibuka kuncinya.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function detailData($id)
    {
        // Anti N+1: eager load employee dan payroll sekaligus
        $query = PayrollDetail::with(['employee', 'payroll'])
            ->where('payroll_id', $id)
            ->select('payroll_details.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nik', fn($row) => $row->employee->nik ?? '-')
            ->addColumn('employee_name', fn($row) => $row->employee->name ?? '-')
            ->editColumn('basic_salary', fn($row) => 'Rp ' . number_format($row->basic_salary, 0, ',', '.'))
            ->editColumn('total_allowance', fn($row) => 'Rp ' . number_format($row->total_allowance, 0, ',', '.'))
            ->editColumn('overtime_pay', fn($row) => 'Rp ' . number_format($row->overtime_pay, 0, ',', '.'))
            ->editColumn('incentive', fn($row) => 'Rp ' . number_format($row->incentive, 0, ',', '.'))
            ->editColumn('total_cuts', fn($row) => 'Rp ' . number_format($row->total_cuts, 0, ',', '.'))
            ->editColumn('net_salary', fn($row) => '<strong class="text-success">Rp ' . number_format($row->net_salary, 0, ',', '.') . '</strong>')
            ->rawColumns(['net_salary'])
            ->make(true);
    }

    public function downloadSlip($detailId)
    {
        $detail = PayrollDetail::with([
            'payroll',
            'employee.department',
            'employee.position',
            'employee.user', // eager load untuk Policy check
        ])->findOrFail($detailId);

        // Gate Policy: mencegah IDOR — validasi kepemilikan berdasarkan user_id (bukan email)
        $this->authorize('download', $detail);

        if ($detail->payroll->status !== 'locked') {
            abort(403, 'Slip gaji hanya tersedia setelah payroll dikunci.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payrolls.slip-gaji', compact('detail'));

        $filename = 'Slip_Gaji_' . str_replace(' ', '_', $detail->employee->name)
                  . '_' . str_replace(' ', '_', $detail->payroll->period_label) . '.pdf';

        return $pdf->download($filename);
    }
}
