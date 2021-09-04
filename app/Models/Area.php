<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $table = 'areas';

    protected $fillable = ['name', 'parent_id', 'created_by', 'condition', 'area_type_id', 'entity_id'];

    protected $guarded = ["id"];

    public function scopeFiltered(Builder $builder): Builder
    {
        $search = request('search') ?? null;
        $sortBy = request('sortBy')[0] ?? null;
        $by     = request('sortDesc')[0] ?? null;
        $order  = $by ? 'desc' : 'asc';

        $areas = $builder
            ->select('id', 'name', 'parent_id', 'area_type_id', 'entity_id')
            ->with(['entity', 'areaType', 'parent'])
            ->where('condition', '1');
        if ($search && strlen($search) > 0) {
            $areas->where('name', 'LIKE', "%$search%");
        }
        switch ($sortBy) {
            case 'name':
            {
                $areas->orderBy($sortBy, $order);
            }
        }
        return $areas;
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function areaType(): BelongsTo
    {
        return $this->belongsTo(AreaType::class, 'area_type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class, 'parent_id');
    }
}
