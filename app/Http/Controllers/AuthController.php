<?php

namespace App\Http\Controllers;

use Validator;

use Carbon\Carbon;
use App\Models\User;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController as BaseController;

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

        $tokenResult = $this->createToken($request);
        if (!$tokenResult) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = User::find($tokenResult->token->user_id);
        if ($user) {
            return $this->sendResponse(true, 'Acceso Concedido', ['user' => $user, 'access_token' => $tokenResult->accessToken], 200);
        }

        return $this->sendResponse(false, 'Acceso Denegado', null, 200);
    }

    function createToken(Request $request)
    {
        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return false;
        }

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }

        $token->save();

        return $tokenResult;
    }

    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json($user);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['success' => true, 'message' => 'Session finalizada correctamente']);
    }

}
