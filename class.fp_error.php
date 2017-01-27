<?php

/**
 * Class FP_Error, custom error class.
 * TODO: send mail on error
 */
class FP_Error extends Exception
{
    public function __construct ( $message, $code = 0, Exception $previous = NULL )
    {
        parent::__construct( $message, $code, $previous );
    }

    public function __toString ()
    {
        return __CLASS__ . ": [$this->code]: $this->message";
    }
}