<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

use App\Models\Unit;

class AuthController extends Controller
{
    /**
     * @return Illuminate\Http\JsonResponse;
     */
    public function unauthorized() : JsonResponse
    {
        return response()->json([
            'error' => 'Não autorizado'
        ], 401);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function register(Request $request) : array
    {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|digits:11|unique:users,cpf',
            'password' => 'required',
            'password_confirm' => 'required|same:password'
        ]);

        if (! $validator->fails()) {
            $name = $request->input('name');
            $email = $request->input('email');
            $cpf = $request->input('cpf');
            $password = $request->input('password');

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $newUser = new User();
            $newUser->name = $name;
            $newUser->email = $email;
            $newUser->cpf = $cpf;
            $newUser->password = $hash;
            $newUser->save();

            $token = auth()->attempt([
                'cpf' => $cpf,
                'password' => $password
            ]);

            if (! $token) {
                $array['error'] = 'Ocorreu um erro.';
                return $array;
            }

            $array['token'] = $token;

            $user = auth()->user();
            $array['user'] = $user;

            $properties = Unit::select(['id', 'name'])->where('id_owner', $user['id'])->get();

            $array['user']['properties'] = $properties;

        } else {
            $array['error'] = $validator->errors()->first();
            return $array;
        }

        return $array;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function login(Request $request) : array
    {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (! $validator->fails()) {

            $email = $request->input('email');
            $password = $request->input('password');

            $token = auth()->attempt([
                'email' => $email,
                'password' => $password
            ]);

            if (! $token) {
                $array['error'] = 'E-mail e/ou senha inválido(s).';
                return $array;
            }

            $array['token'] = $token;

            $user = auth()->user();
            $array['user'] = $user;

            $properties = Unit::select(['id', 'name'])->where('id_owner', $user['id'])->get();

            $array['user']['properties'] = $properties;

        } else {

            $array['error'] = $validator->errors()->first();
            return $array;

        }

        return $array;
    }

    /**
     * @return array
     */
    public function validateToken() : array
    {
        $array = ['error' => ''];

        $user = auth()->user();
        $array['user'] = $user;

        $properties = Unit::select(['id', 'name'])->where('id_owner', $user['id'])->get();

        $array['user']['properties'] = $properties;

        return $array;
    }

    /**
     * @return array
     */
    public function logout() : array
    {
        $array = ['error' => ''];

        auth()->logout();

        return $array;
    }

}
