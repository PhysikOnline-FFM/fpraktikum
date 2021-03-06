<?php

/**
 * an admin interface to add new courses
 * TODO: this is quite messy
 */

//error_reporting( E_ALL );
//ini_set( 'display_errors', 1 );

require_once '../database/class.FP-Database.php';
require_once '../include/class.exporter.php';
require_once '../include/class.helper.php';

$fp_database = new FP_Database();
$semester = Helper::get_semester();

/**
 * Exports registrations as a plain text file.
 * See end of file for the "export" button.
 *
 * @author Lars Gröber
 * @date   22.01.2017
 */
if ( $_POST["export"] )
{
    echo "# This file was automatically exported on " . date( "d.m.o G:i" ) . ".\n";
    //$path = "/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/admin/test.dat";
    $path = "/tmp/fp_" . str_replace( "/", "", $semester ) . "_anmeldungen.dat";

    $exporter = new Exporter();

    $data = array();

    foreach ( $fp_database->getAllRegistrations( $semester ) as $key => $value )
    {
        $line = array();
        $personal = $fp_database->getAddInfos( $value['hrz1'] );
        array_push( $line, $personal['first_name'] . " " . $personal['last_name'] );
        array_push( $line, $personal['matrikel'] );
        array_push( $line, $value['hrz1'] );

        $personal = $fp_database->getAddInfos( $value['hrz2'] );
        array_push( $line, $personal['first_name'] . " " . $personal['last_name'] );
        array_push( $line, $personal['matrikel'] );
        array_push( $line, $value['hrz2'] );

        array_push( $data, array_merge( $line, array_slice( $value, 2 ) ) );
    }

    $exporter->init( $data );
    $exporter->setHead( array( "Name", "Matrikelnummer", "HRZ", "Partner", "Matrikelnummer", "HRZ", "Studiengang", "Institut1", "Institut2", "Anmeldezeitpunkt", "Bemerkungen" ) );

    if ( $exporter->create_plain_file( $path ) != 0 )
    {
        echo "<p>Something went wrong writing to the file!</p>";
        exit();
    }

    // set headers -> document will be downloaded as a plain text file automatically
    header( 'Content-Type: text/plain' );
    header( "Content-Transfer-Encoding: Binary" );
    header( "Content-disposition: attachment; filename=\"" . basename( $path ) . "\"" );

    readfile( $path );

    exit();
}

/*
 * Part to set Dates
 */
if ( $_POST['end_start_date'] )
{
    $start_date = strtotime( $_POST['start_date'] );
    $end_date = strtotime( $_POST['end_date'] );

    if ( ! ($start_date < $end_date) )
    {
        echo " Der Startzeitpunkt muss vor dem Endzeitpunktliegen.";
        exit();
    }
    if ( $fp_database->setDate( $_POST['start_date'], $_POST['end_date'], $semester ) )
    {
        echo "Zeiten erfolgreich gespeichert";
    }
}
/*
 * Part to delete Dates
 */
if ( $_POST['delete_dates'] )
{
    if ( $fp_database->rmDates( $semester ) )
    {
        echo "Zeiten erfolgreich gelöscht.";
    }
}


if ( $_POST['angebot-hinzufügen'] )
{
    $data = array(
        "institute"     => $_POST['institute'],
        "semester"      => $_POST['semester'],
        "graduation"    => $_POST['graduation'],
        "semester_half" => $_POST['semester_half'],
        "slots"         => $_POST['slots']
    );

    if ( $data['graduation'] == 'LA' )
    {
        $data['semester_half'] = "both";
    }

    // are all fields filled?
    foreach ( $data as $name => $value )
    {
        if ( ! $value && $data[$name] != $data['semester_half'] )
        {
            echo '<h1>Nicht alle Felder wurden ausgefüllt.</h1>';
            exit();
        }
    }

    // for most cases the angebot is the same for both times
    if ( $data['semester_half'] == "both" )
    {
        if ( $fp_database->setOffers( $data['institute'], $data['semester'], $data['graduation'], 0, $data['slots'] )
            && $fp_database->setOffers( $data['institute'], $data['semester'], $data['graduation'], 1, $data['slots'] )
        )
        {
            echo "Das Angebot wurde erfolgreich gespeichert.";
        }
    }
    else
    {
        if ( $fp_database->setOffers( $data['institute'], $data['semester'], $data['graduation'],
            $data['semester_half'], $data['slots'] )
        )
        {
            echo "Das Angebot wurde erfolgreich gespeichert.";
        }
    }
}

if ( $_POST['angebot-löschen'] )
{
    $dates = $fp_database->getDates( Helper::get_semester() );
    if ( Helper::validate_dates( $dates['startdate'], $dates['enddate'] ) )
    {
        echo "Du kannst keine Einträge löschen, wenn die Anmeldung läuft!";
        exit();
    }

    $data = array(
        "institute"     => $_POST['institute'],
        "semester"      => $_POST['semester'],
        "graduation"    => $_POST['graduation'],
        "semester_half" => $_POST['semester_half']
    );

    if ( $fp_database->rmOffer( $data ) )
    {
        echo "Eintrag erfolgreich gelöscht";
    }
}


