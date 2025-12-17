<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenHistory extends Model
{
    use HasFactory;

    protected $table = 'token_history';

    protected $fillable = [
        'token_id',
        'from_status',
        'to_status',
        'changed_by',
        'counter_id',
        'changed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    // Relationships
    public function token()
    {
        return $this->belongsTo(Token::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }
}
