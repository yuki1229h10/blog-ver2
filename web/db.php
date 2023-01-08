<?php

class DBh
{
  protected function connect()
  {
    try {
      $username = "";
      $password = "";
      $dbh = new PDO();
      return $dbh;
    } catch (PDOException $e) {
      print "Error!: " . $e->getMessage() . "<br/>";
      die();
    }
  }
}
