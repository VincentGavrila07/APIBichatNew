<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Field yang bisa diisi massal (mass assignable)
    protected $fillable = [
        'name',
        'email',
        'birthdate',
        'status',
        'faculty_id',
        'major_id',
        'campus',
        'email_verified_at',
        'password',
        'photos',
        'remember_token',
        'gender',
        'description',
    ];

    // Field yang disembunyikan dari array/json (biasanya untuk keamanan)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Tipe data casting otomatis
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthdate' => 'date',
        'photos' => 'array', // anggap format JSON
    ];

    // Contoh relasi: User belongs to Faculty
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    // Contoh relasi: User belongs to Major
    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    // Optional helper: untuk deskripsi kampus
    public static function campusOptions(): array
    {
        return [
            'BINUS @Kemanggisan',
            'BINUS @Alam Sutera',
            'BINUS @Senayan',
            'BINUS @Bekasi',
            'BINUS @Bandung',
            'BINUS @Malang',
            'BINUS @Semarang',
        ];
    }

    // Optional helper: status relationship
    public static function statusOptions(): array
    {
        return ['single', 'taken', 'complicated'];
    }

    // Optional helper: gender
    public static function genderOptions(): array
    {
        return ['male', 'female'];
    }
}
