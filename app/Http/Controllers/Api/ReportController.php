<?php

namespace App\Http\Controllers\Api;

use App\Models\Cash;
use App\Models\DetailInvoicePurchase;
use App\Models\DetailTicketInvoice;
use App\Models\Entity;
use App\Models\InvoicePurchase;

use App\Models\Person;
use App\Models\Product;
use App\Models\TicketInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class ReportController
{
    public function getReportBox($id): JsonResponse
    {
        /*$totalVentas = TicketInvoice::where('cash_id',$id)->where('condition','!=','0')
            ->value(DB::raw('SUM(total)'));*/

        $totalVentas = DetailTicketInvoice::select(
            DB::raw('sum(detail_ticket_invoices.total) as totalVentas'),
        )
            ->join('ticket_invoices', 'ticket_invoices.id', '=', 'detail_ticket_invoices.ticket_invoice_id')
            ->where('ticket_invoices.cash_id', $id)
            ->where('detail_ticket_invoices.condition','!=','0')
            ->get();

        $infoCaja = Cash::select('observations','opening_date', 'closing_date', 'initial_balance' )->where('id',$id)->get();


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
            'detail_ticket_invoices.sale_unit',
            DB::raw('type_buys.name  as nameTypeBuy'),

        )
           ->join('ticket_invoices', 'ticket_invoices.id', '=', 'detail_ticket_invoices.ticket_invoice_id')
           ->join('type_buys', 'type_buys.id', '=', 'ticket_invoices.type_buy_id')
           ->join('products', 'products.id', '=', 'detail_ticket_invoices.product_id')
           ->where('ticket_invoices.cash_id', $id)
           ->where('detail_ticket_invoices.condition','!=','0')
            ->orderBy('ticket_invoices.numero', 'asc')
           ->get();

        return response()->json(
            [
                'details' => $details,
                'responsable' => $responsable,
                'totalVentas' => $totalVentas,
                'infoCaja'=> $infoCaja
            ]
        );
    }

    public function getReportFacturaCompra($id): JsonResponse
    {


        $comprobante = InvoicePurchase::select(
            'invoice_purchases.date',
            'invoice_purchases.subtotal',
            'invoice_purchases.total',
            'invoice_purchases.created_at',
            'invoice_purchases.number',
            'persons.firstName',
            'persons.lastName',
            'type_invoice_purchases.description'
        )
            ->join('persons','persons.id','=','invoice_purchases.created_by')
            ->join('type_invoice_purchases','type_invoice_purchases.id','=','invoice_purchases.type_invoice_purchase_id')
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
                'comprobante' => $comprobante,

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

        /*$users = DB::table('orders')
            ->select('department', DB::raw('SUM(price) as total_sales'))
            ->groupBy('department')
            ->havingRaw('SUM(price) > 2500')
            ->get();*/

        $details = DB::table('detail_invoice_purchases')->select(

            'products.name',
            DB::raw('lab_marks.name as lab'),
            DB::raw('presentations.name as present'),
            DB::raw("if(products.box_quantity>1, concat_ws('F',(detail_invoice_purchases.stock_quantity DIV products.box_quantity),(detail_invoice_purchases.stock_quantity MOD products.box_quantity)), detail_invoice_purchases.stock_quantity) AS stock"),
            'detail_invoice_purchases.lot',
            'detail_invoice_purchases.expiration_date',

        )

            ->join('products', 'products.id', '=', 'detail_invoice_purchases.product_id')
            ->leftjoin('lab_marks', 'products.lab_mark_id', '=', 'lab_marks.id')
            ->leftjoin('presentations', 'products.presentation_id', '=', 'presentations.id')
            ->where('detail_invoice_purchases.entity_id', $id)->where('detail_invoice_purchases.condition','!=','0')
            ->orderBy('products.name', 'asc')
            ->get();


        $sucursal = DB::table('entities')->select('name')->where('id', $id)->get();

        return response()->json(
            [
                'details'     => $details,
                /*'responsable' => $responsable,*/
                'sucursal'    => $sucursal,

            ]
        );
    }
    public function getReportProductStockValorizado($id): JsonResponse
    {

        $details = DB::table('detail_invoice_purchases')->select(

            'products.name',
            DB::raw('lab_marks.name as lab'),
            DB::raw('presentations.name as present'),
            'detail_invoice_purchases.buy_unit',
            'detail_invoice_purchases.sale_unit',
            'detail_invoice_purchases.lot',
            'detail_invoice_purchases.expiration_date',
            DB::raw("if(products.box_quantity>1, concat_ws('F',(detail_invoice_purchases.stock_quantity DIV products.box_quantity),(detail_invoice_purchases.stock_quantity MOD products.box_quantity)), detail_invoice_purchases.stock_quantity) AS stockFormat"),
            DB::raw("cast((detail_invoice_purchases.stock_quantity * detail_invoice_purchases.buy_unit) as decimal(8,2)) as totalCompras"),
            DB::raw("cast(if(products.box_quantity>1, ((detail_invoice_purchases.stock_quantity DIV products.box_quantity)* detail_invoice_purchases.sale_box)+((detail_invoice_purchases.stock_quantity MOD products.box_quantity)*detail_invoice_purchases.sale_unit),detail_invoice_purchases.sale_unit * detail_invoice_purchases.stock_quantity)as decimal(8,2)) as totalventas")

        )

            ->join('products', 'products.id', '=', 'detail_invoice_purchases.product_id')
            ->leftjoin('lab_marks', 'products.lab_mark_id', '=', 'lab_marks.id')
            ->leftjoin('presentations', 'products.presentation_id', '=', 'presentations.id')
            ->where('detail_invoice_purchases.entity_id', $id)->where('detail_invoice_purchases.condition','!=','0')->where('detail_invoice_purchases.stock_quantity','!=','0')
            ->orderBy('products.name', 'asc')
            ->get();

        $utilidad= DB::table('detail_invoice_purchases')->select(

            DB::raw("cast(SUM(detail_invoice_purchases.stock_quantity * detail_invoice_purchases.buy_unit) as decimal(8,2)) as totalCompras"),
            DB::raw("cast(SUM(if(products.box_quantity>1, ((detail_invoice_purchases.stock_quantity DIV products.box_quantity)* detail_invoice_purchases.sale_box)+((detail_invoice_purchases.stock_quantity MOD products.box_quantity)*detail_invoice_purchases.sale_unit),detail_invoice_purchases.sale_unit * detail_invoice_purchases.stock_quantity))as decimal(8,2)) as totalVentas"),
            DB::raw("cast((SUM(if(products.box_quantity>1, ((detail_invoice_purchases.stock_quantity DIV products.box_quantity)* detail_invoice_purchases.sale_box)+((detail_invoice_purchases.stock_quantity MOD products.box_quantity)*detail_invoice_purchases.sale_unit),detail_invoice_purchases.sale_unit * detail_invoice_purchases.stock_quantity))- cast(SUM(detail_invoice_purchases.stock_quantity * detail_invoice_purchases.buy_unit) as decimal(8,2)))as decimal(8,2)) as utilidad")

        )

            ->join('products', 'products.id', '=', 'detail_invoice_purchases.product_id')
            ->leftjoin('lab_marks', 'products.lab_mark_id', '=', 'lab_marks.id')
            ->leftjoin('presentations', 'products.presentation_id', '=', 'presentations.id')
            ->where('detail_invoice_purchases.entity_id', $id)->where('detail_invoice_purchases.condition','!=','0')->where('detail_invoice_purchases.stock_quantity','!=','0')
            ->orderBy('products.name', 'asc')
            ->get();

        $sucursal = DB::table('entities')->select('name')->where('id', $id)->get();

        return response()->json(
            [
                'details'     => $details,
                'utilidad'    => $utilidad,
                'sucursal'    => $sucursal,

            ]
        );
    }

    public function getReportProductStockInventary($id): JsonResponse
    {

        $details = DB::table('detail_invoice_purchases')->select(

            'products.name',
            DB::raw('lab_marks.name as lab'),
            DB::raw('presentations.name as present'),
            DB::raw("sum(detail_invoice_purchases.stock_quantity) as cantidad"),

        )

            ->join('products', 'products.id', '=', 'detail_invoice_purchases.product_id')
            ->leftjoin('lab_marks', 'products.lab_mark_id', '=', 'lab_marks.id')
            ->leftjoin('presentations', 'products.presentation_id', '=', 'presentations.id')
            ->where('detail_invoice_purchases.entity_id', $id)
            ->where('detail_invoice_purchases.condition','!=','0')
            ->groupBy('products.name', 'lab_marks.name', 'presentations.name')
            ->orderBy('products.name', 'asc')
            ->get();



        $sucursal = DB::table('entities')->select('name')->where('id', $id)->get();

        return response()->json(
            [
                'details'     => $details,

                'sucursal'    => $sucursal,

            ]
        );
    }

    public function getReportProductExpiration($id): JsonResponse
    {

        $details = DB::table('detail_invoice_purchases')->select(

            'products.name',
            DB::raw('lab_marks.name as lab'),
            DB::raw('presentations.name as present'),
            'detail_invoice_purchases.lot',
            DB::raw('detail_invoice_purchases.expiration_date as expiration'),
            DB::raw("if(products.box_quantity>1, concat_ws('F',(detail_invoice_purchases.stock_quantity DIV products.box_quantity),(detail_invoice_purchases.stock_quantity MOD products.box_quantity)), detail_invoice_purchases.stock_quantity) AS cantidad "),

        )

            ->join('products', 'products.id', '=', 'detail_invoice_purchases.product_id')
            ->leftjoin('lab_marks', 'products.lab_mark_id', '=', 'lab_marks.id')
            ->leftjoin('presentations', 'products.presentation_id', '=', 'presentations.id')
            ->where('expiration_date','>', Carbon::now())
            ->where('stock_quantity','>','0')
            ->where('products.control_expiration','=','1')
            ->where('detail_invoice_purchases.condition','!=','0')
            ->where('detail_invoice_purchases.entity_id', $id)
            ->orderBy('detail_invoice_purchases.expiration_date')
            ->get();



        $sucursal = DB::table('entities')->select('name')->where('id', $id)->get();

        return response()->json(
            [
                'details'     => $details,

                'sucursal'    => $sucursal,

            ]
        );
    }


    public function getReportProductDefeated($id): JsonResponse
    {

        $details = DB::table('detail_invoice_purchases')->select(

            'products.name',
            DB::raw('lab_marks.name as lab'),
            DB::raw('presentations.name as present'),
            'detail_invoice_purchases.lot',
            DB::raw('detail_invoice_purchases.expiration_date as expiration'),
            DB::raw("if(products.box_quantity>1, concat_ws('F',(detail_invoice_purchases.stock_quantity DIV products.box_quantity),(detail_invoice_purchases.stock_quantity MOD products.box_quantity)), detail_invoice_purchases.stock_quantity) AS cantidad "),
        )

            ->join('products', 'products.id', '=', 'detail_invoice_purchases.product_id')
            ->leftjoin('lab_marks', 'products.lab_mark_id', '=', 'lab_marks.id')
            ->leftjoin('presentations', 'products.presentation_id', '=', 'presentations.id')
            ->where('expiration_date','<', Carbon::now())
            ->where('stock_quantity','>','0')
            ->where('products.control_expiration','=','1')
            ->where('detail_invoice_purchases.condition','!=','0')
            ->where('detail_invoice_purchases.entity_id', $id)

            ->orderBy('detail_invoice_purchases.expiration_date','desc')
            ->get();

        $sucursal = DB::table('entities')->select('name')->where('id', $id)->get();

        return response()->json(
            [
                'details'     => $details,
                'sucursal'    => $sucursal,

            ]
        );
    }

    public function getReportProductStockMin($id): JsonResponse
    {

        $details = DB::table('detail_invoice_purchases')->select(

            'products.name',
            DB::raw('lab_marks.name as lab'),
            DB::raw('presentations.name as present'),
            DB::raw("sum(detail_invoice_purchases.stock_quantity) as cantidad"),
            DB::raw('products.minimum_stock as stockMin'),
        )

            ->join('products', 'products.id', '=', 'detail_invoice_purchases.product_id')
            ->leftjoin('lab_marks', 'products.lab_mark_id', '=', 'lab_marks.id')
            ->leftjoin('presentations', 'products.presentation_id', '=', 'presentations.id')
            ->where('detail_invoice_purchases.condition','!=','0')
            ->where('detail_invoice_purchases.entity_id', $id)
            ->groupBy('products.name','lab_marks.name', 'presentations.name', 'products.minimum_stock')
            ->havingRaw('cantidad < stockMin')
            ->get();

        $sucursal = DB::table('entities')->select('name')->where('id', $id)->get();

        return response()->json(
            [
                'details'     => $details,
                'sucursal'    => $sucursal,

            ]
        );
    }

    public function getReportProductTopSales($id): JsonResponse
    {



        $details = DB::table('detail_ticket_invoices')->select(

            'products.name',
            DB::raw('lab_marks.name as lab'),
            DB::raw('presentations.name as present'),
            DB::raw("sum(detail_ticket_invoices.quantity) as cantidad"),

        )

            ->join('products', 'products.id', '=', 'detail_ticket_invoices.product_id')
            ->leftjoin('lab_marks', 'products.lab_mark_id', '=', 'lab_marks.id')
            ->leftjoin('presentations', 'products.presentation_id', '=', 'presentations.id')
            ->whereRaw('detail_ticket_invoices.created_at BETWEEN DATE_SUB(NOW(),INTERVAL 31 day) AND now()')
            ->where('detail_ticket_invoices.condition','!=','0')
            ->groupBy('products.name','lab_marks.name', 'presentations.name')
            ->orderBy('cantidad' ,'DESC')

            ->get();

        $sucursal = DB::table('entities')->select('name')->where('id', $id)->get();

        return response()->json(
            [
                'details'     => $details,
                'sucursal'    => $sucursal,

            ]
        );
    }

    public function getReportSalesCashesPersons($id): JsonResponse
    {
        $details = DB::table('cashes')->select(

            'persons.firstName',
            'persons.lastName',
            DB::raw('cashes.id as caja'),
            DB::raw('cashes.opening_date as fechaApertura'),
            DB::raw('cashes.initial_balance as montoApertura'),
            DB::raw('cashes.closing_date as fechaCierre'),
            DB::raw('cashes.final_balance as montoCierre'),
            DB::raw('cashes.observations as observaciones'),
            DB::raw('areas.name as areaAsignada'),

        )
            ->join('persons', 'persons.id', '=', 'cashes.created_by')
            ->join('area_assignments', 'area_assignments.id', '=', 'cashes.area_assignment_id')
            ->join('areas', 'areas.id', '=', 'area_assignments.area_id')
            ->orderBy('cashes.id' ,'DESC')
            ->get();

        $sucursal = DB::table('entities')->select('name')->where('id', $id)->get();

        return response()->json(
            [
                'details'     => $details,
                'sucursal'    => $sucursal,

            ]
        );
    }


    public function getReportSalesMonthPersonal($id): JsonResponse
    {
        $details = DB::table('detail_ticket_invoices')->select(

            'persons.firstName',
            'persons.lastName',
            DB::raw('DATE_FORMAT(detail_ticket_invoices.created_at, "%b") AS month'),
            DB::raw('DATE_FORMAT(detail_ticket_invoices.created_at, "%Y-%m") as md'),
            DB::raw('sum(detail_ticket_invoices.total) as total'),

        )
            ->join('persons', 'persons.id', '=', 'detail_ticket_invoices.created_by')
            ->whereRaw('(detail_ticket_invoices.created_at <= NOW() and detail_ticket_invoices.created_at >= Date_add(Now(),interval - 12 month))')
            ->where('detail_ticket_invoices.condition','!=','0')
            ->groupBy('month','md','persons.firstName','persons.lastName')
            ->orderBy('md' ,'DESC')
            ->get();

        $sucursal = DB::table('entities')->select('name')->where('id', $id)->get();

        return response()->json(
            [
                'details'     => $details,
                'sucursal'    => $sucursal,

            ]
        );
    }
    public function getReportSalesMonth($id): JsonResponse
    {
        $details = DB::table('detail_ticket_invoices')->select(
            DB::raw('DATE_FORMAT(detail_ticket_invoices.created_at, "%b") AS month'),
            DB::raw('DATE_FORMAT(detail_ticket_invoices.created_at, "%Y-%m") as md'),
            DB::raw('sum(detail_ticket_invoices.total) as total'),
        )
            ->whereRaw('(detail_ticket_invoices.created_at <= NOW() and detail_ticket_invoices.created_at >= Date_add(Now(),interval - 12 month))')
            ->where('detail_ticket_invoices.condition','!=','0')
            ->groupBy('month','md')
            ->orderBy('md' ,'DESC')
            ->get();

        $sucursal = DB::table('entities')->select('name')->where('id', $id)->get();

        return response()->json(
            [
                'details'     => $details,
                'sucursal'    => $sucursal,

            ]
        );
    }
    public function getReportComprobanteVenta($id): JsonResponse
    {
        $entidad = Entity::select(
            'name',
            'direction',
            'ruc'
        )->get();

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

            'type_ticket_invoices.name as type',
            'ticket_invoices.prefijo',
            'ticket_invoices.numero',
            'ticket_invoices.date',
            'products.name',
            'detail_ticket_invoices.quantity',
            'detail_ticket_invoices.sale_unit',
            'detail_ticket_invoices.total',

        )

            ->join('ticket_invoices', 'ticket_invoices.id', '=', 'detail_ticket_invoices.ticket_invoice_id')
            ->join('type_ticket_invoices', 'type_ticket_invoices.id', '=', 'ticket_invoices.type_ticket_invoice_id')
            ->join('products', 'products.id', '=', 'detail_ticket_invoices.product_id')
            ->join('persons', 'persons.id', '=', 'ticket_invoices.created_by')
            ->where('ticket_invoices.id', $id)
            ->get();

        return response()->json(
            [
                'entity'      => $entidad,
                'details'     => $details,
                'responsable' => $responsable,
                'totalVentas' => $totalVentas

            ]
        );
    }


}
