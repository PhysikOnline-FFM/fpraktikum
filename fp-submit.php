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
  "studiengang" =>$_POST['studiengang']  
];

foreach ($data as $name => $value) {
  if (!$value) {
    echo '<h1>Bitte rufe diese Seite nur über das '.$Anmeldeformular.' auf.</h1>';
    exit();
  }
}

require '/home/elearning-www/public_html/elearning/ilias-4.3/Customizing/global/include/fpraktikum/database/class.FP-Database.php';

$error = "";

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
// institutes TODO

$fp_database = new FP_Database();


// check user input again
if ($fp_database->checkUser($data['matrikel'], $data['hrz']) != false) {
	$error = "Du bist bereits angemeldet oder wurdest als Partner von jemandem anderen hinzugefügt, bitte gehe wieder zum ".$Anmeldeformular." zurück";
}
if ($partner) {
  if (!$fp_database->checkPartner($partner_hrz, $partner_name)) {
    $error = "Dein angebener Partner ist nicht in der Datenbank, bitte gehe wieder zum ".$Anmeldeformular." zurück";
  }
}
// more checks

if ($error != "") {
  echo '<h1>'.$error.'</h1>';
  exit ();
}

// should be determined automatically
$semester = 'WS16/17';

// it should be save now to access the db
$partner_db = ($partner) ? $partner_hrz : NULL;
$fp_database->neueAnmeldung($data, $partner_db, $semester);

?>
Deine Daten wurden erfolgreich gespeichert!
