<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    
    public function create(CreateUserRequest $request){

        try{

           $user =  User::create($request->only([
                'name',
                'email',
                'password',
                'password_confirmation'
            ]));

            return response(UserResource::make($user), Response::HTTP_CREATED);
        }catch(Exception $e ){

            Log::error([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'code' => $e->getCode(),
            ]);
            return response("erro ao criar usuário",Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function getUser(){

        try{
            return response(UserResource::make(auth()->user()), Response::HTTP_OK);
        }catch(Exception $e){

            Log::error([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'code' => $e->getCode(),
            ]);
            return response("erro ao obter usuário",Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
       
}
