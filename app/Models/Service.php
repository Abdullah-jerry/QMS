<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'department_id',
        'prefix',
        'token_start',
        'token_end',
        'vip_token_start',
        'vip_token_end',
        'status',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function counters()
    {
        return $this->belongsToMany(Counter::class, 'counter_service')
                    ->withPivot('priority')
                    ->withTimestamps();
    }
}
