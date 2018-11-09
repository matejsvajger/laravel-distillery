<?php

namespace matejsvajger\Distillery\Http\Controllers;

use Distillery;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DistilleryController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function boil(Request $request, $model)
    {
        $allowed    = config('distillery.routing.models');
        $namespace  = config('distillery.models.namespace');
        $modelname  = Str::studly(
            Str::singular($model)
        );

        $class = "${namespace}\\${modelname}";

        return class_exists($class) && in_array($class, $allowed)
            ? Distillery::distill($class)->toArray($request)
            : abort(404);
    }
}
