<?php

namespace matejsvajger\Distillery\Traits;

use Distillery;

trait Distillable
{

    /**
     * Distillery model config array.
     *
     * @var int
     */
    protected $distillery = [
        'hidden' => [
            //
        ],
        'default' => [
            //
        ]
    ];

    /**
     * Get the Distilled collection.
     *
     * @return DistilledCollection
     */
    public static function distill($filters = null)
    {
        return Distillery::distill(static::class, $filters);
    }

    /**
     * Get the distillery model config.
     *
     * @return array
     */
    public function getDistilleryConfig()
    {
        return $this->distillery;
    }

    /**
     * Set the distillery model config.
     *
     * @param  array  $distillery
     * @return $this
     */
    public function setDistilleryConfig($distillery)
    {
        $this->distillery = $distillery;

        return $this;
    }

}
