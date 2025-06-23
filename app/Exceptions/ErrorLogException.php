<?php

namespace App\Exceptions;

use Exception;
use App\Models\ErrorLog;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ErrorLogException extends Exception
{
    public function __invoke(Throwable $e): void
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
    }
}
