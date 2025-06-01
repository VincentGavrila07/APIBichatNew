<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hobby extends Model
{
    protected $table = 'hobbies';

    protected $fillable = ['name'];

    public $timestamps = false;

    // Relasi many-to-many ke User lewat tabel pivot user_hobbies
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_hobbies', 'hobby_id', 'user_id');
    }
}
