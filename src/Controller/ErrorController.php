<?php

namespace App\Controller;

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ErrorController extends BaseController
{
    public function displayError(Request $request, Kernel $kernel): Response
    {
        /** @var Throwable $exception */
        $exception = $request->attributes->get('exception');
        $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 400;

        $responseData = ['error' => $exception->getMessage()];

        if (in_array($kernel->getEnvironment(), ['dev', 'test'])) {
            try {
                $stackTrace = $this->normalizer->normalize(
                    $exception->getTrace()
                );
            } catch (Throwable $exception) {
                $stackTrace = explode("\n", $exception->getTraceAsString());
            }

            $responseData['stack_trace'] = $stackTrace;
        }

        return $this->json($responseData, $statusCode);
    }
}
