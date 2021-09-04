<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AreaAssignment extends Model
{
    protected $table = 'area_assignments';

    protected $fillable = ['role_id', 'area_id', 'person_id', 'assignment_state_id', 'condition', 'created_by'];

    protected $guarded = ["id"];

    public function scopeFiltered(Builder $builder): Builder
    {
        $search = request('search') ?? null;

        $assignments = $builder->select('id', 'role_id', 'area_id', 'person_id', 'assignment_state_id')
            ->with(['role', 'state', 'person', 'area.entity'])
            ->where('condition', '1');
        if ($search && strlen($search) > 0) {
            $assignments->whereHas('person' , function ($query) use ($search) {
                $query->where('firstName', 'LIKE', '%' . $search . '%');
            });
        }

        return $assignments;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(AssignmentState::class, 'assignment_state_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

}
