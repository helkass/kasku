<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FinanceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'finance_id',
        'amount',
        'note',
        'transaction_id'
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

    public function finance()
    {
        return $this->belongsTo(Finance::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
