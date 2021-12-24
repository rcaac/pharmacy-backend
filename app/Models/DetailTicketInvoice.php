<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTicketInvoice extends Model
{
    protected $table = 'detail_ticket_invoices';

    protected $fillable = [
        'lot',
        'expiration_date',
        'quantity',
        'sale_unit',
        'sale_blister',
        'sale_box',
        'total',
        'created_by',
        'condition',
        'ticket_invoice_id',
        'detail_invoice_purchase_id',
        'product_id',
        'entity_id'
    ];

    protected $guarded = ["id"];

    public function scopeFiltered(Builder $builder): Builder
    {
        $search = request('search') ?? null;
        $sortBy = request('sortBy')[0] ?? null;
        $by     = request('sortDesc')[0] ?? null;
        $order  = $by ? 'desc' : 'asc';


        return $builder->select('id', 'lot', 'expiration_date', 'quantity', 'sale_unit', 'total', 'created_by', 'condition', 'ticket_invoice_id', 'product_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id')->with(['laboratory', 'generic', 'category', 'presentation', 'location']);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(TicketInvoice::class, 'ticket_invoice_id');
    }

}
