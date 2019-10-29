<?php


namespace m4dn3ss\framework\exceptions;


use Throwable;

class NotFoundException extends \Exception
{
    public function __construct($message = "", $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}