<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $result = DB::select('CALL ManageServices(?, ?, ?, ?, ?, @Message)', [null, null, null, 'G', 0]);
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
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            try {
                if ($request->hasFile('image_path')) {
                    $image = $request->file('image_path');
                    $imagePath = $this->storeImage($image);
                    DB::statement(
                        'CALL ManageServices(?, ?, ?, ?, ?, @Message)',
                        [$request->name, $request->description, $imagePath, 'I', null]
                    );
                    $message = DB::select('SELECT @Message as outParam')[0]->outParam;
                } else {
                    return response()->json(['message' => 'Please Upload Image.', 'status' => 0], 404);
                }
            } catch (\Throwable $th) {
                return response()->json(['message' => $th, 'status' => 0], 500);
            }

            if (!empty($message)) {
                return response()->json(['message' => 'Service Added Successfully.', 'status' => 1], 200);
            } else {
                return response()->json(['message' => 'Internal Server Error', 'status' => 0, 'error' => $message], 500);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $query = Services::find($id);
            if (is_null($query)) {
                $response = ['message' => 'Service not found', 'status' => 0];
            } else {
                $response = ['message' => 'Service found', 'status' => 1, 'data' => $query];
            }
        } catch (\Throwable $th) {
            $response = ['message' => 'Exception : Something Went Wrong : ' . $th, 'status' => 0];
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
        $validator = Validator::make($request->all(), ['name' => 'required']);

        if ($validator->fails()) {
            $response = $validator->messages();
            $responseCode = 400;
            return response()->json($validator->messages(), 400);
        } else {
            try {
                if ($request->hasFile('image_path')) {
                    $image = $request->file('image_path');
                    $imagePath = $this->storeImage($image);
                    DB::statement(
                        'CALL ManageServices(?, ?, ?, ?, ?, @Message)',
                        [$request->name, $request->description, $imagePath, 'U', $id]
                    );
                    $message = DB::select('SELECT @Message as outParam')[0]->outParam;
                } else {
                    $response = ['message' => 'Please Upload Image.', 'status' => 0];
                    $responseCode = 404;
                }
            } catch (\Throwable $th) {
                $response = ['message' => $th, 'status' => 0];
                $responseCode = 500;
            }

            if (!empty($message)) {
                $response = ['message' => 'Service Updated Successfully.', 'status' => 1];
                $responseCode = 200;
            } else {
                $response = ['message' => 'Internal Server Error', 'status' => 0, 'error' => $message];
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
        $query = Services::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Service Not Found.', 'status' => 0];
            $responseCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $query->delete();
                DB::commit();
                $response = ['message' => 'Service Deleted Successfully.', 'status' => 1, 'data' => $query];
                $responseCode = 200;
            } catch (\Throwable $th) {
                $response = ['message' => 'Internal Serve Error. ' . $th];
                DB::rollBack();
                $responseCode = 500;
            }
        }
        return response()->json($response, $responseCode);
    }

    private function storeImage($image)
    {
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        // Store the image in the storage/app/public directory
        $relativePath = 'public/images/service/' . $imageName;
        Storage::disk()->put($relativePath, file_get_contents($image));
        return $relativePath;
    }
}
