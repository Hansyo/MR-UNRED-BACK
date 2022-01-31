<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserve;

class ReserveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
