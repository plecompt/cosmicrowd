<?php

namespace App\Utils;

/**
 * Represents a 3D vector with x, y, and z coordinates.
 */
class Vector3
{
    /** @var float X coordinate */
    public float $x;

    /** @var float Y coordinate */
    public float $y;

    /** @var float Z coordinate */
    public float $z;

    /**
     * Create a new Vector3 instance.
     *
     * @param float $x X coordinate (default 0)
     * @param float $y Y coordinate (default 0)
     * @param float $z Z coordinate (default 0)
     */
    public function __construct(float $x = 0, float $y = 0, float $z = 0)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /**
     * Convert the vector to an associative array.
     *
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return ['x' => $this->x, 'y' => $this->y, 'z' => $this->z];
    }

    /**
     * Return a string representation of the vector.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "($this->x, $this->y, $this->z)";
    }
}
