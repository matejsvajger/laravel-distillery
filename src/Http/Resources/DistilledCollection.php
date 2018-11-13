<?php

namespace matejsvajger\Distillery\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\UrlWindow;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class DistilledCollection extends ResourceCollection
{
    protected $filters;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  Collection  $filters
     * @param  string  $collects
     * @return void
     */
    public function __construct($resource, Collection $filters, string $collects = null)
    {
        $this->filters  = $filters;
        $this->collects = $collects;

        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'pagination' => $this->elements(),

            'meta' => [
                'has_pages' => $this->hasPages(),
                'on_first_page' => $this->onFirstPage(),
                'has_more_pages' => $this->hasMorePages(),

                'next_page_url' => $this->nextPageUrl(),
                'previous_page_url' => $this->previousPageUrl(),
            ],
        ];
    }
    /**
     * Get the URL for the previous page, or null.
     *
     * @return string|null
     */
    public function previousPageUrl()
    {
        $url = parent::previousPageUrl();
        return $url ? $url . $this->qs() : null;
    }

    /**
     * The URL for the next page, or null.
     *
     * @return string|null
     */
    public function nextPageUrl()
    {
        $url = parent::nextPageUrl();
        return $url ? $url . $this->qs() : null;
    }

    /**
     * Render the paginator using the given view.
     *
     * @param  string|null  $view
     * @param  array  $data
     * @return \Illuminate\Support\HtmlString
     */
    public function links($view = null, $data = [])
    {
        return $this->render($view, $data);
    }

    /**
     * Render the paginator using the given view.
     *
     * @param  string|null  $view
     * @param  array  $data
     * @return \Illuminate\Support\HtmlString
     */
    public function render($view = null, $data = [])
    {
        return new HtmlString(static::viewFactory()->make($view ? : Paginator::$defaultView, array_merge($data, [
            'paginator' => $this,
            'elements' => $this->elements(),
        ]))->render());
    }

    /**
     * Get the array of pagination elements.
     *
     * @return array
     */
    protected function elements()
    {
        $qs = $this->qs();
        $window = UrlWindow::make($this->resource);

        foreach ($window as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $index => $url) {
                    $value[$index] = $url . $qs;
                }
                $window[$key] = $value;
            }
        }

        return array_values(array_filter([
            $window['first'],
            is_array($window['slider']) ? '...' : null,
            $window['slider'],
            is_array($window['last']) ? '...' : null,
            $window['last'],
        ]));
    }

    /**
     * Get the url query string of filter values.
     *
     * @return string
     */
    protected function qs()
    {
        $config  = $this->resource->first()->getDistilleryConfig();
        $hidden  = array_key_exists('hidden', $config) ? $config['hidden'] : [];
        $filters = $this->filters->except(array_merge(
            ['page'],
            $hidden
        ));

        return $filters->count() > 0 ? '&' . http_build_query(
            $filters->all()
        ) : '';
    }
}
