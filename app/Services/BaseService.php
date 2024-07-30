<?php

namespace App\Services;

use App\Interfaces\BaseCrudServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

Class BaseService implements BaseCrudServiceInterface{

    public function __construct(private Model $model){}

    public function create(Array $data): Model{

        return $this->model->create($data)->first();
    }

    public function list(Array $filters = []) : Collection{

        return $this->model->all();
    }

    public function delete(): bool{
        return $this->model->delete();
    }

    public function update(string $id, Array $data): Model{
        $model = $this->getById($id);
        $model->update($data);
        return $model;
    }

    public function getById(string $id): Model{
        return $this->model->find($id);
    }
}