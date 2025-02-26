<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EntityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $jsonData = [
        "count" => 3,
        "entries" => [
            [
                "API" => "AdoptAPet",
                "Description" => "Resource to help get pets adopted",
                "Auth" => "apiKey",
                "HTTPS" => true,
                "Cors" => "yes",
                "Link" => "https://www.adoptapet.com/public/apis/pet_list.html",
                "Category" => "Animals"
            ],
            [
                "API" => "Application Environment Verification",
                "Description" => "Android library and API to verify the safety of user devices, detect rooted devices and other risks",
                "Auth" => "apiKey",
                "HTTPS" => true,
                "Cors" => "yes",
                "Link" => "https://github.com/fingerprintjs/aev",
                "Category" => "Security"
            ],
            [
                "API" => "Yandex.Weather",
                "Description" => "Assesses weather condition in specific locations",
                "Auth" => "apiKey",
                "HTTPS" => true,
                "Cors" => "no",
                "Link" => "https://yandex.com/dev/weather/",
                "Category" => "Weather"
            ]
        ]
    ];

    public function test_sync_entries_success_from_api()
    {
        Http::fake([
            config('apichallenge.api_url') => Http::response($this->jsonData, 200),
        ]);

        Category::factory()->create(['category' => 'Animals']);
        Category::factory()->create(['category' => 'Security']);

        $response = $this->get('/entries');

        $response->assertStatus(200);
        $this->assertDatabaseHas('entities', ['api' => 'AdoptAPet']);
        $this->assertDatabaseHas('entities', ['api' => 'Application Environment Verification']);
    }

    public function test_sync_entries_success_from_backup()
    {
        Http::fake([
            config('apichallenge.api_url') => Http::response([], 500),
        ]);
        Storage::fake('local');
        Storage::put(config('apichallenge.json_file'), json_encode($this->jsonData));
        $category = Category::factory()->create(['category' => 'Animals']);
        Category::factory()->create(['category' => 'Security']);
        $response = $this->get('/entries');

        $response->assertStatus(200);
        $this->assertDatabaseHas('entities', ['api' => 'AdoptAPet']);
        $this->assertDatabaseHas('entities', ['api' => 'Application Environment Verification']);
    }

    public function test_sync_entries_failure_api_and_backup()
    {
        Http::fake([
            config('apichallenge.api_url') => Http::response([], 500),
        ]);
        Storage::fake('local');
        $response = $this->get('/entries');
        $response->assertStatus(500);
    }

    public function test_sync_entries_store_data_failure()
    {
        Http::fake([
            config('apichallenge.api_url') => Http::response(['entries' => [['API' => 'Test', 'Description' => 'Test Desc', 'Link' => 'http://test.com', 'Category' => 'TestCat']]], 200),
        ]);

        $response = $this->get('/entries');
        $response->assertStatus(500);
    }
}
