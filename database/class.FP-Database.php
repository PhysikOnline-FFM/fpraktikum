<?php

error_reporting(-1);
//ini_set('display_errors', 1);

require_once('/home/elearning-www/public_html/elearning/ilias-4.3/Customizing/global/include/fpraktikum/database/class.Database.php');

/**
 * class containing all functions necessary to communicate with the database
 *
 * TODO: 
 */
class FP_Database extends Database
{
  public function __construct()
  {
    $dbConfig = parse_ini_file('/home/elearning-www/public_html/elearning/ilias-4.3/Customizing/global/include/fpraktikum/database/private/db-credentials.ini', true) or die("Can not read ini-file");

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
   * -> DB call to determine institutes TODO
   */
  public function freePlaces($semester) {
    $stmt_angebote = $this->dbFP->prepare("SELECT institut, plaetze FROM ".$this->configFP['tbl-angebote']."
      WHERE semester=? && (abschluss=? or abschluss='ALLE') && semester_haelfte=?");

    $stmt_anmeldung = $this->dbFP->prepare("SELECT hrz, partner FROM ".$this->configFP['tbl-anmeldung']." 
      WHERE semester=? && abschluss=? && (semesterhaelfte1=? or semesterhaelfte2=?)");

    $stmt_angebote->bind_param("sss", $semester, $abschluss, $semester_haelfte);
    
    $stmt_anmeldung->bind_param("ssss", $semester, $abschluss, $haelfte[0], $haelfte[1]);

    $abschluss_array = array("BA", "MA", "MAIT", "LA");

    $result = [];
    /*
    result = [abschluss =>
                          institut =>
                                      haelfte =>
                                                freeplaces]
     */
    
    // loop through abschluss
    foreach ($abschluss_array as $key => $abschluss) {
      $result[$abschluss] = [];

      // loop through semesterhälfte
      for ($semester_haelfte=1; $semester_haelfte <= 2; $semester_haelfte++) { 
        
        // loop through institut
        $stmt_angebote->execute();
        $stmt_angebote->bind_result($institut, $plaetze);
        while ($stmt_angebote->fetch()) {
          
          $belegt = 0;

          $haelfte[0] = NULL;
          $haelfte[1] = NULL;

          $haelfte[$semester_haelfte-1] = $institut;

          $stmt_angebote->store_result();

          // loop through anmeldungen
          $stmt_anmeldung->execute();
          $stmt_anmeldung->bind_result($hrz, $partner);
          while ($stmt_anmeldung->fetch()) {
            if ($partner) {
              $belegt += 2;
            } else {
              $belegt += 1;
            }
          }
          
          $result[$abschluss][$institut][$semester_haelfte] = $plaetze - $belegt;
        }
      }
    }

    return $result;
    $stmt_anmeldung->close();
    $stmt_angebote->close();
  }

  /**
   * function to check whether the hrz-number and name can be found in the ILIAS-DB
   * @param [string] $hrz the partners hrz-account
   * @param [string] $name the partners lastname 
   */
  public function checkPartner($hrz, $name) {    
    $stmt = $this->dbIL->prepare("SELECT usr_id FROM ".$this->configIL['tbl-name']." WHERE login=? && lastname=?");
    $stmt->bind_param("ss", $hrz, $name);

    $stmt->execute();
    $stmt->bind_result($usr_id);

    if ($stmt->fetch()) {
       return true;
    } else {
      return false;
    }
  }

  /**
   * function to check whether the logged-in user is already registered/a partner or not
   * TODO was passiert wenn ein Nutzer angemeldet und ein partner ist?
   */
  public function checkUser($user_matrikel, $user_login, $semester) {

    $stmt_angemeldet = $this->dbFP->prepare("SELECT hrz FROM ".$this->configFP['tbl-anmeldung']." 
      WHERE matrikelnummer=? && semester=?");
    $stmt_partner = $this->dbFP->prepare("SELECT hrz FROM ".$this->configFP['tbl-anmeldung']." 
      WHERE partner=? && semester=?");

    $stmt_angemeldet->bind_param("ss", $user_matrikel, $semester);
    $stmt_partner->bind_param("ss", $user_login, $semester);

    $stmt_angemeldet->execute();
    $stmt_partner->execute();

    $stmt_angemeldet->bind_result($hrz);
    $stmt_partner->bind_result($hrz);

    if ($stmt_angemeldet->fetch()) {
        return "angemeldet";
      } else if ($stmt_partner->fetch()) {
        return "partner";
      } else {
        return false;
      }
  }

  /**
   * function to add a new registration to the db
   * @param  [array] $data       information given by the user
   * @param  [string|null] $partner_db the hrz of the partner or NULL
   */
  public function setAnmeldung($data, $partner_db)
  {    
    
    if ($partner_db == NULL) {
      $stmt = $this->dbFP->prepare("INSERT INTO ".$this->configFP['tbl-anmeldung']." 
        VALUES(?, ?, ?, ?, ?, NULL, ?, ?, NOW())");

      $stmt->bind_param("sssssss", $data['hrz'], $data['name'], $data['matrikel'], 
        $data['abschluss'], $data['semester'], $data['institut1'], $data['institut2']);
    } else {
      $stmt = $this->dbFP->prepare("INSERT INTO ".$this->configFP['tbl-anmeldung']." 
        VALUES(?, ?, ?, ?, ?, ?, ?, ?, NOW())");

      $stmt->bind_param("ssssssss", $data['hrz'], $data['name'], $data['matrikel'], 
        $data['abschluss'], $data['semester'], $partner_db, $data['institut1'], $data['institut2']);
    }

    return $stmt->execute();
  }

  /**
   * function to get data about a user
   * @param  [stinrg] $hrz
   * @param  [string] $semester
   * @return [array]           information found
   */
  public function getAnmeldung($hrz, $semester)
  {

    $stmt = $this->dbFP->prepare("SELECT name, matrikelnummer, abschluss, partner, semesterhaelfte1, semesterhaelfte2, datum
     FROM ".$this->configFP['tbl-anmeldung']." WHERE hrz=? && semester=?");

    $stmt->bind_param("ss", $hrz, $semester);
    $stmt->execute();
    $stmt->bind_result($name, $matrikel, $abschluss, $partner, $institut1, $institut2, $datum);

    if ($stmt->fetch()) {
      return array('name' => $name,
        'matrikel' => $matrikel,
        'abschluss' => $abschluss,
        'partner' => $partner,
        'datum' => $datum,
        'institut1' => $institut1,
        'institut2' => $institut2
        );
    } else {
      die('Fehler beim Abfragen der Anmeldedaten!');
    }
  }

  /**
   * function to delete the registration of one user
   * TODO: Partner
   * @param  [array] $data 
   * @return [bool]
   */
  public function rmAnmeldung($data)
  {
    $stmt = $this->dbFP->prepare("DELETE FROM ".$this->configFP['tbl-anmeldung']." 
      WHERE hrz=? && matrikelnummer=? && semester=?");

    $stmt->bind_param("sss", $data['hrz'], $data['matrikel'], $data['semester']);
    return $stmt->execute();
  }

  /**
   * function to add a new angebot to the db, plaetze needs to be an integer
   */
  public function setAngebote($institut, $semester, $abschluss, $haelfte, $plaetze)
  {
    $stmt = $this->dbFP->prepare("INSERT INTO ".$this->configFP['tbl-angebote']."
      VALUES(?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssi", $institut, $semester, $abschluss, $haelfte, $plaetze);
    
    if($stmt->execute()) {
      return true;
    } else {
      die('Fehler beim Eintragen des Angebots.');
    }
  }

  /**
   * function to reciece an multidim array containing the angebot data
   */
  public function getAngebote($semester)
  {

    $stmt = $this->dbFP->prepare("SELECT institut, abschluss, semester_haelfte, plaetze 
      FROM ".$this->configFP['tbl-angebote']." WHERE semester=? ORDER BY abschluss, institut, semester_haelfte");

    $stmt->bind_param("s", $semester);
    $stmt->execute();
    $stmt->bind_result($institut, $abschluss, $haelfte, $plaetze);

    $result = [];
    while($stmt->fetch()) {
       array_push($result, array(
        'institut' => $institut,
        'semester' => $semester,
        'abschluss' => $abschluss,
        'semester_haelfte' => $haelfte,
        'plaetze' => $plaetze
       ));
    } 
    return $result;
  }  

  public function rmAngebot($data)
  {
    $stmt = $this->dbFP->prepare("DELETE FROM ".$this->configFP['tbl-angebote']." 
      WHERE institut=? && semester=? && abschluss=? && semester_haelfte=?");
    
    $stmt->bind_param("ssss", $data['institut'], $data['semester'], $data['abschluss'], $data['semester_haelfte']);
    return $stmt->execute();
  }
}