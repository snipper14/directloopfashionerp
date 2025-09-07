<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BranchScope implements Scope
{

    public function apply(Builder $builder, Model $model)
    {
        // dd(">>>>" + Auth::user()->branch_id);
        //$builder->where('branch_id',  Auth::user()->branch_id);
         $table = $model->getTable(); // dynamically get table name, e.g. 'inventories'
        $builder->where($table . '.branch_id', Auth::user()->branch_id);
    }
}
