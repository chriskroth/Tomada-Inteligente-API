<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlugStoreRequest;
use App\Models\Plug;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PlugController extends Controller
{
    public function store(PlugStoreRequest $request)
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
}
