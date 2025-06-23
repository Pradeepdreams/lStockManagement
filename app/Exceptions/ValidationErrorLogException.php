<?php

namespace App\Exceptions;

use App\Models\ErrorLog;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ValidationErrorLogException extends Exception
{
    public function __invoke(ValidationException $e): JsonResponse
    {
        ErrorLog::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'input' => json_encode(request()->all()),
        ]);

        return response()->json([
            'message' => $e->getMessage(),
            'errors' => $e->errors(),
        ], 422);
    }
}
