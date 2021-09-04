<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentState extends Model
{
    protected $table = 'assignment_states';

    protected $fillable = ['name'];

    protected $guarded = ["id"];
}
