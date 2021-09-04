<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entity extends Model
{
    protected $table = 'entities';

    protected $fillable = ['name', 'direction', 'ruc', 'parent_id', 'created_by', 'condition', 'entity_type_id'];

    protected $guarded = ["id"];

    public function scopeFiltered(Builder $builder): Builder
    {
        $search = request('search') ?? null;
        $sortBy = request('sortBy')[0] ?? null;
        $by     = request('sortDesc')[0] ?? null;
        $order  = $by ? 'desc' : 'asc';

        $entities = $builder
            ->select('id AS entity_id', 'name', 'parent_id', 'direction', 'ruc', 'entity_type_id')
            ->with(['entityType','parent'])
            ->where('condition', '1');
        if ($search && strlen($search) > 0) {
            $entities->where('name', 'LIKE', "%$search%");
        }
        switch ($sortBy) {
            case 'name':
            {
                $entities->orderBy($sortBy, $order);
            }
        }
        return $entities;
    }

    public function entityType(): BelongsTo
    {
        return $this->belongsTo(EntityType::class, 'entity_type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class, 'parent_id');
    }
}
