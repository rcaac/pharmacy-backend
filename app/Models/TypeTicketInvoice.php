<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TypeTicketInvoice extends Model
{
    protected $table = 'type_ticket_invoices';

    protected $fillable = ['id', 'name'];
}
