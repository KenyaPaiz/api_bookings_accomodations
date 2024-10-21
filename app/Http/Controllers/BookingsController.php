<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
/**
 * @OA\Tag(name="Bookings", description="Bookings API Endpoints")
 */

class BookingsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/V1/bookings",
     *     summary="Get all bookings",
     *     tags={"Bookings"},
     *     @OA\Response(
     *         response=200,
     *         description="Bookings found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="ID of the booking"),
     *                 @OA\Property(property="user", type="string", description="Name of the user"),
     *                 @OA\Property(property="accomodation", type="string", description="Name of the accomodation"),
     *                 @OA\Property(property="check_in_date", type="string", format="date", description="Check-in date"),
     *                 @OA\Property(property="check_out_date", type="string", format="date", description="Check-out date"),
     *                 @OA\Property(property="total_amount", type="number", format="float", description="Total amount"),
     *                 @OA\Property(property="status", type="string", description="Status of the booking")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No bookings found"
     *     )
     * )
     */
    public function get_bookings(){
        $bookings = Bookings::join('users','bookings.user_id', 'users.id')->join('accomodations','bookings.accomodation_id', 'accomodations.id')->select('bookings.*', 'users.name as user', 'accomodations.name as accomodation')->get();

        if(count($bookings) > 0){
            return response()->json($bookings, 200);
        }

        return response()->json([], 400);
    }

    //metodo para actualizar el estado de la reservacion
    /**
     * @OA\Patch(
     *     path="/api/V1/status_booking/{id}",
     *     summary="Update the status of a booking",
     *     tags={"Bookings"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the booking",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", example="CANCELLED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status successfully updated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error"
     *     )
     * )
     */
    public function update_status(Request $request, $id){

        //validando la entrada de datos
        $validator = Validator::make($request->all(), [
            'status' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(), 
            ]);
        }
        //actualizar el estado
        $booking = Bookings::find($id); //{}
        $booking->status = $request->input('status');
        $booking->update();

        return response()->json(['message' => 'status successfully updated'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/V1/booking",
     *     summary="Register a new booking",
     *     tags={"Bookings"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking", "check_in_date", "check_out_date", "total_amount", "accomodation_id", "user_id"},
     *             @OA\Property(property="booking", type="string", maxLength=10, example="BK123456"),
     *             @OA\Property(property="check_in_date", type="string", format="date", example="2024-12-10"),
     *             @OA\Property(property="check_out_date", type="string", format="date", example="2024-12-15"),
     *             @OA\Property(property="total_amount", type="number", example=500.00),
     *             @OA\Property(property="accomodation_id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully registered"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error"
     *     )
     * )
     */
    public function store(Request $request){
        //validaciones de datos
        $validator = Validator::make($request->all(), [
            'booking' => 'required|string|max:10',
            'check_in_date' => 'required|date_format:Y-m-d',
            //validamos que la fecha de salida sea despues de la fecha de entrada
            'check_out_date' => 'required|date_format:Y-m-d|after:check_in_date',
            'total_amount' => 'required|numeric',
            //Validamos que los id del alojamiento y usuario existan en la base de datos
            'accomodation_id' => 'required|exists:accomodations,id',
            'user_id' => 'required|numeric|exists:users,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(), 
            ]);
        }

        //guardar el booking
        $booking = new Bookings();
        $booking->booking = $request->input('booking');
        $booking->check_in_date = $request->input('check_in_date');
        $booking->check_out_date = $request->input('check_out_date');
        $booking->total_amount = $request->input('total_amount');
        $booking->status = "CONFIRMED";
        $booking->accomodation_id = $request->input('accomodation_id');
        $booking->user_id = $request->input('user_id');
        $booking->save();

        return response()->json(['message' => 'Successfully registered'], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/V1/bookings/calendar/{id_accomodation}",
     *     summary="Get accommodation bookings calendar",
     *     tags={"Bookings"},
     *     @OA\Parameter(
     *         name="id_accomodation",
     *         in="path",
     *         description="ID of the accommodation",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date in Y-m-d format",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-12-10")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date in Y-m-d format",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-12-15")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bookings found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="ID of the booking"),
     *                 @OA\Property(property="user", type="string", description="Name of the user"),
     *                 @OA\Property(property="accomodation", type="string", description="Name of the accomodation"),
     *                 @OA\Property(property="check_in_date", type="string", format="date", description="Check-in date"),
     *                 @OA\Property(property="check_out_date", type="string", format="date", description="Check-out date"),
     *                 @OA\Property(property="total_amount", type="number", format="float", description="Total amount"),
     *                 @OA\Property(property="status", type="string", description="Status of the booking")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No bookings found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="The date range cannot exceed 3 months"
     *     )
     * )
     */
    public function calendar_accomodation_bookings(Request $request, $id_accomodation){

        //validando las fechas
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after:start_date'
        ]);

        if($validator->fails()){
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }

        //query builder
        $query = Bookings::where('accomodation_id',$id_accomodation);
        //validando si la persona ingreso las fechas
        if($request->has('start_date') && $request->has('end_date')){
            $start_date = Carbon::parse($request->input('start_date')); //2024-12-10
            $end_date = Carbon::parse($request->input('end_date')); //
            if($start_date->diffInMonths($end_date) > 3){
                return response()->json(['error' => 'The date range cannot exceed 3 months'], 422);
            }
            $query->whereBetween('check_out_date', [$start_date, $end_date]);
        }

        $bookings = $query->get();
        if($bookings->count() > 0){
            return response()->json($bookings, 200);
        }
        return response()->json(['message' => 'No bookings at this time'], 400);
    }
}
