<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'month',
        'year',
        'status',
        'total_expenditure',
        'employee_count',
        'generated_by',
        'approved_by',
        'locked_at',
        'notes',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'total_expenditure' => 'decimal:2',
    ];

    public function details()
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    /**
     * Mendapatkan label bulan dalam Bahasa Indonesia
     */
    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return ($months[$this->month] ?? $this->month) . ' ' . $this->year;
    }
}
