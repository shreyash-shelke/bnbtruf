<?php

namespace App\Http\Controllers\API;

use App\Models\SportCourt;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CourtsSportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $result = DB::select('CALL ManageSportCourt(?, ?, ?, ?, @Message)', [null, null, null,  'G']);
            if (!empty($result)) {
                $response = ['message' => 'Data Found.', 'status' => 1, 'data' => $result];
                $resCode = 200;
            } else {
                $response = ['message' => 'Data Not Found.', 'status' => 0];
                $resCode = 404;
            }
        } catch (\Exception $e) {
            $response = ['message' => $e];
            $resCode = 500;
        }

        return response()->json($response, $resCode);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'SportId' => 'required',
            'CourtId' => 'required',
        ]);
        try {
            if ($validator->fails()) {
                $response = ['errors' => $validator->errors()];
                $resCode = 422;
            } else {
                $result = DB::statement('CALL ManageSportCourt(?, ?, ?, ?, @Message)', [$request->SportId, $request->CourtId, null, 'I']);
                $message = DB::select('SELECT @Message as outParam')[0]->outParam;
                if ($message != null) {
                    $response = ['message' => 'Court Sport Added Successfully.', 'status' => 1, 'data' => $result];
                    $resCode = 200;
                } else {
                    $response = ['message' => 'Something went wrong.', 'status' => 0];
                    $resCode = 500;
                }
            }
        } catch (\Throwable $th) {
            $response = ['message' => $th];
            $resCode = 500;
        }

        return response()->json($response, $resCode);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $ID, string $actionType)
    {
        $validator = Validator::make([$ID => 'required', $actionType => 'required'], []);
        try {
            if ($validator->fails()) {
                $response = ['errors' => $validator->errors()];
                $resCode = 422;
            } else {
                $result = DB::select('CALL ManageSportCourt(?, ?, ?, ?, @Message)', [null, null, $ID, $actionType]);
                if ($result != null) {
                    $response = ['message' => 'Data Found.', 'status' => 1, 'data' => $result];
                    $resCode = 200;
                } else {
                    $response = ['message' => 'Data Not Found.', 'status' => 0];
                    $resCode = 404;
                }
            }
        } catch (\Throwable $th) {
            $response = ['message' => $th];
            $resCode = 500;
        }

        return response()->json($response, $resCode);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $ID)
    {
        $validator = Validator::make($request->all(), [
            'SportId' => 'required',
            'CounrtId' => 'required',
        ]);
        try {
            if ($validator->fails()) {
                $response = ['errors' => $validator->errors()];
                $resCode = 422;
            } else {
                $result = DB::select('CALL ManageSportCourt(?, ?, ?, ?, @Message)', [$request->SportId, $request->CounrtId, $ID, 'U']);
                $message = DB::select('SELECT @Message as outParam')[0]->outParam;
                if ($message != null) {
                    $response = ['message' => 'Court Sport Updated Successfully.', 'status' => 1, 'data' => $result];
                    $resCode = 200;
                } else {
                    $response = ['message' => 'Internal Server Error.', 'status' => 0];
                    $resCode = 500;
                }
            }
        } catch (\Throwable $th) {
            $response = ['message' => $th];
            $resCode = 200;
        }

        return response()->json($response, $resCode);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $ID)
    {
        $validator = Validator::make(['ID' => $ID], [
            'ID' => 'required',
        ]);
        try {
            if ($validator->fails()) {
                $response = ['errors' => $validator->errors()];
                $resCode = 422;
            } else {
                DB::statement('CALL ManageSportCourt(?, ?, ?, ?, @Message)', [null, null, $ID, 'D']);
                $message = DB::select('SELECT @Message as outParam')[0]->outParam;
                if ($message == null) {
                    $response = ['message' => 'Court Sport Deleted Successfully.', 'status' => 1];
                    $resCode = 200;
                } else {
                    $response = ['message' => 'Something went wrong.', 'status' => 0];
                    $resCode = 500;
                }
            }
        } catch (\Throwable $th) {
            $response = ['message' => $th];
            $resCode = 500;
        }

        return response()->json($response, $resCode);
    }
}
