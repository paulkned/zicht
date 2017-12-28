<?php

namespace App\Exceptions;

/**
 * Class UnsupportedMediaTypeException
 * @package App\Exceptions
 */
class UnsupportedMediaTypeException extends HttpException
{
    /**
     * The HTTP status code to return
     *
     * @var int
     */
    protected $statusCode = 415;
}