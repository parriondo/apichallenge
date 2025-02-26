<?php

namespace App\Http\Controllers;

use App\Services\EntityService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class EntityController extends Controller
{
    protected EntityService $apiService;

    public function __construct(EntityService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function syncEntries(): JsonResponse
    {
        try {
            $data = $this->apiService->fetchApiData();
        } catch (\Exception $e) {
            try {
                $data = $this->apiService->getBackupData();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudieron recuperar los datos ni desde la API ni desde el backup: ' . $e->getMessage()
                ], 500);
            }
        }
        try {
            $res =  $this->apiService->storeData($data);
            return response()->json($res);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudieron almacenar los datos: ' . $e->getMessage()
            ], 500);
        }
    }
}
