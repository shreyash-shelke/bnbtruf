<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $result = DB::select('CALL ManageSports(?, ?, ?, @Message)', [null, null, 'G']);
            $message = DB::select('SELECT @Message as outParam')[0]->outParam;
        } catch (\Throwable $th) {
            $response = ['message' => $th, 'status' => 0];
            $responseCode = 500;
        }

        if (!empty($message)) {
            $response = ['message' => $message, 'status' => 1, 'data' => $result];
            $responseCode = 200;
        } else {
            $response = ['message' => 'Internal Server Error'];
            $responseCode = 500;
        }
        return response()->json($response, $responseCode);
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
        $validator = Validator::make($request->all(), ['name' => ['required']]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            try {
                DB::select('CALL ManageSports(?, ?, ?, @Message)', [$request->name, null, 'I']);
                $message = DB::select('SELECT @Message as outParam')[0]->outParam;
            } catch (\Throwable $th) {
                return response()->json(['message' => $th, 'status' => 0], 500);
            }

            if (!empty($message)) {
                return response()->json(['message' => 'Sport Added Successfully.', 'status' => 1], 200);
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
        $query = Sports::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Sport not found', 'status' => 0];
        } else {
            $response = ['message' => 'Sport found', 'status' => 1, 'data' => $query];
        }
        return response()->json($response, 200);
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
        $validator = Validator::make($request->all(), ['name' => ['required']]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            try {
                DB::select('CALL ManageSports(?, ?, ?, @Message)', [$request->name, $id, 'U']);
                $message = DB::select('SELECT @Message as outParam')[0]->outParam;
            } catch (\Throwable $th) {
                return response()->json(['message' => $th, 'status' => 0], 500);
            }

            if (!empty($message)) {
                return response()->json(['message' => 'Sport Updated Successfully.', 'status' => 1], 200);
            } else {
                return response()->json(['message' => 'Internal Server Error'], 500);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $query = Sports::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Sport not found', 'status' => 0];
            $responseCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $query->delete();
                DB::commit();
                $response = ['message' => 'Sport is Deleted Successfully.', 'status' => 1, 'data' => $query];
                $responseCode = 200;
            } catch (\Throwable $th) {
                $response = ['message' => 'Internal Serve Error. ' . $th];
                DB::rollBack();
                $responseCode = 500;
            }
        }
        return response()->json($response, $responseCode);
    }
}
