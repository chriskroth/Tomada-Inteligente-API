<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Models\Plug;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(UserStoreRequest $request)
    {
        $input = [
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
        ];

        $user = User::create($input);
        if (is_null($user)) {
            return response(["message" => "Erro ao registrar os dados de usuário"], Response::HTTP_BAD_GATEWAY);
        }

        return response([], Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        if (Auth::attempt(["email" => $request->email, "password" => $request->password])) {
            /* @var User $user */
            $user = Auth::user();
            $token = $user->createToken("TomadaInteligenteAPI");

            return response(["user" => $user, "token" => $token->plainTextToken], Response::HTTP_OK);
        }

        return response(['message' => "E-mail ou senha incorretos"], Response::HTTP_UNAUTHORIZED);
    }

    public function show(Request $request)
    {
        $user = Auth::user();
        if ($user)
            return response($user, Response::HTTP_OK);

        return response(['message' => "Não foi possível identificar o usuário logado"], Response::HTTP_UNAUTHORIZED);
    }

    public function attachPlugToLoggedUser(Plug $plug)
    {
        /* @var User $user */
        $user = Auth::user();
        if ($user->plugs->contains($plug->id)) {
            return response([], Response::HTTP_CREATED);
        }

        $user->plugs()->attach($plug->id);
        return response([], Response::HTTP_CREATED);
    }
}
