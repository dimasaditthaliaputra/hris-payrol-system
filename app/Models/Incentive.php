<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incentive extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'employee_id',
        'date',
        'amount',
        'description',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
