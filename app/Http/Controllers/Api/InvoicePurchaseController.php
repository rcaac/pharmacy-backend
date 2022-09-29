<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AreaAssignment;
use App\Models\DetailInvoicePurchase;
use App\Models\InvoicePurchase;
use App\Models\Kardex;
use App\Models\Person;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StateInvoicePurchase;
use App\Models\Supplier;
use App\Models\TypeInvoicePurchase;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoicePurchaseController extends Controller
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

    private function getAreaAssignment()
    {
        return AreaAssignment::where('person_id', $this->getPersonId())->value('id');
    }

    public function listTypeInvoicePurchases(): JsonResponse
    {
        $list = TypeInvoicePurchase::select('id', 'description')->orderBy('description', 'DESC')->get();

        return response()->json(
            [
                "success"       => true,
                'listPurchases' => $list
            ]
        );
    }

    public function listStateInvoicePurchases(): JsonResponse
    {
        $list = StateInvoicePurchase::select('id', 'description')->orderBy('description', 'DESC')->get();

        return response()->json(
            [
                "success" => true,
                'listStates'    => $list
            ]
        );
    }

    public function listProducts($search): JsonResponse
    {
        $entity = $this->getEntity();
        $products = Product::query();
        $products = $products->select(
            'id',
            'barcode',
            'name',
            'short_name',
            'maximum_stock',
            'minimum_stock',
            'box_quantity',
            'blister_quantity',
            'presentation_sale',
            'buy_unit',
            'buy_blister',
            'buy_box',
            'sale_unit',
            'sale_blister',
            'sale_box',
            'minimum_sale_unit',
            'minimum_sale_blister',
            'minimum_sale_box',
            'control_expiration',
            'control_stock',
            'control_refrigeration',
            'control_prescription',
            'lab_mark_id',
            'active_principle_id',
            'therapeutic_action_id',
            'presentation_id',
            'location_id',
            'created_at'
        )
        ->with([
            'laboratory',
            'generic',
            'category',
            'presentation',
            'location',
            'stock' => function($query) use ($entity){
                $query->where('entity_id', $entity);
            },
            'details' => function($query) use ($entity){
                $query->where('stock_quantity', '>', 0);
                $query->where('entity_id', $entity);
            }
        ])
        ->where('condition', '1');

        if (strpos($search, '*') !== false) {
            $replace = str_replace("*", " ", $search);
            $result = ltrim($replace);
            $products = $products->whereHas('generic', function($query) use ($result) {
                $query->where("name", "LIKE","%$result%");
            })->get();
        }else if (strpos($search, '/') !== false) {
            $replace = str_replace("/", " ", $search);
            $result = ltrim($replace);
            $products = $products->whereHas('category', function($query) use ($result) {
                $query->where("name", "LIKE","%$result%");
            })->get();
        }else {
            $products = $products->where('name', 'LIKE', "%$search%")->orderBy('name')->get();
        }

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
            $invoices = InvoicePurchase::filtered();
            return response()->json(
                [
                    "success"  => true,
                    "data"     => $invoices->where('entity_id', $this->getEntity())->paginate($itemsPerPage != 'undefined' ? $itemsPerPage : 10)
                ]
            );
        }
        abort(401);
    }

    public function listProductBarcode($search): JsonResponse
    {
        $entity = $this->getEntity();
        $products = Product::with([
            'laboratory',
            'generic',
            'category',
            'presentation',
            'location',
            'stock' => function($query) use ($entity){
                $query->where('entity_id', $entity);
            },
            'details' => function($query) use ($entity){
                $query->where('stock_quantity', '>', 0);
                $query->where('entity_id', $entity);
            }
        ])
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

    private function validation($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
                'ruc'                      => 'required',
                'number'                   => 'required',
            ],
            [
                'ruc.required'                      => 'El ruc es obligatorio',
                'number.required'                   => 'El número es obligatorio',
            ]
        );
    }

    public function store(): JsonResponse
    {
        $this->validation(request()->input());

        if ($this->validation(request()->input())->fails()) {
            return response()->json(array(
                'success' => false,
                'errors'  => $this->validation(request()->input())->getMessageBag()->toArray()
            ), 422);
        }
        $purchases = request('purchases');

        try{
            DB::beginTransaction();

            $resultPerson = Person::where('ruc', request('ruc'))->get();

            if (count($resultPerson)) {
                $id = '';
               foreach ($resultPerson as $data) {
                   $id = $data->id;
               }
                $supplier = Supplier::create([
                    'description' => request('supplier'),
                    'person_id'   => $id
                ]);
            }else {
                $person = Person::create([
                    'ruc'            => request('ruc'),
                    'businessName'   => request('businessName'),
                    'phone'          => request('phone'),
                    'email'          => request('email'),
                    'created_by'     => $this->getPersonId(),
                    'condition'      => '1',
                    'person_type_id' => '1'
                ]);
                $supplier = Supplier::create([
                    'description' => request('supplier'),
                    'person_id'   => $person->id
                ]);
            }

            $invoice_purchase = InvoicePurchase::create([
                'number'                    => request('number'),
                'date'                      => request('date'),
                'subtotal'                  => request('subtotal'),
                'total'                     => request('total'),
                'created_by'                => $this->getPersonId(),
                'condition'                 => '1',
                'supplier_id'               => $supplier->id,
                'state_invoice_purchase_id' => '1',
                'type_invoice_purchase_id'  => request('type_invoice_purchase_id'),
                'entity_id'                 => $this->getEntity(),
            ]);

            $first = 0;
            $last = 0;

            foreach($purchases as $purchase) {

                if (strpos($purchase['quantity'], 'F') !== false) {
                    $quantities = explode("F", $purchase['quantity']);

                    for ($i = 0; $i < count($quantities); $i++) {
                        $first = $quantities[0];
                        $last = $quantities[1];
                    }
                    $quantity = (int)$first * (int)$purchase['box_quantity'] + (int)$last;
                }else {
                    $quantity = (int)$purchase['quantity'];
                }

                $product = Product::findOrFail($purchase['id']);

                $product->fill([
                    'buy_unit'             => $purchase['buy_unit'],
                    'buy_blister'          => $purchase['buy_blister'],
                    'buy_box'              => $purchase['buy_box'],
                    'sale_unit'            => $purchase['sale_unit'],
                    'sale_blister'         => $purchase['sale_blister'],
                    'sale_box'             => $purchase['sale_box'],
                    'minimum_sale_unit'    => $purchase['minimum_sale_unit'],
                    'minimum_sale_blister' => $purchase['minimum_sale_blister'],
                    'minimum_sale_box'     => $purchase['minimum_sale_box'],
                ])->save();

                $product_stock = ProductStock::where('product_id', $purchase['id'])
                    ->where('entity_id', request('entity_id'))
                    ->value('stock');

                if ($product_stock === null) {
                    $previousStock = 0;
                    $currentStock = $previousStock + (int)$quantity;
                }else {
                    $previousStock = $product_stock;
                    $currentStock = (int)$product_stock + (int)$quantity;
                }

                Kardex::create([
                    'date'               => Carbon::now(),
                    'quantity'           => $quantity,
                    'previousStock'      => (int)$previousStock,
                    'currentStock'       => $currentStock,
                    'voucher'            => request('number'),
                    'product_id'         => $purchase['id'],
                    'area_assignment_id' => request('area_assignment_id'),
                    'movement_id'        => '1',
                    'entity_id'          => $this->getEntity(),
                ]);

                $product_stock_id = ProductStock::where('product_id', $purchase['id'])->where('entity_id', request('entity_id'))->value('id');

                if ($product_stock_id === null) {
                    ProductStock::create([
                        'stock'      => (int)$previousStock + $quantity,
                        'entity_id'  => $this->getEntity(),
                        'product_id' => $purchase['id']
                    ]);
                }else {
                    $search_product_stock = ProductStock::findOrFail($product_stock_id);
                    $search_product_stock->fill([
                        'stock' => (int)$previousStock + $quantity
                    ])->save();
                }

                DetailInvoicePurchase::create([
                    'lot'                  => $purchase['lot'],
                    'expiration_date'      => $purchase['control_expiration'] == 0 ? null : Carbon::parse($purchase['expiration_date'])->endOfMonth(),
                    'quantity'             => $quantity,
                    'stock_quantity'       => $quantity,
                    'buy_unit'             => $purchase['buy_unit'],
                    'sale_unit'            => $purchase['sale_unit'],
                    'sale_blister'         => $purchase['sale_blister'],
                    'sale_box'             => $purchase['sale_box'],
                    'minimum_sale_unit'    => $purchase['minimum_sale_unit'],
                    'minimum_sale_blister' => $purchase['minimum_sale_blister'],
                    'minimum_sale_box'     => $purchase['minimum_sale_box'],
                    'total'               => $purchase['total'],
                    'created_by'          => $this->getPersonId(),
                    'condition'           => '1',
                    'product_id'          => $purchase['id'],
                    'invoice_purchase_id' => $invoice_purchase->id,
                    'entity_id'           => $this->getEntity(),
                ]);
            }

            DB::commit();

            return response()->json(["message" => "Operación realizada con éxito"],201);

        }catch(\Exception $e){
            DB::rollBack();
            return response()->json($e->getMessage());
        }
    }

    public function update(int $id): JsonResponse
    {
        $invoice_purchase = InvoicePurchase::findOrFail($id);

        $person = Person::findOrFail(request('person_id'));

        $supplier = Supplier::findOrFail(request('supplier_id'));

        $purchases = request('purchases');

        if (!$invoice_purchase) {
            return response()->json(["message" => "Area no encontrada"], 404);
        }
        $this->validation(request()->input());

        if ($this->validation(request()->input())->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $this->validation(request()->input())->getMessageBag()->toArray()
            ), 422);
        }

        try{
            DB::beginTransaction();

            $person->fill([
                'ruc'             => request('ruc'),
                'businessName'    => request('businessName'),
                'phone'           => request('phone'),
                'email'           => request('email'),
                'created_by'      => $this->getPersonId(),
                'condition'       => '1',
                'person_type_id'  => '1'
            ])->save();

            $supplier->fill([
                'description' => request('supplier'),
                'person_id'   => $person->id
            ])->save();

            DB::commit();

        }catch(\Exception $e){
            DB::rollBack();
            return response()->json($e->getMessage());
        }

        return response()->json(["message" => "Operación realizada con éxito"],201);
    }

    public function itemUpdate(int $id): JsonResponse
    {
        $invoice_purchase = InvoicePurchase::findOrFail($id);

        $supplier = Supplier::findOrFail(request('supplier_id'));

        $purchase = request('purchase');

        try{
            DB::beginTransaction();

            $quantity = DetailInvoicePurchase::where('id', $purchase['id'])->value('quantity');

            $quantity_stock = DetailInvoicePurchase::where('id', $purchase['id'])->value('stock_quantity');

            if ($quantity == $quantity_stock) {
                $invoice_purchase->fill([
                    'number'                    => request('number'),

                    'date'                      => Carbon::parse($invoice_purchase['date']),
                    'subtotal'                  => request('subtotal'),
                    'total'                     => request('total'),
                    'created_by'                => $this->getPersonId(),
                    'condition'                 => '1',
                    'supplier_id'               => $supplier->id,
                    'state_invoice_purchase_id' => '1',
                    'type_invoice_purchase_id'  => request('type_invoice_purchase_id')
                ])->save();

                $detail_invoice_purchase = DetailInvoicePurchase::findOrFail($purchase['id']);

                if (strpos($purchase['quantity'], 'F') !== false) {
                    $quantities = explode("F", $purchase['quantity']);

                    for ($i = 0; $i < count($quantities); $i++) {
                        $first = $quantities[0];
                        $last = $quantities[1];
                    }
                    $quantity = (int)$first * (int)$purchase['product']['box_quantity'] + (int)$last;
                }else {
                    $quantity = (int)$purchase['quantity'];
                }

                $detail_invoice_purchase->fill([
                    'lot'                 => $purchase['lot'],
                    'expiration_date'     => Carbon::parse($purchase['expiration_date'])->endOfMonth(),
                    'quantity'            => $quantity,
                    'stock_quantity'      => $quantity,
                    'buy_unit'            => $purchase['buy_unit'],
                    'sale_unit'           => $purchase['sale_unit'],
                    'total'               => $purchase['total'],
                    'created_by'          => $this->getPersonId(),
                    'condition'           => '1',
                    'invoice_purchase_id' => $invoice_purchase->id,
                    'entity_id'           => request('entity_id'),
                ])->save();

                $previousStock = ProductStock::where('product_id', $purchase['product_id'])
                    ->where('entity_id', $this->getEntity())
                    ->value('stock');

                $stock = DetailInvoicePurchase::where('product_id', $purchase['product_id'])
                    ->where('condition', 1)
                    ->where('stock_quantity', '>', 0)
                    ->where('entity_id', $this->getEntity())
                    ->value(DB::raw('SUM(stock_quantity)'));

                Kardex::create([
                    'date'               => request('date'),
                    'quantity'           => $quantity,
                    'previousStock'      => (int)$previousStock,
                    'currentStock'       => (int)$stock,
                    'voucher'            => request('number'),
                    'product_id'         => $purchase['id'],
                    'area_assignment_id' => request('area_assignment_id'),
                    'movement_id'        => '3',
                    'entity_id'          => request('entity_id'),
                ]);

                $product_stock_id = ProductStock::where('product_id', $purchase['product_id'])
                    ->where('entity_id', $this->getEntity())
                    ->value('id');

                $product_stock = ProductStock::findOrFail($product_stock_id);

                $product_stock->fill([
                    'stock'      => $stock,
                    'entity_id'  => $this->getEntity(),
                    'product_id' => $purchase['product_id']
                ])->save();
            }else {
                return response()->json(["message" => "Este producto ya ha sido vendido"],200);
            }

            DB::commit();

            return response()->json(["message" => "Operación realizada con éxito"],201);

        }catch(\Exception $e){
            DB::rollBack();
            return response()->json($e->getMessage());
        }
    }

    public function destroy(): JsonResponse
    {
        $detail = DetailInvoicePurchase::findOrFail(request('id'));
        $product_stock_id = ProductStock::where('product_id', request('product_id'))->value('id');
        $product_stock = ProductStock::findOrFail($product_stock_id );

        $invoice_purchase_id = DetailInvoicePurchase::where('id',request('id'))->value('invoice_purchase_id');
        $invoice_purchase = InvoicePurchase::findOrFail($invoice_purchase_id );

        $costoModificado = InvoicePurchase::select(
            DB::raw('invoice_purchases.total-detail_invoice_purchases.total as totalmodificado')
        )
            ->join('detail_invoice_purchases','invoice_purchases.id','=','detail_invoice_purchases.invoice_purchase_id' )
            ->where('detail_invoice_purchases.id',request('id'))
            ->value('totalmodificado');

        if (!$detail) {
            return response()->json(["message" => "Producto no encontrado"], 404);
        }

        $quantity = DetailInvoicePurchase::where('id', request('id'))->value('quantity');
        $stock_quantity = ProductStock::where('product_id', request('product_id'))->value('stock');

        if ($quantity > $stock_quantity) {
            return response()->json(
                [
                    "message" => "No se puede anular",
                    "code" => 0
                ]
            );
        }else {

            $invoice_purchase->fill([
                'total' => $costoModificado,
                'subtotal' => $costoModificado,
            ])->save();


            $detail->fill([
                'condition' => '0',
                'stock_quantity'=>'0'
            ])->save();

            $product_stock->fill([
                'stock' => (int)$stock_quantity - (int)$quantity,
            ])->save();

            $voucher = InvoicePurchase::select(
                DB::raw("number"),
            )
                ->where('id', $invoice_purchase_id)
                ->value('number');

            Kardex::create([
                'date'               => Carbon::now(),
                'quantity'           => (int)$quantity,
                'previousStock'      => (int)$stock_quantity,
                'currentStock'       => (int)$stock_quantity - (int)$quantity,
                'voucher'            => $voucher,
                'product_id'         => request('product_id'),
                'area_assignment_id' => $this->getAreaAssignment(),
                'movement_id'        => '3',
                'entity_id'          => $this->getEntity(),
            ]);

            return response()->json(
                [
                    "message" => "Producto eliminado",
                    "code" => 1
                ]
            );
        }
    }

    private function getIdInvoicePurchase($id) {
        return DetailInvoicePurchase::select('id')->where('invoice_purchase_id', $id)->get();
    }

    public function destroyInvoicePurchase() {

        $details = request('details');

        $idDetails = $this->getIdInvoicePurchase(request('id'));

        foreach ($idDetails as $idDetail) {
            $detail = DetailInvoicePurchase::findOrFail($idDetail->id);
            $detail->fill(['condition' => '0' ])->save();
        }

        $ticket_invoice = InvoicePurchase::findOrFail(request('id'));
        $ticket_invoice->fill([
            'state_invoice_purchase_id'   => '2'
        ])->save();

        $product_stock_id = ProductStock::where('product_id', request('product_id'))->where('entity_id', $this->getEntity())->value('id');

        foreach($details as $detail) {
            $quantity = DetailInvoicePurchase::where('id', $detail['id'])->value('quantity');
            $stock_quantity = ProductStock::where('product_id', request('product_id'))->value('stock');
        }

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
}
