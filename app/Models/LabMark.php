<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabMark extends Model
{
    protected $table = 'lab_marks';

    protected $fillable = ['name'];

    protected $guarded = ["id"];

}
