<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Overtime;
use Yajra\DataTables\Facades\DataTables;

class AttendanceReportController extends Controller
{
    public function index()
    {
        return view('attendances.report');
    }

    public function data(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $employees = Employee::select('id', 'nik', 'name')
            ->whereNull('deleted_at')
            ->get();

        // Get attendances for this month/year
        $attendances = Attendance::select('employee_id', 'status')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy('employee_id');

        // Get overtimes for this month/year
        $overtimes = Overtime::select('employee_id', 'duration_hours')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy('employee_id');

        $data = $employees->map(function ($employee) use ($attendances, $overtimes) {
            $empAttendances = $attendances->get($employee->id, collect());
            $empOvertimes = $overtimes->get($employee->id, collect());

            return [
                'nik' => $employee->nik,
                'name' => $employee->name,
                'hadir' => $empAttendances->where('status', 'hadir')->count(),
                'terlambat' => $empAttendances->where('status', 'terlambat')->count(),
                'izin' => $empAttendances->where('status', 'izin')->count(),
                'sakit' => $empAttendances->where('status', 'sakit')->count(),
                'alpha' => $empAttendances->where('status', 'alpha')->count(),
                'cuti' => $empAttendances->where('status', 'cuti')->count(),
                'lembur' => $empOvertimes->sum('duration_hours'),
            ];
        });

        return DataTables::of($data)->make(true);
    }
}
