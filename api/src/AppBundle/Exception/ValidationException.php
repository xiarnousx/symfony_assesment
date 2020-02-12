<?php

namespace AppBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends HttpException
{
    public function __construct(ConstraintViolationListInterface $violations)
    {
        $messages = [];

        foreach ($violations as $violation) {
            $messages[$violation->getPropertyPath()] = $violation->getMessage();
        }

        parent::__construct(400, json_encode($messages));
    }
}
