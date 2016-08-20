<?php
/**
 * hier soll eine DB Abfrage für die freien Plätze und die Partnerfrage entstehen.
 * August 2016
 */
$task = $_GET['task'];

switch ($task) {
  case 'freePlaces':
    freePlaces();
    break;
  case 'partner':
    partner();
    break;
}

/**
 * function to determine the free places in each institute
 * -> DB call to determine institutes
 *
 * @return [JSON] JSON-Object to be returned to the js-script
 */
function freePlaces() {
  $testArray = ["IAP1" => 10, "PI1" => 5, "ITP1" => 3,
                "IAP2" => 15, "PI2" => 8, "ITP2" => 10];
  echo json_encode($testArray);
}

/**
 * function to check whether the hrz-number and name can be found in the ILIAS-DB
 */
function partner() {
  $hrz = $_GET['hrz'];
  $name = $_GET['name'];

  if ($hrz == 's1234567' && $name == 'Müller')  {
    echo json_encode('Gefunden');
  } else {
    echo 0;
  }
}
 ?>
