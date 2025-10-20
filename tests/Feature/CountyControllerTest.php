<?php

namespace Tests\Feature;

use App\Models\Iranyitoszamok;
use GuzzleHttp\Promise\Create;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CountyControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_get_by_county()
    {
        Iranyitoszamok::factory()->create(['county' => 'Pest']);

        $response = $this->get('/api/county/Pest');

        $response->assertStatus(200);
    }
        public function test_get_postal_codes(): void
    {
        Iranyitoszamok::factory()->create(['zip' => '1011', 'city' => 'Budapest', 'county' => 'Pest']);
        Iranyitoszamok::factory()->create(['zip' => '1012', 'city' => 'Budapest', 'county' => 'Pest']);
        Iranyitoszamok::factory()->create(['zip' => '3300', 'city' => 'Eger', 'county' => 'Heves']);

        $response = $this->getJson('/api/county/Pest');

        $response->assertOk()
            ->assertJsonFragment(['zip' => '1011', 'city' => 'Budapest', 'county' => 'Pest'])
            ->assertJsonFragment(['zip' => '1012', 'city' => 'Budapest', 'county' => 'Pest'])
            ->assertJsonMissing(['zip' => '3300', 'city' => 'Eger', 'county' => 'Heves']);
    }

    public function test_get_city()
    {
        Iranyitoszamok::factory()->create(['city' => 'Budapest']);

        $response = $this->get('/api/city/Budapest');

        $response->assertStatus(200);
    }

}
