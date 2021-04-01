<?php
/**
 * Connect MySQL with PDO class
 */
class db {
  
  private $dbhost = 'localhost';
  private $dbuser = 'root';
  private $dbpass = '';
  private $dbname = 'db_bitcoin';

  public function connect() {

    // https://www.php.net/manual/en/pdo.connections.php
    $prepare_conn_str = "mysql:host=$this->dbhost;dbname=$this->dbname";
    $dbConn = new PDO( $prepare_conn_str, $this->dbuser, $this->dbpass );

    // https://www.php.net/manual/en/pdo.setattribute.php
    $dbConn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

    // return the database connection back
    return $dbConn;
  }
}