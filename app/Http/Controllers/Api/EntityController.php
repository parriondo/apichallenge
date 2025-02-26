<?php

namespace App\Http\Controllers\Api;

use App\Models\Entity;
use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Resources\EntityCollection;
use App\Exceptions\CategoryNotFoundException;

class EntityController extends Controller
{
    public function category(string $category): EntityCollection
    {
        $categoryModel = Category::where('category', $category)->first();

        if (!$categoryModel) {
            throw new CategoryNotFoundException();
        }

        $entities = Entity::whereRelation("category", "category", $category)->get();

        return new EntityCollection($entities);
    }

}
