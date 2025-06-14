<?php

namespace App\Utils;

class StellarRanges
{
    public static function getStarRanges(string $type): array
    {
        return match($type) {
            'brown_dwarf' => [
                'mass' => ['min' => 0.01, 'max' => 0.08, 'avg' => 0.05],
                'gravity' => ['min' => 10, 'max' => 300, 'avg' => 100],
                'surface_temp' => ['min' => 500, 'max' => 2500, 'avg' => 1200],
                'luminosity' => ['min' => 0.000001, 'max' => 0.0001, 'avg' => 0.00001]
            ],
            
            'red_dwarf' => [
                'mass' => ['min' => 0.08, 'max' => 0.6, 'avg' => 0.3],
                'gravity' => ['min' => 20, 'max' => 100, 'avg' => 50],
                'surface_temp' => ['min' => 2300, 'max' => 3800, 'avg' => 3200],
                'luminosity' => ['min' => 0.0001, 'max' => 0.1, 'avg' => 0.01]
            ],
            
            'yellow_dwarf' => [
                'mass' => ['min' => 0.8, 'max' => 1.2, 'avg' => 1.0],
                'gravity' => ['min' => 200, 'max' => 350, 'avg' => 274],
                'surface_temp' => ['min' => 5200, 'max' => 6000, 'avg' => 5778],
                'luminosity' => ['min' => 0.5, 'max' => 1.5, 'avg' => 1.0]
            ],
            
            'white_dwarf' => [
                'mass' => ['min' => 0.3, 'max' => 1.4, 'avg' => 0.6],
                'gravity' => ['min' => 100000, 'max' => 10000000, 'avg' => 1000000],
                'surface_temp' => ['min' => 4000, 'max' => 150000, 'avg' => 25000],
                'luminosity' => ['min' => 0.0001, 'max' => 0.1, 'avg' => 0.001]
            ],
            
            'red_giant' => [
                'mass' => ['min' => 0.8, 'max' => 8, 'avg' => 2.0],
                'gravity' => ['min' => 0.1, 'max' => 10, 'avg' => 1],
                'surface_temp' => ['min' => 2500, 'max' => 4500, 'avg' => 3500],
                'luminosity' => ['min' => 10, 'max' => 5000, 'avg' => 100]
            ],
            
            'blue_giant' => [
                'mass' => ['min' => 8, 'max' => 25, 'avg' => 15],
                'gravity' => ['min' => 50, 'max' => 200, 'avg' => 100],
                'surface_temp' => ['min' => 10000, 'max' => 30000, 'avg' => 20000],
                'luminosity' => ['min' => 1000, 'max' => 100000, 'avg' => 10000]
            ],
            
            'red_supergiant' => [
                'mass' => ['min' => 8, 'max' => 40, 'avg' => 25],
                'gravity' => ['min' => 0.01, 'max' => 1, 'avg' => 0.1],
                'surface_temp' => ['min' => 2500, 'max' => 4500, 'avg' => 3500],
                'luminosity' => ['min' => 1000, 'max' => 1000000, 'avg' => 100000]
            ],
            
            'blue_supergiant' => [
                'mass' => ['min' => 15, 'max' => 90, 'avg' => 40],
                'gravity' => ['min' => 1, 'max' => 50, 'avg' => 10],
                'surface_temp' => ['min' => 20000, 'max' => 50000, 'avg' => 30000],
                'luminosity' => ['min' => 10000, 'max' => 2000000, 'avg' => 500000]
            ],
            
            'hypergiant' => [
                'mass' => ['min' => 25, 'max' => 315, 'avg' => 100],
                'gravity' => ['min' => 0.01, 'max' => 10, 'avg' => 1],
                'surface_temp' => ['min' => 3000, 'max' => 50000, 'avg' => 25000],
                'luminosity' => ['min' => 50000, 'max' => 8700000, 'avg' => 1000000]
            ],
            
            'neutron_star' => [
                'mass' => ['min' => 1.1, 'max' => 2.17, 'avg' => 1.4],
                'gravity' => ['min' => 100000000000, 'max' => 1000000000000, 'avg' => 200000000000],
                'surface_temp' => ['min' => 100000, 'max' => 10000000, 'avg' => 1000000],
                'luminosity' => ['min' => 0.001, 'max' => 10, 'avg' => 0.1]
            ],
            
            'pulsar' => [
                'mass' => ['min' => 1.1, 'max' => 2.17, 'avg' => 1.4],
                'gravity' => ['min' => 100000000000, 'max' => 1000000000000, 'avg' => 200000000000],
                'surface_temp' => ['min' => 100000, 'max' => 10000000, 'avg' => 1000000],
                'luminosity' => ['min' => 0.1, 'max' => 100, 'avg' => 1] 
            ],
            
            'variable' => [
                'mass' => ['min' => 0.8, 'max' => 20, 'avg' => 5],
                'gravity' => ['min' => 1, 'max' => 200, 'avg' => 20],
                'surface_temp' => ['min' => 2000, 'max' => 30000, 'avg' => 6000],
                'luminosity' => ['min' => 0.1, 'max' => 10000, 'avg' => 50]
            ],
            
            'binary' => [
                'mass' => ['min' => 0.5, 'max' => 50, 'avg' => 2],
                'gravity' => ['min' => 10, 'max' => 1000, 'avg' => 150],
                'surface_temp' => ['min' => 2000, 'max' => 30000, 'avg' => 5000],
                'luminosity' => ['min' => 0.1, 'max' => 1000, 'avg' => 5]
            ],
            
            'ternary' => [
                'mass' => ['min' => 1, 'max' => 100, 'avg' => 3],
                'gravity' => ['min' => 10, 'max' => 500, 'avg' => 100],
                'surface_temp' => ['min' => 2500, 'max' => 25000, 'avg' => 5500],
                'luminosity' => ['min' => 0.5, 'max' => 5000, 'avg' => 10]
            ],
            
            'black_hole' => [
                'mass' => ['min' => 3, 'max' => 66000000000, 'avg' => 10],
                'gravity' => ['min' => PHP_FLOAT_MAX, 'max' => PHP_FLOAT_MAX, 'avg' => PHP_FLOAT_MAX],
                'surface_temp' => ['min' => 0, 'max' => 0, 'avg' => 0],
                'luminosity' => ['min' => 0, 'max' => 0, 'avg' => 0] 
            ],
            
            default => [
                'mass' => ['min' => 1, 'max' => 1, 'avg' => 1],
                'gravity' => ['min' => 274, 'max' => 274, 'avg' => 274],
                'surface_temp' => ['min' => 5778, 'max' => 5778, 'avg' => 5778],
                'luminosity' => ['min' => 1, 'max' => 1, 'avg' => 1]
            ]
        };
    }
    
