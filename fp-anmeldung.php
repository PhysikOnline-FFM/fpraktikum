<?php

/*
 * We call the person who registered him/herself as the 'registrar'
 * and the person who got registered by him/her as the 'partner'.
 */

require_once 'database/class.FP-Database.php';
require_once 'include/class.helper.php';
require_once 'include/class.exporter.php';

//error_reporting( E_ALL );
//ini_set( 'display_errors', 1 );

global $ilUser;

$user_firstname = $ilUser->getFirstname();      // $ilUser is a ILLias system variable ,the function fetches firstname
$user_lastname = $ilUser->getLastname();        // fetches Lastname
$user_login = $ilUser->getLogin();              // fetches logindata


$semester = Helper::get_semester();

$fp_database = new FP_Database();

$user = $fp_database->checkUser( $user_login, $semester );    // Variable containing all needed infos about the current user
$dates = $fp_database->getDates( $semester );
$evaluated_dates = Helper::validate_dates( $dates['startdate'], $dates['enddate'] ); //contains boolean , wether todays date is within registration time
$admins = array(
    'chgad_admin'
    , 'LarsG_admin'
);

$html = "
        <div class='panel panel-default' style='background-color: white; border: 2px solid #b9b9b9'>
		<div class='panel-heading' style='background-color: #b9b9b9;'>
			Anmeldung zum Fortgeschrittenen Praktikum
		</div>";

if ( in_array( $user_login, $admins ) )
{
    $html .= "<div class=\"embed-responsive embed-responsive-16by9\">
                    <iframe src='Customizing/global/include/fpraktikum/admin/fp-admin.php'></iframe>
                  </div>";
    $evaluated_dates = true ;
}

