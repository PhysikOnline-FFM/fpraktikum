<?php

/**
 * script checks input from user and writes registration to db
 * TODO: mehr checks siehe unten
 * August 2016 - LG
 */
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$Anmeldeformular = '<a href="http://5-1.ilias.physikelearning.de/ilias.php?ref_id=11819&cmd=frameset&cmdClass=ilrepositorygui&cmdNode=du&baseClass=ilRepositoryGUI">Anmeldeformular</a>';

$data = [
    "hrz"       => $_POST['hrz'],
    "semester"  => $_POST['semester'],
    "institute1" => $_POST['institute1'],
    "institute2" => $_POST['institute2']
];

//// checks ////

$error = [];

// are all fields filled?
foreach ( $data as $name => $value )
{
    if ( ! $value )
    {
        echo '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Bitte rufe diese Seite nur über das ' . $Anmeldeformular . ' auf.</div>';
        echo $name;
        exit();
    }
}

require '/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/database/class.FP-Database.php';


$fp_database = new FP_Database();


// check user input again
if ( $fp_database->checkUser( $data['hrz'], $data['semester'] )[0] != 'partner-accept' )
{
    array_push( $error, "Du bist bereits angemeldet oder wurdest nicht als Partner hinzugefügt, bitte gehe wieder zum " . $Anmeldeformular . " zurück" );
}
if ( $fp_database->checkUserInfo( $data ) )
{

}
// more checks, e.g. regex checks for entries and check whether info is in il-db and whether there are free places in requested institute

if ( ! empty( $error ) )
{
    echo '<div class="alert alert-danger" role="alert"><strong>Fehler:</strong><ul>';
    foreach ( $error as $key => $text )
    {
        echo '<li>' . $error[$key] . '</li>';
    }
    echo '</ul></div>';
    exit ();
}

// it should be save now to access the db
$partner_db = ($partner) ? $partner_hrz : NULL;

if ( ! $fp_database->setPartnerAccepted( $data['hrz'], $data['semester'] ) )
{
    die( 'Error beim Speichern deiner Daten' );
}

//print_r($data);

header( 'Location: http://5-1.ilias.physikelearning.de/goto_FB13-PhysikOnline_cat_11819.html' );
?>
<div class="alert alert-success" role="alert"><strong>Super!</strong> Deine Daten wurden erfolgreich gespeichert!</div>

