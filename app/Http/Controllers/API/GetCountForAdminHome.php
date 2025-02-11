<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bookings;
use App\Models\Courts;
use App\Models\Users;

class GetCountForAdminHome extends Controller
{
  // public function allBookings()
  // {
  //     $query = Bookings::select();
  //     $bookings = $query->get();
  //     if (count($bookings) > 0) {
  //         $response = ['message' => count($bookings) . ' Bookings found.', 'status' => 1, 'data' => count($bookings)];
  //     } else {
  //         $response = ['message' => 'Bookings not found.', 'status' => 0];
  //     }
  //     return response()->json($response, 200);
  // }
  // public function allUsers()
  // {
  //     $query = Users::select();
  //     $users = $query->get();
  //     if (count($users) > 0) {
  //         $response = ['message' => count($users) . ' Users found.', 'status' => 1, 'data' => count($users)];
  //     } else {
  //         $response = ['message' => 'Users not found.', 'status' => 0];
  //     }
  //     return response()->json($response, 200);
  // }
  // public function allTurfs()
  // {
  //     $query = Courts::select();
  //     $courts = $query->get();
  //     if (count($courts) > 0) {
  //         $response = ['message' => count($courts) . ' Courts found.', 'status' => 1, 'data' => count($courts)];
  //     } else {
  //         $response = ['message' => 'Courts not found.', 'status' => 0];
  //     }
  //     return response()->json($response, 200);
  // }
  public function allCounts()
  {
    $court = Courts::select();
    $user = Users::select();
    $booking = Bookings::select();

    $courts = count($court->get());
    $users = count($user->get());
    $bookings = count($booking->get());

    // if ($courts > 0) {
    //     $courtCount = $courts;
    // } else {
    //     $response = ['message' => 'Courts not found.', 'status' => 0];
    // }

    // if ($users > 0) {
    //     $userCount = $users;
    // } else {
    //     $response = ['message' => 'Users not found.', 'status' => 0];
    // }

    // if ($bookings > 0) {
    //     $bookingCount = $bookings;
    // } else {
    //     $response = ['message' => 'Bookings not found.', 'status' => 0];
    // }

    // if ($bookingCount > 0 || $userCount > 0 || $courtCount > 0) {
    $response = [
      'message' => 'Data found.',
      'status' => 1,
      'data' => [
        ['count' => $bookings, 'title' => 'Total Bookings'],
        ['count' => $users, 'title' => 'Total Users'],
        ['count' => $courts, 'title' => 'Total Turfs']
      ]
    ];
    // }

    return response()->json($response, 200);
  }
}
