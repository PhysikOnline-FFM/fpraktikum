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
$dates = $fp_database->getDates($semester);

$admins = array(
      'chgad_admin'
    , 'LarsG_admin'
);

//$admins = array (
//            array(
//                'firstname'     => "Christian" ,
//                'lastname'      => "Grossmüller"),
//            array(
//                'firstname'     => "Lars",
//                'lastname'      => "Gröber"),
//            );



$html = "<div class='panel panel-default' style='background-color: white; border: 2px solid #b9b9b9'>
		<div class='panel-heading' style='background-color: #b9b9b9;'>
			Anmeldung zum Fortgeschrittenen Praktikum
		</div>";


if ( in_array( $user_login, $admins ) )
{
        $html .= "<div class=\"embed-responsive embed-responsive-16by9\">
                    <iframe src='Customizing/global/include/fpraktikum/admin/fp-admin.php'></iframe>
                  </div>";
//if((($user_firstname == $admins[0]['firstname']) or ($user_firstname == $admins[1]['firstname']))&& (($user_lastname == $admins[0]['lastname']) or ($user_lastname == $admins[1]['lastname'])))
//    {
//        if ( $_POST["export"] && $_POST["semester"] )
//        {
//            $semester = $_POST["semester"];
//
//            echo "# This file was automatically exported on " . date( "d.m.o G:i" ) .".\n";
//            //$path = "/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/admin/test.dat";
//            $path = "/tmp/fp_" . str_replace( "/", "", $semester ) ."_anmeldungen.dat";
//
//            $exporter = new Exporter();
//
//            $data = array();
//
//            foreach ( $fp_database->getAllRegistrations( $semester ) as $key => $value )
//            {
//                $line = array();
//                $personal = $fp_database->getAddInfos( $value['hrz1'] );
//                array_push( $line, $personal['first_name'] . " " . $personal['last_name'] );
//                array_push( $line, $personal['matrikel'] );
//                array_push( $line, $value['hrz1'] );
//
//                $personal = $fp_database->getAddInfos( $value['hrz2'] );
//                array_push( $line, $personal['first_name'] . " " . $personal['last_name'] );
//                array_push( $line, $personal['matrikel'] );
//                array_push( $line, $value['hrz2'] );
//
//                array_push( $data, array_merge( $line, array_slice( $value, 2 ) ) );
//            }
//
//            $exporter->init( $data );
//            $exporter->setHead( array( "Name", "Matrikelnummer", "HRZ", "Partner", "Matrikelnummer", "HRZ", "Studiengang", "Institut1", "Institut2", "Anmeldezeitpunkt", "Bemerkungen" ) );
//
//            if ( $exporter->create_plain_file( $path ) != 0 )
//            {
//                echo "<p>Something went wrong writing to the file!</p>";
//                exit();
//            }
//
//            // set headers -> document will be downloaded as a plain text file automatically
//            header( 'Content-Type: text/plain' );
//            header( "Content-Transfer-Encoding: Binary" );
//            header( "Content-disposition: attachment; filename=\"" . basename( $path ) . "\"" );
//
//            readfile( $path );
//
//            exit();
//        }
//
//
//        $html .= "
//        <form action='#' method='post'>
//            Semester: <input type='text' name='semester' value='" . $semester . "'>
//        </form>";
//
//
//
//        /**
//         * Part to set Dates
//         */
//
//        if ($_POST['end_start_date'])
//        {
//            $start_date = strtotime($_POST['start_date']);
//            $end_date = strtotime($_POST['end_date']);
//
//            if (!($start_date<$end_date))
//            {
//                echo " Der Startzeitpunkt muss vor dem Endzeitpunktliegen.";
//                exit();
//            }
//            if($fp_database->setDate($_POST['start_date'], $_POST['end_date'], Helper::get_semester()))
//            {
//                echo "Zeiten erfolgreich gespeichert";
//            }
//        }
//        /*
//         * Part to delete Dates
//         */
//        if ($_POST['delete_dates'])
//        {
//            if ( $fp_database->rmDates( Helper::get_semester()) )
//            {
//                echo "Zeiten erfolgreich gelöscht.";
//            }
//        }
//
//
//        if ( $_POST['angebot-hinzufügen'] )
//        {
//            $data = array(
//                "institute"     => $_POST['institute'],
//                "semester"      => $_POST['semester'],
//                "graduation"    => $_POST['graduation'],
//                "semester_half" => $_POST['semester_half'],
//                "slots"         => $_POST['slots']
//            );
//
//            if ( $data['graduation'] == 'LA' )
//            {
//                $data['semester_half'] = "both";
//            }
//
//            // are all fields filled?
//            foreach ( $data as $name => $value )
//            {
//
//                if ( ! $value && $data[$name] != $data['semester_half'] )
//                {
//                    echo '<h1>Nicht alle Felder wurden ausgefüllt.</h1>';
//                    exit();
//                }
//            }
//
//            // for most cases the angebot is the same for both times
//            if ( $data['semester_half'] == "both" )
//            {
//                if ( $fp_database->setOffers( $data['institute'], $data['semester'], $data['graduation'], 0, $data['slots'] )
//                    && $fp_database->setOffers( $data['institute'], $data['semester'], $data['graduation'], 1, $data['slots'] )
//                )
//                {
//                    echo "Das Angebot wurde erfolgreich gespeichert.";
//                }
//            }
//            else
//            {
//                if ( $fp_database->setOffers( $data['institute'], $data['semester'], $data['graduation'],
//                    $data['semester_half'], $data['slots'] )
//                )
//                {
//                    echo "Das Angebot wurde erfolgreich gespeichert.";
//                }
//            }
//        }
//
//        if ( $_POST['angebot-löschen'] )
//        {
//
//            $data = array(
//                "institute"     => $_POST['institute'],
//                "semester"      => $_POST['semester'],
//                "graduation"    => $_POST['graduation'],
//                "semester_half" => $_POST['semester_half']
//            );
//
//            if ( $fp_database->rmOffer( $data ) )
//            {
//                echo "Eintrag erfolgreich gelöscht";
//            }
//        }
//
//        if ( $_POST['semester'] )
//        {
//            $semester = $_POST['semester'];
//
//            $html .= "<p>Hier sind die momentanen Angebote:</p>";
//
//            $angebote = $fp_database->getOffers( $semester );
//            $freePlaces = $fp_database->freePlaces( $semester );
//
//            //var_dump($angebote);
//            $html .= "
//    <table>
//      <tr>
//        <th>Institut</th>
//        <th>Abschluss</th>
//        <th>Semesterhälfte</th>
//        <th>Max Plätze</th>
//        <th>Freie Plätze</th>
//        <th>Eintrag löschen</th>
//      </tr>";
//
//            // listing of all the data
//            foreach ( $angebote as $row => $column )
//            {
//                $html .= "<tr><form action='#' method='post'>";
//                foreach ( $column as $name => $entry )
//                {
//                    $html .= "<td><input type='hidden' name='" . $name . "' value='" . $entry . "'>" . $entry . "</td>";
//                }
//                $html .= "<td>" . $freePlaces[$column['graduation']][$column['institute']][$column['semester_half']] . "</td>";
//                $html .= "<td><input type='submit' name='angebot-löschen' value='Löschen'></td>";
//                $html .= "<input type= 'hidden' name='semester' value='" . $semester . "'>";
//                $html .= "</form></tr>";
//            }
//            $html .= "</table>";
//
//            // form to add a new entry
//            $html .= "
//    <p>Hier können Sie weitere Angebote hinzufügen (es wird nicht überprüft, ob das Angebot bereits besteht):</p>
//    <p>Für den Studiengang 'Lehramt' wird die Option 'Semesterhälfte' ignoriert und das Angebot immer in beide Semesterhälften geschrieben.</p>
//    <form action='#' method='post'>
//      <input type='hidden' name='semester' value='" . $semester . "'>
//
//      <table>
//        <tr>
//          <th>Institut</th>
//          <th>Semester</th>
//          <th>Abschluss</th>
//          <th>Semesterhälfte</th>
//          <th>Plätze</th>
//        </tr>
//        <tr>
//        <td><input type='text' maxlength='10' name='institute'></td>
//        <td><input type='text' maxlength='7' name='semester' value='" . $semester . "' readonly></td>
//        <td>
//          <select name='graduation'>
//            <option value='BA'>Bachelor</option>
//            <option value='MA'>Master</option>
//            <option value='MAIT'>Master IT</option>
//            <option value='LA'>Lehramt</option>
//            <option value=''>Alle</option>
//          </select>
//        </td>
//        <td>
//          <select name='semester_half'>
//            <option value='0'>1</option>
//            <option value='1'>2</option>
//            <option value='both'>beide</option>
//          </select>
//        </td>
//        <td><input type='number' name='slots'></td>
//        </tr>
//      </table>
//      <br>
//      <input type='submit' name='angebot-hinzufügen'>
//    </form>";
//
//            $dates= $fp_database->getDates("SS17");
//            if(!($dates['startdate'] && $dates['enddate']))
//            {
//
//
//                $html .= "
//    <p> hier können sie den Zeitraum angeben, in dem die Anmeldung verfügbar ist :</p>
//    <form action='#' method='post'>
//     <table>
//     <tr>
//        <th>Anfangszeit</th>
//        <th>Endzeit</th>
//     </tr>
//     <tr>
//        <td><input type='datetime' name='start_date' placeholder='DD.MM.YYYY HH:MM:SS'></td>
//        <td><input type='datetime' name='end_date' placeholder='DD.MM.YYYY HH:MM:SS'></td>
//     </tr>
//     </table>
//     <input type='submit' name='end_start_date'>
//    </form>";
//            }
//            else
//            {
//                $html .= "
//            <form action='#' method='post'>
//            <p> Der Anmeldezeitraum für das ". Helper::get_semester() ." ist von ". $dates['startdate'] ." bis ". $dates['enddate'] ."</p>
//            <p><input type='submit' name='delete_dates' value='Anmeldezeiten Löschen' ></p>
//            </form>";
//            }
//            //var_dump($angebote);
//            $html .= "
//    <p>Im folgenden werden alle aktuellen Anmeldungen angezeigt:</p>
//    <table>
//      <tr>
//        <th>HRZ1</th>
//        <th>HRZ2</th>
//        <th>Abschluss</th>
//        <th>Institut1</th>
//        <th>Institut2</th>
//        <th>Anmeldezeitpunkt</th>
//        <th>Bemerkungen</th>
//      </tr>";
//
//            $registrations = $fp_database->getAllRegistrations( $semester );
//            // listing of all the data
//            foreach ( $registrations as $row => $column )
//            {
//                $html .= "<tr>";
//                foreach ( $column as $name => $entry )
//                {
//                    $html .= "<td>" . $entry . "</td>";
//                }
//                $html .= "</tr>";
//            }
//            $html .= "</table>";
//
//            // add export button
//            $html .= "<form action='#' method='post'>
//            <input type='submit' name='export' value='Export'>
//            <input hidden name='semester' value='" . $semester . "'>
//          </form>";
//        }
//
//
//
    }


