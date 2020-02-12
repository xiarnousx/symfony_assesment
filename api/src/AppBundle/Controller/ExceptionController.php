<?php

namespace AppBundle\Controller;

use AppBundle\Exception\ValidationException;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ExceptionController extends Controller
{
    use ControllerTrait;

    public function showAction(Request $request, $exception, DebugLoggerInterface $logger = null)
    {
        if ($exception instanceof ValidationException) {
            return $this->getView($exception->getStatusCode(), json_decode($exception->getMessage(), true));
        }

        if ($exception instanceof HttpException) {
            return $this->getView($exception->getStatusCode(), $exception->getMessage());
        }

        return $this->getView(null, 'Internal Server Error!');
    }

    private function getView(?int $statusCode, $message): View
    {
        $error = [
            'code' => $statusCode ?? 500,
            'message' => $message,
        ];

        return $this->view($error, $statusCode ?? 500);
    }
}
