<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BookingsController extends Controller
{
    public function get_bookings(){
        $bookings = Bookings::join('users','bookings.user_id', 'users.id')->join('accomodations','bookings.accomodation_id', 'accomodations.id')->select('bookings.*', 'users.name as user', 'accomodations.name as accomodation')->get();

        if(count($bookings) > 0){
            return response()->json($bookings, 200);
        }

        return response()->json([], 400);
    }

    //metodo para actualizar el estado de la reservacion
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
