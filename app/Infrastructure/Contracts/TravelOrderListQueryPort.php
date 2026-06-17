<?php

declare(strict_types=1);

namespace App\Infrastructure\Contracts;

use App\Domain\TravelOrder\Criteria\ListTravelOrdersCriteria;
use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
use Illuminate\Database\Eloquent\Builder;

interface TravelOrderListQueryPort
{
    /**
     * @param  Builder<TravelOrderModel>  $query
     * @return Builder<TravelOrderModel>
     */
    public function apply(Builder $query, ListTravelOrdersCriteria $criteria): Builder;
}
