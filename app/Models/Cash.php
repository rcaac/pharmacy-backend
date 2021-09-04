<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cash extends Model
{
    protected $table = 'cashes';

    protected $fillable = [
        'opening_date',
        'closing_date',
        'initial_balance',
        'final_balance',
        'state',
        'created_by',
        'condition',
        'observations',
        'area_assignment_id',
        'entity_id'
    ];

    protected $guarded = ["id"];

    public function scopeFiltered(Builder $builder): Builder
    {
        $search = request('search') ?? null;
        $sortBy = request('sortBy')[0] ?? null;
        $by     = request('sortDesc')[0] ?? null;
        $order  = $by ? 'desc' : 'asc';

        $chases = $builder
            ->select(
                'id',
                'opening_date',
                'closing_date',
                'initial_balance',
                'final_balance',
                'state',
                'observations',
                'area_assignment_id',
                'entity_id'
            )
            ->with(['assignment.person'])
            ->where('condition', '1')
            ->orderBy('id', 'DESC');
        if ($search && strlen($search) > 0) {
            $chases->where('opening_date', 'LIKE', "%$search%");
        }
        return $chases;
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AreaAssignment::class, 'area_assignment_id');
    }
}
