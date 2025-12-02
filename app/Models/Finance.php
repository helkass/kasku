<?php

namespace App\Models;

use App\Helpers\FinanceHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Finance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'amount',
        'daily_limit',
        'note',
        'finance_number',
        'finance_unique'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Pastikan user login
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->finance_number = FinanceHelper::generateAccountNumber();
                $model->finance_unique = FinanceHelper::generateFinanceUnique($model->user_id);
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
    public function finance_history()
    {
        return $this->hasMany(FinanceHistory::class, 'finance_id');
    }
}
