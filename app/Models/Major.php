<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    use HasFactory;

    // Nama tabel (opsional, hanya jika tabel bukan "majors")
    protected $table = 'majors';

    // Kolom yang bisa diisi secara massal
    protected $fillable = [
        'faculty_id',
        'name',
    ];

    // Relasi: satu jurusan dimiliki oleh satu fakultas
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    // Relasi: satu jurusan bisa dimiliki oleh banyak user
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
