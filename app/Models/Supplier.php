<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = ['description', 'person_id'];

    protected $guarded = ["id"];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
