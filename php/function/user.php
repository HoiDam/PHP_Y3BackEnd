<?php

function userLogin($email,$password){
    // run select in db
    // if valid generate token & expire time to db
    // then return token to client
    // if invalid , return failed
  $token ="test";
  $res = array("token"=>$token);
  return $res;

}

function detailFund($user_id){
  $sql = "SELECT funds FROM db_bitcoin.user WHERE id = '$user_id';";
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query( $sql );
        $funds = $stmt->fetch()["funds"];
        
        if (is_null($funds)){ //check find or not
          $funds = 0 ; //not found
        } 
        $db = null; // clear db object
        return $funds;     
      } catch( PDOException $e ) {
        return $e;
    }
}

function editFunds($token,$method,$amount){
  $user_id = finduser($token);
    if ($user_id ==-1){
        return msgPack("failed","wrong_token");
    }
  if (($amount) <0 || gettype($amount)!="int"){
    return msgPack("failed","wrong amount");
  }
  return func_editFunds($user_id,$method,$amount);

}

function func_editFunds($user_id,$method,$amount){

  if ($method ==("add")){
    $sql = "UPDATE db_bitcoin.user SET funds =funds + '$amount' WHERE id = '$user_id';";
  }else if ($method ==("minus")){
    $sql = "UPDATE db_bitcoin.user SET funds =funds - '$amount' WHERE id = '$user_id';";
  }else
  return msgPack("failed","wrong method");


    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare( $sql );
        $stmt->execute();
        $db = null; // clear db object
        return msgPack("success","updated");     
      } catch( PDOException $e ) {
        return msgPack("success",$e);
    }
}


?>