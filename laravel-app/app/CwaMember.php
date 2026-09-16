<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CwaMember extends Model
{
    const STATUS_AWAITING = 'awaiting_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $table = 'cwa_members';

    protected $guarded = [];

    protected $dates = [
        'bylaws_agreed_at',
        'admitted_at',
        'reviewed_at',
    ];

    public function letter()
    {
        return $this->belongsTo(Letter::class, 'letter_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function isAwaiting()
    {
        return $this->status === self::STATUS_AWAITING;
    }

    public function publicUrl($relative)
    {
        if (! $relative) {
            return null;
        }

        return url('/'.ltrim($relative, '/'));
    }
}