if ( $evaluated_dates )
{
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
            $html .= "
			<!--<button  type='button' class='btn btn-default pull-right' data-target='#demo' data-toggle='collapse' >Hilfe nötig?</button>-->
		<div class='panel-body' id='fprakikum_registration' >
			<!--<div class='alert alert-danger'>Die Anmeldung ist noch nicht freigeschaltet!</div>-->
			<form name='registration' action='./Customizing/global/include/fpraktikum/submit/fp-signin.php' method='post' onsubmit='return formValidate()' class='form-horizontal'>
				<p class=''>Hi " . $user_firstname . ", <br />hier kannst du dich für das Fortgeschrittenen Praktikum im " . $semester . " anmelden.</p>
				<div id='demo' class='collapse text-info bg-info'></div>
				<p>Du findest weitere Informationen im <a href='https://physikonline.atlassian.net/wiki/pages/viewpage.action?pageId=496165'>FAQ</a>. 
				Wenn du bei der Anmeldung auf Probleme stößst, zögere nicht uns zu <a href='mailto:team@elearning.physik.uni-frankfurt.de'>schreiben</a>.</p>

				<input type='hidden' name='registrant' value='" . $user_login . "' id='user_login'>
				<input type='hidden' name='semester' value='" . $semester . "'>
                
                <div class='row'>
                    
                    <div class='col-sm-3 col-md-3 col-lg-2 control-label' >
                        <p>Benutzer</p>
                    </div>   
                    
                    <div class='col-sm-9 col-md-9 col-lg-10 control-label-left' >
                        <p><span class='form-control-static value'>" . $user_login . "</span></p>
                    </div>
                    
                </div>    
                <div class='row'>
                    
                    <div class='col-sm-3 col-md-3 col-lg-2 control-label' >
                        <p>Semester</p>
                    </div>
                    
                    <div class='col-sm-9 col-md-9 col-lg-10 control-label-left' >
                        <p><span class='form-control-static value'>" . $semester . "</span></p>
                    </div>
                    
                </div>
                <div class='row'>
                
                    <div class='col-sm-3 col-md-3 col-lg-2 control-label' >
                        <p>Abschluss</p>
                    </div>
                    
                    <div class='col-sm-9 col-md-9 col-lg-10 radio' id='chooseInstitute'>
                        <p>
                        <label for='ba'><input class='radio_graduation' onchange=showInstitut('BA') type='radio' id='ba' name='graduation' value='BA'>Bachelor</label>
                        <label for='ma'><input class='radio_graduation' onchange=showInstitut('MA') type='radio' id='ma' name='graduation' value='MA'>Master</label>
                        <label for='la'><input class='radio_graduation' onchange=showInstitut('LA') type='radio' id='la' name='graduation' value='LA'>Lehramt</label>
                        </p>
                        <p><div id='instituts'></div></p>
                    </div>
                    
                </div>
                <div class='row'>
                
                    <div class='col-sm-3 col-md-3 col-lg-2 control-label' >
                        <p>Partnerwahl</p>
                    </div>
                    
                    <div class='col-sm-9 col-md-9 col-lg-10 checkbox' id='choosePartner'>
                        <p></p><label for='pa'><input class='checkbox_partner' onchange=choosePartner(this) type='checkbox' id='pa' name='check-partner' >Ich möchte zusätzlich eine weitere Person als Partner/Partnerin anmelden.</label></p>
                        <p><div id='partnerForm'></div></p>
                    </div>
                    
                </div>
                <div class='row'>
                
                    <div class='col-sm-3 col-md-3 col-lg-2 control-label' >
                        <p>Bemerkungen</p>
                    </div>
                    
                    <div class='col-sm-9 col-md-9 col-lg-10 control-label-left' id='notes' >
                        <p><textarea name='notes' rows='5' cols='40' placeholder='Bemerkungen...'></textarea></p>  
                    </div> 
                
                </div>
                <div id='fp_errors'></div>
                <div class='row'>
                
                    <div class='col-sm-3 col-md-3 col-lg-2 control-label'>
                        <p></p>
                    </div>
                    
                    <div class='col-sm-9 col-md-9 col-lg-10 control-label-left' id='choosePartner'>
                        <p><input class='submit btn btn-default'  type='submit' name='submit_register' id='submitRegister' value='Anmelden'></p>
                    </div>
                    
                </div>
				</form>
				<script type='text/javascript' src='./Customizing/global/include/fpraktikum/js/fp-anmeldung.js'></script>
		</div>
    ";
            break;

        case 'registered':
            $data = $fp_database->getRegistration( $user_login, $semester );
            $partner_type = "";
            if ( $data['partner'] )
            {
                $data_user = $fp_database->checkUser( $data['partner'], $semester );
                $partner_type = ($data_user['type'] == 'partner-open')
                    ? "(noch nicht bestätigt)" : "(bestätigt)";
            }
            $partner_type = "";

            $html .= "
		<div class='panel-body' >
			<form action='/Customizing/global/include/fpraktikum/submit/fp-signout.php' method='post' class='form-horizontal'>
				<input type='hidden' name='registrant' value='" . $user_login . "'>
				<input type='hidden' name='semester' value='" . $semester . "'>
				<input type='hidden' name='token' value='" . $data['token'] . "' >
				<div class='alert alert-success' role='alert'><strong>Schau mal!</strong> Du bist angemeldet.</div>
                
                <p>Dies sind die Informationen, die in der Datenbank gespeichert sind:</p>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Benutzername</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $user_login . "</span></p>
                    </div>
                    
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Abschluss</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data['graduation'] . "</span></p>
                    </div>
                
                </div>
                <div class='row'>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Partner (Benutzername)</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data['partner'] . " $partner_type" . "</span></p>
                    </div>
                
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>1. Semesterhälfte</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data['institute1'] . "</span></p>
                    </div>
                
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>2. Semesterhälfte</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data['institute2'] . "</span></p>
                    </div>
                
                </div>
                 <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Bemerkungen</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data['notes'] . "</span></p>
                    </div>
                
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p></p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>Hier kannst du dich wieder 
                        <button type='submit' name='submit_signout' class='btn btn-danger'>
                        Abmelden</button></span></p>
                        <p>Fragen? Schaue im 
                        <a href='https://physikonline.atlassian.net/wiki/pages/viewpage.action?pageId=496165'>
                        FAQ</a> nach.</p>
                    </div>
                
                </div>
			</form>
		</div>
    <script type='text/javascript' src='/home/elearning-www/public_html/elearning/ilias-5.1//Customizing/global/include/fpraktikum/js/fp-abmeldung.js'></script>
    ";
            break;
        case 'partner-open':
            // data about user that included partner
            $data = $fp_database->getRegistration( $user['registrant'], $semester );

            $html .= "
		<div class='panel-body' >
			<form class='form-horizontal'>
			    <div class='alert alert-info' role='alert'><strong>Schau mal!</strong> Du wurdest als Partner angegeben, du bist aber noch <strong>nicht</strong> angemeldet!</div>
                <p>Die Daten deines Partners sind:</p>
                
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Benutzername</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $user['registrant'] . "</span></p>
                    </div>
                    
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Abschluss</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data['graduation'] . "</span></p>
                    </div>
                
                </div>
                <div class='row'>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Partner (Benutzername)</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data['partner'] . " $partner_type" . "</span></p>
                    </div>
                
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>1. Semesterhälfte</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data['institute1'] . "</span></p>
                    </div>
                
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>2. Semesterhälfte</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data['institute2'] . "</span></p>
                    </div>
                
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Bemerkungen</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data['notes'] . "</span></p>
                    </div>
                
                </div>
                                
                <p>Deine Daten:</p>
                
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Login</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $user_login . "</span></p>
                    </div>
                    
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Semester</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $semester . "</span></p>
                    </div>
                
                </div>
                </form>
                <div class='row'>
                    
                    <form action='/Customizing/global/include/fpraktikum/submit/fp-signin.php' method='post'>
                            <input type='hidden' name='partner' value='" . $user_login . "'>
                            <input type='hidden' name='semester' value='" . $semester . "'>
                            <input type='hidden' name='token' value='" . $data['token'] . "' >
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                    
                        <span class='form-control-static'><button class='btn btn-default pull-right' type='submit' name='submit_partner-accepts'  value='Anmelden'>Anmelden</button></span>
                    
                    </div>
                    </form>
                    <form action='/Customizing/global/include/fpraktikum/submit/fp-signout.php' method='post' >
                            <input type='hidden' name='partner' value='" . $user_login . "'>
                            <input type='hidden' name='semester' value='" . $semester . "'>
                            <input type='hidden' name='token' value='" . $data['token'] . "' >
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                    
                        
                        <span class='form-control-static'><button class='btn btn-danger' type='submit' name='submit_partner-denies'  value='Abmelden'>
                        Abmelden
                        </button></span>
                        <p>Fragen? Schaue im <a href='https://physikonline.atlassian.net/wiki/pages/viewpage.action?pageId=496165'>FAQ</a> nach.</p>           
                    </div>
                    </form>
                  
                </div>
	    </div>";
            break;
        case 'partner-accepted':
            // data about user that included partner
            $data_registrant = $fp_database->getRegistration( $user['registrant'], $semester );

            $html .= "
		<div class='panel-body' >
			<form action='/Customizing/global/include/fpraktikum/submit/fp-signout.php' method='post' class='form-horizontal'>
				<input type='hidden' name='partner' value='" . $user_login . "'>
				<input type='hidden' name='semester' value='" . $semester . "'>
				<input type='hidden' name='token' value='" . $data_registrant['token'] . "' >
				<div class='alert alert-success' role='alert'><strong>Schau mal!</strong> Du bist als Partner angemeldet.</div>
				
				<div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Benutzername</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $user_login . "</span></p>
                    </div>
                    
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Abschluss</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data_registrant['graduation'] . "</span></p>
                    </div>
                
                </div>
                <div class='row'>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Partner (Benutzername)</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $user['registrant'] . "</span></p>
                    </div>
                
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>1. Semesterhälfte</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data_registrant['institute1'] . "</span></p>
                    </div>
                
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>2. Semesterhälfte</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data_registrant['institute2'] . "</span></p>
                    </div>
                
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p>Bemerkungen</p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>" . $data_registrant['notes'] . "</span></p>
                    </div>
                
                </div>
                <div class='row'>
                
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label'>
                        <p></p>
                    </div>
                    
                    <div class='col-sm-4 col-md-3 col-lg-2 control-label-left'>
                        <p><span class='form-control-static'>Hier kannst du dich wieder 
                        <button onclick=confirmAbmeldung() type='submit' class='btn btn-danger' name='submit_partner-denies' value='Abmelden'>
                        Abmelden</button></span></p>
        				<p>Fragen? Schaue im <a href='https://physikonline.atlassian.net/wiki/pages/viewpage.action?pageId=496165'>FAQ</a> nach.</p>
                    </div>
                    
                </div>
                     
                </form>
		</div>
		
    <script type='text/javascript' src='/home/elearning-www/public_html/elearning/ilias-5.1//Customizing/global/include/fpraktikum/js/fp-abmeldung.js'></script>
    ";
            break;
    }
}
else
{
    if ( strtotime( date( 'd-m-Y H:i:s' ) ) < strtotime( $dates['startdate'] ) )
    {
        $html .= "<div class='alert alert-info' style='margin-top: 20px'>Die Anmeldung ist noch nicht freigeschaltet!<br>Sie beginnt am " . $dates['startdate'] . " Uhr.</div>";
    }
    elseif ( strtotime( date( 'd-m-Y H:i:s' ) ) > strtotime( $dates['enddate'] ) )
    {
        $html .= "<div class='alert alert-info' style='margin-top: 20px'>Die Anmeldung ist beendet!</div>";
    }
    else
    {
        $html .= "<div class='alert alert-danger' style='margin-top: 20px'>Etwas ist falsch gelaufen, die Anmeldung scheint nicht freigeschaltet zu sein.</div>";
    }
}
// Necessary to end the registration div
$html .= "</div>";