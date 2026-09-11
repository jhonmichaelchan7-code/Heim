<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'actor_name', 'actor_role', 'action', 'module',
        'reference_type', 'reference_id', 'description', 'ip_address', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log(
        string $action,
        string $module,
        string $description,
        ?User $user = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?array $metadata = null
    ): static {
        return static::create([
            'user_id' => $user?->id ?? auth()->id(),
            'actor_name' => $user?->name ?? auth()->user()?->name ?? 'System',
            'actor_role' => $user?->role ?? auth()->user()?->role ?? 'system',
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'ip_address' => request()?->ip(),
            'metadata' => $metadata,
        ]);
    }
}
