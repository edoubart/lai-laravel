<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class SuccessTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @param $successes
     * @return array
     */
    public function transform($successes)
    {
        $data = [];

        foreach ($successes as $key => $success) {
            $data[$key] = $success;
        }

        return ['successes' => $data];
    }
}