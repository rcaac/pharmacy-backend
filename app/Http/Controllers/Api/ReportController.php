<?php

namespace App\Http\Controllers\Api;

use App\Models\DetailInvoicePurchase;
use App\Models\DetailTicketInvoice;
use App\Models\InvoicePurchase;

use App\Models\Person;
use App\Models\Product;
use App\Models\TicketInvoice;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class ReportController
{
    public function getReportBox($id): JsonResponse
    {
        $totalVentas = TicketInvoice::where('cash_id',$id)
            ->value(DB::raw('SUM(total)'));


        $responsable = Person::select(

        )
            ->join('cashes','persons.id','=','cashes.created_by')
            ->where('cashes.id',$id)
            ->get();

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
                'details' => $details,
                'responsable' => $responsable,
                'totalVentas' => $totalVentas
            ]
        );
    }

    public function getReportFacturaCompra($id): JsonResponse
    {
        $responsable = InvoicePurchase::select(

            'persons.firstName',
            'persons.lastName',
        )
            ->join('persons','persons.id','=','invoice_purchases.created_by')
            ->where('invoice_purchases.id',$id)
            ->get();




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
                'details'     => $details,
                'responsable' => $responsable,


            ]
        );
    }

    public function getReportProductStock($id): JsonResponse
    {
        /*$responsable = InvoicePurchase::select(

            'persons.firstName',
            'persons.lastName',
        )
            ->join('persons','persons.id','=','invoice_purchases.created_by')
            ->where('invoice_purchases.id',$id)
            ->get();*/

        $details = DetailInvoicePurchase::select(

            'products.name',
            'lab_marks.name as lab',
            'presentations.name as present',
            'detail_invoice_purchases.stock_quantity',
            'detail_invoice_purchases.lot',
            'detail_invoice_purchases.expiration_date',

        )

            ->join('products', 'products.id', '=', 'detail_invoice_purchases.product_id')
            ->join('lab_marks', 'products.lab_mark_id', '=', 'lab_marks.id')
            ->join('presentations', 'products.presentation_id', '=', 'presentations.id')
            ->where('detail_invoice_purchases.entity_id', $id)
            ->orderBy('products.name', 'asc')
            ->get();

        return response()->json(
            [
                'details'     => $details,
                /*'responsable' => $responsable,*/


            ]
        );
    }

    public function getReportComprobanteVenta($id): JsonResponse
    {
        $totalVentas = TicketInvoice::where('id',$id)
            ->value(DB::raw('total'));


        $responsable = TicketInvoice::select(

            'persons.firstName',
            'persons.lastName',
        )
            ->join('persons','persons.id','=','ticket_invoices.created_by')
            ->where('ticket_invoices.id',$id)
            ->get();

        $details = DetailTicketInvoice::select(


            'products.name',
            'detail_ticket_invoices.quantity',
            'detail_ticket_invoices.sale_unit',
            'detail_ticket_invoices.total',

        )

            ->join('ticket_invoices', 'ticket_invoices.id', '=', 'detail_ticket_invoices.ticket_invoice_id')
            ->join('products', 'products.id', '=', 'detail_ticket_invoices.product_id')
            ->join('persons', 'persons.id', '=', 'ticket_invoices.created_by')
            ->where('ticket_invoices.id', $id)
            ->get();

        return response()->json(
            [
                'details'     => $details,
                'responsable' => $responsable,
                'totalVentas' => $totalVentas

            ]
        );
    }


}
