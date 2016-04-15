<?php

namespace Core\Exceptions;

class PageNotFoundException extends HttpException {
    /**
     * PageNotFoundException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = "Page is Not Found", $code = 404, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}