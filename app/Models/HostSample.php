<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostSample extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'reported_at',
        'cpu_percent',
        'memory_percent',
        'root_disk_percent',
        'docker_running',
        'docker_total',
        'incus_running',
        'incus_total',
        'checks_total',
        'checks_clean',
        'checks_warning',
        'checks_critical',
        'updates_pending',
        'updates_security',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }
}
