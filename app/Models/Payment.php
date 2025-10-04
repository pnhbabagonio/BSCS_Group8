<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'requirement_id',
        'amount_paid',
        'paid_at',
        'status',
        'notes',
        'payment_method',
        'first_name',
        'middle_name',
        'last_name',
        'student_id'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    // Get display name - either from user or manual entry
    public function getDisplayNameAttribute()
    {
        if ($this->user_id) {
            return $this->user->first_name . ' ' . 
                   ($this->user->middle_name ? $this->user->middle_name . ' ' : '') . 
                   $this->user->last_name;
        }
        
        return $this->first_name . ' ' . 
               ($this->middle_name ? $this->middle_name . ' ' : '') . 
               $this->last_name;
    }

    // Check if payment is linked to a user
    public function getIsLinkedToUserAttribute()
    {
        return !is_null($this->user_id);
    }
}