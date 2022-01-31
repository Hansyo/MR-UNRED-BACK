<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserve;
use App\Http\Requests\GetIndexReserveRequest;

class ReserveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(GetIndexReserveRequest $request)
    {
        $start_at = $request->query('start_date_time');
        $end_at = $request->query('end_date_time');
        #$room_id = $request->query('room_id');

        return $request->whenHas('room_id', function($room_id) use($start_at, $end_at){
            return Reserve::whereHasReservation($start_at, $end_at)->where('room_id', '=', $room_id)->get();
        }, function() use($start_at, $end_at){
            return Reserve::whereHasReservation($start_at, $end_at)->get();
        });
        
        #return Reserve::whereHasReservation($start_at, $end_at)->where('room_id', '=', $room_id)->get();
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
        /*
        $validator = $request->validate([
            'start_date_time' => 'bail|required|date',
            #'start_date_time' => ['bail', 'required', 'date', 'regex:/^(\d{4})-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])T([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/'],
            'end_date_time' => 'bail|required|date',
            #'end_date_time' => ['bail', 'required', 'date', 'regex:/^(\d{4})-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])T([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/'],
            'room_id' => 'required | min:1 | max:6',
        ]);
        */

        // これで作成後にJSONを返してくれる。
        return Reserve::create($request->only(['guest_name', 'start_date_time', 'end_date_time', 'purpose', 'guest_detail', 'room_id']));
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = Reserve::where('id', $id)->delete();
        if ($result) {
            return response()->json([
                'message' => 'Reserve deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'message' => 'ID not found',
            ], 404);
        }
        //
    }
}
