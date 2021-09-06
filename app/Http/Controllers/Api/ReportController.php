<?php

namespace App\Http\Controllers\Api;


use App\Models\DetailTicketInvoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Date;

class ReportController
{
    public function getReportBox($id) {

        $users = User::all();

        // share data to view
        view()->share('reports.box.report_boxes',$users);

        $details = DetailTicketInvoice::select(
            'ticket_invoices.prefijo',
            'ticket_invoices.numero',
            'ticket_invoices.date',
            'products.name',
            'detail_ticket_invoices.quantity',
            'detail_ticket_invoices.total'
        )
           ->join('ticket_invoices', 'ticket_invoices.id', '=', 'detail_ticket_invoices.ticket_invoice_id')
           ->join('products', 'products.id', '=', 'detail_ticket_invoices.product_id')
           ->where('ticket_invoices.cash_id', $id)
           ->get();

        return response()->json(
            [
                'details' => $details
            ]
        );
    }
}
