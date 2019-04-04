<?php

/*
 * This file extends JsonApiSerializer from the League\Fractal package 
 * to implement relationships correctly.
 *
 * (c) Phil Sturgeon <me@philsturgeon.uk>
 *     Hamza Whitmore <hwhitmore@skylinedynamics.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the League\Fractal source code.
 */

namespace App\Api\V1\Serializer;

use InvalidArgumentException;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\JsonApiSerializer;

class SDJsonApiSerializer extends JsonApiSerializer
{

    /**
     * Serialize an item.
     *
     * @param string $resourceKey
     * @param array $data
     *
     * @return array
     */
    public function item($resourceKey, array $data)
    {
        $id = $this->getIdFromData($data);

        $resource = [
            'data' => [
                'type' => $resourceKey,
                'id' => "$id",
                'attributes' => $data
            ],
        ];

        unset($resource['data']['attributes']['id']);

        if ($this->shouldIncludeLinks()) {
            $resource['data']['links'] = [
                'self' => $resource['data']['attributes']['self']
            ];
            unset($resource['data']['attributes']['self']);
        }

        if (array_key_exists('relationships', $data)) {
            $relationships = $data['relationships'];
            if (is_array($relationships) && count($relationships) > 0) {
                $resource['data']['relationships'] = $relationships;
            }
            unset($resource['data']['attributes']['relationships']);
        }

        return $resource;
    }

}
