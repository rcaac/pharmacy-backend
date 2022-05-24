<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\AreaAssignment;
use App\Models\AssignmentState;
use App\Models\Entity;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    private function getPersonId()
    {
        return auth()->user()->person->id;
    }

    public function filterAssignments( int $id): JsonResponse
    {
        $filters = AreaAssignment::select('id', 'role_id', 'area_id', 'person_id', 'assignment_state_id')
            ->with(['role', 'state', 'person', 'area.entity'])
            ->whereHas('area', function($query) use ($id) {
                $query->where('entity_id', $id);
            })
            ->where('condition', '1')
            ->paginate(10);

        return response()->json([
            "data" => $filters
        ]);
    }

    public function filterPersons(): JsonResponse
    {
        $search = request('search') ?? null;
        $filters = Person::select('id', 'dni', 'email', 'firstName', 'lastName', 'direction', 'phone')
            ->where('dni', $search)
            ->get();
        return response()->json([
            "data" => $filters
        ]);
    }

    public function filterPersonLegal(): JsonResponse
    {
        $search = request('search') ?? null;
        $filters = Person::select('id', 'dni', 'firstName', 'lastName', 'ruc', 'businessName', 'phone', 'direction', 'email')
            ->where('ruc', $search)
            ->get();
        return response()->json([
            "data" => $filters
        ]);
    }

    public function listAreas($id): JsonResponse
    {
        if ($id == 0) {
            return response()->json([
                'areas' => Area::select('id', 'name')->get(),
            ]);
        }else {
            return response()->json([
                'areas' => Area::select('id', 'name')->where('entity_id', $id)->get(),
            ]);
        }
    }

    public function index(): JsonResponse
    {
        if (request()->wantsJson()) {
            $itemsPerPage = (int) request('itemsPerPage');
            $assignments = AreaAssignment::filtered();
            return response()->json(
                [
                    "success"  => true,
                    "data"     => $assignments->paginate($itemsPerPage != 'undefined' ? $itemsPerPage : 10),
                    'entities' => Entity::select('id', 'name')->get(),
                    'roles'    => Role::select('id', 'description')->get(),
                    'states'   => AssignmentState::select('id', 'name')->get()
                ]
            );
        }
        abort(401);
    }

    private function validation($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'firstName'           => 'required',
            'lastName'            => 'required',
            'dni'                 => ['required', 'max:8'],
            'entity_id'           => 'required|integer|not_in:0',
            'area_id'             => 'required|integer|not_in:0',
            'role_id'             => 'required|integer|not_in:0',
            'assignment_state_id' => 'required|integer|not_in:0',
        ],
        [
            'firstName.required' => 'El nombre es obligatorio',
            'lastName.required'  => 'Debe de elegir un tipo de area',
            'dni.required'       => 'El dni es obligatorio',
            'dni.numeric'        => 'El dni debe de contener caracteres numéricos',
            'dni.max'            => 'El dni debe de contener 8 dígitos exactamente',
            'entity_id.not_in'   => 'Debe de elegir un tipo de entidad',
            'area_id.not_in'     => 'Debe de elegir una area',
            'role_id.not_in'     => 'Debe de elegir un rol'
        ]
        );
    }

    private function validationRule($request, $person): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'firstName'           => 'required',
            'lastName'            => 'required',
            'dni'                 => ['required', 'max:8', Rule::unique('persons')->ignore($person)],
            'entity_id'           => 'required|integer|not_in:0',
            'area_id'             => 'required|integer|not_in:0',
            'role_id'             => 'required|integer|not_in:0',
            'assignment_state_id' => 'required|integer|not_in:0',
        ],
        [
            'firstName.required'         => 'El nombre es obligatorio',
            'lastName.required'          => 'Debe de elegir un tipo de area',
            'dni.required'               => 'El dni es obligatorio',
            'dni.numeric'                => 'El dni debe de contener caracteres numéricos',
            'dni.max'                    => 'El dni debe de contener 8 dígitos exactamente',
            'dni.unique'                 => 'El dni ya existe',
            'entity_id.not_in'           => 'Debe de elegir un tipo de entidad',
            'area_id.not_in'             => 'Debe de elegir una area',
            'role_id.not_in'             => 'Debe de elegir un rol',
            'assignment_state_id.not_in' => 'Debe de elegir un rol'
        ]
        );
    }

    public function store(): JsonResponse
    {
        if (request()->filled('id')) {
            $person = Person::findOrFail(request('id'));

            $this->validationRule(request()->input(), $person);

            if ($this->validationRule(request()->input(), $person)->fails()) {
                return response()->json(array(
                    'success' => false,
                    'errors' => $this->validationRule(request()->input(), $person)->getMessageBag()->toArray()
                ), 422);
            }
        } else {
            $this->validation(request()->input());

            if ($this->validation(request()->input())->fails()) {
                return response()->json(array(
                    'success' => false,
                    'errors' => $this->validation(request()->input())->getMessageBag()->toArray()
                ), 422);
            }
        }

        try{
            DB::beginTransaction();

            if (request()->filled('id')) {
                AreaAssignment::create([
                    'role_id'             => request('role_id'),
                    'area_id'             => request('area_id'),
                    'person_id'           => request('id'),
                    'created_by'          => $this->getPersonId(),
                    'condition'           => '1',
                    'assignment_state_id' => request('assignment_state_id'),
                ]);
            }else {
                $person = Person::create([
                    'firstName'      => request('firstName'),
                    'lastName'       => request('lastName'),
                    'dni'            => request('dni'),
                    'ruc'            => request('dni'),//temporalmente
                    'phone'          => request('phone'),
                    'direction'      => request('direction'),
                    'businessName'   => request('businessName'),
                    'created_by'     => $this->getPersonId(),
                    'condition'      => '1',
                    'person_type_id' => '1'
                ]);

                User::create([
                    'name'       => request('dni'),
                    'password'   => Hash::make(request('password')),
                    'created_by' => $this->getPersonId(),
                    'condition'  => '1',
                    'person_id'  => $person->id
                ]);

                AreaAssignment::create([
                    'role_id'             => request('role_id'),
                    'area_id'             => request('area_id'),
                    'person_id'           => $person->id,
                    'created_by'          => $this->getPersonId(),
                    'condition'           => '1',
                    'assignment_state_id' => request('assignment_state_id'),
                ]);
            }

            DB::commit();

        }catch(\Exception $e){
            DB::rollBack();
            return response()->json($e->getMessage());
        }

        return response()->json(["message" => "Persona asignada"],201);
    }

    public function update(int $id): JsonResponse
    {
        $assignment = AreaAssignment::findOrFail($id);

        $person = Person::findOrFail($assignment->person_id);

        if (!$assignment) {
            return response()->json(["message" => "Area no encontrada"], 404);
        }
        $this->validationRule(request()->input(), $person);

        if ($this->validationRule(request()->input(), $person)->fails()) {
            return response()->json(array(
                'success' => false,
                'errors' => $this->validationRule(request()->input(), $person)->getMessageBag()->toArray()
            ), 422);
        }

        try{
            DB::beginTransaction();

            $person->fill([
                'firstName'      => request('firstName'),
                'lastName'       => request('lastName'),
                'dni'            => request('dni'),
                'ruc'            => request('dni'),//temporalmente
                'phone'          => request('phone'),
                'direction'      => request('direction'),
                'created_by'     => $this->getPersonId(),
                'condition'      => '1',
                'person_type_id' => '1'
            ])->save();

            $user_id = User::where('person_id', request('id'))->value('id');

            $user = User::findOrFail($user_id);

            $user->fill([
                'password'   => Hash::make(request('password')),
            ])->save();

            $assignment->fill([
                'role_id'             => request('role_id'),
                'area_id'             => request('area_id'),
                'person_id'           => request('person_id'),
                'created_by'          => $this->getPersonId(),
                'condition'           => '1',
                'assignment_state_id' => request('assignment_state_id'),
            ])->save();

            DB::commit();

        }catch(\Exception $e){
            DB::rollBack();
            return response()->json($e);
        }

        return response()->json(["message" => "Operación exitosa"],201);
    }

    public function destroy(int $id): JsonResponse
    {
        $assignment = AreaAssignment::findOrFail($id);

        $person = Person::findOrFail($assignment->person_id);

        if (!$assignment && !$person) {
            return response()->json(["message" => "Persona no encontrada"], 404);
        }
        $assignment->fill(['condition' => '0'])->save();
        $person->fill(['condition' => '0' ])->save();
        return response()->json(["message" => "Persona eliminada"]);
    }
}
