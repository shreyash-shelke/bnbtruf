<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookings extends Model
{
    use HasFactory;

    protected $table = 'bookings'; // Ensure this matches your database table name

    protected $fillable = [
        'user_id',
        'court_id',
        'sport_id',
        'booking_date',
        'start_time',
        'end_time',
        'payment_id',
        'status_id',
        'description',
        'name_customer',
        'contact_customer'
    ];
}
