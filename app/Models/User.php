<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\QueuedResetPasswordNotification;
use Laravel\Sanctum\HasApiTokens; // Add this

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'name',       // still used by Laravel auth
        'email',
        'password',
        'student_id', 
        'program',
        'year',
        'role',
        'status',
        'last_login', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Send the password reset notification (queued).
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new QueuedResetPasswordNotification($token));
    }

    /**
     * Accessor for formatted last login date.
     */
    public function getFormattedLastLoginAttribute()
    {
        return $this->last_login ? $this->last_login->format('Y-m-d') : 'Never';
    }

    /**
     * Accessor for full name.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        return implode(' ', $parts) ?: $this->name; // fallback to old "name" field
    }

    /**
     * Scope for active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for specific role.
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }
    /**
     * Many-to-Many relationship with Requirements through payments
     */
    public function requirements()
    {
        return $this->belongsToMany(Requirement::class, 'payments')
                    ->withPivot('paid_at', 'amount_paid', 'status')
                    ->withTimestamps();
    }

    /**
     * Get paid requirements
     */
    public function paidRequirements()
    {
        return $this->belongsToMany(Requirement::class, 'payments')
                    ->wherePivot('status', 'paid')
                    ->withPivot('paid_at', 'amount_paid')
                    ->withTimestamps();
    }

    /**
     * Get unpaid requirements
     */
    public function unpaidRequirements()
    {
        return $this->belongsToMany(Requirement::class, 'payments')
                    ->wherePivot('status', 'pending')
                    ->withPivot('paid_at', 'amount_paid')
                    ->withTimestamps();
    }

    /**
     * Direct relationship with payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get manual payments (payments without user_id but with matching student_id)
     */
    public function manualPayments()
    {
        if (!$this->student_id) {
            return $this->hasMany(Payment::class)->whereNull('user_id')->whereNull('student_id');
        }
        
        return $this->hasMany(Payment::class, 'student_id', 'student_id')
                    ->whereNull('user_id');
    }


    // Relationship with attendees (event registrations)
    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }
}
