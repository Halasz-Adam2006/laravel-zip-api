<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PostalCodesImport;

class ImportPostalCodes extends Command
{
    protected $signature = 'import:postal-codes {path}';
    protected $description = 'Import postal codes from an Excel file';

    public function handle()
    {
        $path = $this->argument('path');
        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        Excel::import(new PostalCodesImport, $path);
        $this->info('Import completed.');
        return 0;
    }
}
