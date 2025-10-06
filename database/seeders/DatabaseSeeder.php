<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $this->command->info('Starting postal codes import…');

        $path = storage_path('storage/app/iranyitoszamok.xlsx');
        Artisan::call('import:postal-codes', ['path' => $path]);

        $this->command->info('Postal codes import finished.');
    }
    }
    

