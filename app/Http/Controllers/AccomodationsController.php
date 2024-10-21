<?php

namespace App\Http\Controllers;

use App\Models\Accomodations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
/**
 * @OA\Tag(name="Accomodations", description="Accommodations API Endpoints")
 */

class AccomodationsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/V1/accomodations",
     *     tags={"Accomodations"},
     *     summary="Get all accomodations",
     *     @OA\Response(response="200", description="Successful retrieval of properties"),
     *     @OA\Response(response="400", description="No accomodations at the moment")
     * )
     */
    public function getAccomodations(){
        $accomodations = Accomodations::all(); //[]

        if(count($accomodations) > 0){
            //mandamos los registros con status 200 (OK)
            return response()->json($accomodations, 200);
        }

        //No hay data
        return response()->json(['message' => 'No accomodations at the moment'], 400);
    }


    //metodo para buscar un alojamiento
    /**
     * @OA\Get(
     *     path="/api/V1/accomodation/{id}",
     *     tags={"Accomodations"},
     *     summary="Find a accomodation by ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Successful retrieval of accomodation"),
     *     @OA\Response(response="400", description="Accomodation not found")
     * )
     */
    public function get_accomodation_by_id($id){
        //select * from accomodations where id = ?
        $accomodation = Accomodations::find($id); // {} / null

        if($accomodation != null){
            return response()->json($accomodation, 200);
        }

        return response()->json(['message' => 'Accomodation not found'], 400);
    }

    //metodo para registrar un alojamiento
    /**
     * @OA\Post(
     *     path="/api/V1/accomodation",
     *     tags={"Accomodations"},
     *     summary="Store a new accomodation",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","description","address","image"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="address", type="string")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Successfully registered"),
     *     @OA\Response(response="400", description="Validation Error")
     * )
     */
    public function store(Request $request){

        //validar entrada de datos
        $validator = Validator::make($request->all(), [
            //reglas para cada entrada de dato
            'name' => 'required|string|max:70',
            'address' => 'required|string|max:100',
            'description' => 'required|string',
            // 'image' => 'required|string'
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
        //$accomodation->image = $request->input('image'); //name
        $accomodation->image = "https://res.cloudinary.com/dmddi5ncx/image/upload/v1727886846/practicas/laravel/alojamiento2_fx7q75.webp";
        $accomodation->save();

        return response()->json(['message' => 'Successfully registered'], 201);
    }

    //metodo para actualizar un alojamiento
    /**
     * @OA\Put(
     *     path="/api/V1/accomodation/{id}",
     *     tags={"Accomodations"},
     *     summary="Update an existing accomodation",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","description","address","image"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="image", type="string")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Successfully updated"),
     *     @OA\Response(response="400", description="Validation Error")
     * )
     */

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
