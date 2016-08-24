<?php

error_reporting(-1);

require_once('/home/elearning-www/public_html/elearning/ilias-4.3/Customizing/global/include/fpraktikum/database/class.Database.php');

/**
 * class containing all functions necessary to communicate with the database
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
   */
  public function checkPartner($hrz, $name) {
    if (mysqli_num_rows($this->dbIL->makeQuery("SELECT usr_id FROM ".$this->configIL['tbl-name']." WHERE login='".$hrz."' && lastname='".$name."'")) != 0) {
       return true;
    } else {
      return false;
    }
  }

  /**
   * function to check whether the logged-in user is already registered/a partner or not
   * TODO
   */
  public function checkUser($user_matrikel, $user_login) {
    if (mysqli_num_rows($this->dbFP->makeQuery("SELECT hrz FROM ".$this->configFP['tbl-anmeldung']." WHERE matrikelnummer='".$user_matrikel."'")) == 1) {
        return "angemeldet";
      } else if (mysqli_num_rows($this->dbFP->makeQuery("SELECT hrz FROM ".$this->configFP['tbl-anmeldung']." WHERE partner='".$user_login."'")) == 1) {
        return "partner";
      } else {
        return false;
      }
  }

  public function neueAnmeldung($data, $partner_db, $semester)
  {
    $db_data = implode("', '", array_values($data));
    if ($partner_db == NULL) {
      echo "INSERT INTO ".$this->configFP['tbl-anmeldung']." 
        VALUES('".$db_data."', '".$semester."', NULL, NOW())<br>";
      $this->dbFP->makeQuery("INSERT INTO ".$this->configFP['tbl-anmeldung']." 
        VALUES('".$db_data."', '".$semester."', NULL, NOW())");
    } else {
      echo "INSERT INTO ".$this->configFP['tbl-anmeldung']." 
        VALUES('".$db_data."', '".$semester."', '".$partner_db."', NOW())<br>";
      $this->dbFP->makeQuery("INSERT INTO ".$this->configFP['tbl-anmeldung']." 
        VALUES('".$db_data."', '".$semester."', '".$partner_db."', NOW())");
    }
  }

}


