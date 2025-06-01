<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserHobby extends Pivot
{
    protected $table = 'user_hobbies';

    public $timestamps = false;

    protected $fillable = ['user_id', 'hobby_id'];
}
