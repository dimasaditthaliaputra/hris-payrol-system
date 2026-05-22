<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'user_id', 'nik', 'name', 'place_of_birth', 'date_of_birth', 'gender', 
        'address', 'phone', 'email', 'status_kerja', 'status_pajak', 
        'npwp', 'bpjs_kesehatan', 'bpjs_ketenagakerjaan', 'department_id', 
        'position_id', 'join_date', 'basic_salary', 'nama_bank', 'rekening_bank', 'profile_photo'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function allowances()
    {
        return $this->hasMany(EmployeeAllowance::class);
    }

    public function deductions()
    {
        return $this->hasMany(EmployeeDeduction::class);
    }
}
