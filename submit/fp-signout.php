<?php

require_once("class.fp_register.php");
require_once("../include/fp_constants.php");
include "../include/header.php";

/**
 * Script that is called after user wants to delete registration.
 *
 * @date January 2017
 * @author Lars Gröber
 */

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

$Register = new Register();
$fp_database = new FP_Database();

if ( isset( $_POST['submit_signout'] ) )
{
    $Register->signOut( htmlspecialchars( $_POST['registrant'] )
        , htmlspecialchars( $_POST['semester'] ), htmlspecialchars( $_POST['token'] ) );
}
elseif ( isset( $_POST['submit_partner-denies'] ) )
{
    $Register->partnerDenies( htmlspecialchars( $_POST['partner'] ),
        htmlspecialchars( $_POST['semester'] ), htmlspecialchars( $_POST['token'] ) );
}

if ( $Register->isErrorBit() )
{
    echo '<div style="margin-top: 50px" class="container">';
    echo '<div class="alert alert-danger" role="alert"><strong>Fehler:</strong><ul>';
    foreach ( $Register->getError() as $key => $text )
    {
        echo '<li>' . $text . '</li>';
    }
    echo '</ul></div>';
    echo '</div>';
    include "../include/footer.php";
    exit ();

}
else
{
    echo '<div class=\"alert alert-danger\" role=\"alert\"><strong>Erfolg:</strong> Du hast dich erfolgreich ausgetragen!</p>';
}
header( "Location: " . fp_const\REGISTRATION_MASK );