<?php

namespace App\Http\Controllers;

use App\Models\Tips;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;

class TipsController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {

            $query = Tips::select('*');

            $category = $request->get('category');
            if ($category) {
                $query->where('category', '=', $category);
            }

            $list = $request->get('list');
            $listBoolean = filter_var($list, FILTER_VALIDATE_BOOLEAN);
    
            $action = $listBoolean ? 'get' : 'paginate';
            $tips = $query->$action();

            return $this->sendResponse(true, 'Listado obtenido exitosamente', $tips, 200);

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
        $title = $request->get('title');
        $description = $request->get('description');
        $active = $request->get('active');

        $tip = new Tips();
        $tip->title = $title;
        $tip->description = $description;
        $tip->active = $active ? $active : 1;

        if ($tip->save()) {

            if ($request->hasFile('image')) {
            
            }

            return $this->sendResponse(true, 'El consejo ha sido registrado correctamente', $tip, 201);
        }

        return $this->sendResponse(false, 'Ha ocurrido un problema al intentar registrar el consejo', null, 500);
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
        $title = $request->get('title');
        $description = $request->get('description');
        $active = $request->get('active');

        $tip = Tips::find($id);
        $tip->title = $title;
        $tip->description = $description;
        $tip->active = $active ? $active : 1;

        if ($tip->save()) {

            if ($request->hasFile('image')) {
            
            }

            return $this->sendResponse(true, 'El consejo ha sido actualizado correctamente', $tip, 200);
        }

        return $this->sendResponse(false, 'Ha ocurrido un problema al intentar actualizar el consejo', null, 500);
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
