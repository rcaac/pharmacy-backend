<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Person extends Model
{
    protected $table = 'persons';

    protected $fillable = ['firstName', 'lastName', 'dni', 'ruc', 'direction', 'phone', 'email', 'businessName', 'created_by', 'condition', 'person_type_id'];

    protected $guarded = ["id"];

    protected $appends = ['full_name'];

    public function scopeFiltered(Builder $builder): Builder
    {
        $search = request('search') ?? null;
        $sortBy = request('sortBy')[0] ?? null;
        $by     = request('sortDesc')[0] ?? null;
        $order  = $by ? 'desc' : 'asc';

        $persons = $builder
            ->select('id', DB::raw("CONCAT(firstName, ' ', lastName) as fullName"), 'dni', 'direction', 'phone');
        if ($search && strlen($search) > 0) {
            $persons->where('fullName', 'LIKE', "%$search%");
        }
        switch ($sortBy) {
            case 'fullName':
            {
                $persons->orderBy($sortBy, $order);
            }
        }
        return $persons;
    }

    public function getFullNameAttribute(): string
    {
        return $this->attributes['firstName'] . ' ' . $this->attributes['lastName'];
    }
}
