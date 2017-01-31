<?php

require_once ( "class.fp_register.php" );
require_once ( "../fp_constants.php" );
require_once ( "../class.logger.php" );

ini_set( 'E_ALL', 1 );
ini_set( 'display_errors', 1 );

require_once '../database/class.FP-Database.php';

/**
 * Script handles all signing up processes.
 *
 * @date January 2017
 * @author Lars Gröber
 */
$Register = new Register();

/**
 * A user wants to register.
 */
if ( isset( $_POST['submit_register'] ) )
{
    // saves form-input data of student
    $data = [
        "registrant" => $_POST['registrant'],
        "graduation" => $_POST['graduation'],
        "semester"   => $_POST['semester'],
        "institute1" => $_POST['institute1'],
        "institute2" => $_POST['institute2'],
        "notes"      => $_POST['notes']
    ];

    $partner = NULL;

    // check if the user chose a partner
    if ( isset( $_POST['check-partner'] ) )
    {
        $partner = $_POST['partner-hrz'];
    }
    else
    {
        $partner = NULL;
    }

    // actual sign up process
    $Register->signUp_registrant( $data, $partner );
}
/**
 * A partner wants to accept.
 */
elseif ( isset( $_POST['submit_partner-accepts'] ) )
{
    $Register->signUp_partner( $_POST['partner'], $_POST['semester'] );
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

echo '<div class=\"alert alert-danger\" role=\"alert\"><strong>Erfolg:</strong> Deine Daten wurden erfolgreich gespeichert!</p>';

header( "Location: " . fp_const\REGISTRATION_MASK );

?>

<!--Deine Daten wurden erfolgreich gespeichert!-->
