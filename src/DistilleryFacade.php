<?php
namespace matejsvajger\Distillery;

use Illuminate\Support\Facades\Facade;

class DistilleryFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'distillery';
    }
}
