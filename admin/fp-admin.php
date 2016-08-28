<?php

error_reporting(E_ERROR);
ini_set('display_errors', 1);

require '/home/elearning-www/public_html/elearning/ilias-4.3/Customizing/global/include/fpraktikum/database/class.FP-Database.php';

$fp_database = new FP_Database();

?>

<form action="#" method="post">
  Semester: <input type="text" name="semester" value="WS16/17">
</form>

<?php

if ($_POST['angebot-hinzufügen']) {

  $data = [
    "institut" => $_POST['institut'],
    "semester" => $_POST['semester'],
    "abschluss" => $_POST['abschluss'],
    "semester_haelfte" => $_POST['semester_haelfte'],
    "plaetze" => $_POST['plaetze']
  ];

  // are all fields filled?
  foreach ($data as $name => $value) {
    if (!$value) {
      echo '<h1>Nicht alle Felder wurden ausgefüllt.</h1>';
      exit();
    }
  }

  // for most cases the angebot is the same for both times
  if ($data['semester_haelfte'] == "both") {
    if ($fp_database->setAngebote($data['institut'], $data['semester'], $data['abschluss'], 1, $data['plaetze'])
      && $fp_database->setAngebote($data['institut'], $data['semester'], $data['abschluss'], 2, $data['plaetze'])) {
      echo "Das Angebot wurde erfolgreich gespeichert.";
    }
  } 
  else {
    if ($fp_database->setAngebote($data['institut'], $data['semester'], $data['abschluss'], 
      $data['semester_haelfte'], $data['plaetze'])) {
      echo "Das Angebot wurde erfolgreich gespeichert.";
    }
  }
}

if ($_POST['angebot-löschen']) {

  $data = [
    "institut" => $_POST['institut'],
    "semester" => $_POST['semester'],
    "abschluss" => $_POST['abschluss'],
    "semester_haelfte" => $_POST['semester_haelfte']
  ];

  if ($fp_database->rmAngebot($data)) {
    echo "Eintrag erfolgreich gelöscht";
  }
}

if ($_POST['semester']) {
  $semester = $_POST['semester'];

  echo "<p>Hier sind die momentanen Angebote:</p>";

  $angebote = $fp_database->getAngebote($semester);
  
  //var_dump($angebote);
  echo "
    <table>
      <tr>
        <th>Institut</th>
        <th>Semester</th>
        <th>Abschluss</th>
        <th>Semesterhälfte</th>
        <th>Plätze</th>
        <th>Eintrag löschen</th>
      </tr>";

  // listing of all the data    
  foreach ($angebote as $row => $column) {
    echo "<tr><form action='#' method='post'>";
    foreach ($column as $name => $entry) {
      echo "<td><input type='hidden' name='".$name."' value='".$entry."'>".$entry."</td>";
    }
    echo "<td><input type='submit' name='angebot-löschen' value='Löschen'></td>";
    echo "</form></tr>";
  } 
  echo "</table>";
   
  // form to add a new entry
  echo "
    <p>Hier können Sie weitere Angebote hinzufügen (es wird nicht überprüft, ob das Angebot bereits besteht):</p>
    <form action='#' method='post'>
      <input type='hidden' name='semester' value='".$semester."'>

      <table>
        <tr>
          <th>Institut</th>
          <th>Semester</th>
          <th>Abschluss</th>
          <th>Semesterhälfte</th>
          <th>Plätze</th>
        </tr>
        <tr>
        <td><input type='text' maxlength='10' name='institut'></td>
        <td><input type='text' maxlength='7' name='semester' value='".$semester."' readonly></td>
        <td>
          <select name='abschluss'>
            <option value='BA'>Bachelor</option>
            <option value='MA'>Master</option>
            <option value='MAIT'>Master IT</option>
            <option value='L3'>Lehramt</option>
            <option value='ALLE'>Alle</option>
          </select>
        </td>
        <td>
          <select name='semester_haelfte'>
            <option value='1'>1</option>
            <option value='2'>2</option>
            <option value='both'>beide</option>
          </select>
        </td>
        <td><input type='number' name='plaetze'></td>
        </tr>
      </table>
      <br>
      <input type='submit' name='angebot-hinzufügen'>
    </form>";
}

  