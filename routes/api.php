<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\BookingsController;
use App\Http\Controllers\API\CourtsController;
use App\Http\Controllers\API\CourtsSportsController;
use App\Http\Controllers\API\GetCountForAdminHome;
use App\Http\Controllers\API\PaymentsController;
use App\Http\Controllers\API\PriceController;
use App\Http\Controllers\API\ServicesController;
use App\Http\Controllers\API\SportsController;
use App\Http\Controllers\API\StatusController;
use App\Http\Controllers\API\TimeSlotsController;
use App\Http\Controllers\API\TimeSlotsPricingController;
use App\Http\Controllers\API\UsersController;
use Illuminate\Http\Request;
use App\Http\Controllers\MembershipController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('admin/get-counts', [GetCountForAdminHome::class, 'allCounts']); //get Counts for Admin Home Page
Route::post('admin/create', [AdminController::class, 'store']); // To create admin
Route::post('admin/login', [AdminController::class, 'login']); // To login admin
Route::put('admin/update-admin/{id}', [AdminController::class, 'update']); // To update admin
Route::put('admin/update-logo/{id}', [AdminController::class, 'updateLogo']); // To update admin
Route::patch('admin/update-password/{id}', [AdminController::class, 'changePassword']); // To update single admin Password(Single Column)
Route::get('admin/get-admins', [AdminController::class, 'index']); // To get admin
Route::get('admin/get-admin/{id}', [AdminController::class, 'show']); // To get admin

// OTP Verification
Route::post('user/send-otp', [UsersController::class, 'getContactNumber']); // To login admin

// Authentication Route Group For User
// Route::middleware(['auth:api'])->group(function () {

// Authentication Route Group For Admin
// Route::middleware(['auth:admin-api'])->group(function () {
// Admin Users Management Routes

Route::post('user/create', [UsersController::class, 'store']); // To create user
// Route::post('user/login', [UsersController::class, 'login']); // To login user
Route::get('get-users', [UsersController::class, 'index']); // To fetch active(1) & verified(1), inactive(0) & unverified(0) & all users
Route::get('get-user/{id}', [UsersController::class, 'show']); // To fetch single user
Route::delete('remove-user/{id}', [UsersController::class, 'destroy']); // To delete single user
Route::put('update-user/{id}', [UsersController::class, 'update']); // To update single user(Multiple Columns)
Route::patch('user/update-password/{id}', [UsersController::class, 'changePassword']); // To update single user(Single Column)
Route::patch('update-user-status/{id}', [UsersController::class, 'edit']); // To Update Court Status

