<?php

namespace Core\Exceptions;

class ControllerNotFoundException extends HttpException
{
    /**
     * ControllerNotFoundException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = "Controller Not Found", $code = 604, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}