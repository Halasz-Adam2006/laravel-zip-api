<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PostalCode;

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
        
        $total = max(0, count(file($path)) - 1);

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 1000, ',');
        $header = array_map(function($h) {
            return trim($h, "\" \t\n\r\0\x0B");
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

            \App\Models\PostalCode::create([
                'zip'    => $row['Irányítószám'],
                'city'   => $row['Település'] ?? null,
                'county' => $row['Megye'] ?? null,
            ]);

            $bar->advance();
        }

        fclose($handle);
        $bar->finish();
        $this->newLine();
        $this->info('Import completed.');
    }
}


?>
