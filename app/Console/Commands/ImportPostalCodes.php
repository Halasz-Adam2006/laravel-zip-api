<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PostalCode;
use App\Models\County;

class ImportPostalCodes extends Command
{
    protected $signature = 'import:postal-codes {path}';
    protected $description = 'Import postal codes from CSV';

    public function handle()
    {
        $path = $this->argument('path');
        $this->info("Importing from: $path");

        if (!file_exists($path)) {
            $this->error("File not found: $path");
            return;
        }
        
        
        // Clear existing data
        $this->info("Clearing existing data...");
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PostalCode::truncate();
        County::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $total = max(0, count(file($path)) - 1);

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 1000, ',');
        $header = array_map(function($h) {
            $h = trim($h, "\" \t\n\r\0\x0B");
            // If header contains an embedded newline (e.g. "Postal Code\nIrányítószám"),
            // take the last line which contains the localized column name.
            $parts = preg_split('/\r?\n/', $h);
            return trim(end($parts));
        }, $header);

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $data = array_map(function($d) {
                return trim($d, "\" \t\n\r\0\x0B");
            }, $data);

            $row = array_combine($header, $data);

            if (empty($row['Irányítószám'])) {
                $bar->advance();
                continue;
            }

            // Find or create county
            $countyName = $row['Megye'] ?? null;
            
            // If county is empty and city is Budapest, set county to Budapest
            if (empty($countyName) && isset($row['Település']) && trim($row['Település']) === 'Budapest') {
                $countyName = 'Budapest';
            }
            
            if (empty($countyName)) {
                $this->warn("Skipping row - no county: " . json_encode($row));
                $bar->advance();
                continue; // Skip rows without county
            }
            
            // Trim and normalize county name
            $countyName = trim($countyName);
            
            $county = County::firstOrCreate(['name' => $countyName]);
            
            if (!$county || !$county->id) {
                $this->error("Failed to create/find county: " . $countyName);
                $bar->advance();
                continue;
            }

            PostalCode::create([
                'zip'        => $row['Irányítószám'],
                'city'       => $row['Település'] ?? null,
                'county_id'  => $county->id,
            ]);

            $bar->advance();
        }

        fclose($handle);
        $bar->finish();
        $this->newLine();
        $this->info('Counts after import - counties: ' . County::count() . ', postal_codes: ' . PostalCode::count());
        $this->info('Import completed.');
    }
}


?>
