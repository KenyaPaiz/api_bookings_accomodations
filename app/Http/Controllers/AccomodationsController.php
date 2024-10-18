<?php

namespace App\Http\Controllers;

use App\Models\Accomodations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccomodationsController extends Controller
{
    public function getAccomodations(){

        //select * from accomodations
        $accomodations = Accomodations::all(); //[]

        if(count($accomodations) > 0){
            //mandamos los registros con status 200 (OK)
            return response()->json($accomodations, 200);
        }

        //No hay data
        return response()->json(['message' => 'No accomodations at the moment'], 400);
    }

    //metodo para buscar un alojamiento
    public function get_accomodation_by_id($id){
        //select * from accomodations where id = ?
        $accomodation = Accomodations::find($id); // {} / null

        if($accomodation != null){
            return response()->json($accomodation, 200);
        }

        return response()->json(['message' => 'Accomodation not found'], 400);
    }

    //metodo para registrar un alojamiento
    public function store(Request $request){

        //validar entrada de datos
        $validator = Validator::make($request->all(), [
            //reglas para cada entrada de dato
            'name' => 'required|string|max:70',
            'address' => 'required|string|max:100',
            'description' => 'required|string',
            'image' => 'required|string'
        ]);

        //en base a las regla de validaciones verificar si se cumple o no se cumple
        if($validator->fails()){
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 400);
        }

        //guardando un alojamiento (INSERT INTO....)
        //tarea = new Tarea();
        $accomodation = new Accomodations();
        $accomodation->name = $request->input('name'); //name
        $accomodation->address = $request->input('address'); //name
        $accomodation->description = $request->input('description'); //name
        $accomodation->image = $request->input('image'); //name
        $accomodation->save();

        return response()->json(['message' => 'Successfully registered'], 201);
    }

    //metodo para actualizar un alojamiento
    public function update(Request $request, $id){
        //validar entrada de datos
        $validator = Validator::make($request->all(), [
            //reglas para cada entrada de dato
            'name' => 'required|string|max:70',
            'address' => 'required|string|max:100',
            'description' => 'required|string',
            'image' => 'required|string'
        ]);

        //en base a las regla de validaciones verificar si se cumple o no se cumple
        if($validator->fails()){
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 400);
        }

        //actualizar (update table set campo = valor ... where id = ?)
        //metodo para encontrar un registro por su id
        $accomodation = Accomodations::find($id); //{}
        if($accomodation != null){
            $accomodation->name = $request->input('name'); //name
            $accomodation->address = $request->input('address'); //name
            $accomodation->description = $request->input('description'); //name
            $accomodation->image = $request->input('image'); //name
            $accomodation->update();

            return response()->json(['message' => 'correctly updated'], 200);
        }
        
        return response()->json(['message' => 'Accomodation not found'], 400);
    }
}
