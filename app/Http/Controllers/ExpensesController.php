<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpensesRequest;
use App\Http\Resources\ExpensesResource;
use App\Models\Expenses;
use App\Models\User;
use App\Notifications\ExpenseCreated;
use Exception;
use Illuminate\Http\Response;

class ExpensesController extends Controller
{
    public function index(){

        try{
            $userId = auth()->user()->id;
            $expenses = Expenses::where('user_id', $userId)->get();
           return response(ExpensesResource::collection($expenses), Response::HTTP_OK);
        }
        catch(Exception $e){
            return response(['message' => 'erro ao obter despesas'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function getById(Response $response, Expenses $expenses){
        try{
           return  response(ExpensesResource::make($expenses));
        }
        catch(Exception $e){
            
        }
    }


    public function create(ExpensesRequest $request){

        try{
            $expenses = Expenses::create($request->only([
                'description',
                'value',
                'date',
                'user_id'
            ]));

            $user = User::find($request->user_id);
            $user->notify(new ExpenseCreated($expenses));
            return  response(ExpensesResource::make($expenses));
        }
        catch(Exception $e){
            return response(['Message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(ExpensesRequest $request, Expenses $expenses){

        try{
            $expenses->update($request->only([
                'description',
                'value',
                'date',
                'user_id',

            ]));
           return  response(ExpensesResource::make($expenses));
        }
        catch(Exception $e){
            return response(['Message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(ExpensesRequest $request, Expenses $expenses){

        try{
            $expenses->delete();
           return  response(ExpensesResource::make($expenses));
        }
        catch(Exception $e){
            
        }
    }
}
