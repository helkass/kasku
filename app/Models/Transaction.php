<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'source_id',
        'title',
        'type',
        'amount',
        'category',
        'can_deleted'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Pastikan user login
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function finance()
    {
        return $this->belongsTo(Finance::class, 'source_id');
    }
}
