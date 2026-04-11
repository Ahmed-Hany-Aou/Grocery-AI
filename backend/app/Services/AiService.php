<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class AiService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.ai.url', env('AI_SERVICE_URL', 'http://ai-service:7777'));
        $this->apiKey = config('services.ai.key', env('API_SECRET_KEY', 'change-secret-key-2026'));
    }

    /**
     * Extract products from an image using the AI service.
     */
    public function extractFromImage(UploadedFile $image, ?string $command = null): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->attach('image', fopen($image->getRealPath(), 'r'), $image->getClientOriginalName())
            ->post($this->baseUrl . '/api/v1/extract', [
                'command' => $command,
            ]);

            if ($response->failed()) {
                Log::error('AI Service Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return ['success' => false, 'error' => 'AI Service failed'];
            }

            return ['success' => true, 'data' => $response->json()];
        } catch (\Exception $e) {
            Log::error('AI Service Exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
