<?php

namespace OpiloClient\Response;

class ValidationException extends CommunicationException
{
    /**
     * @var string
     */
    protected $httpResponseBody;

    /**
     * @var array
     */
    protected $errors;

    public function __construct($httpResponseBody)
    {
        parent::__construct('Input Validation Failed', static::INVALID_INPUT);
        $this->httpResponseBody = $httpResponseBody;
        $errors = json_decode($httpResponseBody, true);
        if(is_array($errors) && array_key_exists('errors', $errors)) {
            $this->errors = $errors['errors'];
        }
    }

    /**
     * @return string
     */
    public function getHttpResponseBody()
    {
        return $this->httpResponseBody;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}