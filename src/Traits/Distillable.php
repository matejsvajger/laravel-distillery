<?php

namespace matejsvajger\Distillery\Traits;

use Distillery;
use Illuminate\Support\Collection;

trait Distillable
{
    public static function distill($filters = null)
    {
        return Distillery::distill(static::class, $filters);
    }

    public static function distilleryDefaults()
    {
        return [
            //
        ];
    }
}
