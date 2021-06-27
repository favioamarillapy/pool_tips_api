<?php

namespace App\Http\Controllers;

use App\Models\Pool;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            } elseif ($request->user()) {
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
    }

    public function graphicDay()
    {
        $today = date("Y-m-d");

        $query = Pool::select(DB::raw('coalesce(sum(ph) / count(id)) as ph,  coalesce(sum(temperature) / count(id)) as temperature'))
        ->whereDate('created_at', $today)
        ->first();


        $graphic = new class {};
        $graphic->labels = ['PH','Temperatura'];
        $graphic->values = [$query->ph, $query->temperature];

        $data = new class {};
        $data->data = $graphic;

        return $this->sendResponse(true, 'Datos obtenidos correctamente', $data, 200);
    }

    public function graphicWeek()
    {
        $today = date("m");

        $query = Pool::select(DB::raw("case 
                                        when DAYOFWEEK(created_at) = 1 then 'Domingo' 
                                        when DAYOFWEEK(created_at) = 2 then 'Lunes' 
                                        when DAYOFWEEK(created_at) = 3 then 'Martes' 
                                        when DAYOFWEEK(created_at) = 4 then 'Miercoles' 
                                        when DAYOFWEEK(created_at) = 5 then 'Jueves' 
                                        when DAYOFWEEK(created_at) = 6 then 'Viernes' 
                                        when DAYOFWEEK(created_at) = 7 then 'Sabado' 
                                        end as dia,
                                        coalesce(sum(ph) / count(id), 0) as ph , sum(temperature) / count(id) as temperature "))
        ->whereMonth('created_at', $today)
        ->groupBy(DB::raw("case 
                    when DAYOFWEEK(created_at) = 1 then 'Domingo' 
                    when DAYOFWEEK(created_at) = 2 then 'Lunes' 
                    when DAYOFWEEK(created_at) = 3 then 'Martes' 
                    when DAYOFWEEK(created_at) = 4 then 'Miercoles' 
                    when DAYOFWEEK(created_at) = 5 then 'Jueves' 
                    when DAYOFWEEK(created_at) = 5 then 'Viernes' 
                    when DAYOFWEEK(created_at) = 7 then 'Sabado' 
                    end"))
        ->groupBy('created_at')
        ->orderBy(DB::raw('DAYOFWEEK(created_at)'))
        ->get();

        $graphic = new class {};
        $graphic->labels = array();
        $graphic->values = array();
        $valuesP = array();
        $valuesT = array();

        foreach ($query as $value) {
            array_push($graphic->labels, $value->dia);

            array_push($valuesP, $value->ph);
        
            array_push($valuesT, $value->temperature);
        }
        
        $valuesData = new class {};
        $valuesData->label = 'PH';
        $valuesData->data = $valuesP;
        array_push($graphic->values, $valuesData);

        $valuesData = new class {};
        $valuesData->label = 'Temperatura';
        $valuesData->data = $valuesT;
        array_push($graphic->values, $valuesData);

        $data = new class {};
        $data->data = $graphic;


        return $this->sendResponse(true, 'Datos obtenidos correctamente', $data, 200);
    }

    public function graphicMonth()
    { 
        $today = date("Y");

        $query = Pool::select(DB::raw("case 
                                        when MONTH(created_at) = 1 then 'Enero' 
                                        when MONTH(created_at) = 2 then 'Febrero' 
                                        when MONTH(created_at) = 3 then 'Marzo' 
                                        when MONTH(created_at) = 4 then 'Abril' 
                                        when MONTH(created_at) = 5 then 'Mayo' 
                                        when MONTH(created_at) = 6 then 'Junio' 
                                        when MONTH(created_at) = 7 then 'Julio' 
                                        when MONTH(created_at) = 8 then 'Agosto' 
                                        when MONTH(created_at) = 9 then 'Setiembre' 
                                        when MONTH(created_at) = 10 then 'Octubre' 
                                        when MONTH(created_at) = 11 then 'Noviembre' 
                                        when MONTH(created_at) = 12 then 'Diciembre'
                                        end as mes,
                                        coalesce(sum(ph) / count(id), 0) as ph , sum(temperature) / count(id) as temperature "))
        ->whereYear('created_at', $today)
        ->groupBy(DB::raw("case 
                            when MONTH(created_at) = 1 then 'Enero' 
                            when MONTH(created_at) = 2 then 'Febrero' 
                            when MONTH(created_at) = 3 then 'Marzo' 
                            when MONTH(created_at) = 4 then 'Abril' 
                            when MONTH(created_at) = 5 then 'Mayo' 
                            when MONTH(created_at) = 6 then 'Junio' 
                            when MONTH(created_at) = 7 then 'Julio' 
                            when MONTH(created_at) = 8 then 'Agosto' 
                            when MONTH(created_at) = 9 then 'Setiembre' 
                            when MONTH(created_at) = 10 then 'Octubre' 
                            when MONTH(created_at) = 11 then 'Noviembre' 
                            when MONTH(created_at) = 12 then 'Diciembre' 
                            end"))
        ->groupBy(DB::raw('MONTH(created_at)'))
        ->orderBy(DB::raw('MONTH(created_at)'))
        ->get();

        $graphic = new class {};
        $graphic->labels = array();
        $graphic->values = array();
        $valuesP = array();
        $valuesT = array();

        foreach ($query as $value) {
            array_push($graphic->labels, $value->mes);

            array_push($valuesP, $value->ph);
        
            array_push($valuesT, $value->temperature);
        }
        
        $valuesData = new class {};
        $valuesData->label = 'PH';
        $valuesData->data = $valuesP;
        array_push($graphic->values, $valuesData);

        $valuesData = new class {};
        $valuesData->label = 'Temperatura';
        $valuesData->data = $valuesT;
        array_push($graphic->values, $valuesData);

        $data = new class {};
        $data->data = $graphic;


        return $this->sendResponse(true, 'Datos obtenidos ada correctamente', $data, 200);
    }
}
