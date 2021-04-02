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

function geturl($url){
  $headerArray =array("Content-type:application/json;","Accept:application/json");
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArray);
  $output = curl_exec($ch);
  curl_close($ch);
  $output = json_decode($output,true);
  return $output;
}


function posturl($url,$data){
  $data  = json_encode($data);    
  $headerArray =array("Content-type:application/json;charset='utf-8'","Accept:application/json");
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
  curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($curl);
  curl_close($curl);
  return json_decode($output,true);
}