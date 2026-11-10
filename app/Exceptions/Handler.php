<?php 

use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

public function render($request, \Throwable $e)
{
    // Handle CSRF token mismatch
    if ($e instanceof TokenMismatchException) {

        // JSON request (API / Axios / Fetch / AJAX)
        if ($request->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'CSRF token mismatch.',
                    'errors'  => [
                        'csrf' => ['Your session may have expired. Please refresh and try again.']
                    ],
                ], 419)
            );
        }

        // Web (non-AJAX) → redirect back with error
        return redirect()
            ->back()
            ->with('error', 'Your session expired. Please refresh the page.');
    }

    return parent::render($request, $e);
}
