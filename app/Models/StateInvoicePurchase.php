<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class StateInvoicePurchase extends Model
{
    protected $table = 'state_invoice_purchases';

    protected $fillable = ['id', 'description'];
}
