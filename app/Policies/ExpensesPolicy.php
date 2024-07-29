<?php

namespace App\Policies;

use App\Models\Expenses;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ExpensesPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function toManage(User $user,Expenses $expenses):bool{
        return  $user->id === $expenses->user_id;
    }

}
