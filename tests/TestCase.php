<?php
namespace matejsvajger\Distillery\Tests;

use matejsvajger\Distillery\DistilleryFacade;
use matejsvajger\Distillery\DistilleryServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     * @return lasselehtinen\MyPackage\MyPackageServiceProvider
     */
    protected function getPackageProviders($app)
    {
        return [
            DistilleryServiceProvider::class
        ];
    }

    /**
     * Load package alias
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Distillery' => DistilleryFacade::class,
        ];
    }
}
