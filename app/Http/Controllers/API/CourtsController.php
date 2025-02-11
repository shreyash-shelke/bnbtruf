<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Courts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CourtsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $result = DB::select(
                'CALL ManageCourt(?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)',
                [null, null, null, null, null, null, null, 'G', 0]
            );
            $message = DB::select('SELECT @Message as outParam')[0]->outParam;
        } catch (\Throwable $th) {
            return response()->json(['message' => $th], 500);
        }

        if ($message != null) {
            return response()->json(['message' => 'Court Found.', 'status' => 1, 'data' => $result], 200);
        } else {
            return response()->json(['message' => 'Internal Server Error', 'status' => 1, 'error' => $message], 500);
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
        // return p($request);
        $validator = Validator::make($request->all(), [
            'open_time' => ['required'],
            'close_time' => ['required'],
            'court_name' => ['required', 'unique:courts,court_name,except,id'],
            'description' => ['required'],
            'image_path' => ['required'],
            'is_active' => ['required'],
            'rate_per_hour' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            try {
                if ($request->hasFile('image_path')) {
                    $image = $request->file('image_path');
                    $imagePath = $this->storeImage($image);
                    DB::statement(
                        'CALL ManageCourt(?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)',
                        [
                            $request->open_time,
                            $request->close_time,
                            $request->court_name,
                            $request->description,
                            $imagePath,
                            $request->is_active,
                            $request->rate_per_hour,
                            'I',
                            null
                        ]
                    );
                    $message = DB::select('SELECT @Message as outParam')[0]->outParam;
                } else {
                    return response()->json(['message' => 'Please Upload Image.', 'status' => 0], 404);
                }
            } catch (\Throwable $th) {
                return response()->json(['message' => $th, 'status' => 0], 500);
            }

            if ($message == null) {
                return response()->json(['message' => 'Court Added Successfully.' . $message, 'status' => 1], 200);
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
        $query = Courts::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Court not found', 'status' => 0];
        } else {
            $response = ['message' => 'Court found', 'status' => 1, 'data' => $query];
        }

        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        //Create laravel patch method api
        $query = Courts::find($id);
        if (!$query) {
            return response()->json(['error' => 'Court not found'], 404);
        }

        $data = $request->only(['is_active']);

        // Validate the incoming data if needed
        $validator = Validator::make($data, [
            'is_active' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $query->update($data);
        return response()->json(['data' => $query, 'message' => 'Court Status is Updated Successfully.']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'open_time' => ['required'],
            'close_time' => ['required'],
            'court_name' => ['required'],
            'description' => ['required'],
            // 'image_path' => ['required'],
            'is_active' => ['required'],
            'rate_per_hour' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            try {
                if ($request->hasFile('image_path')) {
                    $image = $request->file('image_path');
                    $imagePath = $this->storeImage($image);
                }
                DB::statement(
                    'CALL ManageCourt(?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)',
                    [
                        $request->open_time,
                        $request->close_time,
                        $request->court_name,
                        $request->description,
                        $imagePath,
                        $request->is_active,
                        $request->rate_per_hour,
                        'U',
                        $id
                    ]
                );
                $message = DB::select('SELECT @Message as outParam')[0]->outParam;
                // } else {
                //     return response()->json(['message' => 'Please upload Image.'], 404);
                // }
            } catch (\Throwable $th) {
                return response()->json(['message' => $th], 500);
            }

            if ($message != null) {
                return response()->json(['message' => 'Court Updated Successfully.', 'status' => 1], 200);
            } else {
                return response()->json(['message' => 'Internal Server Error.', 'status' => 1], 500);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // try {
        //     DB::statement(
        //         'CALL ManageCourt(?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)',
        //         [null, null, null, null, null, null, null, 'D', $id]
        //     );
        //     $message = DB::select('SELECT @Message as outParam')[0]->outParam;
        // } catch (\Throwable $th) {
        //     return response()->json(['message' => $th], 500);
        // }

        // if ($message != null) {
        //     return response()->json(['message' => 'Court Deleted Successfully. ' . $message, 'status' => 1], 200);
        // } else {
        //     return response()->json(['message' => 'Internal Server Error', 'status' => 1, 'error' => $message], 500);
        // }

        $query = Courts::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Court not found', 'status' => 0];
            $responseCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $query->delete();
                DB::table('TimeSlots')->where('CourtId', $id)->delete();
                DB::table('TimeSlotPricing')->where('CourtId', $id)->delete();
                DB::commit();
                $response = ['message' => 'Court is Deleted Successfully.', 'status' => 1, 'data' => $query];
                $responseCode = 200;
            } catch (\Throwable $th) {
                $response = ['message' => 'Internal Serve Error. ' . $th];
                DB::rollBack();
                $responseCode = 500;
            }
        }
        return response()->json($response, $responseCode);
    }


    // My Custome Functions
    private function storeImage($image)
    {
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        // Store the image in the storage/app/public directory
        $relativePath = 'public/images/courts/' . $imageName;
        Storage::disk()->put($relativePath, file_get_contents($image));
        return $relativePath;
    }
}
