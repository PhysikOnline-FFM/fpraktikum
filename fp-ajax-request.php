<?php

require_once('/home/elearning-www/public_html/elearning/ilias-4.3/Customizing/global/include/fpraktikum/database/class.FP-Database.php');

if ($_GET['task'])
{
  $task = $_GET['task'];
  $hrz = $_GET['hrz'];
  $name = $_GET['name'];

  $fp_database = new FP_Database();

  switch ($task) {
    case 'freePlaces':
      echo json_encode($fp_database->freePlaces());
      break;
    case 'partner':
      echo json_encode($fp_database->checkPartner($hrz, $name));
      break;
  }  
}