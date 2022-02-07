<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserve;
use App\Http\Requests\GetIndexReserveRequest;
use App\Http\Requests\StoreReserveRequest;
use Illuminate\Support\Facades\DB;

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
        //$room_id = $request->query('room_id');

        return $request->whenHas('room_id', function($room_id) use($start_at, $end_at){
            return Reserve::whereHasReservation($start_at, $end_at)->where('room_id', '=', $room_id)->get();
        }, function() use($start_at, $end_at){
            return Reserve::whereHasReservation($start_at, $end_at)->get();
        });
        
        //return Reserve::whereHasReservation($start_at, $end_at)->where('room_id', '=', $room_id)->get();
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreReserveRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReserveRequest $request)
    {
        $result = DB::transaction(function () use ($request) {
            $result = Reserve::where('room_id', '=', $request->room_id)
                ->whereHasReservation($request->start_date_time, $request->end_date_time)->get();
            if ($result->isNotEmpty()) {
                return response()->json([
                    'message' => 'Reservation is conflicting',
                    'conflictings' => $result
                ], 409);
            }
            return Reserve::create($request->only(['guest_name', 'start_date_time', 'end_date_time', 'purpose', 'guest_detail', 'room_id']));
        });
        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $result = Reserve::find($id);
        if ($result) {
            return $result;
        } else {
            return response()->json([
                'message' => 'ID not found',
            ], 404);
        }
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
