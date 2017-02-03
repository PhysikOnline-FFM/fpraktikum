<?php

require_once ( "../fp_constants.php" );
require_once ( "../class.logger.php" );

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
        foreach ( $to as $mail )
        {
            mail( $mail, $subject, $body );
            Logger::log( "Eine Mail wurde an $mail gesendet.", 2 );
        }
    }
}