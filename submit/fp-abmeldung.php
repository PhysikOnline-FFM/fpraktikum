<?php

/**
 * script that is called after user wants to delete registration
 */
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

$Anmeldeformular = '<a href="http://5-1.ilias.physikelearning.de/ilias.php?ref_id=11819&cmd=frameset&cmdClass=ilrepositorygui&cmdNode=du&baseClass=ilRepositoryGUI">Anmeldeformular</a>';

$data = [
    "registrant"        => $_POST['registrant'],
    "semester" => $_POST['semester']
];

foreach ( $data as $name => $value )
{
    if ( ! $value )
    {
        echo '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Bitte rufe diese Seite nur über das ' . $Anmeldeformular . ' auf.</div>';
        exit();
    }
}

require '/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/database/class.FP-Database.php';

$error = "";

// something needs to happen if the user has a partner

$fp_database = new FP_Database();


// check user input again
if ( $fp_database->checkUser( $data['registrant'], $data['semester'] ) == false )
{
    $error = "Du bist nicht angemeldet und kannst dich nicht abmelden, bitte gehe wieder zum " . $Anmeldeformular . " zurück";
}

// more checks, e.g. regex checks for entries

if ( $error != "" )
{
    echo '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> ' . $error . '</div>';
    exit ();
}

// it should be save now to access the db


if ( !isset($_POST['partner-denies']))
    {

    if ($fp_database->rmAnmeldung($data))
    {
        echo '<div class="alert alert-success" role="alert"><strong>Super!</strong> Du hast dich erfolgreich ausgetragen.!</div>';
    }
    else
    {
        // TO-DO: Error-message + Info for user what he has to do (mailing admin? maybe?)
        echo '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Du konntest dich aus unbekannten Gründen nicht austragen.</div>';

    }
    }
else {
    if($fp_database->rmPartner($data['registrant'],$data['semester']))
    {
        echo '<div class="alert alert-success" role="alert"><strong>Super!</strong> Du hast dich erfolgreich ausgetragen.!</div>';
    }
    else
    {
        echo '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Du konntest dich aus unbekannten Gründen nicht austragen.</div>';

    }

}

header('Location: http://5-1.ilias.physikelearning.de/goto_FB13-PhysikOnline_cat_11819.html');
?>



