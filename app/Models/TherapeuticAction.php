<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TherapeuticAction extends Model
{
    protected $table = 'therapeutic_actions';

    protected $fillable = ['name'];

    protected $guarded = ["id"];
}
