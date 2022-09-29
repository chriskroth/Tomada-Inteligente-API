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
}
