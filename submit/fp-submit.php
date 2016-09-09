<?php

/**
 * script checks input from user and writes registration to db
 * 
 * August 2016 - LG
 */
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$Anmeldeformular = '<a href="http://4-3.ilias.physikelearning.de/ilias.php?ref_id=11819&cmd=frameset&cmdClass=ilrepositorygui&cmdNode=du&baseClass=ilRepositoryGUI">Anmeldeformular</a>';

$data = [
  "hrz" => $_POST['hrz'],
  "graduation" => $_POST['graduation'],
  "semester" => $_POST['semester'],
  "institute1" => $_POST['institute1'],
  "institute2" => $_POST['institute2']
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
}

//// checks ////

$error = [];

// are all fields filled?
foreach ($data as $name => $value) {
  if (!$value) {
    echo '<h1>Bitte rufe diese Seite nur über das '.$Anmeldeformular.' auf. Nicht alle Felder wurden ausgefüllt.</h1>';
    exit();
  }
}

require '/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/database/class.FP-Database.php';


$fp_database = new FP_Database();


// check if user is not already registered
if ($fp_database->checkUser($data['hrz'], $data['semester'])[0] != false) {
	array_push($error, "Du bist bereits angemeldet oder wurdest als Partner von jemandem anderen hinzugefügt, bitte gehe wieder zum ".$Anmeldeformular." zurück");
}
// check if partner is valid
if ($partner) {
  if ($fp_database->checkPartner($partner_hrz, $partner_name, $data['semester'])[0] != false) {
    array_push($error, "Dein angebener Partner ist nicht valid, bitte gehe wieder zum ".$Anmeldeformular." zurück");
  }
}
// check if hez-account is valid
if (!$fp_database->checkUserInfo($data['hrz'])) {
  array_push($error, "Wir konnten dich nicht mit ".$data['hrz']." in der Datenbank finden, bitte gehe wieder zum ".$Anmeldeformular." zurück");
}

// check whether there are enough slots left
$remaining_places = $fp_database->freePlaces($data['semester']);
$slots_needed = ($partner) ? 2 : 1;

if ($remaining_places[$data['graduation']][$data['institute1']][0] < $slots_needed) {
  array_push($error, "Leider sind in deinem gewünschtem Institut ".$data['institute1']." nicht genügend Plätze frei, bitte gehe wieder zum ".$Anmeldeformular." zurück");
}
if ($remaining_places[$data['graduation']][$data['institute2']][1] < $slots_needed) {
  array_push($error, "Leider sind in deinem gewünschtem Institut ".$data['institute2']." nicht genügend Plätze frei, bitte gehe wieder zum ".$Anmeldeformular." zurück");
}

// check whether both instituts are different
if ($data['institute1'] == $data['institute2']) {
  array_push($error, "In beiden Semesterhälften müssen unterschiedliche Institute gewählt werden, bitte gehe wieder zum ".$Anmeldeformular." zurück");
}

if ($error != []) {
  foreach ($error as $key => $text) {
    echo '<h1>'.$error[$key].'</h1><br>';
  }  
  exit ();
}

// it should be save now to access the db
$partner_db = ($partner) ? $partner_hrz : NULL;

if (!$fp_database->setAnmeldung($data, $partner_db)) {
  die('Es ist ein Fehler beim Speichern deiner Daten aufgetreten.');
}

header('Location: http://5-1.ilias.physikelearning.de/goto_FB13-PhysikOnline_cat_11819.html');
?>

<!--Deine Daten wurden erfolgreich gespeichert!-->
