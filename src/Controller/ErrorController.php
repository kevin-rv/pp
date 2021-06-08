<?php

namespace App\Controller;

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class ErrorController extends BaseController
{
    public function displayError(Request $request, Kernel $kernel)
    {
        /** @var Throwable $exception */
        $exception = $request->attributes->get('exception');
        $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 400;

        $responseData = ['error' => $exception->getMessage()];

        if ('dev' !== $kernel->getEnvironment()) {
            $responseData['stack_trace'] = explode("\n#", $exception->getTraceAsString());
        }

        return $this->json($responseData, $statusCode);
    }
}
