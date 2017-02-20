<?php

require_once("fp_constants.php");
require_once("class.logger.php");

class Mail
{
    /**
     * Function sends a mail to all addresses specified.
     * @param $subject string       The subject of the message.
     * @param $body string          The body of the message.
     * @param $to array             Addresses to send mail to.
     */
    static public function send ( $subject, $body, $to = array( fp_const\MAIL_ADDRESS ) )
    {
        $headers = "Content-Type: text/plain; charset=UTF-8";
        foreach ( $to as $mail )
        {
            mail( $mail, "[FPraktikum] " . $subject, $body, $headers );
            Logger::log( "Eine Mail wurde an $mail gesendet.", 2 );
        }
    }
}