<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    // Nama tabel (jika tidak mengikuti konvensi jamak: "faculties")
    protected $table = 'faculties';

    // Kolom yang bisa diisi secara massal
    protected $fillable = [
        'name',
    ];

    // Relasi: satu fakultas memiliki banyak user
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
