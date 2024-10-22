<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/V1/users",
     *     summary="Get list of users",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="List of Users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string",example="Abner"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="password", type="string", example="hashed_password")
     *             )
     *         )
     *     )
     * )
     */
    public function getUsers(){
        $users = User::select('id','name','email','password')->get();

        if(count($users) > 0){
            //mandamos los registros con status 200 (OK)
            return response()->json($users, 200);
        }

        //No hay data
        return response()->json([], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/V1/login",
     *     summary="Login",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="string", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="token123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You are not authorized")
     *         )
     *     )
     * )
     */
    public function login(Request $request){
        $email = $request->input('email');
        $passowrd = $request->input('password');

        $user = User::where('email',$email)->where('password','=',$passowrd)->first();

        if($user){
            //generar un token
            $token = $user->createToken('api-token')->plainTextToken;
            return response()->json([
                "user" => $email,
                "token" => $token
            ], 200);
        }else{
            return response()->json(["message" => "You are not authorized"], 401);
        }
    }
}
