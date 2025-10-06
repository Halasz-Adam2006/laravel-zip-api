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

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 1000, ',');
        $header = array_map(function($h) {
            return trim($h, "\" \t\n\r\0\x0B");
        }, $header);
        $this->info('CSV Header: ' . json_encode($header));

        // Import all rows
        $count = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $data = array_map(function($d) {
                return trim($d, "\" \t\n\r\0\x0B");
            }, $data);

            $row = array_combine($header, $data);

            // Skip rows with missing zip
            if (empty($row['Irányítószám'])) {
                continue;
            }

            PostalCode::create([
                'zip'    => $row['Irányítószám'],
                'city'   => $row['Település'] ?? null,
                'county' => $row['Megye'] ?? null,
            ]);
            $count++;
        }
        fclose($handle);
        $this->info("Import completed. Imported $count rows.");
    }
}


?>
