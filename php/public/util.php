<?php

function finduser($token) {
  
  $current = date('Y-m-d H:i:s');
  $sql = "SELECT user_id FROM db_bitcoin.session WHERE session_id = '{$token}' AND expire_time < '{$current}' LIMIT 1; ";

  try {
    $db = new db();
    $db = $db->connect();
    $stmt = $db->query( $sql );
    $res = $stmt->fetch()["user_id"];
    if (is_null($res)) //check find or not
      $res = -1 ; //not found 
    echo $res;
    $db = null; // clear db object
            
  } catch( PDOException $e ) {

    // show error message as Json format
    $res =  "Invalid Action";
    $res =  $e->getMessage();
  }
    return $res;
}
