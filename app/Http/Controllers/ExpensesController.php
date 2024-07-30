<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpensesRequest;
use App\Http\Resources\ExpensesResource;
use App\Models\Expenses;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExpensesController extends Controller
{


    public function __construct(private ExpenseService $service){}
    public function index(){
        try{
            $expenses = $this->service->list();
            return response(ExpensesResource::collection($expenses), Response::HTTP_OK);
        }
        catch(\Exception $e){
            return response(['message' => 'erro ao obter despesas'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function getById(Request $request, Expenses $expenses){
        try{
           return  response(ExpensesResource::make($expenses));
        }
        catch(\Exception $e){
            return response(['Message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function create(ExpensesRequest $request){

        try{
            $expenses = $this->service->create($request->only([
                'description',
                'value',
                'date',
                'user_id'
            ]));
            return  response(ExpensesResource::make($expenses),Response::HTTP_CREATED);
        }
        catch(\Exception $e){
            return response(['Message' => 'erro ao criar uma despesa'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
           return  response(ExpensesResource::make($expenses), Response::HTTP_OK);
        }
        catch(\Exception $e){
            return response(['Message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Request $request, Expenses $expenses){

        try{
            $expenses->delete();
           return  response(ExpensesResource::make($expenses));
        }
        catch(\Exception $e){
            
        }
    }
}
