<?php
namespace App\Api\V1\Models;


class Point
{
    private $x;
    private $y;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @return mixed
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return mixed
     */
    public function getY()
    {
        return $this->y;
    }

    public function toCoordinates(){
        return [
            'x'=> $this->getX(),
            'y'=> $this->getY()
        ];
    }

}