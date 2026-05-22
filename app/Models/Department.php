<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function positions()
    {
        return $this->hasMany(Position::class);
    }
}
