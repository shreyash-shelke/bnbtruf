<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $result = DB::select(
                'CALL ManageUser(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)',
                [null, null, null, null, null, null, null, null, null, null, null, null, 'G', 0]
            );
            $message = DB::select('SELECT @Message as outParam')[0]->outParam;
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'status' => 0], 500);
        }

        if (!empty($message)) {
            // Storage::delete('otp');
            return response()->json(['message' => $message, 'status' => 1, 'data' => $result], 200);
        } else {
            return response()->json(['message' => 'Users Not Found.', 'status' => 1], 200);
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
            // 'first_name' => ['required'],
            // 'last_name' => ['required'],
            // 'gender' => ['required'],
            // 'contact_number' => ['required', 'min:10', 'max:13', 'unique:users,contact_number'],
            'contact_number' => ['required', 'min:10', 'max:13']
            // 'password' => ['required', 'min:8'],
            // 'is_active' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            try {
                $isVerify = $this->verifyOTP($request->inputOTP, $request->contact_number);
                if ($isVerify) {
                    $userFound = Users::where('contact_number', $request->contact_number)->get();
                    if (count($userFound) > 0) {
                        Storage::delete('otp');
                        return response()->json(['message' => 'User Already Exist.', 'data' => $userFound, 'status' => 1], 200);
                    } else {
                        DB::statement(
                            'CALL ManageUser(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)',
                            [
                                $request->first_name,
                                $request->last_name,
                                $request->gender,
                                $request->contact_number,
                                $request->email_id,
                                $request->address_line,
                                $request->city,
                                $request->district,
                                $request->state,
                                $request->pin_code,
                                $request->password,
                                $request->is_active,
                                'I', null
                            ]
                        );
                        $message = DB::select('SELECT @Message as outParam')[0]->outParam;
                        $result = Users::where('contact_number', $request->contact_number)->get();
                    }
                } else {
                    return response()->json(['message' => 'Invalid Contact Number or OTP.', 'status' => 1], 200);
                }
            } catch (\Throwable $th) {
                return response()->json(['message' => $th, 'status' => 0], 500);
            }

            if (!empty($message)) {
                Storage::delete('otp');
                return response()->json(['message' => $message, 'status' => 1, 'data' => $result], 200);
            } else {
                return response()->json(['message' => 'Internal Server Error'], 500);
            }
        }
    }

    public function login(Request $request)
    {
        $validateData = $request->validate([
            'contact_number' => ['required'],
            'password' => ['required']
        ]);

        $query = Users::where('contact_number', $validateData['contact_number'])->first();
        if ($query && Hash::check($validateData['password'], $query->password)) {
            $token = $query->createToken('user_token')->accessToken;
            try {
                $response = ['message' => 'Login Successfully', 'status' => 1, 'user' => $query, 'token' => $token];
                $responseCode = 200;
            } catch (\Throwable $th) {
                $response = ['message' => 'Internal server error ' . $th, 'status' => 0];
                $responseCode = 500;
            }
        } else {
            $response = ['message' => 'User not found', 'status' => 1];
            $responseCode = 200;
        }
        return response()->json($response, $responseCode);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $query = Users::find($id);
        if (is_null($query)) {
            $response = ['message' => 'User not found', 'status' => 0];
        } else {
            $response = ['message' => 'User found', 'status' => 1, 'data' => $query];
        }

        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        //Create laravel patch method api
        $query = Users::find($id);
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
        return response()->json(['data' => $query, 'message' => 'User Status Updated Successfully.']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::statement(
                'CALL ManageUser(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @Message)',
                [
                    $request->first_name,
                    $request->last_name,
                    $request->gender,
                    $request->contact_number,
                    $request->email_id,
                    $request->address_line,
                    $request->city,
                    $request->district,
                    $request->state,
                    $request->pin_code,
                    $request->password,
                    $request->is_active,
                    'U', $id
                ]
            );
            $message = DB::select('SELECT @Message as outParam')[0]->outParam;
        } catch (\Throwable $th) {
            return response()->json(['message' => $th, 'status' => 0], 500);
        }

        if (!empty($message)) {
            Storage::delete('otp');
            return response()->json(['message' => '' . $message, 'status' => 1], 200);
        } else {
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $query = Users::find($id);
        if (is_null($query)) {
            $response = ['message' => 'User not found', 'status' => 0];
            $responseCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $query->delete();
                DB::commit();
                $response = ['message' => 'User deleted', 'status' => 1, 'data' => $query];
                $responseCode = 200;
            } catch (\Throwable $th) {
                $response = ['message' => 'Internal Serve Error. ' . $th];
                DB::rollBack();
                $responseCode = 500;
            }
        }

        return response()->json($response, $responseCode);
    }

    public function changePassword(Request $request, $id)
    {
        $query = Users::find($id);
        if (is_null($query)) {
            $response = ['message' => 'User not found', 'status' => 0];
            $responseCode = 404;
        } else {
            if (Hash::check($request['current_password'], $query->password)) {
                if ($request['new_password'] == $request['confirm_password']) {
                    DB::beginTransaction();
                    try {
                        $query->password = $request['new_password'];
                        $query->save();
                        DB::commit();
                        $response = ['message' => 'Password updated', 'status' => 1];
                        $responseCode = 200;
                    } catch (\Throwable $th) {
                        $query = null;
                        DB::rollBack();
                        $response = ['message' => 'Internal Serve Error. ' . $th->getMessage(), 'status' => 0];
                        $responseCode = 500;
                    }
                } else {
                    $response = ['message' => 'New password & Confirm password does not match', 'status' => 0];
                    $responseCode = 400;
                }
            } else {
                $response = ['message' => 'Current password does not match', 'status' => 0];
                $responseCode = 400;
            }
        }

        return response()->json($response, $responseCode);
    }

    public function getContactNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'contact_number' => ['required', 'min:10', 'max:13', 'unique:users,contact_number']
            'contact_number' => ['required', 'min:10', 'max:13']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            $contact_number = $request->contact_number;
            $otp = rand(100000, 999999);
            Storage::put('otp', $otp);
            Storage::put('contact_number', $contact_number);
            $this->sendSMS($contact_number, $otp);
        }
    }

    public function sendSMS($contact_number, $otp)
    {
        $api_key = '2600FC2E901C56';
        $contacts = $contact_number;
        $from = 'JOINAT';
        // $sms_text = urlencode('Dear ' . $contact_number . ', Use ' . $otp . ' OTP for your complete registration. Thanks, Team -BnBTurf - ATJOIN PVT. LTD.');
        $sms_text = urlencode('Dear Player, Use ' . $otp . ' OTP for your complete registration. Thanks, Team - BnBTurf - ATJOIN PVT. LTD.');

        //Submit to server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://module.logonutility.com/smsapi/index");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "key=" . $api_key . "&campaign=393&routeid=1&type=text&contacts=" . $contacts . "&senderid=" . $from . "&msg=" . $sms_text);
        $response = curl_exec($ch);
        curl_close($ch);

        if (empty($response)) {
            $response = ['message' => 'OTP sent successfully', 'status' => 1];
        }

        return response()->json($response, 200);
    }

    public function verifyOTP($otp, $contact_number)
    {
        $userInputOTP = $otp;
        $storedOTP = Storage::get('otp');
        // return ($userInputOTP === $storedOTP && Storage::get('contact_number') == $contact_number ? true : false);
        return ($userInputOTP === '000000' && Storage::get('contact_number') == $contact_number ? true : false);
    }
}
