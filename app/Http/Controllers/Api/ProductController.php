<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\ActivePrinciple;
use App\Models\AreaAssignment;
use App\Models\DetailInvoicePurchase;
use App\Models\Kardex;
use App\Models\LabMark;
use App\Models\Location;
use App\Models\Presentation;
use App\Models\Product;
use App\Models\TherapeuticAction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private function getPersonId()
    {
        return auth()->user()->person->id;
    }

    public function getProducts($search): JsonResponse
    {
        $products = Product::select('id', 'name')
            ->where('name', 'LIKE', "%$search%")
            ->get();

        return response()->json(
            [
                "success" => true,
                'products'    => $products
            ]
        );
    }

    public function listLaboratories(): JsonResponse
    {
        $laboratories = LabMark::select('id', 'name')->orderBy('name', 'DESC')->get();

        return response()->json(
            [
                "success" => true,
                'laboratories'    => $laboratories
            ]
        );
    }

    public function listGenerics(): JsonResponse
    {
        $generics = ActivePrinciple::select('id', 'name')->orderBy('name', 'DESC')->get();

        return response()->json(
            [
                "success" => true,
                'generics'    => $generics
            ]
        );
    }

    public function listCategories(): JsonResponse
    {
        $categories = TherapeuticAction::select('id', 'name')->orderBy('name', 'DESC')->get();

        return response()->json(
            [
                "success" => true,
                'categories'    => $categories
            ]
        );
    }

    public function listPresentations(): JsonResponse
    {
        $presentations = Presentation::select('id', 'name')->orderBy('name', 'DESC')->get();

        return response()->json(
            [
                "success" => true,
                'presentations'    => $presentations
            ]
        );
    }

    public function listLocations(): JsonResponse
    {
        $locations = Location::select('id', 'name')->orderBy('name', 'DESC')->get();

        return response()->json(
            [
                "success" => true,
                'locations'    => $locations
            ]
        );
    }

    public function index(): JsonResponse
    {
        if (request()->wantsJson()) {

            $itemsPerPage = (int) request('itemsPerPage');
            $products = Product::filtered();

            return response()->json(
                [
                    "success"  => true,
                    "data"     => $products->paginate($itemsPerPage != 'undefined' ? $itemsPerPage : 10),
                    "products" => Product::select('id', 'name')->get()
                ]
            );
        }

        abort(401);
    }

    public function getExpired(): JsonResponse
    {
        $itemsPerPage = (int) request('itemsPerPage') ?? 10;
        $search = request('search1') ?? null;
        $date_now = Carbon::now()->format('Y-m-d');

        $details = DB::table('detail_invoice_purchases')->select(
            'products.name AS product',
            'lab_marks.name AS laboratory',
            'presentations.name AS presentation',
            'detail_invoice_purchases.expiration_date AS date',
            DB::raw("if(products.box_quantity>1, concat_ws('F',(detail_invoice_purchases.stock_quantity DIV products.box_quantity),(detail_invoice_purchases.stock_quantity MOD products.box_quantity)), detail_invoice_purchases.stock_quantity) AS stock"),
        )
            ->join('products', 'detail_invoice_purchases.product_id', '=', 'products.id')
            ->join('presentations', 'presentations.id', '=', 'products.presentation_id')
            ->join('lab_marks', 'lab_marks.id', '=', 'products.lab_mark_id')
            ->where('detail_invoice_purchases.expiration_date', '>', $date_now)
            ->where('detail_invoice_purchases.stock_quantity', '>', 0)
            ->where('products.control_expiration', '=', 1)
            ->where('products.name', 'LIKE', "%$search%")
            ->orderBy('detail_invoice_purchases.expiration_date')
            ->paginate($itemsPerPage ?? 10);


        return response()->json(
            [
                "success"  => true,
                "data"     => $details,
            ]
        );
    }

    public function getToExpire(): JsonResponse
    {
        $itemsPerPage = (int) request('itemsPerPage');
        $search = request('search2') ?? null;
        $date_now = Carbon::now()->format('Y-m-d');

        $details = DB::table('detail_invoice_purchases')->select(
            'products.name AS product',
            'lab_marks.name AS laboratory',
            'presentations.name AS presentation',
            'detail_invoice_purchases.expiration_date AS date',
            DB::raw("if(products.box_quantity>1, concat_ws('F',(detail_invoice_purchases.stock_quantity DIV products.box_quantity),(detail_invoice_purchases.stock_quantity MOD products.box_quantity)), detail_invoice_purchases.stock_quantity) AS stock"),
        )
            ->join('products', 'detail_invoice_purchases.product_id', '=', 'products.id')
            ->join('presentations', 'presentations.id', '=', 'products.presentation_id')
            ->join('lab_marks', 'lab_marks.id', '=', 'products.lab_mark_id')
            ->where('detail_invoice_purchases.expiration_date', '<', $date_now)
            ->where('detail_invoice_purchases.stock_quantity', '>', 0)
            ->where('products.control_expiration', '=', 1)
            ->where('products.name', 'LIKE', "%$search%")
            ->orderBy('detail_invoice_purchases.expiration_date')
            ->paginate($itemsPerPage ?? 10);

        return response()->json(
            [
                "success"  => true,
                "data"     => $details,
            ]
        );
    }

    private function validation($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'name'                  => 'required',
            'barcode'               => 'string|nullable|unique:products',
            'maximum_stock'         => 'required',
            'minimum_stock'         => 'required',
            'box_quantity'          => 'required',
            'blister_quantity'      => 'required',
            'presentation_sale'     => 'required',
            'buy_unit'              => 'required',
            'buy_blister'           => 'required',
            'buy_box'               => 'required',
            'sale_unit'             => 'required',
            'sale_blister'          => 'required',
            'sale_box'              => 'required',
            'minimum_sale_unit'     => 'required',
            'minimum_sale_blister'  => 'required',
            'minimum_sale_box'      => 'required',
            'control_expiration'    => 'required',
            'control_stock'         => 'required',
            'control_refrigeration' => 'required',
            'control_prescription'  => 'required',
        ],
        [
            'name.required'                  => 'Este campo es requerido',
            'barcode.unique'                 => 'El código debe ser único',
            'maximum_stock.required'         => 'Este campo es requerido',
            'minimum_stock.required'         => 'Este campo es requerido',
            'box_quantity.required'          => 'Este campo es requerido',
            'blister_quantity.required'      => 'Este campo es requerido',
            'presentation_sale.required'     => 'Este campo es requerido',
            'buy_unit.required'              => 'Este campo es requerido',
            'buy_blister.required'           => 'Este campo es requerido',
            'buy_box.required'               => 'Este campo es requerido',
            'sale_unit.required'             => 'Este campo es requerido',
            'sale_blister.required'          => 'Este campo es requerido',
            'sale_box.required'              => 'Este campo es requerido',
            'minimum_sale_unit.required'     => 'Este campo es requerido',
            'minimum_sale_blister.required'  => 'Este campo es requerido',
            'minimum_sale_box.required'      => 'Este campo es requerido',
            'control_expiration.required'    => 'Este campo es requerido',
            'control_stock.required'         => 'Este campo es requerido',
            'control_refrigeration.required' => 'Este campo es requerido',
            'control_prescription.required'  => 'Este campo es requerido',
        ]
        );
    }

    public function store(): JsonResponse
    {
        $this->validation(request()->input());

        if ($this->validation(request()->input())->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $this->validation(request()->input())->getMessageBag()->toArray()
            ), 422);
        }

        $product = Product::create([
            'barcode'               => request('barcode'),
            'name'                  => request('name'),
            'short_name'            => request('short_name'),
            'maximum_stock'         => request('maximum_stock'),
            'minimum_stock'         => request('minimum_stock'),
            'box_quantity'          => request('box_quantity'),
            'blister_quantity'      => request('blister_quantity'),
            'presentation_sale'     => request('presentation_sale'),
            'buy_unit'              => request('buy_unit'),
            'buy_blister'           => request('buy_blister'),
            'buy_box'               => request('buy_box'),
            'sale_unit'             => request('sale_unit'),
            'sale_blister'          => request('sale_blister'),
            'sale_box'              => request('sale_box'),
            'minimum_sale_unit'     => request('minimum_sale_unit'),
            'minimum_sale_blister'  => request('minimum_sale_blister'),
            'minimum_sale_box'      => request('minimum_sale_box'),
            'control_expiration'    => request('control_expiration'),
            'control_stock'         => request('control_stock'),
            'control_refrigeration' => request('control_refrigeration'),
            'control_prescription'  => request('control_prescription'),
            'lab_mark_id'           => request('lab_mark_id'),
            'active_principle_id'   => request('active_principle_id'),
            'therapeutic_action_id' => request('therapeutic_action_id'),
            'presentation_id'       => request('presentation_id'),
            'location_id'           => request('location_id'),
            'created_by'            => $this->getPersonId(),
            'condition'             => '1'
        ]);

        return response()->json(compact('product'),201);
    }

    public function update(ProductRequest $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        if (!$product) {
            return response()->json(["message" => "Area no encontrada"], 404);
        }

        $product->fill([
            'barcode'               => $request->input('barcode'),
            'name'                  => $request->input('name'),
            'short_name'            => $request->input('short_name'),
            'maximum_stock'         => $request->input('maximum_stock'),
            'minimum_stock'         => $request->input('minimum_stock'),
            'box_quantity'          => $request->input('box_quantity'),
            'blister_quantity'      => $request->input('blister_quantity'),
            'presentation_sale'     => $request->input('presentation_sale'),
            'buy_unit'              => $request->input('buy_unit'),
            'buy_blister'           => $request->input('buy_blister'),
            'buy_box'               => $request->input('buy_box'),
            'sale_unit'             => $request->input('sale_unit'),
            'sale_blister'          => $request->input('sale_blister'),
            'sale_box'              => $request->input('sale_box'),
            'minimum_sale_unit'     => $request->input('minimum_sale_unit'),
            'minimum_sale_blister'  => $request->input('minimum_sale_blister'),
            'minimum_sale_box'      => $request->input('minimum_sale_box'),
            'control_expiration'    => $request->input('control_expiration'),
            'control_stock'         => $request->input('control_stock'),
            'control_refrigeration' => $request->input('control_refrigeration'),
            'control_prescription'  => $request->input('control_prescription'),
            'lab_mark_id'           => $request->input('lab_mark_id'),
            'active_principle_id'   => $request->input('active_principle_id'),
            'therapeutic_action_id' => $request->input('therapeutic_action_id'),
            'presentation_id'       => $request->input('presentation_id'),
            'location_id'           => $request->input('location_id'),
            'created_by'            => $this->getPersonId(),
            'condition'             => '1'
        ])->save();

        return response()->json(compact('product'),201);
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        if (!$product) {
            return response()->json(["message" => "Persona no encontrada"], 404);
        }
        $product->fill(['condition' => '0' ])->save();
        return response()->json(["message" => "Producto eliminado"]);
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

    public function storeLaboratory(): JsonResponse
    {
        $this->validationLaboratories(request()->input());

        if ($this->validationLaboratories(request()->input())->fails()) {
            return response()->json(array(
                'success' => false,
                'lab_errors' => $this->validationLaboratories(request()->input())->getMessageBag()->toArray()
            ), 422);
        }

        $laboratory = LabMark::create([
            'name' => request('name')
        ]);

        return response()->json(compact('laboratory'),201);
    }

    public function storeGeneric(): JsonResponse
    {
        $this->validationLaboratories(request()->input());

        if ($this->validationLaboratories(request()->input())->fails()) {
            return response()->json(array(
                'success' => false,
                'lab_errors' => $this->validationLaboratories(request()->input())->getMessageBag()->toArray()
            ), 422);
        }

        $generic = ActivePrinciple::create([
            'name' => request('name')
        ]);

        return response()->json(compact('generic'),201);
    }

    public function storeCategory(): JsonResponse
    {
        $this->validationLaboratories(request()->input());

        if ($this->validationLaboratories(request()->input())->fails()) {
            return response()->json(array(
                'success' => false,
                'lab_errors' => $this->validationLaboratories(request()->input())->getMessageBag()->toArray()
            ), 422);
        }

        $category = TherapeuticAction::create([
            'name' => request('name')
        ]);

        return response()->json(compact('category'),201);
    }

    public function storePresentation(): JsonResponse
    {
        $this->validationLaboratories(request()->input());

        if ($this->validationLaboratories(request()->input())->fails()) {
            return response()->json(array(
                'success' => false,
                'lab_errors' => $this->validationLaboratories(request()->input())->getMessageBag()->toArray()
            ), 422);
        }

        $presentation = Presentation::create([
            'name' => request('name')
        ]);

        return response()->json(compact('presentation'),201);
    }

    public function storeLocation(): JsonResponse
    {
        $this->validationLaboratories(request()->input());

        if ($this->validationLaboratories(request()->input())->fails()) {
            return response()->json(array(
                'success' => false,
                'lab_errors' => $this->validationLaboratories(request()->input())->getMessageBag()->toArray()
            ), 422);
        }

        $location = Location::create([
            'name' => request('name')
        ]);

        return response()->json(compact('location'),201);
    }

    public function getKardex(): JsonResponse
    {
        $itemsPerPage = (int) request('itemsPerPage');

        $kardex = Kardex::select('date', 'quantity', 'previousStock', 'currentStock', 'voucher', 'product_id', 'area_assignment_id', 'movement_id', 'entity_id')
                    ->with(['product', 'movement', 'assigment.person', 'entity'])
                    ->orderBy('id', 'DESC')
                    ->paginate($itemsPerPage != 'undefined' ? $itemsPerPage : 10);

        return response()->json(
            [
                "success" => true,
                'data'    => $kardex
            ]
        );
    }

    public function fetchKardex(): JsonResponse
    {   $itemsPerPage = (int) request('itemsPerPage');
        $fetch= Kardex::select('date', 'quantity', 'previousStock', 'currentStock', 'voucher', 'product_id', 'area_assignment_id', 'movement_id', 'entity_id')
            ->with(['product', 'movement', 'assigment.person', 'entity'])
            ->where('product_id', request('product_id'))
            ->where('entity_id', request('entity_id'))
            ->orderBy('id', 'DESC')
            ->paginate($itemsPerPage != 'undefined' ? $itemsPerPage : 50);


        return response()->json(
            [
                "success" => true,
                'data'    => $fetch
            ]
        );
    }

    public function filterKardex(): JsonResponse
    {
        $filter = Product::select(
            'products.id as id',
            DB::raw('concat(products.name," :: ",lab_marks.name) as name'),
        )
            ->join('lab_marks', 'products.lab_mark_id', '=', 'lab_marks.id')
            ->where('condition', 1)
            ->get();

        return response()->json(
            [
                "success" => true,
                'data'    => $filter
            ]
        );
    }
}
