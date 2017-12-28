<?php

namespace App\Exceptions;

/**
 * Class HttpException
 * @package App\Exceptions
 */
class HttpException extends \Exception
{
    /**
     * The HTTP status code to return
     *
     * @var int
     */
    protected $statusCode;

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}