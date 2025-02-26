<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Entity;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EntityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_entity_endpoint_returns_data_when_category_exists()
    {
        // Crear categoría y entidades en la BD
        $category = Category::factory()->create(['category' => 'Security']);
        Entity::factory()->count(2)->create(['category_id' => $category->id]);

        // Hacer petición al endpoint
        $response = $this->getJson("/api/Security");

        // Verificar respuesta
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['api', 'description', 'link', 'category' => ['id', 'category']]
                ]
            ]);
    }

    public function test_entity_endpoint_returns_404_when_category_does_not_exist()
    {
        // Hacer petición a una categoría inexistente
        $response = $this->getJson("/api/NonExistentCategory");

        // Verificar respuesta
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Categoría no encontrada.',
            ]);
    }
}
