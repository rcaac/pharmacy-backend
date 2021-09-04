<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AreaAssignment;
use App\Models\Cash;
use App\Models\Customer;
use App\Models\DetailInvoicePurchase;
use App\Models\DetailTicketInvoice;
use App\Models\Entity;
use App\Models\Kardex;
use App\Models\Person;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\TicketInvoice;
use App\Models\TypeBuy;
use App\Models\TypeTicketInvoice;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class TicketInvoiceController extends Controller
{
    private function getPersonId()
    {
        return auth()->user()->person->id;
    }

    private function getEntity()
    {
        $query = AreaAssignment::with('area.entity')->where('person_id', $this->getPersonId())->get();
        return $query[0]->area->entity->id;
    }

    public function getRole()
    {
        $role = AreaAssignment::with('role')->where('person_id', $this->getPersonId())->get();
        return $role[0]->role->name;
    }

    public function listTypeTicketInvoices(): JsonResponse
    {
        $list = TypeTicketInvoice::select('id', 'name')->orderBy('id', 'ASC')->get();

        return response()->json(
            [
                "success"       => true,
                'typeTicketInvoice' => $list
            ]
        );
    }

    public function listTypeBuys(): JsonResponse
    {
        $list = TypeBuy::select('id', 'name')->orderBy('id', 'ASC')->get();

        return response()->json(
            [
                "success" => true,
                'typeBuy'    => $list
            ]
        );
    }

    public function listProducts($search): JsonResponse
    {
        $products = Product::with([
            'laboratory',
            'generic',
            'category',
            'presentation',
            'location',
            'stock' => function($query){
                $query->where('entity_id', $this->getEntity());
            },
            'details' => function($query){
                $query->where('stock_quantity', '>', 0);
            }
        ])
        ->where('condition', '1');

        if (strpos($search, '*') !== false) {
            $replace = str_replace("*", " ", $search);
            $result = ltrim($replace);
            $products = $products->whereHas('generic', function($query) use ($result) {
                $query->where("name", "LIKE","%$result%");
            });
        }else if (strpos($search, '/') !== false) {
            $replace = str_replace("/", " ", $search);
            $result = ltrim($replace);
            $products = $products->whereHas('category', function($query) use ($result) {
                $query->where("name", "LIKE","%$result%");
            });
        }else {
            $products = $products->where('name', 'LIKE', "%$search%");
        }

        $products = $products->get();


        return response()->json(
            [
                "success"  => true,
                "data"     => $products
            ]
        );
    }

    public function listProductBarcode($search): JsonResponse
    {
        $products = Product::with(['laboratory', 'generic', 'category', 'presentation', 'location', 'stock' => function($query){
            $query->where('entity_id', $this->getEntity());
        }])
            ->where('condition', '1')
            ->where('barcode', $search)
            ->get();

        return response()->json(
            [
                "success"  => true,
                "data"     => $products
            ]
        );
    }

    public function index(): JsonResponse
    {
        if (request()->wantsJson()) {
            $itemsPerPage = (int) request('itemsPerPage');
            $invoices = TicketInvoice::filtered();
            return response()->json(
                [
                    "success"  => true,
                    "data"     => $invoices->where('entity_id', $this->getEntity())->paginate($itemsPerPage != 'undefined' ? $itemsPerPage : 10)
                ]
            );
        }
        abort(401);
    }

    private function validation($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
                'ruc'                    => 'required',
                'type_ticket_invoice_id' => 'required',
            ],
            [
                'ruc.required'                    => 'El ruc es obligatorio',
                'type_ticket_invoice_id.required' => 'Debe de elegir un tipo de venta',
            ]
        );
    }

    public function store(): JsonResponse
    {
        $sales = request('sales');

        try{
            DB::beginTransaction();

            if (request('type_ticket_invoice_id') == 1 || request('type_ticket_invoice_id') == 4) {

                $this->validation(request()->input());

                if ($this->validation(request()->input())->fails()) {
                    return response()->json(array(
                        'success' => false,
                        'errors'  => $this->validation(request()->input())->getMessageBag()->toArray()
                    ), 422);
                }

                $person = Person::create([
                    'ruc'            => request('ruc'),
                    'businessName'   => request('businessName'),
                    'phone'          => request('phone'),
                    'email'          => request('email'),
                    'created_by'     => $this->getPersonId(),
                    'condition'      => '1',
                    'person_type_id' => '1'
                ]);
            }else {
                $person = Person::create([
                    'dni'            => request('dni'),
                    'firstName'      => request('firstName'),
                    'lastName'       => request('lastName'),
                    'direction'      => request('direction'),
                    'phone'          => request('phone'),
                    'email'          => request('email'),
                    'created_by'     => $this->getPersonId(),
                    'condition'      => '1',
                    'person_type_id' => '2'
                ]);
            }

            $customer = Customer::create([
                'points'    => request('points'),
                'person_id' => $person->id
            ]);

            $date    = Carbon::now();
            $year    = $date->format('Y');
            $type    = TypeTicketInvoice::where("id",request('type_ticket_invoice_id'))->value("name");
            $prefijo = "$type[0]" . "$year";

            $cuantity = TicketInvoice::where('type_ticket_invoice_id', request('type_ticket_invoice_id'))
                ->where(DB::raw('YEAR(created_at)'), Carbon::now()->year)
                ->value(DB::raw('MAX(numero)'));

            if ( $cuantity == null){
                $count = str_pad(1, 6, "0", STR_PAD_LEFT);
            }
            else {
                $count = str_pad($cuantity+1, 6, "0", STR_PAD_LEFT);
            }

            $numero = str_pad($count, 6, "0", STR_PAD_LEFT);

            $ticket_invoice = TicketInvoice::create([
                'prefijo'                 => $prefijo,
                'numero'                  => $numero,
                'date'                    => Carbon::now(),
                'subtotal'                => request('subtotal'),
                'igv'                     => request('igv'),
                'total'                   => request('total'),
                'created_by'              => $this->getPersonId(),
                'condition'               => '1',
                'type_ticket_invoice_id'  => request('type_ticket_invoice_id'),
                'state_ticket_invoice_id' => '1',
                'customer_id'             => $customer->id,
                'cash_id'                 => request('cash_id'),
                'type_buy_id'             => request('type_buy_id'),
                'entity_id'               => request('entity_id')
            ]);

            $first = 0;
            $last = 0;

            foreach($sales as $clave => $sale) {

                if (strpos($sale['quantity'], 'F') !== false) {
                    $quantities = explode("F", $sale['quantity']);

                    for ($i = 0; $i < count($quantities); $i++) {
                        $first = $quantities[0];
                        $last  = $quantities[1];
                    }
                    $quantity = (int)$first * (int)$sale['box_quantity'] + (int)$last;
                }else {
                    $quantity = (int)$sale['quantity'];
                }

                $product_stock = ProductStock::where('product_id', $sale['id'])
                    ->where('entity_id', request('entity_id'))
                    ->value('stock');

                if ($product_stock === null) {
                    $previousStock = 0;
                    $currentStock  = $previousStock - (int)$quantity;
                }else {
                    $previousStock = $product_stock;
                    $currentStock  = (int)$product_stock - (int)$quantity;
                }

                Kardex::create([
                    'date'               => Carbon::now(),
                    'quantity'           => $quantity,
                    'previousStock'      => (int)$previousStock,
                    'currentStock'       => $currentStock,
                    'voucher'            => $sale['id'],
                    'product_id'         => $sale['id'],
                    'area_assignment_id' => request('area_assignment_id'),
                    'movement_id'        => '2',
                    'entity_id'          => request('entity_id'),
                ]);

                $product_stock_id = ProductStock::where('product_id', $sale['id'])->where('entity_id', request('entity_id'))->value('id');

                $search_product_stock = ProductStock::findOrFail($product_stock_id);
                $search_product_stock->fill([
                    'stock' => (int)$previousStock - $quantity
                ])->save();

                $sale_unit = number_format($sale['total']/ $quantity ,4);

                DetailTicketInvoice::create([
                    'lot'               => $sale['lot'],
                    'expiration_date'   => $sale['details'][$clave]['expiration_date'],
                    'quantity'          => $quantity,
                    'sale_unit'         => $sale_unit,
                    'sale_blister'      => $sale['sale_blister'],
                    'sale_box'          => $sale['sale_box'],
                    'total'             => $sale['total'],
                    'created_by'        => $this->getPersonId(),
                    'condition'         => '1',
                    'product_id'        => $sale['id'],
                    'ticket_invoice_id' => $ticket_invoice->id,
                    'entity_id'         => request('entity_id')
                ]);



                $date_now = Carbon::now()->format('Y-m-d');

                $quantity_current = -1;

                while($quantity_current < 0) {

                    $current = DetailInvoicePurchase::where('expiration_date', '>', $date_now)
                        ->where('stock_quantity', '!=', 0)
                        ->where('product_id', $sale['id'])
                        ->value('stock_quantity');


                    $quantity_current = (int)$current - $quantity;

                    $detail_invoice_purchase_id = DetailInvoicePurchase::where('expiration_date', '>', $date_now)
                        ->where('stock_quantity', '!=', 0)
                        ->where('product_id', $sale['id'])
                        ->value('id');

                    $detail_invoice_purchase = DetailInvoicePurchase::findOrFail($detail_invoice_purchase_id);

                    $detail_invoice_purchase->fill([
                        'stock_quantity' => $quantity_current < 0 ? '0' : $quantity_current
                    ])->save();

                    $quantity = -1 * ($quantity_current);
                }

            }

            DB::commit();

            return response()->json(
                [
                    "message"   => "Operación realizada con éxito",
                    "idticktet" => $ticket_invoice->id
                ],201);

        }catch(Exception $e){
            DB::rollBack();
            return response()->json($e->getMessage());

        }
    }

    public function update($id): JsonResponse
    {
        $ticket_invoice = TicketInvoice::findOrFail($id);

        $person = Person::findOrFail(request('person_id'));

        $customer = Customer::findOrFail(request('customer_id'));

        $sales = request('sales');


        try{
            DB::beginTransaction();

            if (request('type_ticket_invoice_id') == 1 || request('type_ticket_invoice_id') == 4) {

                $this->validation(request()->input());

                if ($this->validation(request()->input())->fails()) {
                    return response()->json(array(
                        'success' => false,
                        'errors'  => $this->validation(request()->input())->getMessageBag()->toArray()
                    ), 422);
                }
                $person->fill([
                    'ruc'             => request('ruc'),
                    'businessName'    => request('businessName'),
                    'phone'           => request('phone'),
                    'email'           => request('email'),
                    'created_by'      => $this->getPersonId(),
                    'condition'       => '1',
                    'person_type_id'  => '1'
                ])->save();
            }else {
                $person->fill([
                    'dni'            => request('dni'),
                    'firstName'      => request('firstName'),
                    'lastName'       => request('lastName'),
                    'direction'      => request('direction'),
                    'phone'          => request('phone'),
                    'email'          => request('email'),
                    'created_by'     => $this->getPersonId(),
                    'condition'      => '1',
                    'person_type_id' => '2'
                ])->save();
            }

            $customer->fill([
                'points' => request('customer'),
                'person_id'   => $person->id
            ])->save();

            $ticket_invoice->fill([
                'date'                      => request('date'),
                'subtotal'                  => request('subtotal'),
                'total'                     => request('total'),
                'created_by'                => $this->getPersonId(),
                'condition'                 => '1',
                'customer_id'               => $customer->id,
                'state_ticket_invoice_id'   => request('state_ticket_invoice_id'),
                'type_ticket_invoice_id'    => request('type_ticket_invoice_id'),
                'cash_id'                   => request('cash_id'),
                'type_buy_id'               => request('type_buy_id'),
                'entity_id'                 => request('entity_id')
            ])->save();

            foreach($sales as $sale) {

                $previousStock = DetailInvoicePurchase::where('product_id', $sale['id'])->value('stock_quantity');
                $currentStock = (int)$previousStock + (int)$sale['quantity'];

                Kardex::create([
                    'date'               => Carbon::now(),
                    'quantity'           => (int)$sale['quantity'],
                    'previousStock'      => (int)$previousStock,
                    'currentStock'       => $currentStock,
                    'voucher'            => $sale['id'],
                    'product_id'         => $sale['id'],
                    'area_assignment_id' => request('area_assignment_id'),
                    'movement_id'        => '2',
                    'entity_id'          => request('entity_id'),
                ]);

                ProductStock::create([
                    'stock'      => $currentStock - (int)$sale['quantity'],
                    'entity_id'  => request('entity_id'),
                    'product_id' => $sale['id']
                ]);

                DetailTicketInvoice::create([
                    'lot'               => $sale['lot'],
                    'expiration_date'   => $sale['expiration_date'],
                    'quantity'          => (int)$sale['quantity'],
                    'sale_unit'         => $sale['sale_unit'],
                    'sale_blister'      => $sale['sale_blister'],
                    'sale_box'          => $sale['sale_box'],
                    'total'             => $sale['total'],
                    'created_by'        => $this->getPersonId(),
                    'condition'         => '1',
                    'product_id'        => $sale['id'],
                    'ticket_invoice_id' => $ticket_invoice->id,
                    'entity_id'         => request('entity_id')
                ]);
            }

            DB::commit();

            return response()->json(["message" => "Operación realizada con éxito"],201);

        }catch(Exception $e){
            DB::rollBack();
            return response()->json($e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $detail = DetailTicketInvoice::findOrFail($id);
        if (!$detail) {
            return response()->json(["message" => "Persona no encontrada"], 404);
        }
        $detail->fill(['condition' => '0' ])->save();
        return response()->json(["message" => "Compra eliminada"]);
    }

    private function getIdTicketInvoice($id) {
        return DetailTicketInvoice::select('id')->where('ticket_invoice_id', $id)->get();
    }

    public function destroyTicketInvoice($id) {

        $idDetails = $this->getIdTicketInvoice($id);

        foreach ($idDetails as $idDetail) {
            $detail = DetailTicketInvoice::findOrFail($idDetail->id);
            $detail->fill(['condition' => '0' ])->save();
        }

        $ticket_invoice = TicketInvoice::findOrFail($id);
        $ticket_invoice->fill([
            'state_ticket_invoice_id'   => '2'
        ])->save();

    }

    public function validationLaboratories($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request,
            [
                'name' => 'required'
            ],
            [
                'name.required' => 'El nombre es requerido',
            ]
        );
    }

    public function getBox(): JsonResponse
    {
        if (request()->wantsJson()) {
            $itemsPerPage = (int) request('itemsPerPage');
            $cashes = Cash::filtered();

            if ($this->getRole() == 'admin') {
                $cashes = $cashes->paginate($itemsPerPage != 'undefined' ? $itemsPerPage : 10);
            }else {
                $cashes = $cashes->where('entity_id', $this->getEntity())
                    ->where('created_by', $this->getPersonId())
                    ->paginate($itemsPerPage != 'undefined' ? $itemsPerPage : 10);
            }

            return response()->json(
                [
                    "success" => true,
                    "data" => $cashes,
                    "entities" => Entity::select('id', 'name')->get(),
                    'role' => $this->getRole()
                ]
            );
        }
        abort(401);
    }

    public function boxStore(): JsonResponse
    {
        Cash::create([
            'opening_date'       => Carbon::parse(request('opening_date'))->setTimeFromTimeString(date("H:i:s")),
            'closing_date'       => request('closing_date'),
            'initial_balance'    => request('initial_balance'),
            'final_balance'      => request('final_balance'),
            'state'              => 1,
            'created_by'         => $this->getPersonId(),
            'condition'          => 1,
            'observations'       => request('observations'),
            'area_assignment_id' => request('area_assignment_id'),
            'entity_id'          => request('entity_id')
        ]);

        return response()->json(["message" => "Caja creada"],201);
    }

    public function boxUpdate (): JsonResponse
    {
        $cash = Cash::findOrFail(request('id'));

        $cash->fill([
            'initial_balance'    => request('initial_balance'),
            'final_balance'      => request('final_balance'),
            'state'              => 1,
            'created_by'         => $this->getPersonId(),
            'condition'          => 1,
            'observations'       => request('observations'),
            'area_assignment_id' => request('area_assignment_id'),
            'entity_id'          => request('entity_id')
        ])->save();

        return response()->json(["message" => "Caja actualizada"],201);
    }

    public function reverse (): JsonResponse
    {
        $cash = Cash::findOrFail(request('id'));

        $cash->fill([
            'final_balance' => null,
            'closing_date'  => null,
            'state'         => 1
        ])->save();

        return response()->json(["message" => "Revertido exitosamente"],201);
    }

    public function getInvoices(): JsonResponse
    {
        $list = TicketInvoice::select('id', 'customer_id', 'cash_id', 'date', 'subtotal', 'total', 'state_ticket_invoice_id')
            ->with(['customer.person', 'cash.assignment.person', 'state'])
            ->paginate(10);

        return response()->json([
            "data" => $list
        ]);
    }

    public function closeBox(): JsonResponse
    {
        $cash = Cash::findOrFail(request('id'));

        $cash->fill([
            'state' => '0',
            'final_balance' => request('final_balance'),
            'observations'  => request('observations'),
            'closing_date'  => Carbon::now()
        ])->save();

        return response()->json(["message" => "Caja cerrada"]);
    }

    public function boxTotal($id): JsonResponse
    {
        $total = TicketInvoice::select(DB::raw('SUM(total) as total'))
            ->where('cash_id', $id)
            ->groupBy("entity_id")
            ->first();

        return response()->json([
            "data" => $total
        ]);
    }

    public function printTicketInvoice(){

        $sucursal = Entity::select('id','name','direction','ruc')->where('id', request('entity_id'))->first();
        $ventas = DetailTicketInvoice::with(['ticket','product'])->where('ticket_invoice_id', request('sale_id'))->get();
        $ventat = TicketInvoice::with(['type'])->where('id', request('sale_id'))->first();
        $ventaResponsable= TicketInvoice::with(['responsable'])->where('id', request('sale_id'))->first();
        $ventaCliente= TicketInvoice::with(['customer.person'])->where('id', request('sale_id'))->first();
        print($ventas);
        $nombreImpresora = env("NOMBRE_IMPRESORA");
        $connector = new WindowsPrintConnector($nombreImpresora);
        $impresora = new Printer($connector);
        $impresora->setJustification(Printer::JUSTIFY_CENTER);
        $impresora->setEmphasis(true);
        $impresora->setTextSize(2, 4);
        $impresora->text($sucursal->name."\n\n");
        $impresora->setTextSize(1, 1);
        $impresora->text("RUC: ".$sucursal->ruc."\n");
        $impresora->text("CENTRAL: ".$sucursal->direction."\n\n");
        $impresora->setEmphasis(false);

        $impresora->text(   $ventat->type->name." ".$ventat->prefijo."-".$ventat->numero." \n");
        $impresora->setJustification(Printer::JUSTIFY_LEFT);
        $impresora->text("FECHA DE EMISION: ".$ventat->created_at."\n");
        $impresora->text("CAJERO: ".$ventaResponsable->responsable->firstName."\n\n");

        $impresora->text("DNI CLIENTE: ".$ventaCliente->customer->person->dni."\n");
        $impresora->text("NOMBRE DE CLIENTE: ".$ventaCliente->customer->person->firstName."\n");
        $impresora->text("________________________________________");
        $impresora->text("CODIGO");
        $impresora->text(" DESCRIPCION");
        $impresora->text(" CANT.");
        $impresora->text(" P.UNIT");
        $impresora->text(" IMPORTE ");
        $impresora->text("________________________________________\n");

         foreach ($ventas as $venta) {
             if(is_null($venta->product->barcode)){
                 $impresora->text("0"." ");
             }
             else{
                 $impresora->text($venta->product->barcode." ");
             }
             $impresora->text($venta->product->name." ");
             $impresora->text($venta->quantity." ");
             $impresora->setJustification(Printer::JUSTIFY_RIGHT);
             $impresora->text($venta->sale_unit."    ");
             $impresora->text($venta->total."\n");
             $impresora->setJustification(Printer::JUSTIFY_LEFT);
         }

        $impresora->text("________________________________________\n");

        $impresora->setJustification(Printer::JUSTIFY_RIGHT);
        $impresora->setEmphasis(true);
        $impresora->text("Sub Total: $" . number_format($ventat->subtotal, 2) . "\n");
        $impresora->text("Total: $" . number_format($ventat->total, 2) . "\n\n");
        $impresora->setJustification(Printer::JUSTIFY_CENTER);
        $impresora->setTextSize(1, 1);
        $impresora->text("****************************************\n");
        $impresora->text("https://www.kfarma.com\n");
        $impresora->text("Gracias por su compra\n");
        $impresora->text("TIENE ACUMULADO: ".$ventaCliente->customer->points." FARMAPUNTOS\n");
        $impresora->text("****************************************\n");
        $impresora->feed(5);
        $impresora->close();
    }

    public function getAmountVoucher(): JsonResponse
    {
        $ticket = TicketInvoice::select('type_ticket_invoice_id', DB::raw('SUM(total) as total'))
            ->with('type')
            ->where('entity_id', $this->getEntity())
            ->where('cash_id', request('cash_id'))
            ->groupBy('type_ticket_invoice_id')
            ->get();

        return response()->json([
            "data" => $ticket
        ]);
    }

    public function getAmountPayment(): JsonResponse
    {
        $ticket = TicketInvoice::select('type_buy_id', DB::raw('SUM(total) as total'))
            ->with('buy')
            ->where('entity_id', $this->getEntity())
            ->where('cash_id', request('cash_id'))
            ->groupBy('type_buy_id')
            ->get();

        return response()->json([
            "data" => $ticket
        ]);
    }

    public function getUserState(): JsonResponse
    {
        $state = Cash::where('created_by', $this->getPersonId())
            ->where('state', 1)
            ->where('entity_id', $this->getEntity())
            ->count();

        return response()->json([
            "data" => $state
        ]);
    }
}
