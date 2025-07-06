<?php

namespace App\Utils;

// Need a Vector3 for position in 3d space
class Vector3
{
    public float $x;
    public float $y;
    public float $z;

    public function __construct(float $x = 0, float $y = 0, float $z = 0)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    public function toArray(): array
    {
        return ['x' => $this->x, 'y' => $this->y, 'z' => $this->z];
    }

    public function __toString(): string
    {
        return "($this->x, $this->y, $this->z)";
    }
}
