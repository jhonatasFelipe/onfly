<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface BaseCrudServiceInterface
{
    public function create(Array $data): Model;

    public function list(Array $filters = []) : Collection;

    public function update(string $id, Array $data): Model;

    public function getById(string $id): Model;
}
