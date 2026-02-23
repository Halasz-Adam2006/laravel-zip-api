<?php

namespace Database\Seeders;

use App\Models\PostalCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PostalCodeSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = base_path('iranyitoszamok.csv');
        
        if (!File::exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $file = fopen($csvFile, 'r');
        
        // Skip header row
        fgetcsv($file);
        // Skip empty row
        fgetcsv($file);
        
        $this->command->info('Importing postal codes...');
        
        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) >= 3 && !empty($row[0])) {
                PostalCode::create([
                    'postal_code' => trim($row[0]),
                    'place_name' => trim($row[1]),
                    'county' => trim($row[2]),
                ]);
                $count++;
            }
        }
        
        fclose($file);
        
        $this->command->info("Imported {$count} postal codes successfully!");
    }
}
