<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 
        'date', 
        'check_in', 
        'check_out', 
        'photo_path', 
        'status', 
        'latitude', 
        'longitude', 
        'location_name', 
        'approval_status', 
        'reject_reason', 
        'document_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
