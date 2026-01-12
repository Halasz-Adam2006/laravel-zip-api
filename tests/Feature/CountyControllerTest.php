<?php

use App\Models\County;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class CountyControllerTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_index_returns_all_counties()
    {
        County::factory()->create(['name' => 'Pest']);
        County::factory()->create(['name' => 'Baranya']);

        $response = $this->getJson('/api/counties');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Pest'])
            ->assertJsonFragment(['name' => 'Baranya']);
    }

    public function test_index_filters_by_needle()
    {
        County::factory()->create(['name' => 'Bács-Kiskun']);
        County::factory()->create(['name' => 'Baranya']);

        $response = $this->getJson('/api/counties?needle=bar');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Baranya'])
            ->assertJsonMissing(['name' => 'Bács-Kiskun']);
    }

    public function test_store_creates_new_county()
    {
        $response = $this->postJson('/api/counties', [
            'name' => 'Somogy'
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Somogy']);

        $this->assertDatabaseHas('counties', ['name' => 'Somogy']);
    }

    public function test_update_modifies_existing_county()
    {
        $county = County::factory()->create(['name' => 'Heves']);

        $response = $this->putJson("/api/counties/{$county->id}", [
            'name' => 'Nógrád'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Nógrád']);

        $this->assertDatabaseHas('counties', ['id' => $county->id, 'name' => 'Nógrád']);
    }

    public function test_update_returns_404_for_missing_county()
    {
        $response = $this->putJson('/api/counties/999', [
            'name' => 'Tolna'
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Not found!']);
    }

    public function test_delete_removes_county()
    {
        $county = County::factory()->create(['name' => 'Vas']);

        $response = $this->deleteJson("/api/counties/{$county->id}");

        $response->assertStatus(410)
            ->assertJsonFragment(['message' => 'Deleted']);

        $this->assertDatabaseMissing('counties', ['id' => $county->id]);
    }
}
