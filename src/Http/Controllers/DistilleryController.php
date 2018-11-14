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

    public function boil($key)
    {
        $models = config('distillery.routing.models');

        return array_key_exists($key, $models) && class_exists($models[$key])
            ? Distillery::distill($models[$key])
            : abort(404);
    }
}
