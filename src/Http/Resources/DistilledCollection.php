<?php

namespace matejsvajger\Distillery\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\UrlWindow;
use Illuminate\Support\Collection;

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
        return [
            'hasPages'     => $this->hasPages(),
            'onFirstPage'  => $this->onFirstPage(),
            'hasMorePages' => $this->hasMorePages(),

            'currentPage'     => $this->currentPage(),
            'nextPageUrl'     => $this->nextPageUrl(),
            'previousPageUrl' => $this->previousPageUrl(),

            'data'     => $this->collection,
            'elements' => $this->elements(),
            'total'    => $this->total()
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
     * Get the array of pagination elements.
     *
     * @return array
     */
    protected function elements()
    {
        $window = UrlWindow::make($this->resource);
        $qs = $this->qs();

        foreach ($window as $key => &$value) {
            if (is_array($value)) {
                foreach ($value as &$url) {
                    $url .= $qs;
                }
            }
        }

        return array_filter([
            $window['first'],
            is_array($window['slider']) ? '...' : null,
            $window['slider'],
            is_array($window['last']) ? '...' : null,
            $window['last'],
        ]);
    }

    /**
     * Get the url query string of filter values.
     *
     * @return string
     */
    protected function qs()
    {
        return '&' . http_build_query(
            $this->filters->except('page')->all()
        );
    }
}
