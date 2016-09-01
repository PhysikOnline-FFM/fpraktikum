<?php

/**
 * file is used to accept AJAX calls from js script -> calls functions in class.FP-Database
 * August 2016 - LG
 */

error_reporting(-1);
//ini_set('display_errors', 1);

require_once('/home/elearning-www/public_html/elearning/ilias-4.3/Customizing/global/include/fpraktikum/database/class.FP-Database.php');

if ($_GET['task'])
{
  $task = $_GET['task'];
  $hrz = $_GET['hrz'];
  $name = $_GET['name'];
  $semester = $_GET['semester'];

  $fp_database = new FP_Database();

  switch ($task) {
    case 'freePlaces':
      echo json_encode($fp_database->freePlaces($semester)); // find a better way to get semester
      break;
    case 'partner':
      echo json_encode($fp_database->checkPartner($hrz, $name, $semester));
      break;
  }  
}