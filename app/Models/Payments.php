<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Payments extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    public $table = "payments";
    protected $fillable = [
        'user_id ',
        'other_charges ',
        'net_total',
        'payment_mode',
        'payment_status',
        'booking_status',
        'code',
        'merchant_trans_id',
        'trans_id',
        'success',
    ];
}
