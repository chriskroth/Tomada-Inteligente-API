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

class PlugController extends Controller
{
    public function store(PlugStoreRequest $request)
    {
        /* @var Plug $plug */
        $plug = Plug::create($request->all());
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

        /* @var Plug $plug */
        $plug = Plug::create($request->all());
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
//            ->whereDate("schedules.start_date", ">", now())
            ->select(["schedules.id", "schedules.time", "schedules.emit_sound", "schedules.start_date", "schedules.voltage"])
            ->get();
        if (is_null($schedules) || count($schedules) === 0) {
            return response([], Response::HTTP_NO_CONTENT);
        }

        return response($schedules, Response::HTTP_OK);
    }
}
