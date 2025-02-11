<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MembershipController extends Controller
{
    public function store(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'message' => 'nullable|string',
        ]);

        // Insert data into database
        DB::table('memberships')->insert([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'message' => $request->message,
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Membership request submitted successfully!'], 201);
    }

    public function index()
    {
        $members = DB::table('memberships')->get();
        return response()->json($members);
    }
}
