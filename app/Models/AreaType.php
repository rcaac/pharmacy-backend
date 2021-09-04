<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AreaType extends Model
{
    protected $table = 'area_types';

    protected $fillable = ['id', 'name'];
}
