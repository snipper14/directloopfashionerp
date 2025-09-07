<?php

namespace App\Traits;

use App\Scopes\BranchScope;

trait AddsBranchScope
{
    public static function bootAddsBranchScope()
    {
        static::addGlobalScope(new BranchScope);
    }
}
