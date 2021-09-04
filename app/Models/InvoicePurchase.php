<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoicePurchase extends Model
{
    protected $table = 'invoice_purchases';

    protected $fillable = [
        'number',
        'date',
        'subtotal',
        'total',
        'created_by',
        'condition',
        'supplier_id',
        'state_invoice_purchase_id',
        'type_invoice_purchase_id',
        'entity_id'
    ];

    protected $guarded = ["id"];

    public function scopeFiltered(Builder $builder): Builder
    {
        $search = request('search') ?? null;
        
        $invoice = $builder
            ->select(
            'id',
            'number',
            'date',
            'subtotal',
            'total',
            'created_by',
            'condition',
            'supplier_id',
            'state_invoice_purchase_id',
            'type_invoice_purchase_id',
            'created_at',
        )
        ->with(['supplier.person', 'responsable', 'type', 'state', 'details.product'])
        ->where('condition', '1')
        ->orderByDesc('id');
        if ($search && strlen($search) > 0) {
            $invoice->whereHas('supplier' , function ($query) use ($search) {
                $query->where('firstName', 'LIKE', '%' . $search . '%');
            });
        }
        return $invoice;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeInvoicePurchase::class, 'type_invoice_purchase_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(StateInvoicePurchase::class, 'state_invoice_purchase_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailInvoicePurchase::class)->where('condition', '1');
    }
}
