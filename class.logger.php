<?php

require_once ( "fp_constants.php" );
require_once ( "class.logger.php" );

/**
 * Class Logger. Can log a message to a file.
 */
class Logger
{
    private $log_file;
    private $file;

    public function __construct()
    {
        $this->log_file = fp_const\LOG_FILE;
        $this->file = fopen( $this->log_file, 'a' );

        if ( ! $this->file )
        {
            throw new FP_Error( "Could not open logfile!" );
        }
    }

    function write ( $message, $code = 0 )
    {
        if ( $code > fp_const\LOG_LEVEL )
        {
            return;
        }
        fwrite( $this->file, date( "[d.m.Y H:i:s]" ) . " " . $code . " " . $message . "\n" );
    }

    public function __destruct()
    {
        fclose( $this->file );
    }

    static function log ( $message, $code = 0 )
    {
        $Logger = new Logger();
        $Logger->write( $message, $code );
    }
}