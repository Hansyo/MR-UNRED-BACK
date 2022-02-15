<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Room::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRoomRequest $request)
    {
        $room = Room::create(['name' => $request->name, 'detail' => $request->detail]);
        if($request->has('files')) {
            foreach ($request->file('files') as $index => $e) {
                $path = $e->store('public/images');
                $room->photos()->create(['path' => $path]);
            }
        }
        return $room;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function show(Room $room)
    {
        return $room;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function update(StoreRoomRequest $request, Room $room)
    {
        $room->name = $request->name;
        $room->detail = $request->detail;
        $room->save();
        if($request->file('newfiles')->isValid()) {
            foreach ($request->file('newfiles') as $index => $e) {
                $path = $e->store('public/images');
                $room->photos()->create(['path' => $path]);
            }
        }
        return $room;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function destroy(Room $room)
    {
        $room->photos->delete();
        $room->delete();
        return response()->json([
            'message' => 'Room deleted successfully',
        ], 200);
    }
}
