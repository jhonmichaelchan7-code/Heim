<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'type', 'title', 'message', 'data', 'target_role',
        'is_read', 'resolved_at', 'resolved_by',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeForRole($query, string $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->whereNull('target_role')->orWhere('target_role', $role);
            // Managers and owners see everything targeted at manager+
            if ($role === 'owner') {
                $q->orWhere('target_role', 'manager');
            }
        });
    }
}
