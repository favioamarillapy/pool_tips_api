<?php

namespace App\Http\Controllers;

use App\Models\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Storage;

class CategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = Category::select('*')->withCount(['tips']);
            
            $list = $request->get('list');
            $listBoolean = filter_var($list, FILTER_VALIDATE_BOOLEAN);
            
            $action = $listBoolean ? 'get' : 'paginate';
            $categories = $query->$action();
            
            return $this->sendResponse(true, 'Listado obtenido exitosamente', $categories, 200);
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
        $name = $request->get('name');
        $image = $request->file('file');

        $category = new Category();
        $category->name = $name;
        if ($image) {
            $image_name = time().'.'.$image->getClientOriginalExtension();
            Storage::disk('category')->put($image_name, \File::get($image));
            $category->image = $image_name;
        }

        if ($category->save()) {
            return $this->sendResponse(true, 'La categoria ha sido registrada correctamente', $category, 201);
        }

        return $this->sendResponse(false, 'Ha ocurrido un problema al intentar registrar la categoria', null, 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::find($id);

        if ($category) {
            return $this->sendResponse(true, 'Registro encontrado', $category, 200);
        } else {
            return $this->sendResponse(true, 'La categoria no existe', $category, 200);
        }
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
        return response()->json($request->all());
        $name = $request->get('name');
        $image = $request->file('file');

        $category = Category::find($id);
        $category->name = $name;

        if ($image) {
            $image_name = time().'.'.$image->getClientOriginalExtension();
            Storage::disk('category')->put($image_name, \File::get($image));
            $category->image = $image_name;
        }


        if ($category->save()) {
            return $this->sendResponse(true, 'La categoria ha sido actualizada correctamente', $category, 200);
        }

        return $this->sendResponse(false, 'Ha ocurrido un problema al intentar actualizar la categoria', null, 500);
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

    public function file($filename)
    {
        $isset = Storage::disk('category')->exists($filename);
        if ($isset) {
            $file = Storage::disk('category')->get($filename);
            return new Response($file);
        }
        
        return $this->sendResponse(false, 'La imagen no existe', null, 200);
    }
}
