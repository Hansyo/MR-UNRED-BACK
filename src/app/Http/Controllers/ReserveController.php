<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserve;
use App\Http\Requests\GetIndexReserveRequest;
use App\Http\Requests\StoreReserveRequest;
use App\Models\Room;
use Carbon\Carbon;
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
        $result = Reserve::whereHasReservation($start_at, $end_at);
        if ($request->filled('room_id')) $result = $result->where('room_id', '=', $request->query('room_id'));
        return $result->get();
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
            $reserve = Reserve::create([
                'guest_name' => $request->guest_name,
                'start_date_time' => new Carbon($request->start_date_time),
                'end_date_time' => new Carbon($request->end_date_time),
                'purpose' => $request->purpose,
                'guest_detail' => $request->guest_detail,
                'room_id' => $request->room_id,
            ]);
            return $reserve;
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
