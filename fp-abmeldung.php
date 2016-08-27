<?php

/**
 * script that is called after user wants to delet registration
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$Anmeldeformular = '<a href="http://4-3.ilias.physikelearning.de/ilias.php?ref_id=11819&cmd=frameset&cmdClass=ilrepositorygui&cmdNode=du&baseClass=ilRepositoryGUI">Anmeldeformular</a>';

$data = [
  "hrz" => $_POST['hrz'],
  "matrikel" => $_POST['matrikel'],
  "semester" => $_POST['semester']
];

foreach ($data as $name => $value) {
  if (!$value) {
    echo '<h1>Bitte rufe diese Seite nur über das '.$Anmeldeformular.' auf.</h1>';
    exit();
  }
}

require '/home/elearning-www/public_html/elearning/ilias-4.3/Customizing/global/include/fpraktikum/database/class.FP-Database.php';

$error = "";

// something needs to happen if the user has a partner

$fp_database = new FP_Database();


// check user input again
if ($fp_database->checkUser($data['matrikel'], $data['hrz'], $data['semester']) == false) {
  $error = "Du bist nicht angemeldet und kannst dich nicht abmelden, bitte gehe wieder zum ".$Anmeldeformular." zurück";
}

// more checks, e.g. regex checks for entries

if ($error != "") {
  echo '<h1>'.$error.'</h1>';
  exit ();
}

// it should be save now to access the db

if ($fp_database->rmAnmeldung($data)) {
  echo "<br>Deine Daten wurden erfolgreich ggelöscht!<br>";
} else {
  echo "<br>Deine Daten konnten nicht gelöscht werden!<br>";
}

?>

