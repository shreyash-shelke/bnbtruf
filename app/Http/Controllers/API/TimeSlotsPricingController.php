<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TimeSlotPricings;
use App\Models\TimeSlots;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TimeSlotsPricingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $result = DB::select('CALL ManageTimeSlotPricing(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)', [null, null, null, null, null, null, null, null, null, 'G']);
            $message = DB::select('SELECT @Message as outParam')[0]->outParam;
        } catch (\Throwable $th) {
            return response()->json(['message' => $th], 500);
        }

        if ($message != null) {
            return response()->json(['message' => $message, 'status' => 1, 'data' => $result], 200);
        } else {
            return response()->json(['message' => 'Internal Server Error', 'status' => 0], 500);
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
        // return $request;
        $validator = Validator::make($request->all(), [
            'CourtID' => 'required',
            'TimeSlotID' => 'required',
            'PriceVariation' => 'required',
            'DayType' => 'required',
            // 'specialDayDate' => 'required',
            // 'OfferDescription' => 'required',
            'isActive' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        } else {
            try {
                if ($request->hasFile('OfferImagePath')) {
                    $image = $request->file('OfferImagePath');
                    $imagePath = $this->storeImage($image);
                    $result = DB::select(
                        'CALL ManageTimeSlotPricing(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)',
                        [
                            $request->Id, $request->CourtID, $request->TimeSlotID, $request->PriceVariation, $request->DayType,
                            $request->specialDayDate, $request->OfferDescription,  $imagePath,  $request->isActive, 'I'
                        ]
                    );
                } else {
                    // return response()->json(['message' => 'Please Upload Image.', 'status' => 0], 404);
                    $result = DB::select(
                        'CALL ManageTimeSlotPricing(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)',
                        [
                            $request->Id, $request->CourtID, $request->TimeSlotID, $request->PriceVariation, $request->DayType,
                            null, null,  null, $request->isActive, 'I'
                        ]
                    );
                }
                $message = DB::select('SELECT @Message as outParam')[0]->outParam;
            } catch (\Throwable $th) {
                return response()->json(['message' => $th, 'status' => 0], 500);
            }

            if ($message != null) {
                return response()->json(['message' => $message, 'status' => 1, 'data' => $result], 200);
            } else {
                return response()->json(['message' => 'Internal Server Error', 'status' => 0], 500);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $result = DB::select('CALL ManageTimeSlotPricing(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)', [$id, null, null, null, null, null, null, null, null, 'S']);
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
     * Display the specified resource by Court ID.
     */
    public function showByCourtIDTSPID(string $cid, string $tsid)
    {
        $model = new TimeSlots();
        $data = $model->where('CourtID', $cid)->where('TimeSlotID', $tsid)->get();
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
    public function edit(Request $request, string $id)
    {
        //Create laravel patch method api
        $query = TimeSlotPricings::find($id);
        if (!$query) {
            return response()->json(['error' => 'Court not found'], 404);
        }

        $data = $request->only(['isActive']);

        // Validate the incoming data if needed
        $validator = Validator::make($data, ['isActive' => 'required']);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $query->update($data);
        return response()->json(['data' => $query, 'message' => 'TimeSlot Pricing updated successfully.']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // return $request;
        $validator = Validator::make($request->all(), [
            'CourtID' => 'required',
            'TimeSlotID' => 'required',
            'PriceVariation' => 'required',
            'DayType' => 'required',
            'isActive' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        } else {
            try {
                if ($request->isOffer) {
                    if ($request->hasFile('OfferImagePath')) {
                        $image = $request->file('OfferImagePath');
                        $imagePath = $this->storeImage($image);
                    }
                    if ($request->find($request->Id)) {
                        Storage::delete($request->find($request->Id)['OfferImagePath']);
                    }
                    $result = DB::select(
                        'CALL ManageTimeSlotPricing(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)',
                        [
                            $request->Id, $request->CourtID, $request->TimeSlotID, $request->PriceVariation, $request->DayType,
                            $request->specialDayDate, $request->OfferDescription, $imagePath ?? null, $request->isActive, 'U'
                        ]
                    );
                } else {
                    // return response()->json(['message' => 'Please Upload Image.', 'status' => 0], 404);
                    $result = DB::select(
                        'CALL ManageTimeSlotPricing(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)',
                        [
                            $request->Id, $request->CourtID, $request->TimeSlotID, $request->PriceVariation, $request->DayType,
                            null, null, null, $request->isActive, 'U'
                        ]
                    );
                }
                $message = DB::select('SELECT @Message as outParam')[0]->outParam;
            } catch (\Throwable $th) {
                return response()->json(['message' => $th, 'status' => 0], 500);
            }

            if ($message != null) {
                return response()->json(['message' => $message, 'status' => 1, 'data' => $result], 200);
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
        try {
            $result = DB::select('CALL ManageTimeSlotPricing(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)', [$id, null, null, null, null, null, null, null, null, 'D']);
            $message = DB::select('SELECT @Message as outParam')[0]->outParam;
        } catch (\Throwable $th) {
            return response()->json(['message' => $th], 500);
        }
        return response()->json(['message' => 'TimeSlot Pricing deleted successfully.', 'Message' => $message, 'status' => 1, 'data' => $result], 200);
    }

    public function getOffers(Request $request)
    {
        $result = DB::select('CALL getOffers(?, @Message)', [$request->id]);
        $message = DB::select('SELECT @Message as outParam')[0]->outParam;
        if ($message != null) {
            return response()->json(['message' => $message, 'status' => 1, 'data' => $result], 200);
        } else {
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    public function setOfferDuration(string $t)
    {
        if (!empty($t)) {
            Storage::put('OfferDuration', $t);
        } else {
            Storage::put('OfferDuration', 3);
        }
        return response()->json(['message' => Storage::get('OfferDuration') . ' months duration set successfully.'], 200);
    }

    public function getOfferDuration()
    {
        return response()->json(['data' => Storage::get('OfferDuration')]);
    }

    private function storeImage($image)
    {
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        // Store the image in the storage/app/public directory
        $relativePath = 'public/images/offer/' . $imageName;
        Storage::disk()->put($relativePath, file_get_contents($image));
        return $relativePath;
    }
}
