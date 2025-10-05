<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'amount',
        'deadline',
        'total_users',
        'paid',
        'unpaid',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deadline' => 'date',
        'total_users' => 'integer',
        'paid' => 'integer',
        'unpaid' => 'integer',
    ];

    /**
     * Many-to-Many relationship with Users through payments
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'payments')
                    ->withPivot('paid_at', 'amount_paid', 'status')
                    ->withTimestamps();
    }

    /**
     * Get users who have paid this requirement
     */
    public function paidUsers()
    {
        return $this->belongsToMany(User::class, 'payments')
                    ->wherePivot('status', 'paid')
                    ->withPivot('paid_at', 'amount_paid')
                    ->withTimestamps();
    }

    /**
     * Get users who haven't paid this requirement
     */
    public function unpaidUsers()
    {
        return $this->belongsToMany(User::class, 'payments')
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

    // Calculate unpaid automatically
    public function getUnpaidAttribute()
    {
        return max(0, $this->total_users - $this->paid);
    }

    // Status accessor
    public function getStatusAttribute()
    {
        $now = now();
        $deadline = $this->deadline;

        if ($this->paid < $this->total_users) {
            return $deadline >= $now ? 'Pending' : 'Overdue';
        }
        return 'Done';
    }

    /**
     * Calculate paid count from actual payments
     */
    public function calculatePaidCount()
    {
        // Count distinct users who have paid
        $userPaidCount = $this->payments()
            ->where('status', 'paid')
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // Count manual payments (no user_id)
        $manualPaidCount = $this->payments()
            ->where('status', 'paid')
            ->whereNull('user_id')
            ->count();

        return $userPaidCount + $manualPaidCount;
    }

    /**
     * Recalculate and update paid/unpaid counts
     */
    public function recalculateCounts()
    {
        $paidCount = $this->calculatePaidCount();
        $unpaidCount = max(0, $this->total_users - $paidCount);

        $this->update([
            'paid' => $paidCount,
            'unpaid' => $unpaidCount,
        ]);
    }
}