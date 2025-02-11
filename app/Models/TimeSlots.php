<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class TimeSlots extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    public $table = "timeslots";
    public $timestamps = false;
    protected $fillable = [
        'CourtID',
        'start_Time',
        'End_Time',
        'SlotName'
    ];
}
