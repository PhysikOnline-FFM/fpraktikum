<?php

//error_reporting(-1);
//ini_set('display_errors', 1);

require_once('/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/database/class.Database.php');

/**
 * Brainfuck SQL O.o?
 *
 * Checks if student has is student1
 * SELECT COUNT(snumber1) FROM tbl_partners WHERE tbl_partners.snumber1 = 'Blah' 
 * returns: 0 or more
 *
 * TODO: 
 * - Query: Update snumber1 to value of snumber2 if snumber1 student was snumber1
 * - Query: Update snumber2 to NULL  if student was snumber2
 *
 * @author: Bastian
 * @date: 02.09.2016
 */

/**
 * class containing all functions necessary to communicate with the database
 * for the registration process
 *
 * @author LG, BK
 */
class FP_Database extends Database
{
  public function __construct()
  {
    $dbConfig = parse_ini_file('/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/database/private/db-credentials.php', true) or die("Can not read ini-file");

    $configFP = $dbConfig['fpraktikum'];
    $configIL = $dbConfig['ilias'];

    $dbFP = new Database($configFP['link'], $configFP['username'], $configFP['passwd'], $configFP['dbname']);
    $dbIL = new Database($configIL['link'], $configIL['username'], $configIL['passwd'], $configIL['dbname']);

    $dbFP->initDb();
    $dbIL->initDb();

    $this->dbIL = $dbIL;
    $this->dbFP = $dbFP;
    $this->configIL = $configIL;
    $this->configFP = $configFP;
  }

