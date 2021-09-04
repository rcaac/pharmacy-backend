<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TypeBuy extends Model
{
    protected $table = 'type_buys';

    protected $fillable = ['id', 'name'];
}
