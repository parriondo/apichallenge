<?php

namespace App\Services;

use App\Models\Entity;
use App\DTOs\EntityDTO;
use App\Models\Category;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EntityService
{
    protected string $apiUrl;
    protected string $backupFile;

    public function __construct()
    {
        $this->apiUrl = config("apichallenge.api_url");
        $this->backupFile =  config("apichallenge.json_file");
    }

    public function fetchApiData()
    {
        $response = Http::timeout(5)->get($this->apiUrl);
        if ($response->failed()) {
            throw new Exception("Error en la API externa: {$response->status()}", $response->status());
        }
        return $response->json();
    }

    public function getBackupData()
    {
        if (Storage::exists($this->backupFile)) {
            return json_decode(Storage::get($this->backupFile), true);
        }
        throw new Exception("No se pudo recuperar datos de la API ni del archivo local", 500);
    }

    public function storeData($data)
    {
        if (!isset($data['entries']) || !is_array($data['entries'])) {
            throw new \InvalidArgumentException("El formato de datos no es válido.");
        }
        // Limpiar la tabla
        Entity::truncate();

        $categories = Category::all();
        if ($categories->isEmpty()) {
            throw new Exception("No hay categorías");
        }

        $result = [
            'success' => [],
            'failures' => []
        ];

        foreach ($data['entries'] as $entry) {
            try {
                $category = $categories->firstWhere('category', $entry['Category']);
                if (!$category) {
                    continue;
                };
                // Crear DTO para la entidad
                $entityDTO = EntityDTO::fromArray($entry, $category->id);

                // Guardar en la BD
                Entity::create([
                    'api' => $entityDTO->api,
                    'description' => $entityDTO->description,
                    'link' => $entityDTO->link,
                    'category_id' => $entityDTO->categoryId
                ]);

                // Agregar a resultados exitosos
                $result['success'][] = $entry;
            } catch (\Throwable $e) {
                $result['failures'][] = "Error procesando entrada " . json_encode($entry) . ": " . $e->getMessage();
            }
        }
        // Devolver los resultados
        return $result;
    }
}
