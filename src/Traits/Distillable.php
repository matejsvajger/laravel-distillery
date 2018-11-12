<?php

namespace matejsvajger\Distillery\Traits;

use Distillery;

trait Distillable
{
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
        return property_exists($this, 'distillery')
            ? $this->distillery
            : [
                'hidden' => [
                    //
                ],
                'default' => [
                    //
                ]
            ];
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
