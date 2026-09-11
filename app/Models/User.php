<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ROLE_CASHIER = 'cashier';
    const ROLE_SUPERVISOR = 'supervisor';
    const ROLE_MANAGER = 'manager';
    const ROLE_OWNER = 'owner';

    const ROLE_HIERARCHY = [
        self::ROLE_CASHIER => 1,
        self::ROLE_SUPERVISOR => 2,
        self::ROLE_MANAGER => 3,
        self::ROLE_OWNER => 4,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isAtLeast(string $role): bool
    {
        return (self::ROLE_HIERARCHY[$this->role] ?? 0) >= (self::ROLE_HIERARCHY[$role] ?? 0);
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isManager(): bool
    {
        return $this->isAtLeast(self::ROLE_MANAGER);
    }

    public function isSupervisor(): bool
    {
        return $this->isAtLeast(self::ROLE_SUPERVISOR);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
