<?php
/*
 * TODO: 
 * Eleminate Error-Messages, error_reporting aswell
 * Include Try/Catch
 * check all error-states
 * check logical issues
 */ 
print_r('Its me, ...');
echo '<h1>Test</h1>';
 ini_set('E_ALL',1);
 echo '<h1>Test</h1>';
 ini_set('display_errors', 1);
 echo '<h1>Test</h1>';
 require '/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/database/class.FP-Database.php';
 echo '<h1>Test</h1>';
/**
 * script checks input from user and writes registration to db
 * 
 * August 2016 - LG
 */
$Anmeldeformular = '<a href="http://5-1.ilias.physikelearning.de/ilias.php?ref_id=11819&cmd=frameset&cmdClass=ilrepositorygui&cmdNode=du&baseClass=ilRepositoryGUI">Anmeldeformular</a>';
$data = [														// saves form-input data of student
  "hrz" => $_POST['hrz'],
  "graduation" => $_POST['graduation'],
  "semester" => $_POST['semester'],
  "institute1" => $_POST['institute1'],
  "institute2" => $_POST['institute2']
];
print_r($data);
$wantsPartner = false;
$partner_hrz = $_POST['partner-hrz'];
$partner_name = $_POST['partner-name'];
$error = [];													// stores errors of formular-input
$fp_database = new FP_Database();								// initializes db-connection
$remaining_places = 0;											// stores remaining slots for students
$slots_needed = 1;

if($_POST['check-partner'] == "on"){ print_r('yammi'); $wantsPartner = true; } else { $wantsPartner = false; }	// stores boolean of checkbox: true if student wants partner
print_r("LOL");
if ($wantsPartner) {
	$slots_needed = 2;													// if student set checkbox, student wants partner
	$remaining_places = $fp_database->freePlaces($data['semester']);	// stores remaining places
	if($data['hrz'] == $_POST['partner-hrz'])							// if partner-hrz is same as logged in user, forward to origin page
	{
		// TODO: die(), emit error to user to tell him it is not possible to partner himself.
		// header('Location: http://5-1.ilias.physikelearning.de/goto_FB13-PhysikOnline_cat_11819.html');
		echo '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Es ist nicht möglich sich selbst als Partner einzutragen.</div>';
	}	
}

// are all fields filled?
foreach ($data as $name => $value) {
  if (!$value) {
    echo '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Bitte rufe diese Seite nur über das '.$Anmeldeformular.' auf.</div>';
    exit();
  }
}

// check if user is not already registered
if ($fp_database->checkUser($data['hrz'], $data['semester'])[0] != false) {
	array_push($error, "Du bist bereits angemeldet oder wurdest als Partner von jemandem anderen hinzugefügt, bitte gehe wieder zum ".$Anmeldeformular." zurück");
}
print_r('here');
// check if partner is valid
if ($partner) {
  if ($fp_database->checkPartner($partner_hrz, $partner_name, $data['semester'])[0] != false) {
    array_push($error, "Dein angebener Partner konnte nicht gefunden werden, bitte gehe wieder zum ".$Anmeldeformular." zurück");
  }
}

// check if hrz-account is valid
if (!$fp_database->checkUserInfo($data['hrz'])) {
  array_push($error, "Wir konnten dich nicht mit ".$data['hrz']." in der Datenbank finden, bitte gehe wieder zum ".$Anmeldeformular." zurück");
}
print_r('hmmm');
if ($remaining_places[$data['graduation']][$data['institute1']][0] < $slots_needed) {
  array_push($error, "Leider sind in deinem gewünschtem Institut ".$data['institute1']." nicht genügend Plätze frei, bitte gehe wieder zum ".$Anmeldeformular." zurück");
}

if ($remaining_places[$data['graduation']][$data['institute2']][1] < $slots_needed) {
  array_push($error, "Leider sind in deinem gewünschtem Institut ".$data['institute2']." nicht genügend Plätze frei, bitte gehe wieder zum ".$Anmeldeformular." zurück");
}

// check whether both instituts are differente
if ($data['institute1'] == $data['institute2']) {
  array_push($error, "In beiden Semesterhälften müssen unterschiedliche Institute gewählt werden, bitte gehe wieder zum ".$Anmeldeformular." zurück");
}

// if any error appears, a message should be sent to the user.
if ($error != []) {
  echo '<div class="alert alert-danger" role="alert"><strong>Fehler:</strong><ul>';
  foreach ($error as $key => $text) {
    echo '<li>'.$error[$key].'</li>';
  }  
  echo '</ul></div>';
  exit ();
} else {
	// it should be save now to access the db
	$partner_db = ($wantsPartner) ? $partner_hrz : NULL;
	
	if (!$fp_database->setAnmeldung($data, $partner_db)) {
	  die('Es ist ein Fehler beim Speichern deiner Daten aufgetreten.');
	}
	//header('Location: http://5-1.ilias.physikelearning.de/goto_FB13-PhysikOnline_cat_11819.html');
}
print_r('Mario!');
?>

<!--Deine Daten wurden erfolgreich gespeichert!-->
