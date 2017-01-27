<?php

require_once ( "class.fp_register.php" );
require_once ( "../fp_constants.php" );

ini_set( 'E_ALL', 1 );

ini_set( 'display_errors', 1 );

require_once '../database/class.FP-Database.php';

/**
 * script checks input from user and writes registration to db
 *
 * Januar 2017 - LG
 */

$Register = new Register();

$data = [                                                        // saves form-input data of student
    "registrant" => $_POST['registrant'],
    "graduation" => $_POST['graduation'],
    "semester"   => $_POST['semester'],
    "institute1" => $_POST['institute1'],
    "institute2" => $_POST['institute2']
];

$partner = NULL;

if ( isset( $_POST['check_partner'] ) )
{
    $partner = $_POST['partner-hrz'];
}

$return = $Register->signUp_registrant( $data, $partner );

if ( $return[0] != 'success' )
{
    echo '<div class="alert alert-danger" role="alert"><strong>Fehler:</strong><ul>';
    foreach ( $return as $key => $text )
    {
        echo '<li>' . $text . '</li>';
    }
    echo '</ul></div>';
    exit ();
}

echo "Erfolg: Deine Daten wurden erfolgreich gespeichert!";

header( "Location: $REGISTRATION_MASK" );

?>

<!--Deine Daten wurden erfolgreich gespeichert!-->
