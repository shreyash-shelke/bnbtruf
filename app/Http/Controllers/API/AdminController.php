<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Admin::select();

        $admin = $query->get();
        if (count($admin) > 0) {
            unset($admin['password']);
            $response = ['message' => 'Admin found.', 'status' => 1, 'data' => $admin];
        } else {
            $response = ['message' => 'Admin not found.', 'status' => 0];
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
            'first_name' => ['required'],
            'last_name' => ['required'],
            'contact_number_one' => ['required', 'min:10', 'max:13', 'unique:admins,contact_number_one'],
            'password' => ['required', 'min:8'],
            'pin_code' => ['required', 'min:6', 'max:6']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            DB::beginTransaction();
            $data = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'org_name' => $request->org_name,
                'org_logo' => $request->org_logo,
                'contact_number_one' => $request->contact_number_one,
                'password' => $request->password,
                'email_id' => $request->email_id,
                'address_line' => $request->address_line,
                'city' => $request->city,
                'district' => $request->district,
                'state' => $request->state,
                'pin_code' => $request->pin_code,
            ];
            try {
                $admin = Admin::create($data);
                unset($admin['password']);
                $token = $admin->createToken('admin_token')->accessToken;
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                $admin = null;
                return response()->json(['message' => 'Internal Server Error ' . $th], 500);
            }

            if ($admin != null) {
                return response()->json(['message' => 'Registration Successfully', 'status' => 1, 'user' => $admin, 'token' => $token], 200);
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
        $admin = Admin::find($id);
        if (empty($admin)) {
            $response = ['message' => 'Admin not found.', 'status' => 0];
        } else {
            unset($admin['password']);
            $response = ['message' => 'Admin found.', 'status' => 1, 'data' => $admin];
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
        $query = Admin::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Admin Not Found', 'status' => 0];
            $responseCode = 404;
        } else {
            $validator = Validator::make($request->all(), [
                'first_name' => ['required'],
                'last_name' => ['required'],
                'contact_number_one' => ['required', 'min:10', 'max:13']
            ]);
            if ($validator->fails()) {
                $response = [$validator->messages()];
                $responseCode = 400;
            } else {
                DB::beginTransaction();
                try {
                    $query->first_name = $request['first_name'];
                    $query->last_name = $request['last_name'];
                    $query->org_name = $request['org_name'];
                    $query->contact_number_one = $request['contact_number_one'];
                    $query->contact_number_two = $request['contact_number_two'];
                    // $query->password = $request['password'];
                    $query->email_id = $request['email_id'];
                    $query->address_line = $request['address_line'];
                    $query->city = $request['city'];
                    $query->district = $request['district'];
                    $query->state = $request['state'];
                    $query->pin_code = $request['pin_code'];
                    $query->map = $request['map'];
                    $query->facebook = $request['facebook'];
                    $query->twitter = $request['twitter'];
                    $query->instagram = $request['instagram'];
                    $query->whatsapp = $request['whatsapp'];
                    $query->save();
                    DB::commit();
                    $response = ['message' => 'Profile Updated Successfully.', 'status' => 1];
                    $responseCode = 200;
                } catch (\Throwable $th) {
                    $query = null;
                    DB::rollBack();
                    $response = ['message' => 'Internal Server Error. ' . $th->getMessage()];
                    $responseCode = 500;
                }
            }
        }

        return response()->json($response, $responseCode);
    }

    public function updateLogo(Request $request, string $id)
    {
        $query = Admin::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Admin Not Found', 'status' => 0];
            $responseCode = 404;
        } else {
            if ($request->hasFile('org_logo')) {
                $image = $request->file('org_logo');
                $imagePath = $this->storeImage($image);
                DB::beginTransaction();
                try {
                    if ($query->org_logo != null) {
                        Storage::delete($query->org_logo);
                    }
                    $query->update(['org_logo' => $imagePath]);
                    DB::commit();
                    $response = ['message' => 'Logo Updated Successfully.', 'status' => 1];
                    $responseCode = 200;
                } catch (\Throwable $th) {
                    $query = null;
                    DB::rollBack();
                    $response = ['message' => 'Internal Server Error. ' . $th->getMessage()];
                    $responseCode = 500;
                }
            } else {
                $response = ['message' => 'Please Upload Image.', 'status' => 0];
                $responseCode = 404;
            }
        }

        return response()->json($response, $responseCode);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function login(Request $request)
    {
        $validateData = $request->validate([
            'contact_number_one' => ['required'],
            'password' => ['required']
        ]);

        $query = Admin::where('contact_number_one', $validateData['contact_number_one'])->first();
        if ($query && Hash::check($validateData['password'], $query->password)) {
            $token = $query->createToken('admin_token')->accessToken;
            try {
                $response = ['message' => 'Login Successfully.', 'status' => 1, 'admin' => $query, 'token' => $token];
                $responseCode = 200;
            } catch (\Throwable $th) {
                $response = ['message' => 'Internal server error ' . $th, 'status' => 0];
                $responseCode = 500;
            }
        } else {
            $response = ['message' => 'Admin Not Exist.', 'status' => 1];
            $responseCode = 200;
        }
        return response()->json($response, $responseCode);
    }

    public function changePassword(Request $request, $id)
    {
        $query = Admin::find($id);
        if (is_null($query)) {
            $response = ['message' => 'Admin not found', 'status' => 0];
            $responseCode = 404;
        } else {
            if (Hash::check($request['current_password'], $query->password)) {
                if ($request['new_password'] == $request['confirm_password']) {
                    DB::beginTransaction();
                    try {
                        $query->password = $request['new_password'];
                        $query->save();
                        DB::commit();
                        $response = ['message' => 'Password Updated Successfully.', 'status' => 1];
                        $responseCode = 200;
                    } catch (\Throwable $th) {
                        $query = null;
                        DB::rollBack();
                        $response = ['message' => 'Internal Server Error.' . $th->getMessage(), 'status' => 0];
                        $responseCode = 500;
                    }
                } else {
                    $response = ['message' => 'New Password & Confirm Password Does Not Match.', 'status' => 1];
                    $responseCode = 200;
                }
            } else {
                $response = ['message' => 'Current Password Does Not Match.', 'status' => 1];
                $responseCode = 200;
            }
        }

        return response()->json($response, $responseCode);
    }

    private function storeImage($image)
    {
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        // Store the image in the storage/app/public directory
        $relativePath = 'public/images/logo/' . $imageName;
        Storage::disk()->put($relativePath, file_get_contents($image));
        return $relativePath;
    }
}
