<?php

namespace App\Api\V1\Services;

use App\Api\V1\Models\Point;

class PointLocationService
{

    const BOUNDARY = 1;
    const INSIDE = 2;
    const OUTSIDE = 3;
    const VERTEX = 4;

    public $pointOnVertex = true;

    private $polygon;

    function __construct(array $polygon)
    {
        $this->polygon = $polygon;
    }

    function pointInPolygon(Point $point, $pointOnVertex = true)
    {
        $this->pointOnVertex = $pointOnVertex;

        $point = $point->toCoordinates();
        $points = array();

        /** @var Point $vertex */
        foreach ($this->polygon as $vertex) {
            $points[] = $vertex->toCoordinates();
        }

        if ($this->pointOnVertex == true && $this->pointOnVertex($point, $points) == true) {
            return self::VERTEX;
        }

        $intersections = 0;
        $points_count = count($points);

        for ($i = 1; $i < $points_count; $i++) {
            $point1 = $points[$i - 1];
            $point2 = $points[$i];
            if ($point1['y'] == $point2['y'] && $point1['y'] == $point['y'] && $point['x'] > min($point1['x'], $point2['x']) && $point['x'] < max($point1['x'], $point2['x'])) { // Check if point is on an horizontal polygon boundary
                return self::BOUNDARY;
            }
            if ($point['y'] > min($point1['y'], $point2['y']) && $point['y'] <= max($point1['y'], $point2['y']) && $point['x'] <= max($point1['x'], $point2['x']) && $point1['y'] != $point2['y']) {
                $xinters = ($point['y'] - $point1['y']) * ($point2['x'] - $point1['x']) / ($point2['y'] - $point1['y']) + $point1['x'];

                if ($xinters == $point['x']) {
                    return self::BOUNDARY;
                }
                if ($point1['x'] == $point2['x'] || $point['x'] <= $xinters) {
                    $intersections++;
                }
            }
        }

        if ($intersections % 2 != 0) {
            return self::INSIDE;
        } else {
            return self::OUTSIDE;
        }
    }

    function pointOnVertex($point, $points)
    {
        foreach ($points as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }

    }

}
