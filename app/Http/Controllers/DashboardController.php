<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Data untuk metrik (berlaku untuk admin/hrd/finance)
        $totalKaryawan = Employee::count();
        
        $currentMonth = date('n');
        $currentYear = date('Y');
        $payrollCurrentMonth = Payroll::where('month', $currentMonth)
                                      ->where('year', $currentYear)
                                      ->first();
        
        $totalPengeluaranBulanIni = $payrollCurrentMonth ? $payrollCurrentMonth->total_expenditure : 0;

        // Data untuk grafik tren pengeluaran gaji (6 bulan terakhir)
        $months = [];
        $expenditures = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $dt = now()->subMonthsNoOverflow($i);
            $m = $dt->month;
            $y = $dt->year;
            $monthName = $dt->translatedFormat('M Y');
            
            $p = Payroll::where('month', $m)->where('year', $y)->first();
            
            $months[] = $monthName;
            $expenditures[] = $p ? (float)$p->total_expenditure : 0;
        }

        return view('dashboard', compact(
            'totalKaryawan', 
            'totalPengeluaranBulanIni',
            'months',
            'expenditures'
        ));
    }
}
