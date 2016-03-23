<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class ErrorTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @param $errors
     * @return array
     */
    public function transform($errors)
    {
        $data = [];

        foreach ($errors as $key => $error) {
            $data[$key] = $error;
        }

        return ['errors' => $data];
    }
}