  /**
   * function to determine the free places in each institute
   * -> DB call to determine institutes
   *
   * @param string $semester current semester
   * @return array containing all data about the 'courses'
   *               following: [graduation =>
   *                                        institute =>
   *                                                    semester_half =>
   *                                                                    slots_remaining]
   *               TODO: Partner is not being counted yet
   */
  public function freePlaces($semester) {
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
     *
     */ 
    $stmt_courses = $this->dbFP->prepare(
      "SELECT `institute`, `max_slots`
        FROM `tbl_courses`
        WHERE `semester`= ? 
	        && ( `graduation` = ? 
            OR `graduation` = '' 
            OR `graduation` IS NULL) 
          && `semester_half`= ? "
    );

    $stmt_courses->bind_param("ssi", $semester, $graduation, $semester_half);
    
    $stmt_angebote_remaining = $this->dbFP->prepare("
      SELECT (c.max_slots - COUNT(*)) 
        FROM tbl_registrations AS r
      JOIN tbl_partners AS p
        ON p.registration_id = r.registration_id
      JOIN tbl_courses AS c
        ON c.course_id = r.course_id1
        OR c.course_id = r.course_id2
      WHERE c.institute = ? 
        AND c.semester = ?
        AND c.graduation = ?
        AND c.semester_half = ?");

    $stmt_angebote_remaining->bind_param("sssi", $institute, $semester, $graduation, $semester_half);

    /**********************************************
     *
     * prepared_state: get remaining places
     * @author: Bastian
     * @date: 31.08.2016 18:30
     * TODO: Testing prepared statement
     * 
     * PREPARED_STATEMENT: EXAMPLE
     * $stmt_angebote_remaining = $this->dbFP->prepate("
      SELECT (c.max_slots - COUNT(*)) 
      FROM tbl_registrations AS r
      JOIN tbl_partners AS p
      ON p.registration_id = r.registration_id
      JOIN tbl_courses AS c
      ON c.course_id = r.course_id1
      OR c.course_id = r.course_id2
      WHERE c.institute = ? 
      AND c.semester = ?
      AND c.graduation = ?
      AND c.semester_half = ?");
     *
     * SQL-QUERY: EXAMPLE 
     * returns remaining slots of current registration
     * SELECT (c.max_slots - COUNT(*)) AS 'remaining_slots' 
      FROM tbl_registrations AS r
      JOIN tbl_partners AS p
      ON p.registration_id = r.registration_id
      JOIN tbl_courses AS c
      ON c.course_id = r.course_id1
      OR c.course_id = r.course_id2
      WHERE c.institute = 'IAP' 
      AND c.semester = 'WS16/17'
      AND c.graduation = 'BA'
      AND c.semester_half = 0
     *
     **********************************************/
    

    $graduation_array = array("BA", "MA", "MAIT", "LA");

    $result = [];
    /*
    result = [graduation =>
                           institute =>
                                       semester_halg =>
                                                       freeplaces]
     */
    
    // loop through abschluss
    foreach ($graduation_array as $key => $graduation) {
      $result[$graduation] = [];

      // loop through semesterhälfte
      for ($semester_half=0; $semester_half <= 1; $semester_half++) { 
        
        // loop through institut
        $stmt_courses->execute();
        $stmt_courses->bind_result($institute, $max_slots);
        while ($stmt_courses->fetch()) {
          
          $stmt_courses->store_result();

          $stmt_angebote_remaining->execute();
          $stmt_angebote_remaining->bind_result($slots_remaining);
          $stmt_angebote_remaining->fetch();

          $slots_remaining = ($slots_remaining == NULL) ? $max_slots : $slots_remaining;

          $result[$graduation][$institute][$semester_half] = $slots_remaining;

          $stmt_angebote_remaining->store_result();
        }
      }
    }

    return $result;
    $stmt_courses->close();
    $stmt_angebote_remaining->close();
  }

  /**
   * function to check whether the hrz-number and name can be found in the ILIAS-DB
   * @param string $hrz the partners hrz-account
   * @param string $name the partners lastname 
   * @return bool true if user is in ILDB, false if not
   *              TODO: check whether user is already registered/a partner or even
   *                    the user online
   */
  public function checkPartner($hrz, $name, $semester) {    
    $stmt = $this->dbIL->prepare("SELECT `usr_id` FROM ".$this->configIL['tbl-name']." 
      WHERE `login` = ? && `lastname` = ?");
    $stmt->bind_param("ss", $hrz, $name);

    $stmt->execute();
    $stmt->bind_result($usr_id);

    $user = $this->checkUser($hrz, $semester);

    if ($stmt->fetch()) {
       return $user;
    } else {
      return false;
    }

    $stmt->close();
  }


  /**
   * function to check whether the logged-in user is already registered/a partner or not
   * To check:  is user registered 
   *            is user a partner but not accepted
   *            is user a partner and accepted
   *
   * @return array containing at index 0 the type of person ('angemeldet' if
   *               user is registered, 'partner' if user is *only* a partner
   *               and false if user is not in db)
   */
  public function checkUser($user_login, $semester) {

    $stmt = $this->dbFP->prepare("SELECT `snumber1`, `snumber2`, `accepted` FROM tbl_partners AS p 
     JOIN tbl_registrations AS r ON p.registration_id = r.registration_id 
     JOIN tbl_courses AS c ON (r.course_id1 = c.course_id OR r.course_id2 = c.course_id) 
     WHERE `c`.`semester` = ? AND (`p`.`snumber1` = ? OR `p`.`snumber2` = ?)");

    $stmt->bind_param("sss", $semester, $user_login, $user_login);
    $stmt->execute();
    $stmt->bind_result($snumber1, $snumber2, $isAccepted);

    $stmt->fetch();
    if ($snumber1 == $user_login) {
      return array('registered');
    } else if ($snumber2 == $user_login && !$isAccepted) {
      return array('partner-accept', $snumber1);
    } else if ($snumber2 == $user_login && $isAccepted) {
      return array('partner-accepted');
    } else {
      return array(false);
    }
    
    $stmt->close();
  }

  /**
   * function to check whether the users hrz-account is actually in the ilDB
   * @param  string containing the hrz-account of user
   * @return bool true if user was found, false if not
   */
  public function checkUserInfo($hrz)
  {
    $stmt = $this->dbIL->prepare("SELECT `".$this->configIL['col-name']."` FROM ".$this->configIL['tbl-name']."
      WHERE `login` = ?");

    $stmt->bind_param("s", $hrz);
    $stmt->execute();
    $stmt->bind_result($user_id);

    return $stmt->fetch();
    $stmt->close();
  }

  ////////// Registration //////////

  /**
   * function to add a new registration to the db
   * @param  array $data       information given by the user:
   *                           hrz, graduation, semester, institute1, institute2
   * @param  string|null $partner_hrz the hrz of the partner or NULL
   */
  public function setAnmeldung($data, $partner_hrz)
  {      
    
    $stmt_registration = $this->dbFP->prepare("INSERT IGNORE INTO ".$this->configFP['tbl-registration']." 
      VALUES(
      NULL, 
      (SELECT `course_id` FROM ".$this->configFP['tbl-courses']." WHERE `semester` = ? AND `semester_half` = 0 AND `institute` = ? AND `graduation` = ?), 
      (SELECT `course_id` FROM ".$this->configFP['tbl-courses']." WHERE `semester` = ? AND `semester_half` = 1 AND `institute` = ? AND `graduation` = ?), 
      NOW())");

    // TODO: join instead of double select
    $stmt_partners = $this->dbFP->prepare("INSERT INTO tbl_partners
      VALUES(
      NULL,
      ?,
      ?,
      (SELECT `registration_id` FROM tbl_registrations 
        WHERE `course_id1` = (SELECT `course_id` FROM ".$this->configFP['tbl-courses']." WHERE `semester` = ? AND 
                                `semester_half` = 0 AND `institute` = ? AND `graduation` = ?)
        AND `course_id2` = (SELECT `course_id` FROM ".$this->configFP['tbl-courses']." WHERE `semester` = ? AND 
                                `semester_half` = 1 AND `institute` = ? AND `graduation` = ?)),
      0)");

    $stmt_registration->bind_param("ssssss", $data['semester'], $data['institute1'], $data['graduation'], 
        $data['semester'], $data['institute2'], $data['graduation']);
    
    $stmt_partners->bind_param("ssssssss", $data['hrz'], $partner_hrz, $data['semester'], $data['institute1'], $data['graduation'], $data['semester'], $data['institute2'], $data['graduation']);

    if ($stmt_registration->execute() && $stmt_partners->execute()) {
      return true;
    } else {
      die ("Fehler beim Eintragen der Daten: <br>registration: ".$stmt_registration->error."<br> partners: ".$stmt_partners->error);
    }
    $stmt_registration->close();
    $stmt_partners->close();
  }

  /**
   * function to get data about a user
   * @param  string $hrz
   * @param  string $semester
   * @return array           information found
   */
  public function getAnmeldung($hrz, $semester)
  {
    /*
      New query:
      SELECT p.snumber2, p.accepted, c.institute, c.graduation, r.register_date FROM tbl_partners AS p JOIN tbl_registrations as r On p.registration_id = r.registration_id JOIN tbl_courses as c ON r.course_id1 = c.course_id OR r.course_id2 = c.course_id WHERE c.semester_half = 0 AND p.snumber1 = 's123456'
     */

    $stmt = $this->dbFP->prepare("SELECT p.snumber2, p.accepted, c.institute, c.graduation, r.register_date 
      FROM tbl_partners AS p 
      JOIN tbl_registrations AS r ON p.registration_id = r.registration_id 
      JOIN tbl_courses AS c ON r.course_id1 = c.course_id OR r.course_id2 = c.course_id 
      WHERE c.semester_half = ? AND p.snumber1 = ? AND c.semester = ?");

    $stmt->bind_param("iss", $semester_half, $hrz, $semester);

    $data = [];
    for ($semester_half = 0; $semester_half <= 1; $semester_half++) { 
      $stmt->execute();
      $stmt->bind_result($snumber2, $isAccepted, $institute, $graduation, $register_date);
      if ($stmt->fetch()) {
        $data['institute'.$semester_half] = $institute;
      } else {
        die("Fehler beim Abfragen der Anmeldedaten!");
      }
    }
    $data['partner'] = $snumber2;
    $data['isAccepted'] = $isAccepted;
    $data['graduation'] = $graduation;
    $data['register_date'] = $register_date;

    return $data;
    
    $stmt->close();
  }

  /**
   * function to delete the registration of one user
   * TODO: Partner
   * @param  array $data 
   * @return bool if query was successfull
   */
  public function rmAnmeldung($data)
  {
    // TODO: Join with other tables to check for right semester
    $stmt = $this->dbFP->prepare("
      DELETE FROM tbl_partners
      WHERE `snumber1` = ?");
    // $stmt = $this->dbFP->prepare("DELETE FROM ".$this->configFP['tbl-anmeldung']." 
    //   WHERE `hrz` = ? && `semester` = ?");

    $stmt->bind_param("s", $data['hrz']);
    return $stmt->execute();

    $stmt->close();
  }

  /**
   * get all registrations in DB
   * @param  string $semester 
   * @return array           
   */
  public function getAllAnmeldungen($semester)
  {
    $stmt = $this->dbFP->prepare("SELECT p.snumber1, p.snumber2, r.register_date, c1.institute, c1.graduation, c2.institute 
      FROM tbl_partners AS p 
      JOIN tbl_registrations AS r ON p.registration_id = r.registration_id 
      JOIN tbl_courses AS c1 ON c1.course_id = r.course_id1 
      JOIN tbl_courses AS c2 ON c2.course_id = r.course_id2 
      WHERE c1.semester = ? AND c2.semester = ?");

    $stmt->bind_param("ss", $semester, $semester);
    $stmt->execute();
    $stmt->bind_result($hrz1, $hrz2, $date, $institute1, $graduation, $institute2);

    $data = [];
    while ($stmt->fetch()) {
      array_push($data, array(
        'hrz1' => $hrz1,
        'hrz2' => $hrz2,
        'graduation' => $graduation,
        'institute1' => $institute1,
        'institute2' => $institute2,
        'date' => $date
        ));
    }

    return $data;
  }

  ////////// Courses //////////

  /**
   * function to add a new course to the db, slots needs to be an integer
   *
   * @return bool if query was successfull
   */
  public function setAngebote($institute, $semester, $graduation, $semester_half, $slots)
  {
    $stmt = $this->dbFP->prepare("INSERT INTO tbl_courses
      VALUES(NULL, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssisi", $institute, $semester, $semester_half, $graduation, $slots);
    
    if($stmt->execute()) {
      return true;
    } else {
      die('Fehler beim Eintragen des Angebots.');
    }

    $stmt->close();
  }

  /**
   * function to reciece an multidim array containing all course data
   *
   * @return array containing data about all angebote:
   *               [['institut', 'semester', 'abschluss', 'semesterhaelfte', 'plaetze']]
   */
  public function getAngebote($semester)
  {

    $stmt = $this->dbFP->prepare("SELECT `institute`, `semester_half`, `graduation`, `max_slots` 
      FROM tbl_courses WHERE `semester` = ? 
      ORDER BY `graduation`, `institute`, `semester_half`");

    $stmt->bind_param("s", $semester);
    $stmt->execute();
    $stmt->bind_result($institute, $semester_half, $graduation, $max_slots);

    $result = [];
    while($stmt->fetch()) {
       array_push($result, array(
        'institute' => $institute,
        'graduation' => $graduation,
        'semester_half' => $semester_half,
        'max_slots' => $max_slots
       ));
    } 
    return $result;

    $stmt->close();
  }  

  /**
   * remove one course from db
   * @param  array $data name of institut, semester, abschluss, semesterhaelfte
   * @return bool
   */
  public function rmAngebot($data)
  {
    $stmt = $this->dbFP->prepare("DELETE FROM tbl_courses 
      WHERE `institute` = ? AND `semester` = ? AND `semester_half` = ? AND `graduation` = ?");
    
    $stmt->bind_param("ssis", $data['institute'], $data['semester'], $data['semester_half'], $data['graduation']);
    return $stmt->execute();

    $stmt->close();
  }  
}