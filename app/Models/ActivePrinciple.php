<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivePrinciple extends Model
{
    protected $table = 'active_principles';

    protected $fillable = ['name'];

    protected $guarded = ["id"];
}
