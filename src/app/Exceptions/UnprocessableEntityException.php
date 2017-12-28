<?php

namespace App\Exceptions;

/**
 * Class UnprocessableEntityException
 * @package App\Exceptions
 */
class UnprocessableEntityException extends HttpException
{
    /**
     * The HTTP status code to return
     *
     * @var int
     */
    protected $statusCode = 422;

    /**
     * The validation errors to return to the client
     *
     * @var array
     */
    protected $validationErrors = [];

    /**
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * @param array $validationErrors
     */
    public function setValidationErrors(array $validationErrors)
    {
        $this->validationErrors = $validationErrors;
    }

    /**
     * Format validation errors to return to the client
     *
     * @return array
     */
    public function getFormattedValidationErrors(): array
    {
        $validationErrors = [];
        foreach ($this->validationErrors as $field => $fieldValidationErrors) {
            $validationErrors[$field] = [];

            foreach ($fieldValidationErrors as $id => $title) {
                $validationErrors[$field][] = [
                    'id' => $id,
                    'title' => $title
                ];
            }
        }

        return $validationErrors;
    }
}