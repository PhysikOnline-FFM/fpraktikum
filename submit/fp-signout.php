<?php

require_once("class.fp_register.php");
require_once("../fp_constants.php");


/**
 * Script that is called after user wants to delete registration.
 *
 * @date January 2017
 * @author Lars Gröber
 */

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

$data = [
    "registrant" => $_POST['registrant'],
    "semester"   => $_POST['semester']
];

$Register = new Register();
$fp_database = new FP_Database();

if ( isset( $_POST['submit_signout'] ) )
{
    $Register->signOut( $data['registrant'], $data['semester'] );
}
elseif ( isset( $_POST['submit_partner-denies'] ) )
{
    $Register->partnerDenies( $_POST['partner'], $data['semester'] );
}

if ( $Register->isErrorBit() )
{
    echo '<div class=\"alert alert-danger\" role=\"alert\"><strong>Fehler:</strong><ul>';
    foreach ( $Register->getError() as $key => $text )
    {
        echo '<li>' . $text . '</li>';
    }
    echo '</ul></div>';
    exit ();
}
else
{
    echo '<div class=\"alert alert-danger\" role=\"alert\"><strong>Erfolg:</strong> Du hast dich erfolgreich ausgetragen!</p>';
}

header( "Location: " . fp_const\REGISTRATION_MASK );