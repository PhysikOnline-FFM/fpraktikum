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

  public function __construct($dbLink, $dbUser, $dbPassword, $dbTable)
  {
    $this->dbLink = $dbLink;
    $this->dbUser = $dbUser;
    $this->dbPassword = $dbPassword;
    $this->dbTable = $dbTable;

  }

  public function __destruct()
  {
      //$this->db->close();
  }

  public function initDb()
  {
    $this->db = new mysqli($this->dbLink, $this->dbUser, $this->dbPassword, $this->dbTable)
    or die("Unable to connect to Database with link ".$this->dbLink."!");

    $this->db->set_charset('UTF8');
  }

  public function makeQuery($query)
  {
    // TODO: Database Security
    //$query = mysqli_real_escape_string($this->db, $query);

    $dbResult = $this->db->query($query) or die("Error ".$this->db->error."!");
    return $dbResult;
  }

  public function prepare($query)
  {
    return $this->db->prepare($query);
  }
}