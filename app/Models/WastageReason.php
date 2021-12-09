<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WastageReason extends Model
{
    protected $table = 'wastage_reasons';

    protected $fillable = ['name'];

    protected $guarded = ["id"];

}
