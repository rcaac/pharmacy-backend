<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\EntityType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EntityController extends Controller
{
    private function getPersonId()
    {
        return auth()->user()->person->id;
    }

    public function filterEntities( int $id): JsonResponse
    {
        $filters = Entity::select('id AS entity_id', 'parent_id', 'name', 'direction', 'ruc', 'entity_type_id')
            ->with(['entityType', 'parent'])
            ->where('id', $id)->paginate(10);
        return response()->json([
            "data" => $filters
        ]);
    }

    public function listEntities(): JsonResponse
    {
        $queries = Entity::with('parent')->get();
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
                'entity'    => $data
            ]
        );
    }

    public function index(): JsonResponse
    {
        if (request()->wantsJson()) {
            $itemsPerPage = (int) request('itemsPerPage');
            $entities = Entity::filtered();
            return response()->json(
                [
                    "success" => true,
                    "data" => $entities->paginate($itemsPerPage != 'undefined' ? $itemsPerPage : 10),
                    'type' => EntityType::select('id', 'name')->get(),
                    'entity' => Entity::select('id', 'name')->get()
                ]
            );
        }
        abort(401);
    }

    private function validation($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'name'           => 'required',
            'direction'      => 'required',
            'ruc'            => ['required', 'max:11', Rule::unique('entities')],
            'entity_type_id' => 'required|integer|not_in:0'
        ],
            [
                'name.required'           => 'El nombre es obligatorio',
                'direction.required'      => 'La dirección es obligatorio',
                'ruc.required'            => 'El ruc es obligatorio',
                'ruc.numeric'             => 'El ruc debe de contener caracteres numéricos',
                'ruc.max'                 => 'El ruc debe de contener 11 dígitos exactamente',
                'ruc.unique'              => 'El ruc ya existe',
                'entity_type_id.not_in'   => 'Debe de elegir un tipo de entidad'
            ]
        );
    }

    private function validationUpdate($request, $entity): \Illuminate\Contracts\Validation\Validator
    {

        return Validator::make($request, [
            'name'           => 'required',
            'direction'      => 'required',
            'ruc'            => ['required', 'max:11', Rule::unique('entities')->ignore($entity)],
            'entity_type_id' => 'required|integer|not_in:0'
        ],
            [
                'name.required'           => 'El nombre es obligatorio',
                'direction.required'      => 'La dirección es obligatorio',
                'ruc.required'            => 'El ruc es obligatorio',
                'ruc.numeric'             => 'El ruc debe de contener caracteres numéricos',
                'ruc.max'                 => 'El ruc debe de contener 11 dígitos exactamente',
                'ruc.unique'              => 'El ruc ya existe',
                'entity_type_id.not_in'   => 'Debe de elegir un tipo de entidad'
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
            $entity = Entity::create([
                'name'           => request('name'),
                'direction'      => request('direction'),
                'ruc'            => request('ruc'),
                'parent_id'      => request('parent_id'),
                'created_by'     => $this->getPersonId(),
                'condition'      => '1',
                'entity_type_id' => request('entity_type_id')
            ]);

        }else {
            $entity = Entity::create([
                'name'           => request('name'),
                'direction'      => request('direction'),
                'ruc'            => request('ruc'),
                'parent_id'      => null,
                'created_by'     => $this->getPersonId(),
                'condition'      => '1',
                'entity_type_id' => request('entity_type_id')
            ]);
        }

        return response()->json(compact('entity'),201);
    }

    public function update(Entity $entity): JsonResponse
    {
        $entidad = Entity::findOrFail($entity->id);
        if (!$entidad) {
            return response()->json(["message" => "Entidad no encontrada"], 404);
        }
        $this->validationUpdate(request()->input(), $entity);

        if ($this->validationUpdate(request()->input(), $entity)->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $this->validationUpdate(request()->input(), $entity)->getMessageBag()->toArray()
            ), 422);
        }

        $entidad->fill([
            'name'           => request('name'),
            'direction'      => request('direction'),
            'ruc'            => request('ruc'),
            'parent_id'      => request('parent_id'),
            'created_by'     => $this->getPersonId(),
            'condition'      => '1',
            'entity_type_id' => request('entity_type_id')
        ])->save();

        return response()->json(compact('entidad'),201);
    }

    public function destroy(int $id): JsonResponse
    {
        $entity = Entity::findOrFail($id);
        if (!$entity) {
            return response()->json(["message" => "Entidad no encontrada"], 404);
        }
        $entity->fill(['condition' => '0' ])->save();
        return response()->json(["message" => "Entidad eliminada"]);
    }
}
