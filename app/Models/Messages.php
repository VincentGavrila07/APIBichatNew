<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
    ];

    // Jika kamu ingin otomatis mengatur created_at dan updated_at, Laravel sudah otomatis
    // tapi jika timestamp kolom berbeda, tambahkan property berikut:
    public $timestamps = true;
}
