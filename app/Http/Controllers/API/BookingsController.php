<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bookings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Bookings::select();

        $payments = $query->get();
        if (count($payments) > 0) {
            $response = ['message' => count($payments) . ' bookings found.', 'status' => 1, 'data' => $payments];
        } else {
            $response = ['message' => 'Bookings not found.', 'status' => 0];
        }
        return response()->json($response, 200);
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
            'UserId' => 'required',
            'CourtId' => 'required',
            'SportId' => 'required',
            'BookingDate' => 'required',
            'StartTime' => 'required',
            'EndTime' => 'required',
            'PaymentId' => 'required',
            'StatusId' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        } else {
            try {
                DB::statement(
                    'CALL BookCourt(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)',
                    [
                        $request->UserId,
                        $request->CourtId,
                        $request->SportId,
                        $request->BookingDate,
                        $request->StartTime,
                        $request->EndTime,
                        $request->PaymentId,
                        $request->StatusId,
                        $request->Description,
                        $request->NameCustomer,
                        $request->ContactCustomer
                    ]
                );
                $message = DB::select('SELECT @Message as outParam')[0]->outParam;
                if (empty($message)) {
                    return response()->json(['message' => 'Booking Done Successfully.', 'status' => 1], 200);
                } else {
                    return response()->json(['message' => 'Something Went Wrong.', 'status' => 0], 200);
                }
            } catch (\Throwable $th) {
                return response()->json(['message' => $th->errorInfo[2]], 500);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        try {
            $result = DB::select(
                'CALL GetBookingDetails(?, ?, ?, ?)',
                [
                    $request->input('CourtID', 0),
                    $request->input('DateFrom', NULL),
                    $request->input('DateTo', NULL),
                    $request->input('UserID', 0)
                ]
            );
            if ($result != null) {
                return response()->json(['message' => 'Bookings Found.', 'status' => 1, 'data' => $result], 200);
            } else {
                return response()->json(['message' => 'Booking Not Found.', 'status' => 1], 200);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => $th], 500);
        }
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
        $query = Bookings::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Booking Not Found', 'status' => 0];
            $responseCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $query->user_id  = $request['user_id'];
                $query->court_id = $request['court_id'];
                $query->sport_id = $request['sport_id'];
                $query->booking_date = $request['booking_date'];
                $query->start_time = $request['start_time'];
                $query->duration = $request['duration'];
                $query->payment_id = $request['payment_id'];
                $query->status_id = $request['status_id'];
                $query->description = $request['description'];
                $query->save();
                DB::commit();
                $response = ['message' => 'Booking Updated', 'status' => 1];
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
        $query = Bookings::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Booking not found', 'status' => 0];
            $responseCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $query->delete();
                DB::commit();
                $response = ['message' => 'Booking deleted', 'status' => 1, 'data' => $query];
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
