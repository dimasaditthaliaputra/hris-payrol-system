<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nama role yang valid dalam sistem.
     */
    public const SUPER_ADMIN = 'super_admin';
    public const HRD         = 'hrd';
    public const FINANCE     = 'finance';
    public const KARYAWAN    = 'karyawan';

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    // =========================================================
    // Relationships
    // =========================================================

    /**
     * Satu role dimiliki oleh banyak user.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
