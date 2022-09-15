<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            return response(["message" => "Erro ao registrar os dados"], Response::HTTP_BAD_GATEWAY);
        }

        return response([], Response::HTTP_CREATED);
    }
}
