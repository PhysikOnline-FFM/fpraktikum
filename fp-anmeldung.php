<?php

/*
 * We call the person who registered him/herself as the 'registrar'
 * and the person who got registered by him/her as the 'partner'.
 */

require 'database/class.FP-Database.php';
require 'include/class.helper.php';


//error_reporting( E_ALL );
//ini_set( 'display_errors', 1 );

global $ilUser;

$user_firstname = $ilUser->getFirstname();      // $ilUser is a ILLias system variable ,the function fetches firstname
$user_lastname = $ilUser->getLastname();        // fetches Lastname
$user_login = $ilUser->getLogin();              // fetches logindata


$semester = Helper::get_semester();

$fp_database = new FP_Database();

$user = $fp_database->checkUser( $user_login, $semester );    // Variable containing all needed infos about the current user
// weather he is already registered or not.

/**
 * Switch to evaluate the different types of user statuses.
 * Statuses :           default , registered, partner accept, partner accepted
 * default :            shows the standard html mask of the site and is equivalent to 'not registered' saved as the variable $html
 * registered :         shows a html mask containing all infos about the registration , degree , etc. saved as $html
 * partner-open:        shows a html mask containing the info that someone else has added the user as partner
 *                      the user gets the chance to see who added him to which group and too accept his partner.
 * partner-accepted:    the partner has accepted, sees all information and can remove themself
 */