echo "<p>Hier sind die momentanen Angebote:</p>";

$angebote = $fp_database->getOffers( $semester );
$freePlaces = $fp_database->freePlaces( $semester );

//var_dump($angebote);
echo "
<table>
  <tr>
    <th>Institut</th>
    <th>Abschluss</th>
    <th>Semesterhälfte</th>
    <th>Max Plätze</th>
    <th>Freie Plätze</th>
    <th>Eintrag löschen</th>
  </tr>";

// listing of all the data
foreach ( $angebote as $row => $column )
{
    echo "<tr><form action='#' method='post'>";
    foreach ( $column as $name => $entry )
    {
        echo "<td><input type='hidden' name='" . $name . "' value='" . $entry . "'>" . $entry . "</td>";
    }
    if ( $column['graduation'] == 'ALL' )
    {
        echo "<td>" . $freePlaces['BA'][$column['institute']][$column['semester_half']] . "</td>";
    }
    else
    {
        echo "<td>" . $freePlaces[$column['graduation']][$column['institute']][$column['semester_half']] . "</td>";
    }
    echo "<td><input type='submit' name='angebot-löschen' value='Löschen'></td>";
    echo "<input type='hidden' name='semester' value='" . $semester . "'>";
    echo "</form></tr>";
}
echo "</table>";

// form to add a new entry
echo "
<p>Hier können Sie weitere Angebote hinzufügen (es wird nicht überprüft, ob das Angebot bereits besteht):</p>
<p>Für den Studiengang 'Lehramt' wird die Option 'Semesterhälfte' ignoriert und das Angebot immer in beide Semesterhälften geschrieben.</p>
<form action='#' method='post'>
  <input type='hidden' name='semester' value='" . $semester . "'>

  <table>
    <tr>
      <th>Institut</th>
      <th>Semester</th>
      <th>Abschluss</th>
      <th>Semesterhälfte</th>
      <th>Plätze</th>
    </tr>
    <tr>
    <td><input type='text' maxlength='10' name='institute'></td>
    <td><input type='text' maxlength='7' name='semester' value='" . $semester . "' readonly></td>
    <td>
      <select name='graduation'>
        <option value='BA'>Bachelor</option>
        <option value='MA'>Master</option>
        <option value='LA'>Lehramt</option>
        <option value='ALL'>Alle</option>
      </select>
    </td>
    <td>
      <select name='semester_half'>
        <option value='0'>1</option>
        <option value='1'>2</option>
        <option value='both'>beide</option>
      </select>
    </td>
    <td><input type='number' name='slots'></td>
    </tr>
  </table>
  <br>
  <input type='submit' name='angebot-hinzufügen'>
</form>";

$dates = $fp_database->getDates( "SS17" );
if ( ! ($dates['startdate'] && $dates['enddate']) )
{


    echo "
<p> hier können sie den Zeitraum angeben, in dem die Anmeldung verfügbar ist :</p>
<form action='#' method='post'>
 <table>
 <tr>
    <th>Anfangszeit</th>
    <th>Endzeit</th>
 </tr>
 <tr>
    <td><input type='datetime' name='start_date' placeholder='DD.MM.YYYY HH:MM:SS'></td>
    <td><input type='datetime' name='end_date' placeholder='DD.MM.YYYY HH:MM:SS'></td>
 </tr>
 </table>
 <input type='submit' name='end_start_date'>
</form>";
}
else
{
    echo "
        <form action='#' method='post'>
        <p> Der Anmeldezeitraum für das " . Helper::get_semester() . " ist von " . $dates['startdate'] . " bis " . $dates['enddate'] . "</p>
        <p><input type='submit' name='delete_dates' value='Anmeldezeiten Löschen' ></p>
        </form>";
}
//var_dump($angebote);
echo "
<p>Im folgenden werden alle aktuellen Anmeldungen angezeigt:</p>
<table>
  <tr>
    <th>HRZ1</th>
    <th>HRZ2</th>
    <th>Abschluss</th>
    <th>Institut1</th>
    <th>Institut2</th>
    <th>Anmeldezeitpunkt</th>
    <th>Bemerkungen</th>
  </tr>";

$registrations = $fp_database->getAllRegistrations( $semester );
// listing of all the data
foreach ( $registrations as $row => $column )
{
    echo "<tr>";
    foreach ( $column as $name => $entry )
    {
        echo "<td>" . $entry . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

// add export button
echo "<form action='#' method='post'>
        <input type='submit' name='export' value='Export'>
        <input hidden name='semester' value='" . $semester . "'>
      </form>";


