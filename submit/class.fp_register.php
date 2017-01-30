<?php

require_once ( "../database/class.FP-Database.php" );
require_once ( "../class.fp_error.php" );

/**
 * Class Register. Is used to do all registration processes.
 * It can:
 *      sign up a user
 *      sign up a partner
 *      delete the registration of a user
 *      delete the registration of a partner
 *
 * @date January 2017
 * @author Lars Gröber
 */
class Register
{
    private $fp_database;
    private $registrant;
    private $partner;
    private $institute1;
    private $institute2;
    private $semester;
    private $graduation;
    private $error = [];
    private $error_bit = false;

    public function __construct ()
    {
        $this->fp_database = new FP_Database();
    }

    /**
     * Function registers a user with and w/o a partner
     * @param $data         array   Data array needed by fp_database->setAnmeldung
     * @param null $partner
     *
     * @return bool
     */
    public function signUp_registrant ( $data, $partner = NULL )
    {
        $this->error = [];

        try
        {
            if ( ! $this->check_array( $data ) )
            {
                array_push( $this->error, "Es wurden womöglich nicht alle Felder ausgefüllt." );
            }
            else
            {
                $this->registrant = $data['registrant'];
                $this->partner = $partner;
                $this->institute1 = $data['institute1'];
                $this->institute2 = $data['institute2'];
                $this->semester = $data['semester'];
                $this->graduation = $data['graduation'];

                if ( ! $this->is_user_type_of( $this->registrant, 'new' ) )
                {
                    array_push( $this->error, "Du bist bereits angemeldet oder wurdest als Partner von jemandem anderen hinzugefügt." );
                }

                if ( ! $this->check_user( $this->registrant ) )
                {
                    array_push( $this->error, "Wir konnten dich nicht mit '" . $this->registrant . "' in der Datenbank finden." );
                }

                if ( $this->partner )
                {
                    if ( ! $this->is_user_type_of( $this->partner, 'new' ) )
                    {
                        array_push( $this->error, "Dein angebener Partner ist bereits angemeldet." );
                    }

                    if ( ! $this->check_user( $this->partner ) )
                    {
                        array_push( $this->error, "Wir konnten deinen Partner mit '" . $this->partner . "' nicht in der Datenbank finden." );
                    }
                }

                if ( $this->institute1 == $this->institute2 )
                {
                    array_push( $this->error, "Bitte wähle zwei verschiedene Institute aus." );
                }

                if ( ! ($institute = $this->check_free_places()) )
                {
                    array_push( $this->error, "Leider sind im Institut '" . $institute . "' nicht ausreichend Plätze vorhanden." );
                }
            }

            if ( $this->error != [] )
            {
                $this->error_bit = true;
                return false;
            }


            $this->fp_database->setAnmeldung( $data, $this->partner );
        }
        catch ( FP_Error $error )
        {
            array_push( $this->error, $error );
            $this->error_bit = true;
            return false;
        }
        catch ( Exception $error )
        {
            array_push( $this->error, $error );
            $this->error_bit = true;
            return false;
        }

        return true;
    }

    /**
     * Function registers a partner
     * @param $partner string   HRZ number of the partner.
     * @param $semester string  Current semester.
     * @return bool             If process was successful.
     */
    public function signUp_partner ( $partner, $semester )
    {
        $this->error = [];

        try
        {
            if ( (! $partner) || (! $semester) )
            {
                array_push( $this->error, "Deine HRZ Nummer oder das aktuelle Semester konnte nicht richtig übermittelt werden." );
            }
            else
            {
                $this->partner = $partner;
                $this->semester = $semester;

                if ( ! $this->is_user_type_of( $this->partner, 'partner-open' ) )
                {
                    array_push( $this->error, "Du bist bereits angemeldet oder wurdest nicht als Partner hinzugefügt." );
                }

                if ( ! $this->check_user( $this->partner ) )
                {
                    array_push( $this->error, "Wir konnten dich mit '" . $this->partner . "' nicht in der Datenbank finden." );
                }
            }

            if ( $this->error != [] )
            {
                $this->error_bit = true;
                return false;
            }

            $this->fp_database->setPartnerAccepted( $this->partner, $this->semester );
        }
        catch ( FP_Error $error )
        {
            array_push( $this->error, $error );
            $this->error_bit = true;
            return false;
        }
        catch ( Exception $error )
        {
            array_push( $this->error, $error );
            $this->error_bit = true;
            return false;
        }

        return true;
    }

    public function signOut ( $registrant, $semester )
    {
        $this->error = [];

        try
        {
            if ( ( ! $registrant) || ( ! $semester) )
            {
                array_push( $this->error, "Deine HRZ Nummer oder das aktuelle Semester konnte nicht richtig übermittelt werden." );
            }
            else
            {
                $this->registrant = $registrant;
                $this->semester = $semester;

                if ( $this->is_user_type_of( $this->registrant, false ) )
                {
                    array_push( $this->error, "Du bist nicht registriert und kannst dich nicht abmelden." );
                }
            }

            if ( $this->error != [] )
            {
                $this->error_bit = true;
                return false;
            }

            $this->fp_database->rmAnmeldung( array( 'registrant' => $this->registrant, 'semester' => $this->semester ) );
        }
        catch ( FP_Error $error )
        {
            array_push( $this->error, $error );
            $this->error_bit = true;
            return false;
        }
        catch ( Exception $error )
        {
            array_push( $this->error, $error );
            $this->error_bit = true;
            return false;
        }

        return true;
    }

    public function partnerDenies ( $partner, $semester )
    {
        try
        {
            if ( (! $partner) || (! $semester) )
            {
                array_push( $this->error, "Deine HRZ Nummer oder das aktuelle Semester konnte nicht richtig übermittelt werden." );
            }
            else
            {
                $this->partner = $partner;
                $this->semester = $semester;

                if ( ! $this->is_user_type_of( $this->partner, 'partner-open' ) )
                {
                    array_push( $this->error, "Du bist bereits angemeldet oder wurdest nicht als Partner hinzugefügt." );
                }

                if ( ! $this->check_user( $this->partner ) )
                {
                    array_push( $this->error, "Wir konnten dich mit '" . $this->partner . "' nicht in der Datenbank finden." );
                }
            }

            if ( $this->error != [] )
            {
                $this->error_bit = true;
                return false;
            }

            $this->fp_database->rmPartner( $this->partner, $this->semester );
        }
        catch ( FP_Error $error )
        {
            array_push( $this->error, $error );
            $this->error_bit = true;
            return false;
        }
        catch ( Exception $error )
        {
            array_push( $this->error, $error );
            $this->error_bit = true;
            return false;
        }

        return true;
    }

    /**
     * @return array $error
     */
    public function getError ()
    {
        return $this->error;
    }

    /**
     * @return bool
     */
    public function isErrorBit ()
    {
        return $this->error_bit;
    }

    /**
     * Function checks if all elements of an array are defined.
     * @param $data array   The array to check
     *
     * @return bool
     */
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

    /**
     * Function checks if a user is of a specific type.
     * @param $hrz
     * @param $type
     *
     * @return bool
     */
    private function is_user_type_of ( $hrz, $type )
    {
        $user_type = $this->fp_database->checkUser( $hrz, $this->semester )['type'];

        return $type == $user_type;
    }

    /**
     * Function checks if a user can be found in the database.
     * @param $hrz
     *
     * @return bool
     */
    private function check_user ( $hrz )
    {
        return $this->fp_database->checkUserInfo( $hrz );
    }

    /**
     * Function checks if there are enough places in both institutes.
     * @return bool
     */
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