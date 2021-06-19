<?php

namespace App\Http\Controllers;

use Validator;

use Carbon\Carbon;
use App\Models\User;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    public function update(Request $request, $id) {

        $user = $request->user();
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;
        $password_confirmation = $request->password_confirmation;

        if ($user) {
            $user->name = $name ? $name : $user->name;
            $user->email = $email ? $email : $user->email;

            if ($password && $password == $password_confirmation) {
                $user->password = bcrypt($password);
            }

            if ($user->save()) {
                $refreshToken = $user->createToken('Personal Access Token')->accessToken;

                $data = [
                    'user' => $user,
                    'refreshToken' => $refreshToken
                ];

                return $this->sendResponse(true, 'Usuario actualizado correctamente', $data, 200);
            }
        }

        return $this->sendResponse(false, 'No se encontro el usuario solicitado', null, 404);
    }

    public function uploadImage(Request $request, $id) {
        $image = $request->file('image');

        $user = $request->user();

        if ($image) {
            $image_name = time().'.'.$image->getClientOriginalExtension();
            Storage::disk('user')->put("$id/".$image_name, \File::get($image));
            $user->image = $image_name;

            if ($user->save()) {
                return $this->sendResponse(true, 'Imagen actualizada correctamente', $user, 200);
            }

            return $this->sendResponse(false, 'Ha ocurrido un problema al intentar actualizar la imagen del usuario', null, 500);
        }

        return $this->sendResponse(false, 'Debe subir una imagen', null, 400);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['success' => true, 'message' => 'Session finalizada correctamente']);
    }

}
