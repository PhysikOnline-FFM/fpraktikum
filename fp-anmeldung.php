<?php
/**
 * Anmeldungsmaske für das FPraktikum des FB Physik. Die Datei wird über das POInclude plugin
 * eingebunden (<PO:Include id="fpraktikum">). Dabei wird nur Variabel $html eingefügt.
 * August 2016
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);


require '/home/elearning-www/public_html/elearning/ilias-4.3/Customizing/global/include/fpraktikum/database/class.FP-Database.php';

global $ilUser;

$user_firstname = $ilUser->getFirstname();
$user_lastname = $ilUser->getLastname();
$user_login = $ilUser->getLogin();
$user_matrikel = $ilUser->getMatriculation();
// for debugging
$user_matrikel = ($user_matrikel == "") ? $user_firstname : $user_matrikel;
// should be determined automatically
$semester = 'WS16/17';

$fp_database = new FP_Database();

switch ($fp_database->checkUser($user_matrikel, $user_login, $semester)) {
  default:
    $html = "
      <small>Dateien liegen in der VM unter Customizing/global/include/fpraktikum/</small>
      <form action='./Customizing/global/include/fpraktikum/fp-submit.php' method='post'>
        <p>Folgende Daten werden automatisch mitgeschickt:</p>

        <input type='hidden' name='hrz' value='".$user_login."'>
        <input type='hidden' name='firstname' value='".$user_firstname."'>
        <input type='hidden' name='lastname' value='".$user_lastname."'>
        <input type='hidden' name='matrikel' value='".$user_matrikel."'>
        <input type='hidden' name='semester' value='".$semester."'>

        <p>Name: ".$user_firstname." ".$user_lastname."</p>
        <p>Matrikelnummer(oder Name, wenn leer): ".$user_matrikel."</p>
        <p>Login: ".$user_login."</p>
        <p>semester: ".$semester."<br>

        <p>Dein Studiengang:
        <input onchange=institutWahl('bachelor') type='radio' name='studiengang' value='Bachelor'>Bachelor
        <input onchange=institutWahl('master') type='radio' name='studiengang' value='Master'>Master
        </p>
        <span id='institut-wahl'></span>
        <br>
        <input onchange=partnerWahl(this) type='checkbox' name='check-partner'>Ich möchte eine Partnerin/einen Partner angeben.</input>
        <br>
        <span id='partnerWahl'></span>


        <input class='submit' type='submit'>
      </form>
      <script type='text/javascript' src='./Customizing/global/include/fpraktikum/js/fp-anmeldung.js'></script>
    ";
    break;
  case "angemeldet":
    $data = $fp_database->anmeldeDaten($user_login, $semester);
    $html = "
      Du bist bereits angemeldet. Dies sind die Informationen, die in der Datenbank gespeichert sind:<br>
      Name: ".$data['name']."<br>
      Matrikelnummer: ".$data['matrikel']."<br>
      Hrz: ".$user_login."<br>
      Studiengang: ".$data['abschluss']."<br>
      Partner-hrz: ".$data['partner']."<br>
      Datum: ".$data['datum']."<br><br>

      1.Semesterhälfte: <br>
      2.Semesterhälfte: <br>

      Hier kannst du dich wieder abmelden:<br>
      <form action='/Customizing/global/include/fpraktikum/fp-abmeldung.php' method='post'>
        <input type='hidden' name='hrz' value='".$user_login."'>
        <input type='hidden' name='matrikel' value='".$user_matrikel."'>
        <input type='hidden' name='semester' value='".$semester."'>

        <button onclick=confirmAbmeldung() type='submit'>Abmelden</button>
      </form>
      <script type='text/javascript' src='./Customizing/global/include/fpraktikum/js/fp-abmeldung.js'></script>
    ";
    break;
  case "partner":
    $html = "Du wurdest von jemandem als Partner angegeben: TODO";
    break;
}


ini_set('display_errors', 0);