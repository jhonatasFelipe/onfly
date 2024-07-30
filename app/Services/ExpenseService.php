<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\ExpenseCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

Class ExpenseService extends BaseService{


    public function __construct(private Model $model){}


    public function list(Array $filters = []) : Collection{
        $userId = auth()->user()->id;
        $expenses = $this->model->where('user_id', $userId)->get();
        return $expenses;
    }

    public function delete(): bool{
        return $this->model->delete();
       
    }

    public function create(Array $data): Model{
        $expenses =  $this->model->create($data);
        $user = User::find($data['user_id']);
        $user->notify(new ExpenseCreated($expenses));
        return $expenses;
    }

}