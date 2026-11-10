<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use DomainException;
use Exception;

/**
 * BaseController provides common functionality for all application controllers,
 * including standardized JSON responses and centralized exception handling.
 */
class BaseController extends Controller
{
    // --- Response Helpers ---
    use AuthorizesRequests, ValidatesRequests;
    /**
     * Standard success JSON response.
     */
    protected function successResponse(mixed $data = null, string $message = 'Operation successful.', int $status = 200, array $meta = []): JsonResponse
    {
        return response()->json(array_merge([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $meta), $status);
    }

    /**
     * Standard error JSON response.
     */
    protected function errorResponse(string $message = 'An error occurred.', int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }

    // --- Exception Handlers ---

    /**
     * Handle ValidationException.
     */
    protected function handleValidationException(ValidationException $e): JsonResponse
    {
        $this->logError('Validation Error', $e->errors());
        return $this->errorResponse('Validation Failed.', 422, $e->errors());
    }

    /**
     * Handle DomainException (Business Logic Errors).
     */
    protected function handleDomainException(DomainException $e): JsonResponse
    {
        $this->logError('Domain Error', ['error' => $e->getMessage()]);
        return $this->errorResponse($e->getMessage(), 400);
    }

    /**
     * Handle QueryException (Database Errors).
     */
    protected function handleQueryException(QueryException $e): JsonResponse
    {
        if ($this->isDuplicateEntryError($e)) {
            // Specific message for duplicate entry, common in many controllers
            return $this->errorResponse('A duplicate entry was found.', 409);
        }

        // For other QueryExceptions, log and return a generic server error
        $this->logError('Query Exception', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ]);
        return $this->errorResponse('A database error occurred.', 500);
    }

    /**
     * Handle a general, unexpected Exception.
     */
    protected function handleUnexpectedException(Exception $e): JsonResponse
    {
        if ($e instanceof DomainException) {
            return $this->errorResponse($e->getMessage(), 409);
        }
        
        $this->logError('Unexpected Error', [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ]);
        return $this->errorResponse('An unexpected error occurred base.', 500);
    }

    /**
     * Check if the QueryException is a duplicate entry error (MySQL error code 1062).
     */
    protected function isDuplicateEntryError(QueryException $e): bool
    {
        // This is a common MySQL error code for duplicate entry
        return str_contains($e->getMessage(), '1062');
    }

    // --- Logging Helpers ---

    /**
     * Standardized logging for informational messages.
     * Assumes a global helper 'logAction' or similar is available.
     */
    protected function logInfo(string $message, array $context = []): void
    {
        // In a real Laravel app, you would use: \Illuminate\Support\Facades\Log::info($message, $context);
        // We'll assume the user's 'logAction' is available or use a placeholder.
        if (function_exists('logAction')) {
            logAction($message, 'info', $context);
        }
    }

    /**
     * Standardized logging for error messages.
     */
    protected function logError(string $message, array $context = []): void
    {
        // In a real Laravel app, you would use: \Illuminate\Support\Facades\Log::error($message, $context);
        if (function_exists('logAction')) {
            logAction($message, 'error', $context);
        }
    }
}