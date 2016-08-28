<?php

/**
 * script that is called after user clicked submit
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$Anmeldeformular = '<a href="http://4-3.ilias.physikelearning.de/ilias.php?ref_id=11819&cmd=frameset&cmdClass=ilrepositorygui&cmdNode=du&baseClass=ilRepositoryGUI">Anmeldeformular</a>';

$data = [
  "hrz" => $_POST['hrz'],
  "name" => $_POST['firstname']." ".$_POST['lastname'],
  "matrikel" => $_POST['matrikel'],
  "abschluss" => $_POST['abschluss'],
  "semester" => $_POST['semester'],
  "institut1" => $_POST['institut1'],
  "institut2" => $_POST['institut2']
];

// get partner
$partner = false;
if ($_POST['check-partner']) {
  $check_partner = $_POST['check-partner'];

  if ($check_partner == "on") {
    $partner = true;
    $partner_hrz = $_POST['partner-hrz'];
    $partner_name = $_POST['partner-name'];
  }
  if ($check_partner != "on" && ($_POST['partner-hrz'] != "" && $_POST['partner-name'] != "")) {
    $error = "Etwas ist bei der Partnerwahl falsch, bitte gehe wieder zum ".$Anmeldeformular." zurück";
  }
}


//// checks ////

$error = [];

// are all fields filled?
foreach ($data as $name => $value) {
  if (!$value) {
    echo '<h1>Bitte rufe diese Seite nur über das '.$Anmeldeformular.' auf.</h1>';
    exit();
  }
}

require '/home/elearning-www/public_html/elearning/ilias-4.3/Customizing/global/include/fpraktikum/database/class.FP-Database.php';



$fp_database = new FP_Database();


// check user input again
if ($fp_database->checkUser($data['matrikel'], $data['hrz'], $data['semester']) != false) {
	array_push($error, "Du bist bereits angemeldet oder wurdest als Partner von jemandem anderen hinzugefügt, bitte gehe wieder zum ".$Anmeldeformular." zurück");
}
if ($partner) {
  if (!$fp_database->checkPartner($partner_hrz, $partner_name)) {
    array_push($error, "Dein angebener Partner ist nicht in der Datenbank, bitte gehe wieder zum ".$Anmeldeformular." zurück");
  }
}
// more checks, e.g. regex checks for entries and check whether info is in il-db and whether there are free places in requested institute

if ($error != []) {
  foreach ($error as $key => $text) {
    echo '<h1>'.$error[$key].'</h1><br>';
  }  
  exit ();
}

// it should be save now to access the db
$partner_db = ($partner) ? $partner_hrz : NULL;

if (!$fp_database->setAnmeldung($data, $partner_db)) {
  die('Error beim Speichern deiner Daten');
}

?>
<br>Deine Daten wurden erfolgreich gespeichert!<br>
