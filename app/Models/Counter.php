<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'counter_number',
        'department_id',
        'is_active',
        'waiting_for_token_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'waiting_for_token_at' => 'datetime',
        ];
    }

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'counter_service')
                    ->withPivot('priority')
                    ->withTimestamps();
    }

    public function tokens()
    {
        return $this->hasMany(Token::class);
    }

    public function currentToken()
    {
        return $this->hasOne(Token::class)
            ->whereIn('status', ['called', 'serving'])
            ->latest('called_at');
    }

    public function tokenHistory()
    {
        return $this->hasMany(TokenHistory::class);
    }

    // Helper Methods
    public function callNextToken()
    {
        $nextToken = Token::where('department_id', $this->department_id)
            ->where('status', 'waiting')
            ->orderBy('issued_at', 'asc')
            ->first();

        if ($nextToken) {
            $nextToken->call($this->id);
            $this->update(['waiting_for_token_at' => null]);
            return $nextToken;
        }

        // No token found, mark counter as waiting
        if (!$this->waiting_for_token_at) {
            $this->update(['waiting_for_token_at' => now()]);
        }

        return null;
    }

    public function assignToken(Token $token)
    {
        $token->call($this->id);
        $this->update(['waiting_for_token_at' => null]);
        return $token;
    }

    public function getCurrentServingToken()
    {
        return Token::where('counter_id', $this->id)
            ->whereIn('status', ['called', 'serving'])
            ->latest('called_at')
            ->first();
    }

    public function isAvailable()
    {
        return $this->is_active && !$this->getCurrentServingToken();
    }

    public function getTokensServedToday()
    {
        return $this->tokens()
            ->whereDate('issued_at', now()->toDateString())
            ->where('status', 'completed')
            ->count();
    }
}
