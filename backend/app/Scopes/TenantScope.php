<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        try {
            if (auth()->check()) {
                $schoolId = auth()->user()->school_id;
                if ($schoolId) {
                    $builder->where($model->getTable() . '.school_id', $schoolId);
                }
            }
        } catch (\Throwable $e) {
            // running in console or no auth available — skip scoping
        }
    }
}
