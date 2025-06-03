<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Matches extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'user_id',
        'liked_user_id',
        'is_mutual',
        'notified',
    ];

    // Relasi ke user yang melakukan like
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke user yang di-like
    public function likedUser()
    {
        return $this->belongsTo(User::class, 'liked_user_id');
    }

    // Cast ke boolean
    protected $casts = [
        'is_mutual' => 'boolean',
        'notified' => 'boolean',
    ];
}
