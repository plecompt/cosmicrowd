<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\StarSeeder;

class GenerateStarsCommand extends Command
{
    protected $signature = 'stars:generate 
                            {count : Nombre d\'étoiles à générer}
                            {--user= : ID de l\'utilisateur (optionnel)}';
    
    protected $description = 'Génère x étoiles dans CosmiCrowd';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $userId = $this->option('user') ? (int) $this->option('user') : null;

        try {
            $starSeeder = new StarSeeder();
            $starsCreated = $starSeeder->generateStars($count, $userId);

            $this->info("🌟 {$starsCreated} étoiles créées avec succès !");
            
            if ($userId) {
                $this->info("🎯 Toutes assignées à l'utilisateur ID: {$userId}");
            } else {
                $this->info("🎲 Distribuées aléatoirement entre tous les utilisateurs");
            }

        } catch (\Exception $e) {
            $this->error("❌ Erreur : " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
