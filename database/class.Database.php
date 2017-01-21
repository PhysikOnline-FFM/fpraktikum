<?php

/**
 * @brief Class to establish a connection to database.
 */
class database
{
    protected $dbLink;
    protected $dbUser;
    protected $dbPassword;
    protected $dbTable;
    protected $db;

    /**
     * database constructor.
     *
     * @param $dbLink     string     URL to access database.
     * @param $dbUser     string     User to access the database with.
     * @param $dbPassword string     Password for the user.
     * @param $dbTable    string     Name of the table to open.
     */
    public function __construct ( $dbLink, $dbUser, $dbPassword, $dbTable )
    {
        $this->dbLink = $dbLink;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbTable = $dbTable;
    }

    public function __destruct ()
    {
        // throws error $this->db would be undefined
        //$this->db->close();
    }

    /**
     * Initializes database.
     */
    public function initDb ()
    {
        // establish connection
        $this->db = new mysqli( $this->dbLink, $this->dbUser, $this->dbPassword, $this->dbTable );

        if ( $this->db->connect_errno )
        {
            echo "Failed to connect to MySQL database " . $this->dbLink . ": " . $this->db->connect_error;
        }

        // necessary to use umlaute
        $this->db->set_charset( 'UTF8' );
    }

    /**
     * Function to prepare a prepared statement.
     *
     * @param $query string     The query which will be executed.
     *
     * @return mixed            A prepared statement object.
     */
    public function prepare ( $query )
    {
        $stmt = $this->db->prepare( $query );

        if ( ! $stmt )
        {
            echo "Error preparing statement" . $this->db->error;
        }

        return $stmt;
    }
}