<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\AreaType;
use App\Models\Entity;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AreaController extends Controller
{
    private function getPersonId()
    {
        return auth()->user()->person->id;
    }

    public function filterAreas( int $id): JsonResponse
    {
        $filters = Area::select('id', 'name', 'parent_id', 'condition', 'area_type_id', 'entity_id')
            ->with(['entity', 'areaType', 'parent'])
            ->where('entity_id', $id)
            ->paginate(10);
        return response()->json([
            "data" => $filters
        ]);
    }

    public function listAreas(): JsonResponse
    {
        $queries = Area::with('parent')->get();
        $data= collect($queries)->transform(function($collection) {
            $collect = (object)$collection;
            if ($collect->parent == null) {
                return [
                    'id' => $collect->id,
                    'name' => $collect->name,
                    'parent' => ['id' => null ,'name' => 'NINGUNO']
                ];
            }
            return [
                'id' => $collect->id,
                'name' => $collect->name,
                'parent' => $collect->parent
            ];
        });

        return response()->json(
            [
                "success" => true,
                'area'    => $data
            ]
        );
    }

    public function index(): JsonResponse
    {
        if (request()->wantsJson()) {
            $itemsPerPage = (int) request('itemsPerPage');
            $areas = Area::filtered();
            return response()->json(
                [
                    "success" => true,
                    "data"    => $areas->paginate($itemsPerPage != 'undefined' ? $itemsPerPage : 10),
                    'type'    => AreaType::select('id', 'name')->get(),
                    'entity'  => Entity::select('id AS entity_id', 'name')->get()
                ]
            );
        }
        abort(401);
    }

    private function validation($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'name'         => 'required',
            'area_type_id' => 'required',
            'entity_id'    => 'required'
        ],
        [
            'name.required'         => 'El nombre es obligatorio',
            'area_type_id.required' => 'Debe de elegir un tipo de area',
            'entity_id.required'    => 'Debe de elegir una entidad'
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

        if (request()->filled('parent_id')) {
            $area = Area::create([
                'name'           => request('name'),
                'parent_id'      => request('parent_id'),
                'created_by'     => $this->getPersonId(),
                'condition'      => '1',
                'area_type_id'   => request('area_type_id'),
                'entity_id'      => request('entity_id')
            ]);

        }else {
            $area = Area::create([
                'name'           => request('name'),
                'parent_id'      => null,
                'created_by'     => $this->getPersonId(),
                'condition'      => '1',
                'area_type_id'   => request('area_type_id'),
                'entity_id'      => request('entity_id')
            ]);
        }

        return response()->json(compact('area'),201);
    }

    public function update(int $id): JsonResponse
    {
        $area = Area::findOrFail($id);
        if (!$area) {
            return response()->json(["message" => "Area no encontrada"], 404);
        }
        $this->validation(request()->input());

        if ($this->validation(request()->input())->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $this->validation(request()->input())->getMessageBag()->toArray()
            ), 422);
        }

        $area->fill([
            'name'           => request('name'),
            'parent_id'      => request('parent_id'),
            'created_by'     => $this->getPersonId(),
            'condition'      => '1',
            'area_type_id'   => request('area_type_id'),
            'entity_id'      => request('entity_id')
        ])->save();

        return response()->json(compact('area'),201);
    }

    public function destroy(int $id): JsonResponse
    {
        $area = Area::findOrFail($id);
        if (!$area) {
            return response()->json(["message" => "Area no encontrada"], 404);
        }
        $area->fill(['condition' => '0' ])->save();
        return response()->json(["message" => "Area eliminada"]);
    }
}
