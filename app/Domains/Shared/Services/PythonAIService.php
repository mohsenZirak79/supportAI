<?php

namespace App\Domains\Shared\Services;

use GuzzleHttp\Client;

class PythonAIService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => env('PYTHON_AI_URL', 'http://python-service:5000')]);
    }

    public function sendToAI($content)
    {
        $response = $this->client->post('/chat', ['json' => ['message' => $content]]);
        return json_decode($response->getBody()->getContents(), true);
    }
}
