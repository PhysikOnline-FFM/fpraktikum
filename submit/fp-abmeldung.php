<?php

require_once ( "class.fp_register.php" );
require_once ( "../fp_constants.php" );



/**
 * script that is called after user wants to delete registration
 */
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

$data = [
    "registrant"        => $_POST['registrant'],
    "semester" => $_POST['semester']
];

$Register = new Register();
$fp_database = new FP_Database();

if ( !isset($_POST['partner-denies']))
{
    if ( ! $Register->signOut( $data['registrant'], $data['semester'] ) )
    {
        echo '<div class=\"alert alert-danger\" role=\"alert\"><strong>Fehler:</strong><ul>';
        foreach ( $Register->getError() as $key => $text )
        {
            echo '<li>' . $text . '</li>';
        }
        echo '</ul></div>';
        exit ();
    }

    echo '<div class=\"alert alert-danger\" role=\"alert\"><strong>Erfolg:</strong> Du hast dich erfolgreich ausgetragen!</p>';



//    if ($fp_database->rmAnmeldung($data))
//    {
//        echo '<div class="alert alert-success" role="alert"><strong>Super!</strong> Du hast dich erfolgreich ausgetragen.!</div>';
//    }
//    else
//    {
//        // TO-DO: Error-message + Info for user what he has to do (mailing admin? maybe?)
//        echo '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Du konntest dich aus unbekannten Gründen nicht austragen.</div>';
//
//    }
}
else
{

    if($fp_database->rmPartner($_POST['partner'],$data['semester']))
    {
        echo '<div class="alert alert-success" role="alert"><strong>Super!</strong> Du hast dich erfolgreich ausgetragen.!</div>';
    }
    else
    {
        echo '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Du konntest dich aus unbekannten Gründen nicht austragen.</div>';
        exit();
    }

}
header( "Location: $REGISTRATION_MASK" );
//header('Location: http://5-1.ilias.physikelearning.de/goto_FB13-PhysikOnline_cat_11819.html');

//foreach ( $data as $name => $value )
//{
//    if ( ! $value )
//    {
//        echo '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Bitte rufe diese Seite nur über das ' . $Anmeldeformular . ' auf.</div>';
//        exit();
//    }
//}
//
//require '/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/database/class.FP-Database.php';
//
//$error = "";
//
//// something needs to happen if the user has a partner
//
//$fp_database = new FP_Database();
//
//
//// check user input again
//if ( $fp_database->checkUser( $data['registrant'], $data['semester'] ) == false )
//{
//    $error = "Du bist nicht angemeldet und kannst dich nicht abmelden, bitte gehe wieder zum " . $Anmeldeformular . " zurück";
//}
//
//// more checks, e.g. regex checks for entries
//
//if ( $error != "" )
//{
//    echo '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> ' . $error . '</div>';
//    exit ();
//}





