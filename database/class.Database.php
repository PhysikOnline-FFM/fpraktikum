<?php
/**
 * class to establish a connection to database
 */
class database
{
  protected $dbLink;
  protected $dbUser;
  protected $dbPassword;
  protected $dbTable;
  protected $db;

  function __construct($dbLink, $dbUser, $dbPassword, $dbTable)
  {
    $this->dbLink = $dbLink;
    $this->dbUser = $dbUser;
    $this->dbPassword = $dbPassword;
    $this->dbTable = $dbTable;

  }

  function __destruct()
  {
      mysqli_close($this->db);
  }

  function initDb()
  {
    $this->db = mysqli_connect($this->dbLink, $this->dbUser, $this->dbPassword, $this->dbTable)
    or die("Unable to connect to Database with link ".$this->dbLink."!");
    mysqli_set_charset($this->db, 'UTF8');
  }

  function makeQuery($query)
  {
    // TODO: Database Security
    //$query = mysqli_real_escape_string($this->db, $query);

    $dbResult = mysqli_query($this->db, $query) or die("Error ".mysqli_error($this->db)."!");
    return $dbResult;
  }
}