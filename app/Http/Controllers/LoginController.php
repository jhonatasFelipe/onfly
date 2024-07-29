<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    
    public function index(Request $request ){
        $user = User::where('email', $request->email)->first();

        
        if(!Hash::check($request->password,$user->password)){
            return response(['message' => "invalid password or user!"], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('expenses');
        return ['token' => $token->plainTextToken];
    }
}
