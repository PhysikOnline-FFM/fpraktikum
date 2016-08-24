<?php
/**
 * Anmeldungsmaske für das FPraktikum des FB Physik. Die Datei wird über das POInclude plugin
 * eingebunden (<PO:Include id="fpraktikum">). Dabei wird nur Variabel $html eingefügt.
 * August 2016
 */
error_reporting(-1);


require '/home/elearning-www/public_html/elearning/ilias-4.3/Customizing/global/include/fpraktikum/database/class.FP-Database.php';

global $ilUser;

$user_firstname = $ilUser->getFirstname();
$user_lastname = $ilUser->getLastname();
$user_login = $ilUser->getLogin();
$user_matrikel = $ilUser->getMatriculation();
$user_matrikel = ($user_matrikel == "") ? $user_firstname : $user_matrikel;

$fp_database = new FP_Database();

switch ($fp_database->checkUser($user_matrikel, $user_login)) {
  default:
    $html = "
      <small>Dateien liegen in der VM unter Customizing/global/include/fpraktikum/</small>
      <form action='./Customizing/global/include/fpraktikum/fp-submit.php' method='post'>
      <p>Folgende Daten werden automatisch mitgeschickt:</p>

      <input type='hidden' name='hrz' value='".$user_login."'>
      <input type='hidden' name='firstname' value='".$user_firstname."'>
      <input type='hidden' name='lastname' value='".$user_lastname."'>
      <input type='hidden' name='matrikel' value='".$user_matrikel."'>

      <p>Name: ".$user_firstname." ".$user_lastname."</p>
      <p>Matrikelnummer(oder Name, wenn leer): ".$user_matrikel."</p>
      <p>Login: ".$user_login."</p><br>

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
    $html = "
      Du bist bereits angemeldet. Hier kannst du dich wieder abmelden:
      TODO
    ";
    break;
  case "partner":
    $html = "Du wurdest von jemandem als Partner angegeben: TODO";
    break;
}
