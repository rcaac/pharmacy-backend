<?php

namespace App\Http\Controllers\Api;

use App\Models\DetailTicketInvoice;
use App\Models\InvoicePurchase;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;

class ReportController
{
    public function getReportBox($id): JsonResponse
    {
        $details = DetailTicketInvoice::select(
            'ticket_invoices.prefijo',
            'ticket_invoices.numero',
            'ticket_invoices.date',
            'products.name',
            'detail_ticket_invoices.quantity',
            'detail_ticket_invoices.total',

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

    public function getReportFacturaCompra($id): JsonResponse
    {


        $details = InvoicePurchase::select(
            'invoice_purchases.number',
            'invoice_purchases.date',
            'invoice_purchases.subtotal',
            'invoice_purchases.total',
            'invoice_purchases.created_by',
            'products.name',
            'detail_invoice_purchases.lot',
            'detail_invoice_purchases.expiration_date',
            'detail_invoice_purchases.quantity',
            'detail_invoice_purchases.buy_unit',
            'detail_invoice_purchases.total',

        )

            ->join('detail_invoice_purchases', 'invoice_purchases.id', '=', 'detail_invoice_purchases.invoice_purchase_id')
            ->join('products', 'products.id', '=', 'detail_invoice_purchases.product_id')
            ->join('persons', 'persons.id', '=', 'invoice_purchases.created_by')
            ->where('invoice_purchases.id', $id)
            ->get();

        return response()->json(
            [
                'details' => $details

            ]
        );
    }
}
