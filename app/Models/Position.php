<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'department_id',
        'code',
        'name',
        'description',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
