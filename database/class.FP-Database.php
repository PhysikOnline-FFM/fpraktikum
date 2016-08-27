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
  public function freePlaces() {
    return $testArray = ["IAP1" => 10, "PI1" => 5, "ITP1" => 3,
                  "IAP2" => 15, "PI2" => 8, "ITP2" => 10];
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
  public function neueAnmeldung($data, $partner_db)
  {    
    
    if ($partner_db == NULL) {
      $stmt = $this->dbFP->prepare("INSERT INTO ".$this->configFP['tbl-anmeldung']." 
        VALUES(?, ?, ?, ?, ?, NULL, NOW())");
      $stmt->bind_param("sssss", $data['hrz'], $data['name'], $data['matrikel'], 
        $data['studiengang'], $data['semester']);
    } else {
      $stmt = $this->dbFP->prepare("INSERT INTO ".$this->configFP['tbl-anmeldung']." 
        VALUES(?, ?, ?, ?, ?, ?, NOW())");
      $stmt->bind_param("sssss", $data['hrz'], $data['name'], $data['matrikel'], 
        $data['studiengang'], $data['semester'], $partner_db);
    }

    return $stmt->execute();
  }

  /**
   * function to get data about a user
   * @param  [stinrg] $hrz
   * @param  [string] $semester
   * @return [array]           information found
   */
  public function anmeldeDaten($hrz, $semester)
  {

    $stmt = $this->dbFP->prepare("SELECT name, matrikelnummer, abschluss, partner, datum
     FROM ".$this->configFP['tbl-anmeldung']." WHERE hrz=? && semester=?");

    $stmt->bind_param("ss", $hrz, $semester);
    $stmt->execute();
    $stmt->bind_result($name, $matrikel, $abschluss, $partner, $datum);

    if ($stmt->fetch()) {
      return array('name' => $name,
        'matrikel' => $matrikel,
        'abschluss' => $abschluss,
        'partner' => $partner,
        'datum' => $datum
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

}


