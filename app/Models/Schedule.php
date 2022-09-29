<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        "plug_user_id",
        "time",
        "emit_sound",
        "start_date",
        "end_date",
        "voltage"
    ];
}
