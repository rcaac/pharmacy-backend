<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class StateTicketInvoice extends Model
{
    protected $table = 'state_ticket_invoices';

    protected $fillable = ['id', 'name'];
}
