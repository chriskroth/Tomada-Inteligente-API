<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewScheduleRequest;
use App\Http\Requests\PlugStoreRequest;
use App\Models\Plug;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlugController extends Controller
{
    public function store(PlugStoreRequest $request)
    {
        $token = Str::random(100);
        $data = array_merge($request->all(), ['token' => $token]);

        /* @var Plug $plug */
        $plug = Plug::create($data);
        if (is_null($plug)) {
            return response(['message' => "Erro ao registrar os dados da Tomada"], Response::HTTP_BAD_GATEWAY);
        }

        return response(["plug" => $plug], Response::HTTP_CREATED);
    }

    public function update(PlugStoreRequest $request)
    {
        // /* @var Plug $plug */
        $plug = Plug::where('serial_number', $request->serial_number
                           )->update(['name' => $request->name,
                                      'power' => $request->power,
                                      'consumption' => $request->consumption
                                    ]);

        if (is_null($plug)) {
            return response(['message' => "Erro ao atualizar os dados da Tomada"], Response::HTTP_BAD_GATEWAY);
        }

        $updated_plug = Plug::where('serial_number', $request->serial_number)->get();

        return response($updated_plug);
    }
    
    public function findBySerialNumber(PlugStoreRequest $request)
    {
        // /* @var Plug $plug */
        $plug = Plug::where('serial_number', $request->serial_number)->get();
        
        if (is_null($plug)) {
            return response(['message' => "Erro ao buscar os dados da Tomada"], Response::HTTP_BAD_GATEWAY);
        }

         return response($plug);
    }

    public function getPowerBySerialNumber($serial_number)
    {
        // /* @var Plug $plug */
        $plug = Plug::where('serial_number', $serial_number)->get();
        
        if (is_null($plug)) {
            return response(['message' => "Erro ao buscar os dados da Tomada"], Response::HTTP_BAD_GATEWAY);
        }

        //$power = json_decode($plug);

        $power = json_decode($plug);

        return response($power[0]->power);
    }

    public function setConsumptionBySerialNumber(PlugStoreRequest $request)
    {
        // /* @var Plug $plug */
        $plug = Plug::where('serial_number', $request->serial_number
                           )->update(['consumption' => $request->consumption]);

        if (is_null($plug)) {
            return response(['message' => "Erro ao atualizar os dados da Tomada"], Response::HTTP_BAD_GATEWAY);
        }

        $updated_plug = Plug::where('serial_number', $request->serial_number)->get();

        return response($updated_plug);
    }

    public function getConsumptionBySerialNumber(PlugStoreRequest $request)
    {
        // /* @var Plug $plug */
        $plug = Plug::where('serial_number', $request->serial_number)->get();
        
        if (is_null($plug)) {
            return response(['message' => "Erro ao atualizar os dados da Tomada"], Response::HTTP_BAD_GATEWAY);
        }

        $consumption = json_decode($plug);

        return response($consumption[0]->consumption);
    }

    // public function findBySerialNumber($serial_number)
    // {
    //     // /* @var Plug $plug */

    //     $plug = Plug::where('serial_number', $serial_number)->get();
        
    //     if (is_null($plug)) {
    //         return response(['message' => "Erro ao buscar os dados da Tomada"], Response::HTTP_BAD_GATEWAY);
    //     }

    //      return response($plug);
    // }

    public function storeAndAttachToLoggedUser(PlugStoreRequest $request)
    {
        /* @var User $user */
        $user = Auth::user();
        if (is_null($user)) {
            return response(['message' => 'Erro ao identificar o usuário logado'], Response::HTTP_UNAUTHORIZED);
        }

        $token = Str::random(100);
        $plugData = array_merge($request->all(), ['token' => $token]);
        /* @var Plug $plug */
        $plug = Plug::create($plugData);
        if (is_null($plug)) {
            return response(['message' => "Erro ao registrar os dados da Tomada"], Response::HTTP_BAD_GATEWAY);
        }

        $user->plugs()->attach($plug->id);

        return response(["plug" => $plug], Response::HTTP_CREATED);
    }

    public function newSchedule(Plug $plug, NewScheduleRequest $request)
    {
        /* @var User $user */
        $user = Auth::user();

        $plugUser = DB::table('plug_user')
            ->select("id")
            ->where('plug_id', $plug->id)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        $time = $request->input('time');
        $startDate = $request->input('start_date');
        $timeStartDate = strtotime($startDate);
        $timeEndDate = $timeStartDate + $time;
        $endDate = date("Y-m-d H:i:s", $timeEndDate);

        $input = array_merge(["plug_user_id" => $plugUser->id, "end_date" => $endDate], $request->all());

        /* @var Schedule $schedule */
        $schedule = Schedule::create($input);
        if (is_null($schedule))
            return response(['message' => "Erro ao registrar os dados da Tomada"], Response::HTTP_BAD_GATEWAY);

        return response(["schedule" => $schedule], Response::HTTP_CREATED);
    }

    public function listSchedules(Plug $plug)
    {
        $schedules = Plug::query()
            ->join("plug_user", "plugs.id", "=", "plug_user.plug_id")
            ->join("schedules", "schedules.plug_user_id", "=", "plug_user.id")
            ->where("plugs.id", $plug->id)
            ->whereNull("plug_user.deleted_at")
            ->whereNull("schedules.deleted_at")
            ->select(["schedules.id", "schedules.time", "schedules.emit_sound", "schedules.start_date", "schedules.voltage"])
            ->get();
        if (is_null($schedules) || count($schedules) === 0) {
            return response([], Response::HTTP_NO_CONTENT);
        }

        return response($schedules, Response::HTTP_OK);
    }

    public function getNextSchedule(Plug $plug)
    {
        $schedule = Schedule::query()
            ->join("plug_user", "plug_user.id", "=", "schedules.plug_user_id")
            ->where("plug_user.plug_id", $plug->id)
            ->where("schedules.started", false)
            ->where("schedules.start_date", ">", DB::raw("DATE_SUB(NOW(), INTERVAL " . env("SCHEDULE_SEGUNDOS_TOLERANCIA") . " SECOND)"))
            ->orderBy("start_date")
            ->select("schedules.*")
            ->first();
        if (!$schedule) {
            return response([], Response::HTTP_NO_CONTENT);
        }

        return response(['schedule' => $schedule], Response::HTTP_OK);
    }

    public function startSchedule(Plug $plug, Request $request)
    {
        $scheduleId = $request->input('schedule');
        if (intval($scheduleId) <= 0) {
            return response(['message' => "Erro ao identificar o agendamento que deve ser iniciado"], Response::HTTP_BAD_GATEWAY);
        }

        /* @var Schedule $schedule */
        $schedule = Schedule::find(intval($scheduleId));
        if (!$schedule) {
            return response(['message' => "Agendamento não encontrado"], Response::HTTP_BAD_GATEWAY);
        }

        $schedulePlug = $schedule->plug();
        if ($schedulePlug->id !== $plug->id) {
            return response(['message' => "Agendamento e tomada não estão vinculados"], Response::HTTP_BAD_GATEWAY);
        }

        $schedule->start();
        return response([], Response::HTTP_OK);
    }
}
