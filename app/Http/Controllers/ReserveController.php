<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserve;
use App\Http\Requests\GetIndexReserveRequest;
use App\Http\Requests\StoreReserveRequest;
use Illuminate\Support\Facades\DB;

use App\Models\Repitation;
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
        /*
        1. 登録可能か検査する。
        2. 登録可能な場合、repitationを作る。
            1. 終了日まで登録し、配列を作成する。
            2. 配列を返す
        3. 登録不可能な場合、かぶった予定を返す。
        */
        $result = DB::transaction(function () use ($request) {
            $start_at = $request->input('start_date_time');
            $end_at = $request->input('end_date_time');
            $room_id = $request->input('room_id');
            $s_at_c = new Carbon($start_at);
            $e_at_c = new Carbon($end_at);
            $days = collect();
            $bookings = collect();

            switch ($request->input('repitation.type')) {
                case 0: // 繰り返しなし
                    $bookings = Reserve::roomId($room_id)->whereHasReservation($start_at, $end_at)->get();
                    if ($bookings->isEmpty()) // ダブりなしなら作成してreturn
                        return Reserve::create($request->only(['guest_name', 'start_date_time', 'end_date_time', 'purpose', 'guest_detail', 'room_id']));
                    break;

                case 1: // 毎日
                    $f_at_c = (($request->has('repitation.finish_at')) ? (new Carbon($request->input('repitation.finish_at'))) : (new Carbon($end_at))->addDay($request->input('repitation.num') - 1))->endOfDay();
                    /* JSTの0:00 ~ 9:00 までに終了時間が入った場合、追加で一日予約を取られてしまう。対抗策として、終了日を1日前倒しする。もっとスマートな方法があるかも。
                       週毎では、同様の問題が起こらない。なぜなら期間が1週間と長く、1日程度の差を吸収してしまうため。1週間先を指定されることは想定しない。 */
                    if($e_at_c->gte($e_at_c->copy()->setTime(15, 00, 00))) $f_at_c->subDay(1);
                    // 予定を登録する日を予め計算しておく。
                    while ($f_at_c->isAfter($e_at_c) || $f_at_c->isSameDay($e_at_c)) {
                        $days->push([$s_at_c->toISOString(), $e_at_c->toISOString()]);
                        $s_at_c->addDay(1);
                        $e_at_c->addDay(1);
                    }
                    // 衝突確認
                    $bookings = Reserve::roomId($room_id)->whereHasReservation($start_at, $f_at_c->toISOString())->get();
                    break;

                case 2: // 毎週
                    $f_at_c = (($request->has('repitation.finish_at')) ? (new Carbon($request->input('repitation.finish_at'))) : (new Carbon($end_at))->addWeek($request->input('repitation.num') - 1))->endOfDay();
                    Logger("f_at_c", ["f_at_c" => $f_at_c]);
                    // 予定を登録する日を予め計算しておく。
                    while ($f_at_c->isAfter($e_at_c) || $f_at_c->isSameDay($e_at_c)) {
                        $days->push([$s_at_c->toISOString(), $e_at_c->toISOString()]);
                        $s_at_c->addWeek(1);
                        $e_at_c->addWeek(1);
                    }
                    // 衝突確認
                    $bookings = Reserve::roomId($room_id)->whereHasReservation($start_at, $f_at_c->toISOString())->dayOfWeeks($start_at, $end_at)->whereHasReservation($start_at, $end_at)->get();
                    break;
            }

            if ($bookings->isNotEmpty()) { // Conflicting.
                return response()->json([
                    'message' => 'Reservation is conflicting',
                    'conflictings' => $bookings,
                ], 409);
            } else { // Not Conflicting.
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
