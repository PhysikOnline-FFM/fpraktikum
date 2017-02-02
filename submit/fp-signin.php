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
$post_register = htmlspecialchars( $_POST['submit_register'] );
$post_partner_accepts = htmlspecialchars( $_POST['submit_partner-accepts'] );
if ( isset( $post_register ) )
{
    // saves form-input data of student
    $data = [
        "registrant" => htmlspecialchars( $_POST['registrant'] ),
        "graduation" => htmlspecialchars( $_POST['graduation'] ),
        "semester"   => htmlspecialchars( $_POST['semester'] ),
        "institute1" => htmlspecialchars( $_POST['institute1'] ),
        "institute2" => htmlspecialchars( $_POST['institute2'] ),
        "notes"      => htmlspecialchars( $_POST['notes'] )
    ];

    $partner = NULL;

    // check if the user chose a partner
    $post = htmlspecialchars( $_POST['check-partner'] );
    if ( isset( $post ) )
    {
        $partner = htmlspecialchars( $_POST['partner-hrz'] );
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
elseif ( isset( $post_partner_accepts ) )
{
    $Register->signUp_partner( htmlspecialchars( $_POST['partner'] ), htmlspecialchars( $_POST['semester'] ) );
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
