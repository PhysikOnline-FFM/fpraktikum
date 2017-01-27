<?php

require_once ( "../database/class.FP-Database.php" );
require_once ( "../class.fp_error.php" );

class Register
{
    private $fp_database;
    private $registrant;
    private $partner;
    private $institute1;
    private $institute2;
    private $semester;
    private $graduation;

    public function __construct ()
    {
        $this->fp_database = new FP_Database();
    }

    public function signUp_registrant ( $data, $partner = NULL )
    {
        $error = [];

        try
        {
            if ( ! $this->check_array( $data ) )
            {
                array_push( $error, "Es wurden womöglich nicht alle Felder ausgefüllt." );
            }
            else
            {
                $this->registrant = $data['registrant'];
                $this->partner = $partner;
                $this->institute1 = $data['institute1'];
                $this->institute2 = $data['institute2'];
                $this->semester = $data['semester'];
                $this->graduation = $data['graduation'];

                if ( $this->is_user_type_of( $this->registrant, false ) )
                {
                    array_push( $error, "Du bist bereits angemeldet oder wurdest als Partner von jemandem anderen hinzugefügt." );
                }

                if ( ! $this->check_user( $this->registrant ) )
                {
                    array_push( $error, "Wir konnten dich nicht mit '" . $this->registrant . "' in der Datenbank finden." );
                }

                if ( $this->partner )
                {
                    if ( ! $this->is_user_type_of( $this->partner, false ) )
                    {
                        array_push( $error, "Dein angebener Partner ist bereits angemeldet." );
                    }

                    if ( ! $this->check_user( $this->registrant ) )
                    {
                        array_push( $error, "Wir konnten deinen Partner mit '" . $this->partner . "' nicht in der Datenbank finden." );
                    }
                }

                if ( $this->institute1 == $this->institute2 )
                {
                    array_push( $error, "Bitte wähle zwei verschiedene Institute aus." );
                }

                if ( ! ($institute = $this->check_free_places()) )
                {
                    array_push( $error, "Leider sind im Institut '" . $institute . "' nicht ausreichend Plätze vorhanden." );
                }
            }

            if ( $error != [] )
            {
                return $error;
            }


            $this->fp_database->setAnmeldung( $data, $this->partner );
        }
        catch ( FP_Error $error )
        {
            return array( $error );
        }
        catch ( Exception $error )
        {
            return array( $error->getMessage() );
        }

        return array( "success" );
    }

    private function check_array ( $data )
    {
        foreach ( $data as $name => $value )
        {
            if ( ! $value )
            {
                return false;
            }
        }

        return true;
    }

    private function is_user_type_of ( $hrz, $type )
    {
        $user_type = $this->fp_database->checkUser( $hrz, $this->semester );

        return $type == $user_type;
    }

    private function check_user ( $hrz )
    {
        return $this->fp_database->checkUserInfo( $hrz );
    }

    private function check_free_places ()
    {
        $slots_needed = ($this->partner) ? 2 : 1;
        $free_places = $this->fp_database->freePlaces( $this->semester );

        if ( $free_places[$this->graduation][$this->institute1][0] < $slots_needed )
        {
            return $this->institute1;
        }

        if ( $free_places[$this->graduation][$this->institute2][1] < $slots_needed )
        {
            return $this->institute2;
        }

        return true;
    }
}