<?php

namespace matejsvajger\Distillery\Console;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DistilleryFilterCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'distillery:filter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Distillery filter class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Filter';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $this->input->setArgument('name', $name);

        if ($this->argument('model')) {
            $model = $this->handleModel();

            if (empty($model)) {
                $this->error('Model ' . $this->qualifyModel($model) . ' doesn\'t exist!');
                return false;
            }
        }

        if (parent::handle() === false && ! $this->option('force')) {
            return;
        }
    }

    /**
     * Handles model argument
     * Transforms name into PSR-4 class name and
     * checks if model exists and returns fqn.
     *
     * @return string|null
     */
    public function handleModel()
    {
        $model = Str::studly($this->argument('model'));
        $this->input->setArgument('model', $model);

        if ($this->modelExists($model)) {
            return $this->qualifyModel($model);
        }

        return null;
    }

    /**
     * Checks if model exists.
     *
     * @param string $model
     * @return bool
     */
    public function modelExists($model)
    {
        return class_exists(
            $this->qualifyModel($model)
        );
    }

    /**
     * Parse the model class name and format according to the models namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyModel($name)
    {
        $name = ltrim($name, '\\/');

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        $name = str_replace('/', '\\', $name);

        return $this->qualifyModel(
            $this->getModelNamespace(trim($rootNamespace, '\\')) . '\\' . $name
        );
    }



    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../../stubs/filter.stub';
    }

    /**
     * Get the default namespace for the filter.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $model = $this->argument('model');
        return config('distillery.filters.namespace') . ($model ? '\\' . $model : '');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getModelNamespace($rootNamespace)
    {
        return config('distillery.models.namespace');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [

            ['name', InputArgument::REQUIRED, 'The name of the filter'],

            ['model', InputArgument::OPTIONAL, 'The name of the model'],

        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [

            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the filter already exists.'],

        ];
    }
}
