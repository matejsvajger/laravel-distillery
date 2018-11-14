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


    protected $template = 'Blank';

    /**
     * List of fields to sort on.
     *
     * @var string
     */
    protected $sortFields;

    /**
     * Default field to sort on.
     *
     * @var string
     */
    protected $defaultSortField;

    /**
     * Default direction of sorting.
     *
     * @var string
     */
    protected $defaultSortDirection;

    /**
     * Name of field to search on..
     *
     * @var string
     */
    protected $searchField;

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

        $filter = $this->getDefaultNamespace(null) . '\\' . $name;
        $this->comment("\nYou're about to generate {$filter} filter.");

        $this->template = $this->choice('Choose a filter template:', ['Blank', 'Sorting', 'Search'], 0);

        // Handle selected template data
        $templateHanle = 'handle' . $this->template;
        if (method_exists($this, $templateHanle)) {
            $this->$templateHanle();
        }

        // Standard generator handle
        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($name);

        if ((! $this->hasOption('force') ||
             ! $this->option('force')) &&
             $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type.' already exists!');

            return false;
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($name));

        $this->info($this->type.' created successfully.');
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

    public function handleSorting()
    {
        $this->sortFields = $this->ask('On which fields do you wish to sort? (enter comma seperated values)');
        $this->defaultSortField = $this->choice('On which field do you wish to sort by default?', array_map(
            function ($field) { return trim($field); },
            explode(',', $this->sortFields)
        ), 0);
        $this->defaultSortDirection = $this->choice('Default sorting direction?', ['asc', 'desc']);
    }

    public function handleSearch()
    {
        $this->searchField = $this->ask('On which field do you wish to search on?');
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
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        $templateSeeder = 'replace'. $this->template . 'Placeholders';

        $template = $this
            ->replaceNamespace($stub, $name)
            ->replaceClass($stub, $name);

        return method_exists($this, $templateSeeder)
            ? $this->$templateSeeder($template, $name)
            : $template;
    }

    /**
     * Replace the placeholders for the sorting stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceSortingPlaceholders(&$stub, $name)
    {
        return str_replace(
            [':allowed_fields', ':default_field', ':default_direction'],
            [
                "'" .implode("', '", array_map(
                    function ($field) { return trim($field); },
                    explode(',', $this->sortFields)
                )) . "'",
                trim($this->defaultSortField),
                $this->defaultSortDirection
            ],
            $stub
        );
    }

    /**
     * Replace the placeholders for the search stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceSearchPlaceholders(&$stub, $name)
    {
        return str_replace(
            [':field'],
            [$this->searchField],
            $stub
        );
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = strtolower($this->template);
        return __DIR__."/../../stubs/{$stub}.stub";
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
