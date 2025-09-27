<?php

namespace App\Exceptions;

use Exception;

class Handler extends Exception
{
    public function render($request, Throwable $e)
    {
        if ($e instanceof \GuzzleHttp\Exception\RequestException) {
            return response()->json([
                'error' => ['code' => 'AI_TIMEOUT', 'message' => 'AI timed out, suggest handoff to human', 'details' => []]
            ], 503);
        }
        return response()->json([
            'error' => ['code' => 'GENERAL_ERROR', 'message' => $e->getMessage(), 'details' => []]
        ], 400);
    }
}
