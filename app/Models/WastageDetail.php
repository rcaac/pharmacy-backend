<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WastageDetail extends Model
{
    protected $table = 'wastage_details';

    protected $fillable = [
        'quantity',
        'cost_unit',
        'cost_total',
        'lot',
        'date_expiration',
        'condition',
        'entity_id',
        'product_id',
        'wastage_id',
        'detail_invoice_purchase_id'
    ];

    protected $guarded = ["id"];

    public function scopeFiltered(Builder $builder): Builder
    {
        $date = request('date') ?? null;
        $reason = request('wastage_reason_id') ?? null;

        $wastages = $builder
            ->select(
                'id',
                'quantity',
                'cost_unit',
                'cost_total',
                'lot',
                'date_expiration',
                'condition',
                'entity_id',
                'product_id',
                'wastage_id',
                'detail_invoice_purchase_id'
            )
            ->with(['wastage', 'details', 'product'])
            ->where('condition', '!=', 0);

        if ($date && strlen($date) > 0) {
            $wastages->where('date_expiration', 'LIKE', "%$date%");
        }

        if ($reason && strlen($reason) > 0) {
            $wastages->whereHas('wastage' , function ($query) use ($reason) {
                $query->where('wastage_reason_id', $reason);
            });
        }

        return $wastages;
    }

    public function wastage(): BelongsTo
    {
        return $this->belongsTo(Wastage::class, 'wastage_id')
            ->with(['responsable', 'reason']);
    }

    public function details(): BelongsTo
    {
        return $this->belongsTo(DetailInvoicePurchase::class, 'detail_invoice_purchase_id')
            ->with('invoice');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
