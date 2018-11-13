<?php

namespace matejsvajger\Distillery;

use ReflectionClass;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\AbstractPaginator;
use matejsvajger\Distillery\Http\Resources\DistilledCollection;

class Distillery
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function distill($model, $filters = null) : DistilledCollection
    {
        $model = is_string($model) ? new $model : $model;

        return new DistilledCollection(
            $this->filter($model, $filters),
            $this->buildFilters($model, $filters),
            $this->collects($model)
        );
    }

    public function filter($model, $filters = null) : AbstractPaginator
    {
        $model   = is_string($model) ? new $model : $model;
        $filters = $this->buildFilters($model, $filters);
        $builder = static::applyFilters($model, $filters);

        return $builder->paginate(
            $filters->get('limit', $model->getPerPage())
        );
    }

    public function collects($model)
    {
        $model = is_string($model) ? new $model : $model;
        $modelclass = with(new ReflectionClass($model))->getShortName();
        $resource = $this->createResourceDecorator($modelclass);

        return config('distillery.resource.enabled') && $this->isValidDecorator($resource)
            ? $resource
            : with(new ReflectionClass($model))->getName();
    }

    private function buildFilters(Model $model, $filters = null)
    {
        return $this->applyDefaults(
            $model,
            is_array($filters)
                ? collect(array_merge(
                    $this->request->all(),
                    $filters
                ))
                : collect($this->request->all())
        );
    }

    private function applyDefaults(Model $model, Collection $filters)
    {
        $config = $model->getDistilleryConfig();
        if (array_key_exists('default', $config) && is_array($config['default'])) {
            foreach ($config['default'] as $key => $value) {
                ! $filters->has($key) && $filters->put($key, $value);
            }
        }

        return $filters;
    }

    private function applyFilters(Model $model, Collection $filters) : Builder
    {
        $builder = $model->newQuery();

        foreach ($filters as $filter => $value) {
            $decorator = static::createFilterDecorator($model, $filter);

            if (static::isValidDecorator($decorator)) {
                $builder = $decorator::apply($builder, $value);
            }
        }

        return $builder;
    }

    private function createFilterDecorator(Model $model, string $filter) : string
    {
        $config = $model->getDistilleryConfig();
        $namespace = config('distillery.filters.namespace');
        $modelclass = with(new ReflectionClass($model))->getShortName();
        $fallback = array_key_exists('fallback', $config) && $config['fallback'] === true;

        $filterclass = str_replace(' ', '', ucwords(
            str_replace('_', ' ', strtolower($filter))
        ));

        $decorator = $namespace . "\\${modelclass}\\" . $filterclass;
        $filterFQN = $fallback
            ? (static::isValidDecorator($decorator) ? $decorator : $namespace . "\\" . $filterclass)
            : $decorator;

        return $filterFQN;
    }

    private function createResourceDecorator(string $modelclass) : string
    {
        $namespace = config('distillery.resource.namespace');
        return "${namespace}\\${modelclass}";
    }

    private function isValidDecorator(string $decorator) : bool
    {
        return class_exists($decorator);
    }
}
