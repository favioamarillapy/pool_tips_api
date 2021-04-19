<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use App\Models\User;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    public function signup(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string'
        ]);
    
        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de Validacion', $validator->errors(), 400);
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'status' => 1,
            'activation_token'  => Str::random(60),
            'password' => bcrypt($request->password)
        ]);

        $accessToken = $user->createToken('Personal Access Client')->accessToken;

        return $this->sendResponse(true, 'Usuario creado exitosamente', ['user' => $user, 'access_token' => $accessToken], 200);
    }

    public function signin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'Error de Validacion', $validator->errors(), 400);
        }

        $data = [
            'email' => $request->email,
            'password' => $request->password,
            'status' => 1
        ];

        if (auth()->attempt($data)) {
            $tokenResult = auth()->user()->createToken('Personal Access Client');
            $user = User::find($tokenResult->token->user_id);

            return $this->sendResponse(true, 'Acceso Concedido', ['user' => $user, 'access_token' => $tokenResult->accessToken], 200);
        }

        return $this->sendResponse(false, 'Acceso Denegado', null, 200);
    }
}