Route::prefix('admin/')->group(function () {
    // Admin Courts Management Routes
    Route::post('create-court', [CourtsController::class, 'store']); // To Create Court
    Route::get('get-courts', [CourtsController::class, 'index']); // To fetch active(1) & verified(1), inactive(0) & unverified(0) & all users
    Route::get('get-court/{id}', [CourtsController::class, 'show']); // To fetch Single Court
    Route::put('update-court/{id}', [CourtsController::class, 'update']); // To Update Court
    Route::patch('update-court-status/{id}', [CourtsController::class, 'edit']); // To Update Court Status
    Route::delete('remove-court/{id}', [CourtsController::class, 'destroy']); // To delete Single Court

    // Admin Sports Management Routes
    Route::post('create-sport', [SportsController::class, 'store']); // To Create Sport
    Route::get('get-sports', [SportsController::class, 'index']); // To fetch all Sports
    Route::get('get-sport/{id}', [SportsController::class, 'show']); // To fetch Single Sport
    Route::put('update-sport/{id}', [SportsController::class, 'update']); // To Update Sport
    Route::delete('remove-sport/{id}', [SportsController::class, 'destroy']); // To delete Single Sport

    // Admin Services Management Routes
    Route::post('create-court-sport', [CourtsSportsController::class, 'store']); // To Create Court Sport
    Route::get('get-courts-sports', [CourtsSportsController::class, 'index']); // To fetch all Courts Sports
    Route::get('get-court-sport/{id}/{actionType}', [CourtsSportsController::class, 'show']); // To fetch Single Court Sport
    Route::put('update-court-sport/{id}', [CourtsSportsController::class, 'update']); // To Update Court Sport
    Route::delete('remove-court-sport/{id}', [CourtsSportsController::class, 'destroy']); // To delete Single Court Sport

    // Admin Status Management Routes
    Route::post('create-status', [StatusController::class, 'store']); // To Create Status
    Route::get('get-status', [StatusController::class, 'index']); // To fetch all Status
    Route::get('get-status/{id}', [StatusController::class, 'show']); // To fetch Single Status
    Route::put('update-status/{id}', [StatusController::class, 'update']); // To Update Status
    Route::delete('remove-status/{id}', [StatusController::class, 'destroy']); // To delete Single Status

    // Admin Payment Management Routes
    Route::post('create-payment', [PaymentsController::class, 'store']); // To Create Payment
    Route::get('get-payments', [PaymentsController::class, 'index']); // To fetch all Payments
    Route::get('get-payment/{id}', [PaymentsController::class, 'show']); // To fetch Single Payment
    Route::put('update-payment/{id}', [PaymentsController::class, 'update']); // To Update Payment
    Route::delete('remove-payment/{id}', [PaymentsController::class, 'destroy']); // To delete Single Payment

    // Admin Booking Management Routes
    Route::post('create-booking', [BookingsController::class, 'store']); // To Create Booking
    Route::get('get-bookings', [BookingsController::class, 'index']); // To fetch all Bookings
    Route::get('get-booking', [BookingsController::class, 'show']); // To fetch Single Booking
    Route::post('update-booking/{id}', [BookingsController::class, 'update']); // To Update Booking
    Route::delete('remove-booking/{id}', [BookingsController::class, 'destroy']); // To delete Single Booking

    // Admin Services Management Routes
    Route::post('create-service', [ServicesController::class, 'store']); // To Create Service
    Route::get('get-services', [ServicesController::class, 'index']); // To fetch all Services
    Route::get('get-service/{id}', [ServicesController::class, 'show']); // To fetch Single Service
    Route::put('update-service/{id}', [ServicesController::class, 'update']); // To Update Service
    Route::delete('remove-service/{id}', [ServicesController::class, 'destroy']); // To delete Single Service

    // Admin TimeSlots Management Routes
    Route::post('create-slot', [TimeSlotsController::class, 'store']); // To Create Time Slot
    Route::get('get-slots', [TimeSlotsController::class, 'index']); // To fetch all TimeSlots
    Route::get('get-slot/{id}', [TimeSlotsController::class, 'show']); // To fetch Single Time Slot
    Route::put('update-slot/{id}', [TimeSlotsController::class, 'update']); // To Update Time Slot
    Route::delete('remove-slot/{id}', [TimeSlotsController::class, 'destroy']); // To delete Single Time Slot
    Route::get('get-slots-by-court-id/{cid}', [TimeSlotsController::class, 'showByCourtID']); // To Get Time Slots By Court ID

    // Admin TimeSlots Management Routes
    Route::post('create-slot-pricing', [TimeSlotsPricingController::class, 'store']); // To Create Time Slot Pricing
    Route::get('get-slots-pricing', [TimeSlotsPricingController::class, 'index']); // To fetch all TimeSlots Pricing
    Route::get('get-slot-pricing/{id}', [TimeSlotsPricingController::class, 'show']); // To fetch Single Time Slot Pricing
    Route::put('update-slot-pricing/{id}', [TimeSlotsPricingController::class, 'update']); // To Update Time Slot Pricing
    Route::patch('update-slot-pricing-status/{id}', [TimeSlotsPricingController::class, 'edit']); // To Update  Time Slot Pricing Status
    Route::delete('remove-slot-pricing/{id}', [TimeSlotsPricingController::class, 'destroy']); // To delete Single Time Slot Pricing
    Route::get('get-slot-pricing-by-court-id/{cid}/{tsid}', [TimeSlotsPricingController::class, 'showByCourtIDTSPID']); // To Get Time Slot Pricing By Court ID & Time Slot

    // Admin Get Offers Routes
    Route::get('get-offers/{id}', [TimeSlotsPricingController::class, 'getOffers']); // To Get Offers
    Route::get('set-offer-duration/{t}', [TimeSlotsPricingController::class, 'setOfferDuration']); // To Set Offer Duration
    Route::get('get-offer-duration', [TimeSlotsPricingController::class, 'getOfferDuration']); // To Set Offer Duration

    // User Get Pricing Routes
    Route::get('get-price', [PriceController::class, 'getPrice']); // To fetch Price of Slot

    Route::post('/membership', [MembershipController::class, 'store']); // For submitting form
    Route::get('/membership', [MembershipController::class, 'index']); // For viewing members
    Route::post('/contact', [ContactController::class, 'store']);

});
// });
