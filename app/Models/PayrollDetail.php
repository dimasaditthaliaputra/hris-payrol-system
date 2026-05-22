<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'basic_salary',
        'total_allowance',
        'overtime_pay',
        'incentive',
        'thr',
        'total_deduction',
        'bpjs_kesehatan',
        'bpjs_ketenagakerjaan',
        'pph21',
        'cash_advance_installment',
        'gross_salary',
        'total_cuts',
        'net_salary',
        'working_days',
        'overtime_hours',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'total_allowance' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'incentive' => 'decimal:2',
        'thr' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'bpjs_kesehatan' => 'decimal:2',
        'bpjs_ketenagakerjaan' => 'decimal:2',
        'pph21' => 'decimal:2',
        'cash_advance_installment' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'total_cuts' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
