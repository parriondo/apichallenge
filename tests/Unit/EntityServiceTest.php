<?php

namespace Tests\Unit;

use Exception;
use Tests\TestCase;
use App\Models\Category;
use App\Services\EntityService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EntityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EntityService $entityService;

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityService = new EntityService();
    }

    public function test_fetch_api_data_success()
    {
        Http::fake([
            config('apichallenge.api_url') => Http::response($this->jsonData, 200),
        ]);

        $result = $this->entityService->fetchApiData();
        $this->assertEquals($this->jsonData, $result);
    }

    public function test_fetch_api_data_failure()
    {
        Http::fake([
            config('apichallenge.api_url') => Http::response([], 500),
        ]);

        $this->expectException(Exception::class);
        $this->entityService->fetchApiData();
    }

    public function test_get_backup_data_success()
    {
        Storage::fake('local');
        Storage::put(config('apichallenge.json_file'), json_encode($this->jsonData));

        $result = $this->entityService->getBackupData();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('entries', $result);
    }

    public function test_get_backup_data_failure()
    {
        Storage::fake('local');
        $this->expectException(Exception::class);
        $this->entityService->getBackupData();
    }

    public function test_store_data_success()
    {
        Category::factory()->create(['category' => 'Animals']);
        Category::factory()->create(['category' => 'Security']);

        $result = $this->entityService->storeData($this->jsonData);
        $this->assertCount(2, $result['success']);
        $this->assertCount(0, $result['failures']);

        $this->assertDatabaseHas('entities', ['api' => 'AdoptAPet']);
        $this->assertDatabaseHas('entities', ['api' => 'Application Environment Verification']);
    }

    public function test_store_data_invalid_format()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->entityService->storeData(['invalid' => 'data']);
    }

    public function test_store_data_no_categories()
    {
        $this->expectException(Exception::class);
        $this->entityService->storeData($this->jsonData);
    }

}
