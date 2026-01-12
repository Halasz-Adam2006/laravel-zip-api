<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Check if test user exists
$user = User::where('email', 'test@example.com')->first();

if (!$user) {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);
    echo "✓ Created test user: test@example.com / password123\n";
} else {
    echo "✓ Found existing user: test@example.com\n";
}

// Delete old tokens
$user->tokens()->delete();

// Create new token
$token = $user->createToken('api-token')->plainTextToken;

echo "✓ Generated token: " . substr($token, 0, 20) . "...\n";
echo "\nTest with curl:\n";
echo "curl -X GET http://127.0.0.1:8000/api/user \\\n";
echo "  -H 'Authorization: Bearer " . $token . "' \\\n";
echo "  -H 'Accept: application/json'\n";

// Also test login endpoint
echo "\n✓ Or login with:\n";
echo "curl -X POST http://127.0.0.1:8000/api/login \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\"email\": \"test@example.com\", \"password\": \"password123\"}'\n";
