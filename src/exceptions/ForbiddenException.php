<?php


namespace m4dn3ss\framework\exceptions;


use Throwable;

class ForbiddenException extends \Exception
{
    public function __construct($message = "", $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}