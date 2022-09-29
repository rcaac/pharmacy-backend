<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailInvoicePurchase extends Model
{
    protected $table = 'detail_invoice_purchases';

    protected $fillable = [
        'lot',
        'expiration_date',
        'quantity',
        'stock_quantity',
        'buy_unit',
        'sale_unit',
        'sale_blister',
        'sale_box',
        'minimum_sale_unit',
        'minimum_sale_blister',
        'minimum_sale_box',
        'total',
        'created_by',
        'condition',
        'product_id',
        'invoice_purchase_id',
        'entity_id'
    ];

    protected $guarded = ["id"];

    public function scopeFiltered(Builder $builder): Builder
    {
        $search = request('search') ?? null;
        $sortBy = request('sortBy')[0] ?? null;
        $by     = request('sortDesc')[0] ?? null;
        $order  = $by ? 'desc' : 'asc';


        return $builder->select(
            'id',
            'lot',
            'expiration_date',
            'quantity',
            'stock_quantity',
            'unit_cost',
            'created_by',
            'condition',
            'product_id',
            'invoice_purchase_id'
            
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id')->with(['laboratory', 'generic', 'category', 'presentation', 'location', 'stock']);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoicePurchase::class, 'invoice_purchase_id');
    }
}
