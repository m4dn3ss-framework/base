<?php


namespace m4dn3ss\framework\exceptions;


use Throwable;

class InternalServerErrorException extends \Exception
{
    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}