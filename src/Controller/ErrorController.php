<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Throwable;

class ErrorController extends BaseController
{
    public function displayError(Request $request)
    {
        /** @var Throwable $exception */
        $exception = $request->attributes->get('exception');
        $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 400;

        return $this->json(['error' => $exception->getMessage(), 'stack_trace' => $exception->getTrace()], $statusCode);
    }
}
