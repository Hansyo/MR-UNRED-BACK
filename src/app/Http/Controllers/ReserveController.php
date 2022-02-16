<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Reserve;
use App\Models\Repitation;
use App\Http\Requests\GetIndexReserveRequest;
use App\Http\Requests\StoreReserveRequest;
use App\Http\Requests\DestroyReserveRequest;


class ReserveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(GetIndexReserveRequest $request)
    {
        $result = Reserve::query();
        if ($request->filled('room_id')) $result = $result->roomId($request->query('room_id'));
        if ($request->has(['start_date_time', 'end_date_time']))
            $result = $result->whereHasReservation($request->query('start_date_time'), $request->query('end_date_time'));
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
            $guest_name = $request->input('guest_name');
            $purpose = $request->input('purpose');
            $guest_detail = $request->input('guest_detail');

            $s_at_c = new Carbon($start_at);
            $e_at_c = new Carbon($end_at);
            $days = collect();
            $bookings = collect();

            switch ($request->input('repitation.type')) {
                case 0: // 繰り返しなし
                    $bookings = Reserve::roomId($room_id)->whereHasReservation($start_at, $end_at)->get();
                    if ($bookings->isEmpty()) // ダブりなしなら作成してreturn
                        return Reserve::create([
                            'guest_name' => $guest_name,
                            'start_date_time' => $s_at_c,
                            'end_date_time' => $e_at_c,
                            'purpose' => $purpose,
                            'guest_detail' => $guest_detail,
                            'room_id' => $room_id,
                            'repitation_id' => null,
                        ]);
                    break;

                case 1: // 毎日
                    $f_at_c = (($request->has('repitation.finish_at')) ? (new Carbon($request->input('repitation.finish_at'))) : (new Carbon($end_at))->addDay($request->input('repitation.num') - 1))->endOfDay();
                    /* JSTの0:00 ~ 9:00 までに終了時間が入った場合、追加で一日予約を取られてしまう。対抗策として、終了日を1日前倒しする。もっとスマートな方法があるかも。
                       週毎では、同様の問題が起こらない。なぜなら期間が1週間と長く、1日程度の差を吸収してしまうため。1週間先を指定されることは想定しない。 */
                    if ($e_at_c->gte($e_at_c->copy()->setTime(15, 00, 00))) $f_at_c->subDay(1);
                    // 予定を登録する日を予め計算しておく。
                    while ($f_at_c->isAfter($e_at_c) || $f_at_c->isSameDay($e_at_c)) {
                        $days->push([$s_at_c->copy(), $e_at_c->copy()]);
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
                        $days->push([$s_at_c->copy(), $e_at_c->copy()]);
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
                $repitation = Repitation::create();
                $days->eachSpread(function ($start, $end) use ($guest_name, $purpose, $guest_detail, $room_id, $repitation) {
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
     * @param  \App\Http\Requests\DestroyReserveRequest $request
     * @param  \App\Http\Models\Reserve $reserve
     * @return \Illuminate\Http\Response
     */
    public function destroy(DestroyReserveRequest $request, Reserve $reserve)
    {
        $repitation = $reserve->repitation;
        $result = DB::transaction(function () use ($request, $reserve, $repitation) {
            $now = Carbon::now();
            $del_repitations = collect();
            // 削除可能な予約を抽出
            if ($repitation == null || $request->is_all == false) {
                if ($now < $reserve->start_date_time) $del_repitations->push($reserve);
            } else {
                $repitation->reserves->each(function ($res) use ($now, $del_repitations) {
                    if ($now < $res->start_date_time) $del_repitations->push($res);
                });
            }

            // 共通処理
            // 0. 削除する予約がなかったら、削除できないを返す
            // 1. 削除リストに登録されている予約を全て削除
            if ($del_repitations->isEmpty()) return response()->json(['message' => '削除可能な予約はありません。'], 404);
            $del_repitations->each(function ($res) {
                $res->delete();
            });
            return response()->json('', 204);
        });
        // 2. 同期した予約が0なら、同期レコードも削除
        $repitation = Repitation::find($repitation->id); // 自身の情報を更新する必要がある
        DB::transaction(function () use ($repitation) {
            if ($repitation != null && $repitation->reserves->isEmpty()) $repitation->delete();
        });
        return $result;

    }
}
