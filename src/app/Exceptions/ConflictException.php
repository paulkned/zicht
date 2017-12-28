<?php

namespace App\Exceptions;

/**
 * Class ConflictException
 * @package App\Exceptions
 */
class ConflictException extends HttpException
{
    /**
     * The HTTP status code to return
     *
     * @var int
     */
    protected $statusCode = 409;
}