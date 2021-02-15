<?php

namespace App\Exceptions;

use App\Http\CodeResponse;
use Exception;

class RequestException extends Exception
{
    public function __construct(array $codeResponse = CodeResponse::FAIL, $info = '')
    {
    	list($code, $message) = $codeResponse;
    	parent::__construct($info?:$message, $code);
    }
}
