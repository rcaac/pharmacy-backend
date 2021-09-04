<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = ['commission', 'person_id'];

    protected $guarded = ["id"];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
