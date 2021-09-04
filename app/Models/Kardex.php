<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kardex extends Model
{
    protected $table = 'kardex';

    protected $fillable = ['date', 'quantity', 'previousStock', 'currentStock', 'voucher', 'product_id', 'area_assignment_id', 'movement_id', 'entity_id'];

    protected $guarded = ["id"];

    public function scopeFiltered(Builder $builder): Builder
    {
        $search = request('search') ?? null;
        $sortBy = request('sortBy')[0] ?? null;
        $by     = request('sortDesc')[0] ?? null;
        $order  = $by ? 'desc' : 'asc';

        $areas = $builder
            ->select('date', 'quantity', 'previousStock', 'currentStock', 'voucher', 'product_id', 'area_assignment_id', 'movement_id', 'entity_id')
            ->with(['entity', 'product', 'assignment', 'movement']);
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function assigment(): BelongsTo
    {
        return $this->belongsTo(AreaAssignment::class, 'area_assignment_id');
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(Movement::class);
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }
}
