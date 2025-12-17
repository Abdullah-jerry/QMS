<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',
        'login_attempts',
        'locked_until',
        'default_counter_id',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'user_departments');
    }

    public function issuedTokens()
    {
        return $this->hasMany(Token::class, 'issued_by');
    }

    public function tokenHistory()
    {
        return $this->hasMany(TokenHistory::class, 'changed_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Helper Methods


    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isReception()
    {
        return $this->hasRole('reception');
    }

    public function isCounterUser()
    {
        return $this->hasRole('counter_user');
    }



    public function canAccessDepartment($departmentId)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        return $this->departments()->where('department_id', $departmentId)->exists();
    }

    public function isLocked()
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function incrementLoginAttempts()
    {
        $this->increment('login_attempts');
        
        if ($this->login_attempts >= 5) {
            $this->locked_until = now()->addMinutes(30);
            $this->save();
        }
    }

    public function resetLoginAttempts()
    {
        $this->update([
            'login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);
    }

    /**
     * Assign a single role to the user, replacing any existing roles.
     *
     * @param string|int|\Spatie\Permission\Contracts\Role $role
     * @return $this
     */
    public function assignSingleRole($role)
    {
        return $this->syncRoles($role);
    }
}
