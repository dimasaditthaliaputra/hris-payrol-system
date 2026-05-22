<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThrPayroll extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'employee_id',
        'period_year',
        'basic_salary',
        'length_of_service',
        'amount',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
