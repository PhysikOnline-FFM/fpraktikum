<?php

/*
 * This file is not needed anymore (fp-signin.php does all the work)!
 */

/**
 * script checks input from user and writes registration to db
 * TODO: mehr checks siehe unten
 * August 2016 - LG
 */
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
/*
require_once ( "class.fp_register.php" );

$data = [
    "partner"       => $_POST['partner'],
    "semester"  => $_POST['semester']
];

$Register = new Register();

if ( ! $Register->signUp_partner( $data['partner'], $data['semester'] ) )
{
    echo '<div class=\"alert alert-danger\" role=\"alert\"><strong>Fehler:</strong><ul>';
    foreach ( $Register->getError() as $key => $text )
    {
        echo '<li>' . $text . '</li>';
    }
    echo '</ul></div>';
    exit ();
}

echo '<div class=\"alert alert-danger\" role=\"alert\"><strong>Erfolg:</strong> Deine Daten wurden erfolgreich gelöscht!</p>';

header( "Location: $REGISTRATION_MASK" );

//// are all fields filled?
//foreach ( $data as $name => $value )
//{
//    if ( ! $value )
//    {
//        echo '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Bitte rufe diese Seite nur über das ' . $Anmeldeformular . ' auf.</div>';
//        echo $name;
//        exit();
//    }
//}
//
//require '/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/database/class.FP-Database.php';
//
//
//$fp_database = new FP_Database();
//
//
//// check user input again
//if ( $fp_database->checkUser( $data['hrz'], $data['semester'] )[0] != 'partner-open' )
//{
//    array_push( $error, "Du bist bereits angemeldet oder wurdest nicht als Partner hinzugefügt, bitte gehe wieder zum " . $Anmeldeformular . " zurück" );
//}
//if ( $fp_database->checkUserInfo( $data ) )
//{
//
//}
//// more checks, e.g. regex checks for entries and check whether info is in il-db and whether there are free places in requested institute
//
//if ( ! empty( $error ) )
//{
//    echo '<div class="alert alert-danger" role="alert"><strong>Fehler:</strong><ul>';
//    foreach ( $error as $key => $text )
//    {
//        echo '<li>' . $error[$key] . '</li>';
//    }
//    echo '</ul></div>';
//    exit ();
//}
//
//// it should be save now to access the db
//$partner_db = ($partner) ? $partner_hrz : NULL;
//
//if ( ! $fp_database->setPartnerAccepted( $data['hrz'], $data['semester'] ) )
//{
//    die( 'Error beim Speichern deiner Daten' );
//}
//
////print_r($data);

