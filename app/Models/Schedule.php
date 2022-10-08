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

    public function plug(): Plug
    {
        $plugRow = Schedule::query()
            ->join("plug_user", "schedules.plug_user_id", "=", "plug_user.id")
            ->where("schedules.id", $this->id)
            ->select("plug_user.plug_id")
            ->get();

        $plugId = $plugRow[0]->plug_id;
        return Plug::find($plugId);
    }

    public function start()
    {
        if ($this->isStarted()) {
            return;
        }
        
        $this->started = true;
        $this->save();
    }

    public function isStarted()
    {
        return $this->started;
    }
}
