<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AreaAssignment;
use App\Models\DetailInvoicePurchase;
use App\Models\Kardex;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Wastage;
use App\Models\WastageDetail;
use App\Models\WastageReason;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WastageController extends Controller
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
            });
        }else if (strpos($search, '/') !== false) {
            $replace = str_replace("/", " ", $search);
            $result = ltrim($replace);
            $products = $products->whereHas('category', function($query) use ($result) {
                $query->where("name", "LIKE","%$result%");
            });
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

    public function listReason(): JsonResponse
    {
        $reasons = WastageReason::select('id', 'name')->get();

        return response()->json(
            [
                "success"  => true,
                "data"     => $reasons
            ]
        );
    }

    public function index() {
        if (request()->wantsJson()) {
            $itemsPerPage = (int) request('itemsPerPage');
            $assignments = WastageDetail::filtered();
            return response()->json(
                [
                    "success"  => true,
                    "data"     => $assignments->paginate($itemsPerPage != 'undefined' ? $itemsPerPage : 10),
                ]
            );
        }
        abort(401);
    }

    public function searchDate()
    {
        if (request()->wantsJson()) {
            $itemsPerPage = (int) request('itemsPerPage');
            $assignments = WastageDetail::filtered();
            return response()->json(
                [
                    "success"  => true,
                    "data"     => $assignments->paginate($itemsPerPage != 'undefined' ? $itemsPerPage : 10),
                ]
            );
        }
        abort(401);
    }

    public function searchReason()
    {
        if (request()->wantsJson()) {
            $itemsPerPage = (int) request('itemsPerPage');
            $assignments = WastageDetail::filtered();
            return response()->json(
                [
                    "success"  => true,
                    "data"     => $assignments->paginate($itemsPerPage != 'undefined' ? $itemsPerPage : 10),
                ]
            );
        }
        abort(401);
    }

    private function validation($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'wastage_reason_id' => 'required',
        ],
            [
                'wastage_reason_id.required' => 'Debe de elegir un motivo',
            ]
        );
    }

    public function store(): JsonResponse
    {
        $wastages = request('wastages');

        try{
            DB::beginTransaction();

            $this->validation(request()->input());

            if ($this->validation(request()->input())->fails()) {
                return response()->json(array(
                    'success' => false,
                    'errors'  => $this->validation(request()->input())->getMessageBag()->toArray()
                ), 422);
            }

            $wastage_created = Wastage::create([
                'total'             => request('total'),
                'date'              => Carbon::now(),
                'created_by'        => $this->getPersonId(),
                'condition'         => '1',
                'entity_id'         => $this->getEntity(),
                'wastage_reason_id' => request('wastage_reason_id')
            ]);

            $first = 0;
            $last = 0;

            foreach($wastages as $wastage) {

                if (strpos($wastage['quantity'], 'F') !== false) {
                    $quantities = explode("F", $wastage['quantity']);

                    for ($i = 0; $i < count($quantities); $i++) {
                        $first = $quantities[0];
                        $last  = $quantities[1];
                    }
                    $quantity = (int)$first * (int)$wastage['box_quantity'] + (int)$last;
                }else {
                    $quantity = (int)$wastage['quantity'];
                }

                $product_stock = ProductStock::where('product_id', $wastage['id'])
                    ->where('entity_id', $this->getEntity())
                    ->value('stock');

                if ($product_stock === null) {
                    $previousStock = 0;
                    $currentStock  = $previousStock - (int)$quantity;
                }else {
                    $previousStock = $product_stock;
                    $currentStock  = (int)$product_stock - (int)$quantity;
                }

                WastageDetail::create([
                    'quantity'                   => $quantity,
                    'cost_unit'                  => $wastage['sale_unit'],
                    'cost_total'                 => $quantity * $wastage['sale_unit'],
                    'lot'                        => $wastage['lot'],
                    'date_expiration'            => Carbon::now(),
                    'condition'                  => '1',
                    'entity_id'                  => $this->getEntity(),
                    'product_id'                 => $wastage['id'],
                    'wastage_id'                 => $wastage_created->id,
                    'detail_invoice_purchase_id' => $wastage['details'][0]['id']
                ]);

                Kardex::create([
                    'date'               => Carbon::now(),
                    'quantity'           => $quantity,
                    'previousStock'      => (int)$previousStock,
                    'currentStock'       => $currentStock,
                    'voucher'            => request('voucher'),
                    'product_id'         => $wastage['id'],
                    'area_assignment_id' => request('area_assignment_id'),
                    'movement_id'        => '5',
                    'entity_id'          => $this->getEntity()
                ]);

                $product_stock_id = ProductStock::where('product_id', $wastage['id'])->where('entity_id', $this->getEntity())->value('id');

                $search_product_stock = ProductStock::findOrFail($product_stock_id);
                $search_product_stock->fill([
                    'stock' => (int)$previousStock - $quantity
                ])->save();

                $current = DetailInvoicePurchase::where('id', $wastage['details'][0]['id'])->value('stock_quantity');

                $quantity_current = (int)$current - $quantity;

                $detail_invoice_purchase = DetailInvoicePurchase::findOrFail($wastage['details'][0]['id']);

                $detail_invoice_purchase->fill([
                    'stock_quantity' => $quantity_current < 0 ? '0' : $quantity_current
                ])->save();
            }

            DB::commit();

            return response()->json(
                [
                    "message"   => "Operación realizada con éxito"
                ],
                201);

        }catch(Exception $e){
            DB::rollBack();
            return response()->json($e->getMessage());
        }
    }

    public function reverse(): JsonResponse
    {
        $wastage = request('wastage');

        try{
            DB::beginTransaction();

            $first = 0;
            $last = 0;

            if (strpos($wastage['quantity'], 'F') !== false) {
                $quantities = explode("F", $wastage['quantity']);

                for ($i = 0; $i < count($quantities); $i++) {
                    $first = $quantities[0];
                    $last  = $quantities[1];
                }
                $quantity = (int)$first * (int)$wastage['box_quantity'] + (int)$last;
            }else {
                $quantity = (int)$wastage['quantity'];
            }

            $product_stock = ProductStock::where('product_id', $wastage['product_id'])
                ->where('entity_id', $this->getEntity())
                ->value('stock');

            if ($product_stock === null) {
                $previousStock = 0;
                $currentStock  = $previousStock + (int)$quantity;
            }else {
                $previousStock = $product_stock;
                $currentStock  = (int)$product_stock + (int)$quantity;
            }

            $decrease = Wastage::findOrFail($wastage['wastage']['id']);
            if (!$decrease) {
                return response()->json(["message" => "Merma no encontrada"], 404);
            }
            $decrease->fill(['condition' => '0' ])->save();

            $detail = WastageDetail::findOrFail($wastage['id']);

            if (!$detail) {
                return response()->json(["message" => "Detalle Merma no encontrada"], 404);
            }
            $detail->fill(['condition' => '0' ])->save();

            Kardex::create([
                'date'               => Carbon::now(),
                'quantity'           => $quantity,
                'previousStock'      => (int)$previousStock,
                'currentStock'       => $currentStock,
                'voucher'            => $wastage['details']['invoice']['number'],
                'product_id'         => $wastage['product_id'],
                'area_assignment_id' => request('area_assignment_id'),
                'movement_id'        => '5',
                'entity_id'          => $this->getEntity()
            ]);

            $product_stock_id = ProductStock::where('product_id', $wastage['product_id'])->where('entity_id', $this->getEntity())->value('id');

            $search_product_stock = ProductStock::findOrFail($product_stock_id);
            $search_product_stock->fill([
                'stock' => (int)$previousStock + $quantity
            ])->save();

            $current = DetailInvoicePurchase::where('id', $wastage['details']['id'])->value('stock_quantity');

            $quantity_current = (int)$current + $quantity;

            $detail_invoice_purchase = DetailInvoicePurchase::findOrFail($wastage['details']['id']);

            $detail_invoice_purchase->fill([
                'stock_quantity' => $quantity_current < 0 ? '0' : $quantity_current
            ])->save();

            DB::commit();

            return response()->json(
                [
                    "message"   => "Operación realizada con éxito"
                ],
                201);

        }catch(Exception $e){
            DB::rollBack();
            return response()->json($e->getMessage());
        }
    }

}
