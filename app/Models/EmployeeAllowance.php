<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAllowance extends Model
{
    protected $fillable = [
        'employee_id',
        'allowance_type_id',
        'amount',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function allowanceType()
    {
        return $this->belongsTo(AllowanceType::class);
    }
}
