<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class TicketInvoice extends Model
{
    protected $table = 'ticket_invoices';

    protected $fillable = [
        'prefijo',
        'numero',
       'igv',
        'date',
        'subtotal',
        'total',
        'created_by',
        'condition',
        'type_ticket_invoice_id',
        'state_ticket_invoice_id',
        'customer_id',
        'cash_id',
        'type_buy_id',
        'entity_id'
    ];

    protected $guarded = ["id"];

    public function scopeFiltered(Builder $builder): Builder
    {
        $search = request('search') ?? null;

        $invoice = $builder
            ->select(
                'id',
                'prefijo',
                'numero',
                DB::raw("CONCAT(prefijo, '-', numero) as prenum"),
                'date',
                'subtotal',
                'total',
                'created_by',
                'condition',
                'type_ticket_invoice_id',
                'state_ticket_invoice_id',
                'customer_id',
                'cash_id',
                'type_buy_id',
                'entity_id'
            )
            ->with(['customer.person', 'responsable', 'type', 'state', 'details.product'])
            ->where('condition', '1')
            ->orderByDesc('id');
        if ($search && strlen($search) > 0) {
            $invoice->whereHas('customer' , function ($query) use ($search) {
                $query->where('firstName', 'LIKE', '%' . $search . '%');
            });
        }

        return $invoice;
    }



    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeTicketInvoice::class, 'type_ticket_invoice_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(StateTicketInvoice::class, 'state_ticket_invoice_id');
    }

    public function buy(): BelongsTo
    {
        return $this->belongsTo(TypeBuy::class, 'type_buy_id');
    }

    public function cash(): BelongsTo
    {
        return $this->belongsTo(Cash::class, 'cash_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailTicketInvoice::class)->where('condition', '1');
    }
}