    public static function generateRandomStar(string $type): array
    {
        $ranges = self::getStarRanges($type);
        
        // generate mass and surface_temp randomly in range for given star_type
        $mass = self::randomFloat($ranges['mass']['min'], $ranges['mass']['max']);
        $surfaceTemp = random_int($ranges['surface_temp']['min'], $ranges['surface_temp']['max']);
        
        // diameter
        $diameter = self::calculateDiameter($mass, $type);
        
        // gravity
        $gravity = self::calculateGravity($mass, $diameter, $type);
        
        // luminosity
        $luminosity = self::calculateLuminosity($diameter, $surfaceTemp, $type);
        
        return [
            'mass' => (int)round($mass, 3),
            'diameter' => (int)round($diameter, 3), 
            'gravity' => round($gravity, 2),
            'surface_temp' => (int)$surfaceTemp,
            'luminosity' => (int)round($luminosity, 6)
        ];
    }

    private static function calculateDiameter(float $mass, string $type): float
    {
        $baseDiameter = match($type) {
            'brown_dwarf' => 0.1,
            'red_dwarf' => 0.5, 
            'yellow_dwarf' => 1.0,
            'white_dwarf' => 0.01,
            'red_giant' => 50,
            'blue_giant' => 7,
            'red_supergiant' => 1000,
            'blue_supergiant' => 25,
            'hypergiant' => 2000,
            'neutron_star' => 0.00002,
            'pulsar' => 0.00002,
            'variable' => self::randomFloat(0.5, 100),
            'binary' => self::randomFloat(1, 10), 
            'ternary' => self::randomFloat(1, 15),
            'black_hole' => 0.000001,
            default => 1.0
        };
        
        //magic numbers everywhere
        $massEffect = match($type) {
            'white_dwarf' => 1.0,
            'neutron_star', 'pulsar' => 1.0,
            'black_hole' => sqrt($mass / 10),
            'red_giant', 'red_supergiant' => sqrt($mass / 2.0),
            default => pow($mass, 0.8)
        };
        
        return $baseDiameter * $massEffect;
    }
    
    private static function calculateGravity(float $mass, float $diameter, string $type): float
    {
        $radius = $diameter / 2;
        
        if ($type === 'black_hole') {
            return PHP_FLOAT_MAX;
        }

        $solarGravity = 274; 
        
        $gravity = $solarGravity * ($mass / ($radius * $radius));
        
        return max($gravity, 0.01);
    }
    
    // Stefan-Boltzmann
    private static function calculateLuminosity(float $diameter, int $surfaceTemp, string $type): float
    {
        $radius = $diameter / 2;
        
        if ($type === 'black_hole') {
            return 0;
        }
        
        //Stefan-Boltzmann : L ∝ R² × T⁴
        $solarTemp = 5778;
        $tempRatio = $surfaceTemp / $solarTemp;
        
        // L = (R/R_soleil)² × (T/T_soleil)⁴
        $luminosity = ($radius * $radius) * pow($tempRatio, 4);
        
        if (in_array($type, ['neutron_star', 'pulsar'])) {
            // Luminosity in visible spectrum
            $luminosity *= 0.1;
        }
        
        return self::clamp($luminosity, 0.000001, 2147483647); //clamping to int_max (in 32bits for db)
    }
    
    private static function clamp($current, $min, $max) {
        return max($min, min($max, $current));
    }

    private static function randomFloat(float $min, float $max): float
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
} 