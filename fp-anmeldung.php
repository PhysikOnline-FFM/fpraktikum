<?php
/**
 * Anmeldungsmaske für das FPraktikum des FB Physik. Die Datei wird über das POInclude plugin
 * eingebunden (<PO:Include id="fpraktikum">). Dabei wird nur Variabel $html eingefügt.
 * TODO: Partner akzeptiert "Wahl"
 *       Was passiert, wenn ausgewählte Person bereits angemeldet ist/Partner ist?
 *       Keine Bestätigunsseite, sondern alles per AJAX erledigen
 * August 2016 - LG
 */
error_reporting(E_ERROR);
//ini_set('display_errors', 1);


require '/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/database/class.FP-Database.php';

global $ilUser;

$user_firstname = $ilUser->getFirstname();
$user_lastname = $ilUser->getLastname();
$user_login = $ilUser->getLogin();

$semester = 'WS16/17';

$fp_database = new FP_Database();

$user = $fp_database->checkUser($user_login, $semester);

switch ($user[0]) {
  default:
    $html = "	
	<div id=\"fprakikum_registration\">

<small>Dateien liegen in der VM unter Customizing/global/include/fpraktikum/</small>
        <form name='registration' action='./Customizing/global/include/fpraktikum/submit/fp-submit.php' method='post' onsubmit='return formValidate()'>
          <p>Hi ".$user_firstname.", hier kannst du dich für das Fortgeschrittenen Praktikum im ".$semester." anmelden.</p>
         <p> <button  type='button' class='btn btn-default' data-target='#demo' data-toggle='collapse' >Hilfe?/Help?</button>

	<div id='demo' class='collapse'>
		Informationen kommen bald.
	</div>       
	  <p>Wenn du bei der Anmeldung auf Probleme stößst, zögere nicht uns zu <a href='mailto:elearning@itp.uni-frankfurt.de'>schreiben</a>.
			
		
          <div id='chooseInstitute'>
            <input type='hidden' name='hrz' value='".$user_login."'>
            <input type='hidden' name='semester' value='".$semester."'>

            <ul>
              <li><span class=\"\">Dein Login:</span><span class=\"value\">" . $user_login . "</span></li>
              <li><span class=\"\">Semester:</span><span class=\"value\"><span class=\"value\">" . $semester . "</span></li>
            </ul>

            <h6>Dein Studiengang:</h6>
            <input class=\"radio_graduation\" onchange=showInstitut('BA') type='radio' id=\"ba\" name='graduation' value='BA'><label for='ba'>Bachelor</label>
            <input class=\"radio_graduation\" onchange=showInstitut('MA') type='radio' id=\"ma\" name='graduation' value='MA'><label for='ma'>Master</label>
            <span id='instituts'></span>
          </div>

          <div id='choosePartner'>
            <input class=\"checkbox_partner\" onchange=choosePartner(this) type='checkbox' id='pa' name='check-partner'><label for='pa'>Ich möchte eine Partnerin/einen Partner angeben.</label>
            <br>
            <span id='partnerForm'></span>
            <input class='submit btn btn-default' type='submit' value='Anmelden'>
          </div>
        </form>
        <script type='text/javascript' src='./Customizing/global/include/fpraktikum/js/fp-anmeldung.js'></script>
            
	</div>
    ";
    break;
  case 'registered':
    $data = $fp_database->getAnmeldung($user_login, $semester);
    $html = "
      Du bist bereits angemeldet. Dies sind die Informationen, die in der Datenbank gespeichert sind:<br>
      Hrz: ".$user_login."<br>
      Studiengang: ".$data['graduation']."<br>
      Partner-hrz: ".$data['partner']."<br>
      Datum: ".$data['register_date']."<br><br>

      1.Semesterhälfte: ".$data['institute0']."<br>
      2.Semesterhälfte: ".$data['institute1']."<br>

      Hier kannst du dich wieder abmelden:<br>
      <form action='/Customizing/global/include/fpraktikum/submit/fp-abmeldung.php' method='post'>
        <input type='hidden' name='hrz' value='".$user_login."'>
        <input type='hidden' name='graduation' value='".$semester."'>

        <button onclick=confirmAbmeldung() type='submit'>Abmelden</button>
      </form>
      <script type='text/javascript' src='./Customizing/global/include/fpraktikum/js/fp-abmeldung.js'></script>
    ";
    break;
  case 'partner-accept':
    // data about user that included partner
    $data = $fp_database->getAnmeldung($user[1], $semester);

    $html = "Du wurdest von jemandem als Partner angegeben:<br>
    <form action='fehltnoch' method='post'>
      <p>Die Daten deines Partners sind:</p>
      <p>HRZ: ".$data['hrz']."</p>
      <p>Abschluss: ".$data['graduation']."</p>
      <p>Institut1: ".$data['institut1']."</p>
      <p>Institut2: ".$data['institut2']."</p>      

      <p>Hier kannst du dich eintragen:</p>
      <p>Deine Daten:</p>

      <input type='hidden' name='hrz' value='".$user_login."'>
      <input type='hidden' name='semester' value='".$semester."'>
      <input type='hidden' name='institut1' value='".$data['institut1']."'>
      <input type='hidden' name='institut2' value='".$data['institut2']."'>

      <p>Login: ".$user_login."</p>
      <p>semester: ".$semester."<br>

      <input type='submit' name='partner-bestätigen' value='Anmelden'>
    </form>
    ";
    break;
  case 'partner-accepted':
    //show message that user is partner of x
    break;
}

if (new DateTime() < new DateTime("2016-09-18 00:00:00")) {
  // $html = "<b>Die Anmeldung ist noch nicht freigeschaltet!</b>";
} else if (new DateTime() > new DateTime("2016-10-02 00:00:00")) {
  // $html = "<b>Die Anmeldung ist beendet!</b>";
}


ini_set('display_errors', 0);
