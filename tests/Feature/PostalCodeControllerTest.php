<?php

use App\Models\PostalCode;
use App\Models\County;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class PostalCodeControllerTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;


    public function test_index_returns_all_postal_codes()
    {
        $county = County::factory()->create(['name' => 'Pest']);
        PostalCode::factory()->create(['zip' => '1011', 'city' => 'Budapest', 'county_id' => $county->id]);
        PostalCode::factory()->create(['zip' => '1012', 'city' => 'Budapest', 'county_id' => $county->id]);

        $response = $this->getJson('/api/postal-codes');

        $response->assertStatus(200)
            ->assertJsonFragment(['zip' => '1011'])
            ->assertJsonFragment(['zip' => '1012']);
    }

    public function test_show_returns_postal_codes_by_zip()
    {
        $county = County::factory()->create(['name' => 'Pest']);
        PostalCode::factory()->create(['zip' => '1011', 'city' => 'Budapest', 'county_id' => $county->id]);

        $response = $this->getJson('/api/postal-codes/1011');

        $response->assertStatus(200)
            ->assertJsonFragment(['zip' => '1011', 'city' => 'Budapest']);
    }

    public function test_store_creates_new_postal_code()
    {
        $response = $this->postJson('/api/postal-codes', [
            'zip' => '2000',
            'city' => 'Szentendre',
            'county' => 'Pest',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['zip' => '2000', 'city' => 'Szentendre']);

        $this->assertDatabaseHas('postal_codes', ['zip' => '2000', 'city' => 'Szentendre']);
        $this->assertDatabaseHas('counties', ['name' => 'Pest']);
    }

    public function test_update_modifies_postal_code_by_zip()
    {
        $county = County::factory()->create(['name' => 'Pest']);
        PostalCode::factory()->create(['zip' => '1011', 'city' => 'Budapest', 'county_id' => $county->id]);

        $response = $this->putJson('/api/postal-codes/1011', [
            'city' => 'Budapest I.',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['city' => 'Budapest I.']);

        $this->assertDatabaseHas('postal_codes', ['zip' => '1011', 'city' => 'Budapest I.']);
    }

    public function test_destroy_deletes_postal_code_by_zip()
    {
        $county = County::factory()->create(['name' => 'Pest']);
        PostalCode::factory()->create(['zip' => '1011', 'city' => 'Budapest', 'county_id' => $county->id]);

        $response = $this->deleteJson('/api/postal-codes/1011');

        $response->assertStatus(204);
        $this->assertDatabaseMissing('postal_codes', ['zip' => '1011']);
    }


    public function test_show_by_id_returns_postal_code()
    {
        $county = County::factory()->create(['name' => 'Pest']);
        $postalCode = PostalCode::factory()->create(['zip' => '1011', 'city' => 'Budapest', 'county_id' => $county->id]);

        $response = $this->getJson("/api/id/{$postalCode->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['zip' => '1011']);
    }

    public function test_update_by_id_modifies_postal_code()
    {
        $county = County::factory()->create(['name' => 'Pest']);
        $postalCode = PostalCode::factory()->create(['zip' => '1011', 'city' => 'Budapest', 'county_id' => $county->id]);

        $response = $this->putJson("/api/id/{$postalCode->id}", [
            'city' => 'Budapest II.',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['city' => 'Budapest II.']);
    }

    public function test_destroy_by_id_deletes_postal_code()
    {
        $county = County::factory()->create(['name' => 'Pest']);
        $postalCode = PostalCode::factory()->create(['zip' => '1011', 'city' => 'Budapest', 'county_id' => $county->id]);

        $response = $this->deleteJson("/api/id/{$postalCode->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('postal_codes', ['id' => $postalCode->id]);
    }


    public function test_show_by_city_returns_postal_codes()
    {
        $county = County::factory()->create(['name' => 'Pest']);
        PostalCode::factory()->create(['zip' => '1011', 'city' => 'Budapest', 'county_id' => $county->id]);
        PostalCode::factory()->create(['zip' => '1012', 'city' => 'Budapest', 'county_id' => $county->id]);

        $response = $this->getJson('/api/city/Budapest');

        $response->assertStatus(200)
            ->assertJsonFragment(['zip' => '1011'])
            ->assertJsonFragment(['zip' => '1012']);
    }

    public function test_update_by_city_modifies_all_postal_codes()
    {
        $county = County::factory()->create(['name' => 'Pest']);
        PostalCode::factory()->create(['zip' => '1011', 'city' => 'Budapest', 'county_id' => $county->id]);
        PostalCode::factory()->create(['zip' => '1012', 'city' => 'Budapest', 'county_id' => $county->id]);

        $response = $this->putJson('/api/city/Budapest', [
            'city' => 'Budapest Capital',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('postal_codes', ['zip' => '1011', 'city' => 'Budapest Capital']);
        $this->assertDatabaseHas('postal_codes', ['zip' => '1012', 'city' => 'Budapest Capital']);
    }

    public function test_destroy_by_city_deletes_all_postal_codes()
    {
        $county = County::factory()->create(['name' => 'Pest']);
        PostalCode::factory()->create(['zip' => '1011', 'city' => 'Budapest', 'county_id' => $county->id]);
        PostalCode::factory()->create(['zip' => '1012', 'city' => 'Budapest', 'county_id' => $county->id]);

        $response = $this->deleteJson('/api/city/Budapest');

        $response->assertStatus(204);
        $this->assertDatabaseMissing('postal_codes', ['city' => 'Budapest']);
    }

    public function test_show_by_county_returns_postal_codes()
    {
        $county = County::factory()->create(['name' => 'Békés']);
        PostalCode::factory()->create(['zip' => '5600', 'city' => 'Békéscsaba', 'county_id' => $county->id]);
        PostalCode::factory()->create(['zip' => '5700', 'city' => 'Gyula', 'county_id' => $county->id]);

        $response = $this->getJson('/api/county/Békés');

        $response->assertStatus(200)
            ->assertJsonFragment(['city' => 'Békéscsaba'])
            ->assertJsonFragment(['city' => 'Gyula']);
    }

    public function test_show_by_letter_returns_filtered_cities()
    {
        $county = County::factory()->create(['name' => 'Békés']);
        PostalCode::factory()->create(['zip' => '5600', 'city' => 'Békéscsaba', 'county_id' => $county->id]);
        PostalCode::factory()->create(['zip' => '5700', 'city' => 'Gyula', 'county_id' => $county->id]);
        PostalCode::factory()->create(['zip' => '5630', 'city' => 'Békés', 'county_id' => $county->id]);

        $response = $this->getJson('/api/county/Békés/B');

        $response->assertStatus(200)
            ->assertJsonFragment(['city' => 'Békéscsaba'])
            ->assertJsonFragment(['city' => 'Békés'])
            ->assertJsonMissing(['city' => 'Gyula']);
    }

    public function test_export_pdf_returns_pdf_response()
    {
        $county = County::factory()->create(['name' => 'Békés']);
        PostalCode::factory()->create(['zip' => '5600', 'city' => 'Békéscsaba', 'county_id' => $county->id]);

        $response = $this->get('/api/county/Békés/B/export/pdf');

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_export_csv_returns_csv_response()
    {
        $county = County::factory()->create(['name' => 'Békés']);
        PostalCode::factory()->create(['zip' => '5600', 'city' => 'Békéscsaba', 'county_id' => $county->id]);

        $response = $this->get('/api/county/Békés/B/export/csv');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Békéscsaba', $response->getContent());
    }

    public function test_update_by_county_modifies_all_postal_codes()
    {
        $county = County::factory()->create(['name' => 'Békés']);
        PostalCode::factory()->create(['zip' => '5600', 'city' => 'Békéscsaba', 'county_id' => $county->id]);
        PostalCode::factory()->create(['zip' => '5700', 'city' => 'Gyula', 'county_id' => $county->id]);

        $newCounty = County::factory()->create(['name' => 'Csongrád']);

        $response = $this->putJson('/api/county/Békés', [
            'county' => 'Csongrád',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('postal_codes', ['zip' => '5600', 'county_id' => $newCounty->id]);
        $this->assertDatabaseHas('postal_codes', ['zip' => '5700', 'county_id' => $newCounty->id]);
    }

    public function test_destroy_by_county_deletes_all_postal_codes()
    {
        $county = County::factory()->create(['name' => 'Békés']);
        PostalCode::factory()->create(['zip' => '5600', 'city' => 'Békéscsaba', 'county_id' => $county->id]);
        PostalCode::factory()->create(['zip' => '5700', 'city' => 'Gyula', 'county_id' => $county->id]);

        $response = $this->deleteJson('/api/county/Békés');

        $response->assertStatus(204);
        $this->assertDatabaseMissing('postal_codes', ['county_id' => $county->id]);
    }
}