if ( Helper::validate_dates($dates['startdate'],$dates['enddate']) )
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
			<div class='alert alert-danger'>Dies ist nur die Entwicklungsumgebung für die Anmeldung!</div>
			<form name='registration' action='./Customizing/global/include/fpraktikum/submit/fp-signin.php' method='post' onsubmit='return formValidate()' class='form-horizontal'>
				<p class=''>Hi " . $user_firstname . ", <br />hier kannst du dich für das Fortgeschrittenen Praktikum im " . $semester . " anmelden.</p>
				<div id='demo' class='collapse text-info bg-info'></div>
				<p>Du findest weitere Informationen im <a href='https://physikonline.atlassian.net/wiki/pages/viewpage.action?pageId=496165'>FAQ</a>. 
				Wenn du bei der Anmeldung auf Probleme stößst, zögere nicht uns zu <a href='mailto:team@elearning.physik.uni-frankfurt.de'>schreiben</a>.</p>

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
                        <label for='la'><input class='radio_graduation' onchange=showInstitut('LA') type='radio' id='la' name='graduation' value='LA'>Lehramt</label>
                        <div id='instituts'></div>https://elearning.physik.uni-frankfurt.de/ilias.php?baseClass=ilRepositoryGUI&cmd=frameset&set_mode=tree&ref_id=11790
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
						<input class='submit btn btn-default'  type='submit' name='submit_register' id='submitRegister' value='Anmelden'>
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
                    ? "(offen)" : "(bestätigt)";
            }

            $html .= "
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
                <div class='form-group'>
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Benutzername</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $user['registrant'] . "</span>
					</div>
				</div>
			    <div class='form-group'>
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Abschluss</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $data['graduation'] . "</span>
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>1. Semesterhälfte</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $data['institute1'] . "</span>
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>2. Semesterhälfte</label>
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

				<hr>

                <p>Deine Daten:</p>

                <div class='form-group'>
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Login</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $user_login . "</span>
					</div>
				</div>
				<div class='form-group'>
					<label class='col-sm-4 col-md-3 col-lg-2 control-label'>Semester</label>
					<div class='col-sm-8 col-md-9 col-lg-10'>
						<span class='form-control-static'>" . $semester . "</span>
					</div>
				</div>
			</form>

            <p>Hier kannst du dich eintragen oder ablehnen:</p>
            <div class='row'>
                <form action='/Customizing/global/include/fpraktikum/submit/fp-signin.php' method='post' class='col-xs-6 col-sm-2'>
                    <input type='hidden' name='partner' value='" . $user_login . "'>
                    <input type='hidden' name='semester' value='" . $semester . "'>
                    <input type='hidden' name='token' value='" . $data['token'] . "' >
                    <button class='btn btn-default' type='submit' name='submit_partner-accepts' value='Anmelden'>Anmelden</button>
                </form>
                <form action='/Customizing/global/include/fpraktikum/submit/fp-signout.php' method='post' class='col-xs-6 col-sm-2 col-sm-offset-8'>
                    <input type='hidden' name='partner' value='" . $user_login . "'>
                    <input type='hidden' name='semester' value='" . $semester . "'>
                    <input type='hidden' name='token' value='" . $data['token'] . "' >
                    <button class='btn btn-danger pull-right' type='submit' name='submit_partner-denies' value='Abmelden'>Abmelden</button>
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
    <script type='text/javascript' src='/home/elearning-www/public_html/elearning/ilias-5.1//Customizing/global/include/fpraktikum/js/fp-abmeldung.js'></script>
    ";
            break;
    }
}
else
{
    // TODO Give dates for the registration
    $html .= "<div class='alert alert-info'>Die Anmeldung ist noch nicht freigeschaltet!</div>";
}
// Necessary to end the registration div
$html .= "</div>";