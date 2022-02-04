<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserve;
use App\Http\Requests\GetIndexReserveRequest;
use App\Http\Requests\StoreReserveRequest;

use App\Models\Repitation;
use App\Enums\RepitationType;
use Carbon\Carbon;
use Illuminate\Log\Logger;

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
     * @param  \App\Http\Requests\StoreReserveRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReserveRequest $request)
    {
        $start_at = $request->input('start_date_time');
        $end_at = $request->input('end_date_time');
        $room_id = $request->input('room_id');
        $s_at_c = new Carbon($start_at);
        $e_at_c = new Carbon($end_at);
        $days = collect();
        $bookings = collect();

        /*
        1. 登録可能か検査する。
        2. 登録可能な場合、repitationを作る。
            1. 終了日まで登録し、配列を作成する。
            2. 配列を返す
        3. 登録不可能な場合、かぶった予定を返す。
        */
        switch ($request->input('repitation.type')) {
            case RepitationType::NOTHING:
                return Reserve::create($request->only(['guest_name', 'start_date_time', 'end_date_time', 'purpose', 'guest_detail', 'room_id']));
                break;

            case RepitationType::DAILY:
                $f_at_c = (($request->has('repitation.finish_at')) ? (new Carbon($request->input('repitation.finish_at'))) : (new Carbon($end_at))->addDay($request->input('repitation.num')) )->endOfDay();
                // 予定を登録する日を予め計算しておく。
                while ($f_at_c->isAfter($e_at_c) || $f_at_c->isSameDay($e_at_c)) {
                    $days->push([$s_at_c->toISOString(), $e_at_c->toISOString()]);
                    $s_at_c->addDay(1);    $e_at_c->addDay(1);
                }
                // 衝突確認
                $bookings = Reserve::roomId($room_id)->whereHasReservation($start_at, $f_at_c->toISOString())->get();
                break;

            case RepitationType::WEEKLY:
                $f_at_c = (($request->has('repitation.finish_at')) ? (new Carbon($request->input('repitation.finish_at'))) : (new Carbon($end_at))->addWeek($request->input('repitation.num')) )->endOfDay();
                Logger("f_at_c", ["f_at_c" => $f_at_c]);
                // 予定を登録する日を予め計算しておく。
                while ($f_at_c->isAfter($e_at_c) || $f_at_c->isSameDay($e_at_c)) {
                    $days->push([$s_at_c->toISOString(), $e_at_c->toISOString()]);
                    $s_at_c->addWeek(1);    $e_at_c->addWeek(1);
                }
                // 衝突確認
                $bookings = Reserve::roomId($room_id)->whereHasReservation($start_at, $f_at_c->toISOString())->dayOfWeeks($start_at, $end_at)->whereHasReservation($start_at, $end_at)->get();
                break;
        }

        if ($bookings->isNotEmpty()) { // Booking
            return response()->json([
                "message" => "Reserves are booking.",
                "bookings" => $bookings,
            ], 409);
        } else { // Not Booking
            $guest_name = $request->input('guest_name');
            $purpose = $request->input('purpose');
            $guest_detail = $request->input('guest_detail');
    
            $repitation = Repitation::create();
            $days->eachSpread(function ($start, $end) use ($guest_name, $purpose, $guest_detail, $room_id, $repitation,) {
                    $repitation->reserves()->create([
                        'guest_name' => $guest_name,
                        'start_date_time' => $start,
                        'end_date_time' => $end,
                        'purpose' => $purpose,
                        'guest_detail' => $guest_detail,
                        'room_id' => $room_id,
                    ]);
            });
            return $repitation->reserves()->get();
        }
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
