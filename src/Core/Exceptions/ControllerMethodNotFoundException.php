<?php

namespace Core\Exceptions;

class ControllerMethodNotFoundException extends \BadMethodCallException
{
    /**
     * ControllerNotFoundException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = "Controller Method Not Found", $code = 605, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}