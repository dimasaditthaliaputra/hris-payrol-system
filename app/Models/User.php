<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'deleted_at'        => 'datetime',
        ];
    }

    // =========================================================
    // Relationships
    // =========================================================

    /**
     * User dimiliki oleh satu role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // =========================================================
    // Helper Methods (Authorization Shortcuts)
    // =========================================================

    public function isSuperAdmin(): bool
    {
        return $this->role?->name === Role::SUPER_ADMIN;
    }

    public function isHrd(): bool
    {
        return $this->role?->name === Role::HRD;
    }

    public function isFinance(): bool
    {
        return $this->role?->name === Role::FINANCE;
    }

    public function isKaryawan(): bool
    {
        return $this->role?->name === Role::KARYAWAN;
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName;
    }
}