switch ( $user['type'] )
{
    default:
    case 'new':
        $html = "
	<div class='panel panel-default' style='background-color: white; border: 2px solid #b9b9b9'>
		<div class='panel-heading' style='background-color: #b9b9b9;'>
			Anmeldung zum Fortgeschrittenen Praktikum 
			<button  type='button' class='btn btn-default pull-right' data-target='#demo' data-toggle='collapse' >Hilfe nötig?</button>
		</div>
		<div class='panel-body' id='fprakikum_registration' >
			<small class='text-info bg-info'>Dateien liegen in der VM unter Customizing/global/include/fpraktikum/</small>
			<form name='registration' action='./Customizing/global/include/fpraktikum/submit/fp-signin.php' method='post' onsubmit='return formValidate()' class='form-horizontal'>
				<p class=''>Hi " . $user_firstname . ", <br />hier kannst du dich für das Fortgeschrittenen Praktikum im " . $semester . " anmelden.</p>
				<div id='demo' class='collapse text-info bg-info'>
					Informationen kommen bald.
				</div>
				<p>Wenn du bei der Anmeldung auf Probleme stößst, zögere nicht uns an <a href='mailto:team@elearning.physik.uni-frankfurt.de'>schreiben</a>.</p>
				
				<input type='hidden' name='registrant' value='" . $user_login . "'>
				<input type='hidden' name='semester' value='" . $semester . "'>
				
				<div class='form-group'>
					<label class='col-sm-3 col-md-3 col-lg-2 control-label'>Benutzer</label>
					<div class='col-sm-9 col-md-9 col-lg-10'>
						<span class='form-control-static value'>" . $user_login . "</span>	
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-3 col-md-3 col-lg-2 control-label'>Semester</label>
					<div class='col-sm-9 col-md-9 col-lg-10'>
						<span class='form-control-static value'>" . $semester . "</span>
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-3 col-md-3 col-lg-2 control-label' >Studiengang</label>
					<div class='col-sm-9 col-md-9 col-lg-10 radio' id='chooseInstitute'>
                        <label for='ba'><input class='radio_graduation' onchange=showInstitut('BA') type='radio' id='ba' name='graduation' value='BA'>Bachelor</label>
                        <label for='ma'><input class='radio_graduation' onchange=showInstitut('MA') type='radio' id='ma' name='graduation' value='MA'>Master</label>
                        <label for='mait'><input class='radio_graduation' onchange=showInstitut('MAIT') type='radio' id='mait' name='graduation' value='MAIT'>MasterIT</label>
                            <a href='#' id='info'><i class='glyphicon glyphicon-info-sign' ></i></a> 
                        <label for='la'><input class='radio_graduation' onchange=showInstitut('LA') type='radio' id='la' name='graduation' value='LA'>Lehramt</label>
                        <div id='instituts'></div>                            
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-3 col-md-3 col-lg-2 control-label'>Partnerwahl</label>
					<div class='col-sm-9 col-md-9 col-lg-10 checkbox' id='choosePartner'>
						<label for='pa'><input class='checkbox_partner' onchange=choosePartner(this) type='checkbox' id='pa' name='check-partner' >Ich möchte eine Partnerin/einen Partner angeben.</label>
						<div id='partnerForm'></div>
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-3 col-md-3 col-lg-2 control-label'>Bemerkungen</label>
				    <div class='col-sm-9 col-md-9 col-lg-10 checkbox' id='notes'>
				    <textarea name='notes' rows='5' cols='40' placeholder='Bemerkungen...'></textarea>
				    </div>
				</div>
				<div id='fp_errors'></div>
				<div class='form-group'>
					<label class='col-sm-3 col-md-3 col-lg-2 control-label'></label>
					<div class='col-sm-9 col-md-9 col-lg-10' id='choosePartner'>
						<input class='submit btn btn-default'  type='submit' name='submit_register ' id='submitRegister' value='Anmelden'>
					</div>
				</div>
				</form>
				<script type='text/javascript' src='./Customizing/global/include/fpraktikum/js/fp-anmeldung.js'></script>
		</div>
	</div>
    ";
        break;

    case 'registered':
        $data = $fp_database->getRegistration( $user_login, $semester );
        $partner_type = "";
        if ( $data['partner'] )
        {
            $partner_type = ($fp_database->checkUser( $data['partner'], $semester )['type'] == 'partner-open')
                ? "(offen)" : "(bestätigt)";
        }

        $html = "
	<div class='panel panel-default' style='background-color: white; border: 2px solid #b9b9b9'>
		<div class='panel-heading' style='background-color: #b9b9b9;'>
			Anmeldung zum Fortgeschrittenen Praktikum 
		</div>
		<div class='panel-body' >
			<form action='/Customizing/global/include/fpraktikum/submit/fp-signout.php' method='post' class='form-horizontal'>
				<input type='hidden' name='registrant' value='" . $user_login . "'>
				<input type='hidden' name='semester' value='" . $semester . "'>
				<input type='hidden' name='token' value='" . $data['token'] . "' > 
				<div class='alert alert-success' role='alert'><strong>Schau mal!</strong> Du bist angemeldet.</div>
				
				<p>Dies sind die Informationen, die in der Datenbank gespeichert sind:</p>
				<div class='form-group'>
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Benutzername</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $user_login . "</span>
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Studiengang</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $data['graduation'] . "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Partner (Benutzername)</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $data['partner']
                        . " $partner_type" . "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Datum</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $data['register_date'] . "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>1.&nbsp;Semesterhälfte</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $data['institute1'] . "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>2.&nbsp;Semesterhälfte</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $data['institute2'] . "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Bemerkungen</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $data['notes'] . "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'></label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>Hier kannst du dich wieder <button onclick=confirmAbmeldung() type='submit' name='submit_signout' class='btn btn-danger'>Abmelden</button></span>
					</div>
				</div>
			</form>
		</div>
	</div>
    <script type='text/javascript' src='/home/elearning-www/public_html/elearning/ilias-5.1//Customizing/global/include/fpraktikum/js/fp-abmeldung.js'></script>
    ";
        break;
    case 'partner-open':
        // data about user that included partner
        $data = $fp_database->getRegistration( $user['registrant'], $semester );

        $html =
            "<div class='panel panel-default' style='background-color: white; border: 2px solid #b9b9b9'>
		<div class='panel-heading' style='background-color: #b9b9b9;'>
			Anmeldung zum Fortgeschrittenen Praktikum 
		</div>
		<div class='panel-body' >
			<p>Du wurdest von jemandem als Partner angegeben:</p>
			<form action='/Customizing/global/include/fpraktikum/submit/fp-signin.php' method='post'>
			  <p>Die Daten deines Partners sind:</p>
			  <p>HRZ: " . $user['registrant'] . "</p>
			  <p>Abschluss: " . $data['graduation'] . "</p>
			  <p>Institut 1: " . $data['institute1'] . "</p>
			  <p>Institut 2: " . $data['institute2'] . "</p>      
              <p>Bemerkungen:" .$data['notes']."      </p>
			  <p>Deine Daten:</p>

			  <input type='hidden' name='partner' value='" . $user_login . "'>
			  <input type='hidden' name='semester' value='" . $semester . "'>
			  <input type='hidden' name='token' value='" . $data['token'] . "' > 
                
			  <p>Login: " . $user_login . "</p>
			  <p>semester: " . $semester . "<br>

			  <p>Hier kannst du dich eintragen:</p>
			  <input type='submit' name='submit_partner-accepts' value='Anmelden'>
			  </form>
			    <form action='/Customizing/global/include/fpraktikum/submit/fp-signout.php' method='post'>
			      <input type='hidden' name='partner' value='" . $user_login . "'>
                  <input type='hidden' name='semester' value='" . $semester . "'>
                  <input type='hidden' name='token' value='" . $data['token'] . "' > 
			    <input type='submit' name='submit_partner-denies' value='Abmelden'>
			  </form>
		</div>
	</div>";
        break;
    case 'partner-accepted':
        // data about user that included partner
        $data_registrant = $fp_database->getRegistration( $user['registrant'], $semester );

        $html="<div class='panel panel-default' style='background-color: white; border: 2px solid #b9b9b9'>
		<div class='panel-heading' style='background-color: #b9b9b9;'>
			Anmeldung zum Fortgeschrittenen Praktikum 
		</div>
		<div class='panel-body' >
			<form action='/Customizing/global/include/fpraktikum/submit/fp-signout.php' method='post' class='form-horizontal'>
				<input type='hidden' name='partner' value='" . $user_login . "'>
				<input type='hidden' name='semester' value='" . $semester . "'>
				<input type='hidden' name='token' value='" . $data_registrant['token'] . "' > 
				<div class='alert alert-success' role='alert'><strong>Schau mal!</strong> Du bist als Partner angemeldet.</div>
				
				<p>Dies sind die Informationen, die in der Datenbank gespeichert sind:</p>
				<div class='form-group'>
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Benutzername</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $user_login . "</span>
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Studiengang</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $data_registrant['graduation'] . "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Partner (Benutzername)</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $user['registrant'] . "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Datum</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $data_registrant['register_date'] . "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>1.&nbsp;Semesterhälfte</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $data_registrant['institute1'] . "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>2.&nbsp;Semesterhälfte</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $data_registrant['institute2'] . "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Bemerkungen</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $data['notes'] . "</span>
					</div>
				</div>
				<div class='form-group'>	
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'></label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>Hier kannst du dich wieder <button onclick=confirmAbmeldung() type='submit' class='btn btn-danger' name='submit_partner-denies' value='Abmelden'>Abmelden</button></span>
					</div>
				</div>
				
			</form>
		</div>
	</div>
    <script type='text/javascript' src='/home/elearning-www/public_html/elearning/ilias-5.1//Customizing/global/include/fpraktikum/js/fp-abmeldung.js'></script>
    ";
        break;
}
/**
 * statement to determined weather the Registration is available or not.
 * short check if the "todays" date == the wanted date.
 * TODO: make it possible to change the date time which is checked by the statement via admin page.
 */
if ( new DateTime() < new DateTime( "2016-09-18 00:00:00" ) )
{
    // $html = "<b>Die Anmeldung ist noch nicht freigeschaltet!</b>";
}
else if ( new DateTime() > new DateTime( "2016-10-02 00:00:00" ) )
{
    // $html = "<b>Die Anmeldung ist beendet!</b>";
}

// >>>>>>> b465e2910b30d194080600158f52644e40744274
