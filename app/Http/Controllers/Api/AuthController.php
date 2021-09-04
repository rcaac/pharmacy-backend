<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AreaAssignment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login() {
        request()->validate([
            'name'        => 'required',
            'password'    => 'required',
            'entity_id'    => 'required',
            'device_name' => 'required'
        ]);

        $user = User::where('name', request()->name)->first();

        if (!$user || !Hash::check(request()->password, $user->password)) {
            throw ValidationException::withMessages([
                'name' => ['Credenciales incorrectas'],
            ]);
        }

        return $user->createToken(request()->device_name)->plainTextToken;
    }

    public function getUser(): JsonResponse
    {
        $assignment = AreaAssignment::where('person_id', auth()->user()->person->id)
            ->with(['role', 'area.entity', 'person'])
            ->get();

        return response()->json([
            "assignment" => $assignment,
        ]);
    }

    public function logout(): JsonResponse
    {
        $user = auth()->user();
        foreach ($user->tokens as $token) {
            $token->delete();
        }
        return response()->json('User logout...', 200);
    }

    public function entities(): JsonResponse
    {
        $search = request('search');

        $areas = AreaAssignment::with(['area.entity', 'person'])
            ->whereHas('person' , function ($query) use ($search) {
                $query->where('dni', 'LIKE', '%' . $search . '%');
            })->get();

        return response()->json(
            [
            'areas' => $areas
            ]
        );
    }
}
