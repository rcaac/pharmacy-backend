<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wastage extends Model
{
    protected $table = 'wastages';

    protected $fillable = ['total', 'date', 'created_by', 'condition', 'entity_id', 'wastage_reason_id'];

    protected $guarded = ["id"];

    public function scopeFiltered(Builder $builder): Builder
    {
        return $builder
            ->select('id', 'total', 'date', 'created_by', 'entity_id', 'wastage_reason_id')
            ->with(['responsable', 'reason'])
            ->orderByDesc('id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'created_by');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(WastageReason::class, 'wastage_reason_id');
    }
}
