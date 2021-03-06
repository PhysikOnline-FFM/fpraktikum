<?php

//error_reporting(-1);
//ini_set('display_errors', 1);

require_once('class.Database.php');
require_once("/home/elearning-www/public_html/elearning/ilias/Customizing/global/include/fpraktikum/include/class.fp_error.php");

/**
 *
 *
 * Checks if student has is student1
 * SELECT COUNT(snumber1) FROM tbl_partners WHERE tbl_partners.snumber1 = 'x'
 * returns: 0 or more
 *
 * TODO: Query: Update snumber1 to value of snumber2 if snumber1 student was snumber1
 * TODO: Query: Update snumber2 to NULL  if student was snumber2
 *
 * @author: Bastian
 * @date  : 02.09.2016
 */

/**
 * TODO: Extend over all documentation here.
 * class containing all functions necessary to communicate with the database
 * for the registration process
 *
 * @author LG, BK
 */
class FP_Database
{
    private $dbIL;
    private $dbFP;
    private $configIL;
    private $configFP;

    public function __construct ()
    {
        $dbConfig = parse_ini_file( '/home/elearning-www/public_html/elearning/ilias/Customizing/global/include/fpraktikum/database/private/db-credentials.php', true ) or die( "Can not read ini-file" );

        $this->configFP = $dbConfig['fpraktikum'];
        $this->configIL = $dbConfig['ilias'];

        $this->dbFP = new Database( $this->configFP['link'], $this->configFP['username'], $this->configFP['passwd'], $this->configFP['dbname'] );
        $this->dbIL = new Database( $this->configIL['link'], $this->configIL['username'], $this->configIL['passwd'], $this->configIL['dbname'] );

        $this->dbFP->initDb();
        $this->dbIL->initDb();
    }

    /**
     * function to determine the free places in each institute
     * -> DB call to determine institutes
     *
     * @param string $semester current semester
     *
     * @throws FP_Error
     * @return array containing all data about the 'courses'
     *               following: [graduation =>
     *                                        institute =>
     *                                                    semester_half =>
     *                                                                    slots_remaining]
     */
    public function freePlaces ( $semester )
    {
        /*
         *
         * new statement: returns max slots of given institute
         *
         * old statement:
         * $stmt_angebote = $this->dbFP->prepare("SELECT institut, plaetze FROM ".$this->configFP['tbl-angebote']."
         * WHERE semester=? && (abschluss=? or abschluss='ALLE') && semester_haelfte=?");
         *
         * @author: Bastian
         * @date: 31.08.2016
         */
        $stmt_courses = $this->dbFP->prepare(
            "SELECT `institute`, `max_slots`
        FROM `tbl_courses`
        WHERE `semester`= ?
	        && ( `graduation` = ?
            OR `graduation` = 'ALL')
          && `semester_half`= ? "
        );

        $graduation = "";
        $semester_half = 0;
        $stmt_courses->bind_param( "ssi", $semester, $graduation, $semester_half ); // defines the ?'s in the above stmt.

        $stmt_places_remaining = $this->dbFP->prepare( "
      SELECT (any_value(c.max_slots) - COUNT(snumber1)-COUNT(snumber2))
        FROM tbl_registrations AS r
      JOIN tbl_partners AS p
        ON p.registration_id = r.registration_id
      JOIN tbl_courses AS c
        ON c.course_id = r.course_id1
        OR c.course_id = r.course_id2
      WHERE c.institute = ?
        AND c.semester = ?
        AND (c.graduation = ? OR c.graduation = 'ALL')
        AND c.semester_half = ?" );

        $institute = "";
        $stmt_places_remaining->bind_param( "sssi", $institute, $semester, $graduation, $semester_half );

        /**********************************************
         *
         * prepared_state: get remaining places
         *
         * @author: Bastian
         * @date  : 31.08.2016 18:30
         *        TODO: Testing prepared statement
         *
         * PREPARED_STATEMENT: EXAMPLE
         * $stmt_places_remaining = $this->dbFP->prepate("
         * SELECT (c.max_slots - COUNT(*))
         * FROM tbl_registrations AS r
         * JOIN tbl_partners AS p
         * ON p.registration_id = r.registration_id
         * JOIN tbl_courses AS c
         * ON c.course_id = r.course_id1
         * OR c.course_id = r.course_id2
         * WHERE c.institute = ?
         * AND c.semester = ?
         * AND c.graduation = ?
         * AND c.semester_half = ?");
         *
         * SQL-QUERY: EXAMPLE
         * returns remaining slots of current registration
         * SELECT (c.max_slots - COUNT(*)) AS 'remaining_slots'
         * FROM tbl_registrations AS r
         * JOIN tbl_partners AS p
         * ON p.registration_id = r.registration_id
         * JOIN tbl_courses AS c
         * ON c.course_id = r.course_id1
         * OR c.course_id = r.course_id2
         * WHERE c.institute = 'IAP'
         * AND c.semester = 'WS16/17'
         * AND c.graduation = 'BA'
         * AND c.semester_half = 0
         *
         **********************************************/

        $graduation_array = array( "BA", "MA", "MAIT", "LA" );    // TODO: LA = Lehr Amt ?

        $result = array();
        /*
        result = [graduation =>
                               institute =>
                                           semester_half =>
                                                           freeplaces]
         */

        // loop through graduations
        foreach ( $graduation_array as $key => $graduation )
        {
            $result[$graduation] = array();

            // loop through semesterhälfte
            for ( $semester_half = 0; $semester_half <= 1; $semester_half++ )
            {
                $max_slots = 0;
                $slots_remaining = 0;

                // loop through institut
                if ( ! $stmt_courses->execute() )
                {
                    throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt_courses->error );
                }

                $stmt_courses->bind_result( $institute, $max_slots );
                while ( $stmt_courses->fetch() )
                {
                    $stmt_courses->store_result();

                    if ( ! $stmt_places_remaining->execute() )
                    {
                        throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt_places_remaining->error );
                    }

                    $stmt_places_remaining->bind_result( $slots_remaining );
                    $stmt_places_remaining->fetch();

                    $slots_remaining = ($slots_remaining === NULL) ? $max_slots : $slots_remaining;

                    $result[$graduation][$institute][$semester_half] = $slots_remaining;

                    $stmt_places_remaining->store_result();
                }
            }
        }
        $stmt_courses->close();
        $stmt_places_remaining->close();

