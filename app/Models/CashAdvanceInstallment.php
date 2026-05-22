<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAdvanceInstallment extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'cash_advance_id',
        'month',
        'year',
        'amount',
        'status',
    ];

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }
}
