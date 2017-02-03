<?php

require_once ( "../database/class.FP-Database.php" );
require_once ( "../class.fp_error.php" );
require_once ( "../include/class.mail.php" );

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
    private $partner_name;
    private $institute1;
    private $institute2;
    private $semester;
    private $graduation;
    private $error = [];
    private $error_bit = false;
    private $token;
    public function __construct ()
    {
        $this->fp_database = new FP_Database();
    }

    /**
     * Function registers a user with and w/o a partner.
     *
     * @param $data         array   Data array needed by fp_database->setAnmeldung
     * @param null $partner
     *
     * @return bool
     */
    public function signUp_registrant ( $data, $partner = NULL, $partner_name = NULL )
    {
        $this->error = [];

        try
        {
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            $this->registrant = $data['registrant'];
            $this->partner = $partner;
            $this->partner_name = $partner_name;
            $this->institute1 = $data['institute1'];
            $this->institute2 = $data['institute2'];
            $this->semester = $data['semester'];
            $this->graduation = $data['graduation'];

            if ( ($name = $this->check_array( $data )) != "ok" )
            {
                array_push( $this->error, "Das Feld '" . $name . "' wurde nicht ausgefüllt." );
            }
            else
            {
                if ( ! $this->is_user_type_of( $this->registrant, 'new' ) )
                {
                    array_push( $this->error, "Du bist bereits angemeldet oder wurdest als Partner von jemandem anderen hinzugefügt." );
                }

                if ( ! $this->check_user( $this->registrant ) )
                {
                    array_push( $this->error
                        , "Wir konnten dich mit '" . $this->registrant . "' <strong>nicht</strong> in unserer Datenbank finden." );
                }

                if ( $this->partner )
                {
                    if ( ! $this->check_partner() )
                    {
                        array_push( $this->error, "Wir konnten deinen Partner mit " );
                    }

                    if ( ! $this->is_user_type_of( $this->partner, 'new' ) )
                    {
                        array_push( $this->error, "Dein angebener Partner '" . $this->partner . "' ist bereits angemeldet." );
                    }

                    if ( ! $this->check_user( $this->partner ) )
                    {
                        array_push( $this->error
                            , "Wir konnten deinen Partner mit '" . $this->partner . "' <strong>nicht</strong> in der Datenbank finden." );
                    }
                }

                // TODO: not valid for LA's
                if ( $this->institute1 == $this->institute2 )
                {
                    array_push( $this->error, "Bitte wähle zwei verschiedene Institute aus." );
                }

                if ( ($institute = $this->are_offers_valid()) != "ok" )
                {
                    array_push( $this->error, "Leider konnten wir das Institut '" . $institute . "' im Studiengang '"
                        . $this->graduation . "' und Semester '" . $this->semester . "' <strong>nicht</strong> finden." );
                }

                if ( ($institute = $this->check_free_places()) != "ok" )
                {
                    array_push( $this->error
                        , "Leider sind im Institut '" . $institute . "' <strong>nicht</strong> ausreichend Plätze vorhanden." );
                }
            }

            if ( $this->error != [] )
            {
                Logger::log( "There were errors when $this->registrant tried to register: " . implode( " ; ", $this->error ), 1 );
                $this->error_bit = true;
                return false;
            }

            $this->fp_database->setRegistration( $data, $this->partner,$token );
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

        Logger::log( $this->registrant
            . " has registered with '$this->institute1, $this->institute2, $this->graduation, $this->partner'.", 2 );

        $this->send_mail( $this->registrant, "Du hast dich angemeldet.", "Hi $this->registrant, du hast dich angemeldet!" );

        return true;
    }

    /**
     * Function registers a partner if he accepts .
     * @param $partner string   HRZ number of the partner.
     * @param $semester string  Current semester.
     * @return bool             If process was successful.
     */
    public function signUp_partner ( $partner, $semester )
    {
        $this->error = [];

        try
        {
            $this->partner = $partner;
            $this->semester = $semester;

            if ( (! $partner) || (! $semester) )
            {
                array_push( $this->error, "Deine HRZ Nummer oder das aktuelle Semester konnte nicht richtig übermittelt werden." );
            }
            else
            {
                if ( ! $this->is_user_type_of( $this->partner, 'partner-open' ) )
                {
                    array_push( $this->error, "Du bist bereits angemeldet oder wurdest nicht als Partner hinzugefügt." );
                }

                if ( ! $this->check_user( $this->partner ) )
                {
                    array_push( $this->error, "Wir konnten dich mit '" . $this->partner . "' nicht in unserer Datenbank finden." );
                }
            }

            if ( $this->error != [] )
            {
                Logger::log( "There were errors when $this->partner tried to accept: " . implode( " ; ", $this->error ), 1 );
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
            Logger::log( $error );
            array_push( $this->error, $error );
            $this->error_bit = true;
            return false;
        }

        Logger::log( $this->partner . " has registered as a partner.", 2 );

        $this->send_mail( $this->partner, "Du hast dich angemeldet.", "Hi $this->partner, du hast dich angemeldet!" );

        return true;
    }

    public function signOut ( $registrant, $semester, $token )
    {
        $this->error = [];

        try
        {
            $this->registrant = $registrant;
            $this->semester = $semester;
            $this->token = $token;
            if ( ! $this->check_token( $this->registrant,$this->semester,$this->token ) )
            {
                throw new FP_Error("Securitybreach, your Coumputer is about to be hacked!");
            }

            if ( ( ! $registrant) || ( ! $semester) )
            {
                array_push( $this->error, "Deine HRZ Nummer oder das aktuelle Semester konnte nicht richtig übermittelt werden." );
            }
            else
            {
                if ( $this->is_user_type_of( $this->registrant, false ) )
                {
                    array_push( $this->error, "Du bist nicht registriert und kannst dich nicht abmelden." );
                }
            }

            if ( $this->error != [] )
            {
                Logger::log( "There were errors when $this->registrant tried to sign off: " . implode( " ; ", $this->error ), 1 );
                $this->error_bit = true;
                return false;
            }

            $this->fp_database->rmRegistration( array( 'registrant' => $this->registrant, 'semester' => $this->semester ) );
        }
        catch ( FP_Error $error )
        {
            array_push( $this->error, $error );
            $this->error_bit = true;
            return false;
        }
        catch ( Exception $error )
        {
            Logger::log( $error );
            array_push( $this->error, $error );
            $this->error_bit = true;
            return false;
        }

        Logger::log( $this->registrant . " has signed off.", 2 );
        $this->send_mail( $this->registrant, "Du hast dich abgemeldet.", "Hi $this->registrant, du hast dich abgemeldet!" );

        return true;
    }

    public function partnerDenies ( $partner, $semester )
    {
        try
        {
            $this->partner = $partner;
            $this->semester = $semester;
            $this->token = $_POST['token'];
            if ($this->check_token($this->partner,$this->semester,$this->token))
            {
                throw new FP_Error("Securitybreach, your Coumputer is about to be hacked!");
            }
            if ( (! $partner) || (! $semester) )
            {
                array_push( $this->error, "Deine HRZ Nummer oder das aktuelle Semester konnte nicht richtig übermittelt werden." );
            }
            else
            {
                if ( ! $this->is_user_type_of( $this->partner, 'partner-open' ) )
                {
                    array_push( $this->error, "Du bist bereits angemeldet oder wurdest nicht als Partner hinzugefügt." );
                }

                if ( ! $this->check_user( $this->partner ) )
                {
                    array_push( $this->error, "Wir konnten dich mit '" . $this->partner . "' nicht in unserer Datenbank finden." );
                }
            }

            if ( $this->error != [] )
            {
                Logger::log( "There were errors when $this->partner tried to deny: " . implode( " ; ", $this->error ), 1 );
                $this->error_bit = true;
                return false;
            }

            $this->fp_database->rmPartner( $this->partner, $this->semester );
        }
        catch ( FP_Error $error )
        {
            Logger::log( $error );
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

        Logger::log( $this->partner. " has denied.", 2 );
        $this->send_mail( $this->partner, "Du hast abgelehnt.", "Hi $this->partner, du hast abgelehnt!" );

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
     * @return bool $error_bit
     */
    public function isErrorBit ()
    {
        return $this->error_bit;
    }

    /**
     * Function checks if all elements of an array are defined.
     * Prevents a user to not fill out every element.
     *
     * @param $data array   The array to check
     *
     * @return bool
     *
     * @bug When 'notes' is not filled, this throws an error.
     */
    private function check_array ( $data )
    {
        foreach ( $data as $name => $value )
        {
            if ( ! $value )
            {
                return $name;
            }
        }

        return "ok";
    }

    /**
     * Function checks if a user is of a specific type.
     *
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
     * Prevents an unknown user to log in.
     *
     * @param $hrz
     *
     * @return bool
     */
    private function check_user ( $hrz )
    {
        return $this->fp_database->checkUserInfo( $hrz );
    }

    private function check_partner ()
    {
        return $this->fp_database->checkPartner( $this->partner, $this->partner_name, $this->semester )['type'] == "new";
    }

    /**
     * Function checks if there are enough places in both institutes.
     *
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

        return "ok";
    }

    /**
     * Function checks if both institute-semester-graduation combinations are valid.
     * Makes sure that nobody can change the institutes/semester/graduation in the form.
     *
     * @return string   The faulty institute or "ok".
     */
    private function are_offers_valid ()
    {
        if ( ! $this->fp_database->isOffer( $this->institute1, $this->semester, 0, $this->graduation ) )
        {
            return $this->institute1;
        }
        if ( ! $this->fp_database->isOffer( $this->institute2, $this->semester, 1, $this->graduation ) )
        {
            return $this->institute2;
        }

        return "ok";
    }

    private function send_mail ( $hrz, $subject, $message )
    {
        Mail::send( $subject, $message, array( $this->fp_database->getMail( $hrz ) ) );
    }

    public function check_token($registrant,$semester,$post_token)
    {
        return ($this->fp_database->get_token($registrant,$semester) == $post_token);
    }
}