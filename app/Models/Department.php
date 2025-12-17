<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_departments');
    }

    public function counters()
    {
        return $this->hasMany(Counter::class);
    }

    public function tokens()
    {
        return $this->hasMany(Token::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper Methods
    public function getNextTokenNumber()
    {
        $today = now()->toDateString();
        $lastToken = $this->tokens()
            ->whereDate('issued_at', $today)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastToken) {
            $number = 1;
        } else {
            // Extract number from token (e.g., "A001" -> 1)
            $number = (int) substr($lastToken->token_number, strlen($this->code)) + 1;
        }

        return $this->code . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function getWaitingCount()
    {
        return $this->tokens()->where('status', 'waiting')->count();
    }

    public function getActiveCounters()
    {
        return $this->counters()->where('is_active', true)->get();
    }

    public function getNextWaitingCounter()
    {
        return $this->counters()
            ->where('is_active', true)
            ->whereNotNull('waiting_for_token_at')
            ->orderBy('waiting_for_token_at', 'asc')
            ->first();
    }
}
