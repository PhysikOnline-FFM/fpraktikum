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


//require '/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/database/class.FP-Database.php';
require 'class.FP-Database.php';                // only used for local modifications on Pc (Christian Grossmueller)
require 'class.Database.php';                   // comment it out if used on server

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
	<div class='panel panel-default' style='background-color: white; border: 2px solid #b9b9b9'>
		<div class='panel-heading' style='background-color: #b9b9b9;'>
			Anmeldung zum Fortgeschrittenen Praktikum 
			<button  type='button' class='btn btn-default pull-right' data-target='#demo' data-toggle='collapse' >Hilfe nötig?</button>
		</div>
		<div class='panel-body' id='fprakikum_registration'>
			<small class='text-info bg-info'>Dateien liegen in der VM unter Customizing/global/include/fpraktikum/</small>
			<form name='registration' action='./Customizing/global/include/fpraktikum/submit/fp-submit.php' method='post' onsubmit='return formValidate()' class='form-horizontal'>
				<p class=''>Hi ".$user_firstname.", <br />hier kannst du dich für das Fortgeschrittenen Praktikum im ".$semester." anmelden.</p>
				<div id='demo' class='collapse text-info bg-info'>
					Informationen kommen bald.
				</div>
				<p>Wenn du bei der Anmeldung auf Probleme stößst, zögere nicht uns an <a href='mailto:team@elearning.physik.uni-frankfurt.de'>schreiben</a>.</p>
				
				<input type='hidden' name='hrz' value='".$user_login."'>
				<input type='hidden' name='semester' value='".$semester."'>
				
				<div class='form-group'>
					<label class='col-sm-3 col-md-3 col-lg-2 control-label'>Benutzer</label>
					<div class='col-sm-9 col-md-9 col-lg-10'>
						<span class='form-control-static value'>" .$user_login. "</span>	
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-3 col-md-3 col-lg-2 control-label'>Semester</label>
					<div class='col-sm-9 col-md-9 col-lg-10'>
						<span class='form-control-static value'>" .$semester. "</span>
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-3 col-md-3 col-lg-2 control-label'>Studiengang</label>
					<div class='col-sm-9 col-md-9 col-lg-10 radio' id='chooseInstitute'>
						<label for='ba'><input class='radio_graduation' onchange=showInstitut('BA') type='radio' id='ba' name='graduation' value='BA'>Bachelor</label>
						<label for='ma'><input class='radio_graduation' onchange=showInstitut('MA') type='radio' id='ma' name='graduation' value='MA'>Master</label>
						<div id='instituts'></div>
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-3 col-md-3 col-lg-2 control-label'>Partnerwahl</label>
					<div class='col-sm-9 col-md-9 col-lg-10 checkbox' id='choosePartner'>
						<label for='pa'><input class='checkbox_partner' onchange=choosePartner(this) type='checkbox' id='pa' name='check-partner'>Ich möchte eine Partnerin/einen Partner angeben.</label>
						<div id='partnerForm'></div>
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-3 col-md-3 col-lg-2 control-label'></label>
					<div class='col-sm-9 col-md-9 col-lg-10' id='choosePartner'>
						<input class='submit btn btn-default' type='submit' value='Anmelden'>
					</div>
				</div>
				</form>
				<script type='text/javascript' src='./Customizing/global/include/fpraktikum/js/fp-anmeldung.js'></script>
		</div>
	</div>
    ";
    break;
  case 'registered':
    $data = $fp_database->getAnmeldung($user_login, $semester);
    $html = "
	<div class='panel panel-default' style='background-color: white; border: 2px solid #b9b9b9'>
		<div class='panel-heading' style='background-color: #b9b9b9;'>
			Anmeldung zum Fortgeschrittenen Praktikum 
		</div>
		<div class='panel-body' >
			<form action='/Customizing/global/include/fpraktikum/submit/fp-abmeldung.php' method='post' class='form-horizontal'>
				<input type='hidden' name='hrz' value='".$user_login."'>
				<input type='hidden' name='graduation' value='".$semester."'>
				<div class='alert alert-success' role='alert'><strong>Schau mal!</strong> Du bist angemeldet.</div>
				
				<p>Dies sind die Informationen, die in der Datenbank gespeichert sind:</p>
				<div class='form-group'>
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Benutzername</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" .$user_login. "</span>
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Studiengang</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" .$data['graduation']. "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Partner (Benutzername)</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" .$data['partner']. "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Datum</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" .$data['register_date']. "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>1.&nbsp;Semesterhälfte</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" .$data['institute0']. "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>2.&nbsp;Semesterhälfte</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" .$data['institute1']. "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'></label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>Hier kannst du dich wieder <button onclick=confirmAbmeldung() type='submit' class='btn btn-danger'>Abmelden</button></span>
					</div>
				</div>
			</form>
		</div>
	</div>
    <script type='text/javascript' src='./Customizing/global/include/fpraktikum/js/fp-abmeldung.js'></script>
    ";
    break;
  case 'partner-accept':
    // data about user that included partner
    $data = $fp_database->getAnmeldung($user[1], $semester);

    $html = 
	"<div class='panel panel-default' style='background-color: white; border: 2px solid #b9b9b9'>
		<div class='panel-heading' style='background-color: #b9b9b9;'>
			Anmeldung zum Fortgeschrittenen Praktikum 
		</div>
		<div class='panel-body' >
			<p>Du wurdest von jemandem als Partner angegeben:</p>
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
		</div>
	</div>";
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
