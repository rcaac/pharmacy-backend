<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TypeInvoicePurchase extends Model
{
    protected $table = 'type_invoice_purchases';

    protected $fillable = ['id', 'description'];
}
