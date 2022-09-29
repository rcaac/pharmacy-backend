<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'barcode',
        'name',
        'short_name',
        'maximum_stock',
        'minimum_stock',
        'box_quantity',
        'blister_quantity',
        'presentation_sale',
        'buy_unit',
        'buy_blister',
        'buy_box',
        'sale_unit',
        'sale_blister',
        'sale_box',
        'minimum_sale_unit',
        'minimum_sale_blister',
        'minimum_sale_box',
        'control_expiration',
        'control_stock',
        'control_refrigeration',
        'control_prescription',
        'lab_mark_id',
        'active_principle_id',
        'therapeutic_action_id',
        'presentation_id',
        'location_id',
        'created_by',
        'condition'
    ];

    protected $guarded = ["id"];
    protected $appends = ['quantity', 'row_invoice'];
    protected $casts = ['row_invoice' => 'integer'];

    private function getPersonId()
    {
        return auth()->user()->person->id;
    }

    private function getEntity()
    {
        $query = AreaAssignment::with('area.entity')->where('person_id', $this->getPersonId())->get();
        return $query[0]->area->entity->id;
    }

    public function scopeFiltered(Builder $builder): Builder
    {
        $search = request('search') ?? null;
        $sortBy = request('sortBy')[0] ?? null;
        $by     = request('sortDesc')[0] ?? null;
        $order  = $by ? 'desc' : 'asc';

        $products = $builder
            ->select(
                'id',
                'barcode',
                'name',
                'short_name',
                'maximum_stock',
                'minimum_stock',
                'box_quantity',
                'blister_quantity',
                'presentation_sale',
                'buy_unit',
                'buy_blister',
                'buy_box',
                'sale_unit',
                'sale_blister',
                'sale_box',
                'minimum_sale_unit',
                'minimum_sale_blister',
                'minimum_sale_box',
                'control_expiration',
                'control_stock',
                'control_refrigeration',
                'control_prescription',
                'lab_mark_id',
                'active_principle_id',
                'therapeutic_action_id',
                'presentation_id',
                'location_id',
            )
            ->with(['laboratory', 'generic', 'category', 'presentation', 'location', 'stock'])
            ->where('condition', '1')
            ->orderBy('name');
        if ($search && strlen($search) > 0) {
            $products->where('name', 'LIKE', "%$search%");
        }
        if ($sortBy == 'name') {
            {
                $products->orderBy($sortBy, $order);
            }
        }
        return $products;
    }

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(LabMark::class, 'lab_mark_id');
    }

    public function generic(): BelongsTo
    {
        return $this->belongsTo(ActivePrinciple::class, 'active_principle_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TherapeuticAction::class, 'therapeutic_action_id');
    }

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailInvoicePurchase::class);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(ProductStock::class)->where('entity_id', $this->getEntity());
    }

    public function getQuantityAttribute(): ?string
    {
        return null;
    }

    public function getRowInvoiceAttribute(): ?int
    {
        return 1;
    }
}
