<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Payments::select();

        $payments = $query->get();
        if (count($payments) > 0) {
            $response = ['message' => count($payments) . ' payments found.', 'status' => 1, 'data' => $payments];
        } else {
            $response = ['message' => 'Payments not found.', 'status' => 0];
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
            'user_id' => 'required',
            'other_charges' => 'required',
            'net_total' => 'required',
            'payment_mode' => 'required',
            'payment_status' => 'required',
            'booking_status' => 'required',
            'code' => 'required',
            'merchant_trans_id' => 'required',
            'trans_id' => 'required',
            'success' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        } else {
            DB::beginTransaction();
            $data = [
                'user_id' => $request->user_id,
                'other_charges' => $request->other_charges,
                'net_total' => $request->net_total,
                'payment_mode' => $request->payment_mode,
                'payment_status' => $request->payment_status,
                'booking_status' => $request->booking_status,
                'code' => $request->code,
                'merchant_trans_id' => $request->merchant_trans_id,
                'trans_id' => $request->trans_id,
                'success' => $request->success,
            ];
            try {
                $query = Payments::create($data);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                $query = null;
                return response()->json(['message' => 'Internal Server Error ' . $th], 500);
            }

            if ($query != null) {
                return response()->json([
                    'message' => 'Payment Details Added Successfully',
                    'status' => 1,
                    'data' => $query
                ], 200);
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
        $query = Payments::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Payment not found', 'status' => 0];
        } else {
            $response = ['message' => 'Payment found', 'status' => 1, 'data' => $query];
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
        $query = Payments::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Payment Details Not Found', 'status' => 0];
            $responseCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $query->user_id  = $request['user_id '];
                $query->other_charges = $request['other_charges'];
                $query->net_total = $request['net_total'];
                $query->payment_mode = $request['payment_mode'];
                $query->payment_status = $request['payment_status'];
                $query->booking_status = $request['booking_status'];
                $query->code = $request['code'];
                $query->merchant_trans_id = $request['merchant_trans_id'];
                $query->trans_id = $request['trans_id'];
                $query->success = $request['success'];
                $query->save();
                DB::commit();
                $response = ['message' => 'Payment Details Updated', 'status' => 1];
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
        $query = Payments::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Payment not found', 'status' => 0];
            $responseCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $query->delete();
                DB::commit();
                $response = ['message' => 'Payment deleted', 'status' => 1, 'data' => $query];
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
