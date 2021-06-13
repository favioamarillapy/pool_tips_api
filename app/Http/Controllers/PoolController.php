<?php

namespace App\Http\Controllers;

use App\Models\Pool;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController as BaseController;

class PoolController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {

            $query = Pool::select('*');

            $user = $request->get('user');
            if ($user) {
                $query->where('user', '=', $user);
            } else if ($request->user()) {
                $user = $request->user();
                if ($user) {
                    $query->where('user', '=', $user->id);
                }
            }

            $list = $request->get('list');
            $listBoolean = filter_var($list, FILTER_VALIDATE_BOOLEAN);
    
            $action = $listBoolean ? 'get' : 'paginate';
            $pool = $query->$action();

            return $this->sendResponse(true, 'Listado obtenido exitosamente', $pool, 200);

        }

        return $this->sendResponse(false, 'Metodo no permitido', null, 400);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->get('user');
        $cloro = $request->get('cloro');
        $ph = $request->get('ph');

        $pool = new Pool();
        $pool->user = $user;
        $pool->cloro = $cloro;
        $pool->ph = $ph ? $ph : 1;

        if ($pool->save()) {

            return $this->sendResponse(true, 'Datos registrados correctamente', $pool, 201);
        }

        return $this->sendResponse(false, 'Ha ocurrido un problema al intentar registrar', null, 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = $request->get('user');
        $cloro = $request->get('cloro');
        $ph = $request->get('ph');

        $pool = Pool::find($id);
        $pool->user = $user;
        $pool->cloro = $cloro;
        $pool->ph = $ph ? $ph : 1;

        if ($pool->save()) {
            return $this->sendResponse(true, 'Datos actualizados correctamente', $pool, 200);
        }

        return $this->sendResponse(false, 'Ha ocurrido un problema al intentar actualizar', null, 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