        return $result;
    }

    /**
     * function to check whether the hrz-number and name can be found in the ILIAS-DB
     *
     * @param string $hrz  the partners hrz-account
     * @param string $name the partners lastname
     *
     * @throws FP_Error
     * @return array true if user is in ILDB, false if not
     *              TODO: check whether user is already registered/a partner or even
     *                    the user online
     */
    public function checkPartner ( $hrz, $name, $semester )
    {
        $stmt = $this->dbIL->prepare( "SELECT `usr_id` FROM " . $this->configIL['tbl-name'] . "
      WHERE `login` = ? && `lastname` = ?" );
        $stmt->bind_param( "ss", $hrz, $name );

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $usr_id = "";
        $stmt->bind_result( $usr_id );

        $user = $this->checkUser( $hrz, $semester );

        if ( $stmt->fetch() )
        {
            return $user;
        }
        else
        {
            return array( false );
        }
    }


    /**
     * function to check whether the logged-in user is already registered/a partner or not
     * To check:  is user registered
     *            is user a partner but not accepted
     *            is user a partner and accepted
     *
     * @throws FP_Error
     * @return array containing at index 0 the type of person ('angemeldet' if
     *               user is registered, 'partner' if user is *only* a partner
     *               and false if user is not in db)
     *               at index 1 is the hrz number of the partner if user has registered themself
     *               or the hrz number of the person who registered if user is a partner.
     */
    public function checkUser ( $user_login, $semester )
    {

        $stmt = $this->dbFP->prepare( "SELECT `snumber1`, `snumber2`, `accepted` FROM tbl_partners AS p
     JOIN tbl_registrations AS r ON p.registration_id = r.registration_id
     JOIN tbl_courses AS c ON (r.course_id1 = c.course_id OR r.course_id2 = c.course_id)
     WHERE `c`.`semester` = ? AND (`p`.`snumber1` = ? OR `p`.`snumber2` = ?)" );

        $stmt->bind_param( "sss", $semester, $user_login, $user_login );

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $snumber1 = "";
        $snumber2 = "";
        $isAccepted = 0;
        $stmt->bind_result( $snumber1, $snumber2, $isAccepted );

        $stmt->fetch();
        if ( $snumber1 == $user_login )
        {
            $stmt->close();

            return array( 'type' => 'registered'
            , 'partner'          => $snumber2 );
        }
        else if ( $snumber2 == $user_login && ! $isAccepted )
        {
            $stmt->close();

            return array( 'type' => 'partner-open'
            , 'registrant'       => $snumber1 );
        }
        else if ( $snumber2 == $user_login && $isAccepted )
        {
            $stmt->close();

            return array( 'type' => 'partner-accepted'
            , 'registrant'       => $snumber1 );
        }
        else
        {
            $stmt->close();

            return array( 'type' => 'new' );
        }
    }

    /**
     * Function to check whether the users hrz-account is actually in the ilDB.
     *
     * @param  $hrz string containing the hrz-account of user
     *
     * @throws FP_Error
     * @return bool true if user was found, false if not
     */
    public function checkUserInfo ( $hrz )
    {
        $stmt = $this->dbIL->prepare( "SELECT `" . $this->configIL['col-name'] . "` FROM " . $this->configIL['tbl-name'] . "
      WHERE `login` = ?" );

        $stmt->bind_param( "s", $hrz );

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $user_id = "";
        $stmt->bind_result( $user_id );
        $check = $stmt->fetch();
        $stmt->close();

        return $check;
    }

    /**
     * Function returns the E-Mail of a user.
     *
     * @param $hrz string   HRZ-Number
     *
     * @return string       The users mail.
     * @throws FP_Error
     */
    public function getMail ( $hrz )
    {
        $stmt = $this->dbIL->prepare( "SELECT `email` FROM " . $this->configIL['tbl-name'] . "
      WHERE `login` = ?" );

        $stmt->bind_param( "s", $hrz );

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $user_email = "";
        $stmt->bind_result( $user_email );
        $stmt->fetch();

        return $user_email;
    }

    /**
     * Function to fetch additional information about a user from the ILIAS DB.
     *
     * @param $hrz string   HRZ of the user.
     *
     * @return array        first name + last name + matriculation
     * @throws FP_Error
     */
    public function getAddInfos ( $hrz )
    {
        $stmt = $this->dbIL->prepare( "SELECT `firstname`, `lastname`, `matriculation` FROM " . $this->configIL['tbl-name'] . "
      WHERE `login` = ?" );

        $stmt->bind_param( "s", $hrz );

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $firstname = "";
        $lastname = "";
        $matrikel = "";

        $stmt->bind_result( $firstname, $lastname, $matrikel );

        $stmt->fetch();

        return array(
            "first_name" => $firstname,
            "last_name"  => $lastname,
            "matrikel"   => $matrikel,
        );
    }

    ////////// Registration //////////

    /**
     * Function to add a new registration to the db.
     *
     * @param  array $data              information given by the user:
     *                                  hrz, graduation, semester, institute1, institute2
     * @param  string|null $partner_hrz the hrz of the partner or NULL
     *
     * @throws FP_Error                 if queries were not successful
     * @return bool                     if process was successful
     */
    public function setRegistration ( $data, $partner_hrz, $token )
    {
        $stmt_registration = $this->dbFP->prepare( "INSERT IGNORE INTO " . $this->configFP['tbl-registration'] . "
      VALUES(
      NULL,
      (SELECT `course_id` FROM " . $this->configFP['tbl-courses'] . " WHERE `semester` = ? AND `semester_half` = 0 AND `institute` = ? AND (`graduation` = ? OR `graduation` = 'ALL')),
      (SELECT `course_id` FROM " . $this->configFP['tbl-courses'] . " WHERE `semester` = ? AND `semester_half` = 1 AND `institute` = ? AND (`graduation` = ? OR `graduation` = 'ALL')),
      NOW())" );
        /**
         * JOIN hier nicht möglich, da tabelle dadurch redundant wird. z.B.:
         */

        // TODO: join instead of double select
        /** Probably the answer :
         *
         *
         *
         * (still occuring double counts)
         * Example:
         * Institute | course_id1 | course_id2
         * IAP         1              2
         * IAP         2              1
         *
         * Need to eliminate them.
         *
         *         SELECT t1.course_id AS `course_id1` ,t2.course_id AS `course_id2`
         * FROM `tbl_courses`
         * AS t1
         * JOIN `tbl_courses`
         * AS t2
         * ON t1.semester = t2.semester
         * WHERE t1.semester_half != t2.semester_half
         * AND t1.graduation = t2.graduation
         * AND t1.graduation = "BA"
         * AND t1.institute = "IAP"
         * AND t2.Institute = "PI"
         * AND t1.semester = "WS16/17"
         * AND t1.semester_half = 0
         */

        $stmt_partners = $this->dbFP->prepare( "INSERT INTO tbl_partners
      VALUES(
      NULL,
      ?,
      ?,
      (SELECT `registration_id` FROM tbl_registrations
        WHERE `course_id1` = (SELECT `course_id` FROM " . $this->configFP['tbl-courses'] . " WHERE `semester` = ? AND
                                `semester_half` = 0 AND `institute` = ? AND (`graduation` = ? OR `graduation` = 'ALL'))
        AND `course_id2` = (SELECT `course_id` FROM " . $this->configFP['tbl-courses'] . " WHERE `semester` = ? AND
                                `semester_half` = 1 AND `institute` = ? AND (`graduation` = ? OR `graduation` = 'ALL')))
      ,?
      ,0
      ,?
      ,?)
        " );

        $stmt_registration->bind_param( "ssssss", $data['semester'], $data['institute1'], $data['graduation'],
            $data['semester'], $data['institute2'], $data['graduation'] );

        $stmt_partners->bind_param( "sssssssssss", $data['registrant'], $partner_hrz, $data['semester'],
            $data['institute1'], $data['graduation'], $data['semester'], $data['institute2'], $data['graduation'],
            $data['graduation'], $data['notes'], $token );

        // if any of both queries fail, throw an error and log everything useful for debugging -> also important
        // to handle support requests
        if ( ! $stmt_registration->execute() )
        {
            Logger::log( "Database Error in '" . __FUNCTION__ . "()' when trying to write tbl_registrations:\n"
                . "\t" . $stmt_registration->error . "\n\tData: '" . implode( "', '", $data ) . "'\n"
                . "\tPartner: '" . $partner_hrz . "'", 1 );
            throw new FP_Error( "Database Error: " . $stmt_registration->error );
        }

        if ( ! $stmt_partners->execute() )
        {
            Logger::log( "Database Error in '" . __FUNCTION__ . "()' when trying to write into tbl_partners:\n"
                . "\t" . $stmt_partners->error . "\n\tData: '" . implode( ", ", $data ) . "'\n"
                . "\tPartner: '" . $partner_hrz . "'", 1 );
            throw new FP_Error( "Database Error: " . $stmt_partners->error );
        }

        $stmt_registration->close();
        $stmt_partners->close();

        return true;
    }

    /**
     * This function sets the 'accepted' flag in table 'tbl_partners' for a given partner_hrz.
     *
     * @param $partner_hrz string   The HRZ of the partner
     * @param $semester    string   The current semester.
     *
     * @throws FP_Error
     * @return true
     */
    public function setPartnerAccepted ( $partner_hrz, $semester )
    {
        $stmt = $this->dbFP->prepare( "UPDATE tbl_partners AS p
            JOIN tbl_registrations AS r ON p.registration_id = r.registration_id
            JOIN tbl_courses AS c ON (r.course_id1 = c.course_id OR r.course_id2 = c.course_id)
            SET p.accepted = 1
            WHERE c.semester = ? AND p.snumber2 = ?" );

        $stmt->bind_param( "ss", $semester, $partner_hrz );

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $stmt->close();

        return true;
    }

    /**
     * This function unsets the 'accepted' flag in table 'tbl_partners' for a given partner_hrz
     * and removes the partner_hrz from the database.
     *
     * @param $partner_hrz string   The HRZ of the partner
     * @param $semester    string   The current semester.
     *
     * @throws FP_Error
     * @return true
     */
    public function rmPartner ( $partner_hrz, $semester )
    {
        $stmt = $this->dbFP->prepare( "UPDATE tbl_partners AS p
            JOIN tbl_registrations AS r ON p.registration_id = r.registration_id
            JOIN tbl_courses AS c ON (r.course_id1 = c.course_id OR r.course_id2 = c.course_id)
            SET p.accepted = 0, p.snumber2 = NULL
            WHERE c.semester = ? AND p.snumber2 = ?" );

        $stmt->bind_param( "ss", $semester, $partner_hrz );
        $stmt->execute();

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $stmt->close();

        return true;
    }

    /**
     * Gunction to get data about a user.
     *
     * @param  string $hrz
     * @param  string $semester
     *
     * @throws FP_Error
     * @return array           information found
     */
    public function getRegistration ( $hrz, $semester )
    {
        $stmt = $this->dbFP->prepare( "SELECT p.snumber2, p.accepted, c.institute, p.graduation, r.register_date,p.notes,p.token
      FROM tbl_partners AS p
      JOIN tbl_registrations AS r ON p.registration_id = r.registration_id
      JOIN tbl_courses AS c ON r.course_id1 = c.course_id OR r.course_id2 = c.course_id
      WHERE c.semester_half = ? AND p.snumber1 = ? AND c.semester = ?" );

        $semester_half = 0;
        $stmt->bind_param( "iss", $semester_half, $hrz, $semester );

        $data = array();

        $snumber2 = "";
        $isAccepted = 0;
        $institute = "";
        $graduation = "";
        $register_date = "";
        $notes = "";
        $token = "";
        for ( $semester_half = 0; $semester_half <= 1; $semester_half++ )
        {
            if ( ! $stmt->execute() )
            {
                throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
            }

            $stmt->bind_result( $snumber2, $isAccepted, $institute, $graduation, $register_date, $notes, $token );
            if ( $stmt->fetch() )
            {
                $data['institute' . ($semester_half + 1)] = $institute;
            }
            else
            {
                echo("Fehler beim Abfragen der Anmeldedaten!");
            }
        }
        $data['partner'] = $snumber2;
        $data['isAccepted'] = $isAccepted;
        $data['graduation'] = $graduation;
        $data['register_date'] = $register_date;
        $data['notes'] = $notes;
        $data['token'] = $token;
        $stmt->close();

        return $data;
    }

    /**
     * Function to delete the registration of one user.
     *
     * @param  array $data
     *
     * @throws FP_Error
     * @return true
     */
    public function rmRegistration ( $data )
    {
        $stmt = $this->dbFP->prepare(
            "DELETE p
             FROM tbl_partners AS p
             JOIN tbl_registrations AS r ON p.registration_id = r.registration_id
             JOIN tbl_courses AS c ON r.course_id1 = c.course_id OR r.course_id2 = c.course_id
             WHERE `p`.`snumber1` = ? AND `c`.`semester` = ?"
        );

        $stmt->bind_param( "ss", $data['registrant'], $data['semester'] );

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $stmt->close();

        return true;
    }

    /**
     * Get all registrations in DB.
     *
     * @param  string $semester
     *
     * @throws FP_Error
     * @return array
     */
    public function getAllRegistrations ( $semester )
    {
        $stmt = $this->dbFP->prepare(
            "SELECT p.snumber1, p.snumber2, r.register_date, c1.institute, p.graduation, c2.institute, p.notes
             FROM tbl_partners AS p
             JOIN tbl_registrations AS r ON p.registration_id = r.registration_id
             JOIN tbl_courses AS c1 ON c1.course_id = r.course_id1
             JOIN tbl_courses AS c2 ON c2.course_id = r.course_id2
             WHERE c1.semester = ? AND c2.semester = ?"
        );

        $stmt->bind_param( "ss", $semester, $semester );

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $hrz1 = "";
        $hrz2 = "";
        $date = "";
        $institute1 = "";
        $institute2 = "";
        $graduation = "";
        $notes = "";

        $stmt->bind_result( $hrz1, $hrz2, $date, $institute1, $graduation, $institute2, $notes );

        $data = array();
        while ( $stmt->fetch() )
        {
            array_push( $data, array(
                'hrz1'       => $hrz1,
                'hrz2'       => $hrz2,
                'graduation' => $graduation,
                'institute1' => $institute1,
                'institute2' => $institute2,
                'date'       => $date,
                'notes'      => "'$notes'"
            ) );
        }
        $stmt->close();

        return $data;
    }

    ////////// Courses //////////

    /**
     * Function to add a new course to the db, slots needs to be an integer.
     *
     * @throws FP_Error
     * @return bool if query was successfull
     */
    public function setOffers ( $institute, $semester, $graduation, $semester_half, $slots )
    {
        $stmt = $this->dbFP->prepare( "INSERT INTO tbl_courses
      VALUES(NULL, ?, ?, ?, ?, ?)" );

        $stmt->bind_param( "ssisi", $institute, $semester, $semester_half, $graduation, $slots );

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $stmt->close();

        return true;
    }

    /**
     * Function to receive an multidimensional array containing all course data.
     *
     * @param $semester string  Current semester.
     *
     * @throws FP_Error
     * @return array containing data about all angebote:
     *               [['institut', 'semester', 'abschluss', 'semesterhaelfte', 'plaetze']]
     */
    public function getOffers ( $semester )
    {

        $stmt = $this->dbFP->prepare( "SELECT `institute`, `semester_half`, `graduation`, `max_slots`
      FROM tbl_courses WHERE `semester` = ?
      ORDER BY `graduation`, `institute`, `semester_half`" );

        $stmt->bind_param( "s", $semester );

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $institute = "";
        $semester_half = 0;
        $graduation = "";
        $max_slots = 0;

        $stmt->bind_result( $institute, $semester_half, $graduation, $max_slots );

        $result = array();
        while ( $stmt->fetch() )
        {
            array_push( $result, array(
                'institute'     => $institute,
                'graduation'    => $graduation,
                'semester_half' => $semester_half,
                'max_slots'     => $max_slots
            ) );
        }
        $stmt->close();

        return $result;
    }

    /**
     * Function checks if an offer is valid.
     * Can be used to check user entries.
     *
     * @param $institute
     * @param $semester
     * @param $semester_half
     * @param $graduation
     *
     * @return mixed
     * @throws FP_Error
     */
    public function isOffer ( $institute, $semester, $semester_half, $graduation )
    {
        $stmt = $this->dbFP->prepare(
            "SELECT `course_id`
             FROM tbl_courses
             WHERE `institute` = ? AND `semester` = ? AND `semester_half` = ? AND (`graduation` = ? OR `graduation` = 'ALL')"
        );

        $stmt->bind_param( "ssis", $institute, $semester, $semester_half, $graduation );

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        return $stmt->fetch();
    }

    /**
     * Remove one course from db.
     *
     * @param  array $data name of institut, semester, abschluss, semesterhaelfte
     *
     * @throws FP_Error
     * @return true
     */
    public function rmOffer ( $data )
    {
        $stmt = $this->dbFP->prepare(
            "DELETE FROM tbl_courses
            WHERE `institute` = ? AND `semester` = ? AND `semester_half` = ? AND (`graduation` = ? OR `graduation` = 'ALL')"
        );

        $stmt->bind_param( "ssis", $data['institute'], $data['semester'], $data['semester_half'], $data['graduation'] );

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $stmt->close();

        return true;
    }

    /**
     * Functions returns the token linked to a HRZ number.
     *
     * @param $registrant
     * @param $semester
     *
     * @throws
     * @return mixed
     */
    public function get_token ( $registrant, $semester )
    {
        $stmt = $this->dbFP->prepare(
            "SELECT DISTINCT(token) FROM tbl_partners AS p
             JOIN tbl_registrations AS r ON p.registration_id = r.registration_id
             JOIN tbl_courses AS c ON (r.course_id1 = c.course_id OR r.course_id2 = c.course_id)
             WHERE `c`.`semester` = ?
             AND (snumber1 = ? OR snumber2 = ?)"
        );

        $stmt->bind_param( "sss", $semester, $registrant, $registrant );

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $token = "";
        $stmt->bind_result( $token );
        $stmt->fetch();
        $stmt->close();

        return $token;
    }

    public function setDate( $startdate, $enddate, $semester )
    {
        $stmt = $this->dbFP->prepare("INSERT INTO tbl_dates VALUES ( ?, ?, ?,NULL )");
        $stmt->bind_param("sss", $startdate, $enddate, $semester);

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $stmt->close();

        return true;
    }

    public function getDates($semester)
    {
        $stmt = $this->dbFP->prepare("SELECT startdate , enddate FROM tbl_dates WHERE semester = ? ");
        $stmt->bind_param("s", $semester);

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $stmt->bind_result( $startdate ,$enddate);

        $stmt->fetch();

        $dates= array(
            'startdate' => $startdate,
            'enddate'   => $enddate
        );
        $stmt->close();

        return $dates;
    }

    public function rmDates($semester)
    {
        $stmt = $this->dbFP->prepare("DELETE FROM tbl_dates WHERE semester = ?");
        $stmt->bind_param("s", $semester);

        if ( ! $stmt->execute() )
        {
            throw new FP_Error( "Database Error in '" . __FUNCTION__ . "()': " . $stmt->error );
        }

        $stmt->close();

        return true;

    }
}