<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TimeSlots;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TimeSlotsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $result = DB::select('CALL ManageTimeSlots(?, ?, ?, ?, ?, ?, @Message)', [null, null, null, null, null, 'G']);
            $message = DB::select('SELECT @Message as outParam')[0]->outParam;
        } catch (\Throwable $th) {
            return response()->json(['message' => $th], 500);
        }

        if ($message != null) {
            return response()->json(['message' => $message, 'status' => 1, 'data' => $result], 200);
        } else {
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Id' => 'required',
            'CourtID' => 'required',
            'start_Time' => 'required',
            'End_Time' => 'required',
            'SlotName' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        } else {
            try {
                $result = DB::select(
                    'CALL ManageTimeSlots(?, ?, ?, ?, ?, ?, @Message)',
                    [$request->Id, $request->CourtID, $request->start_Time, $request->End_Time, $request->SlotName, 'I']
                );
                $message = DB::select('SELECT @Message as outParam')[0]->outParam;
            } catch (\Throwable $th) {
                return response()->json(['message' => $th], 500);
            }

            if ($message != null) {
                return response()->json(['message' => $message, 'status' => 1, 'data' => $result], 200);
            } else {
                return response()->json(['message' => 'Internal Server Error'], 500);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $query = TimeSlots::find($id);
            if (is_null($query)) {
                $response = ['message' => 'Time Slots not found.', 'status' => 0];
            } else {
                $response = ['message' => 'Time Slots found', 'status' => 1, 'data' => $query];
            }
        } catch (\Throwable $th) {
            $response = ['message' => 'Exception : Something Went Wrong : ' . $th, 'status' => 0];
        }
        return response()->json($response, 200);
    }

    /**
     * Display the specified resource by Court ID.
     */
    public function showByCourtID(string $id)
    {
        $model = new TimeSlots();
        $data = $model->where('CourtID', $id)->get();
        try {
            if (count($data) > 0) {
                $response = ['message' => 'Record found.', 'status' => 1, 'data' => $data];
                $statusCode = 200;
            } else {
                $response = ['message' => 'Record not found.', 'status' => 0, 'data' => $data];
                $statusCode  = 404;
            }
        } catch (\Throwable $th) {
            $response = ['message' => $th->getMessage(), 'status' => 0];
            $statusCode  = 500;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $query = TimeSlots::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Time Slots Not Found', 'status' => 0];
            $responseCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $query->slot_name  = $request['slot_name'];
                $query->court_id = $request['court_id'];
                $query->start_time = $request['start_time'];
                $query->end_time = $request['end_time'];
                $query->rate_per_hour = $request['rate_per_hour'];
                $query->save();
                DB::commit();
                $response = ['message' => 'Time Slots Updated', 'status' => 1];
                $responseCode = 200;
            } catch (\Throwable $th) {
                $response = ['message' => 'Internal Serve Error. ' . $th->getMessage()];
                $query = null;
                DB::rollBack();
                $responseCode = 500;
            }
        }
        return response()->json($response, $responseCode);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $result = DB::select('CALL ManageTimeSlots(?, ?, ?, ?, ?, ?, @Message)', [$id, null, null, null, null, 'D']);
            $message = DB::select('SELECT @Message as outParam')[0]->outParam;
        } catch (\Throwable $th) {
            return response()->json(['message' => $th], 500);
        }

        if ($message != null) {
            return response()->json(['message' => $message, 'status' => 1, 'data' => $result], 200);
        } else {
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
