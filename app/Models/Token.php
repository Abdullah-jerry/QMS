<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TokenHistory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Token extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'token_number',
        'department_id',
        'service_id',
        'counter_id',
        'issued_by',
        'status',
        'is_vip',
        'issued_at',
        'called_at',
        'served_at',
        'completed_at',
        'priority',
        'notes',
    ];

    protected $casts = [
        'issued_at'    => 'datetime',
        'called_at'    => 'datetime',
        'served_at'    => 'datetime',
        'completed_at' => 'datetime',
    ];
    /** Relationships */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function history()
    {
        return $this->hasMany(TokenHistory::class);
    }

    /**
     * Get the wait time in minutes.
     *
     * @return int
     */
    public function getWaitTime()
    {
        if ($this->issued_at && $this->called_at) {
            return $this->issued_at->diffInMinutes($this->called_at);
        }
        return 0;
    }

    /** Log status changes */
    public function logHistory($fromStatus, $toStatus, $counterId = null, $notes = null)
    {
        TokenHistory::create([
            'token_id'    => $this->id,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'changed_by'  => auth()->user()->id,
            'counter_id'  => $counterId,
            'changed_at'  => now(),
            'notes'       => $notes,
        ]);
    }
}