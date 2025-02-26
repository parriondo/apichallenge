<?php

namespace App\Exceptions;

use Exception;

class CategoryNotFoundException extends Exception
{
    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => 'Categoría no encontrada.'
        ], 404);
    }
}
