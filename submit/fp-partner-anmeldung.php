<?php

/**
 * script checks input from user and writes registration to db
 * TODO: mehr checks siehe unten
 * August 2016 - LG
 */
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$Anmeldeformular = '<a href="http://4-3.ilias.physikelearning.de/ilias.php?ref_id=11819&cmd=frameset&cmdClass=ilrepositorygui&cmdNode=du&baseClass=ilRepositoryGUI">Anmeldeformular</a>';

$data = [
  "hrz" => $_POST['hrz'],
  "firstname" => $_POST['firstname'],
  "lastname" => $_POST['lastname'],
  "matrikel" => $_POST['matrikel'],
  "abschluss" => $_POST['abschluss'],
  "semester" => $_POST['semester'],
  "institut1" => $_POST['institut1'],
  "institut2" => $_POST['institut2']
];

//// checks ////

$error = [];

// are all fields filled?
foreach ($data as $name => $value) {
  if (!$value) {
    echo '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Bitte rufe diese Seite nur über das '.$Anmeldeformular.' auf.</div>';
    exit();
  }
}

require '/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/database/class.FP-Database.php';


$fp_database = new FP_Database();


// check user input again
if ($fp_database->checkUser($data['matrikel'], $data['hrz'], $data['semester'])[0] != 'partner') {
	array_push($error, "Du bist bereits angemeldet oder wurdest nicht als Partner hinzugefügt, bitte gehe wieder zum ".$Anmeldeformular." zurück");
}
if ($fp_database->checkUserInfo($data)) {

}
// more checks, e.g. regex checks for entries and check whether info is in il-db and whether there are free places in requested institute

if (!empty($error)) {
  echo '<div class="alert alert-danger" role="alert"><strong>Fehler:</strong><ul>';
  foreach ($error as $key => $text) {
    echo '<li>'.$error[$key].'</li>';
  }  
  echo '</ul></div>';
  exit ();
}

// it should be save now to access the db
$partner_db = ($partner) ? $partner_hrz : NULL;

if (!$fp_database->setAnmeldung($data, $partner_db)) {
  die('Error beim Speichern deiner Daten');
}

?>
<div class="alert alert-success" role="alert"><strong>Super!</strong> Deine Daten wurden erfolgreich gespeichert!</div>
