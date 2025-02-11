<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceController extends Controller
{
    public function getPrice(Request $request)
    {
        try {
            $result = DB::select(
                'CALL getPrice(?, ?, ?, ?, @Message)',
                [$request->BKStart_time, $request->BKEnd_Time, $request->CourtId, $request->BookingDate]
            );
            $message = DB::select('SELECT @Message as outParam')[0]->outParam;
            $response = ['message' => $message, 'status' => 1, 'data' => $result];
            $responseCode = 200;
        } catch (\Throwable $th) {
            $response = ['message' => $th, 'status' => 0];
            $responseCode = 500;
        }

        return response()->json($response, $responseCode);
    }
}
