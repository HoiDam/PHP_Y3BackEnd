<?php

function userLogin($email,$password){
  $user_id = checkExistAC($email,$password);
  if ($user_id ==-1){
    return msgPack("failed","wrong credential");
  }

  $token = genUuid();
  $expire_time = getShiftedTime(0.5); // shift half hour
  
  $sql = "INSERT INTO db_bitcoin.session (session_id,expire_time, user_id) VALUES ('$token','$expire_time','$user_id')";
  try {
      $db = new db();
      $db = $db->connect();
      $stmt = $db->prepare( $sql );
      $stmt->execute();
      $db = null; // clear db object
              
      } catch( PDOException $e ) {
      return msgPack("failed",$e);
    }
  return msgPack("success",$token);

}

function userRegister($email,$nickname,$password){
  if (checkExistEmail($email)!=-1){
    return msgPack("failed","email exist");
  }
  $funds = 20; //initate with some cash
  
  $sql = "INSERT INTO db_bitcoin.user (email,nickname,pw,funds) VALUES ('$email','$nickname','$password',$funds)";
  try {
      $db = new db();
      $db = $db->connect();
      $stmt = $db->prepare( $sql );
      $stmt->execute();
      $db = null; // clear db object
              
      } catch( PDOException $e ) {
      return msgPack("failed",$e);
    }
  return msgPack("success",$email);

}

function userInfo($token){
  $user_id = finduser($token);
  if ($user_id ==-1){
    return msgPack("failed","wrong_token");
  }
  $sql = "SELECT email , nickname , created_at , latest_pw_dt , address , icon , funds  FROM db_bitcoin.user WHERE id = '$user_id';";
  try {
    $db = new db();
    $db = $db->connect();
    $stmt = $db->query( $sql );
    $info = $stmt->fetch();    
    return msgPack("success",$info);
    $db = null; // clear db object
  } catch( PDOException $e ) {
    return msgPack("failed",$e);
  
}

}

function checkExistEmail($email){
  $sql = "SELECT email FROM db_bitcoin.user WHERE email = '$email' LIMIT 1 ; ";
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query( $sql );
        $email = $stmt->fetch()["email"];
        if (is_null($email)){
            return -1 ;
        }
        else{
            return $email ;
        }
        $db = null; // clear db object
      } catch( PDOException $e ) {
        return msgPack("failed",$e);
    }
}

function checkExistAC($email,$password){
  $sql = "SELECT id FROM db_bitcoin.user WHERE email = '$email' AND pw = '$password' LIMIT 1 ; ";
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query( $sql );
        $user_id = $stmt->fetch()["id"];
        if (is_null($user_id)){
            return -1 ;
        }
        else{
            return $user_id ;
        }
        $db = null; // clear db object
      } catch( PDOException $e ) {
        return msgPack("failed",$e);
    }
}

function changePW($email,$old_password,$new_password){
  if (checkExistAC($email,$old_password)==-1){
    return msgPack("failed","incorrect credential");
  }
  $current = getCurrentTime();
  $sql = "UPDATE db_bitcoin.user SET pw = '$new_password',latest_pw_dt ='$current' WHERE email = '$email' AND pw = '$old_password';";
  try {
    $db = new db();
    $db = $db->connect();
    $stmt = $db->prepare( $sql );
    $stmt->execute();
    $db = null; // clear db object
    return msgPack("success","updated");     
  } catch( PDOException $e ) {
    return msgPack("failed",$e);
  }
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
        return msgPack("failed",$e);
    }
}

function genUuid() {
  return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      // 32 bits for "time_low"
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

      // 16 bits for "time_mid"
      mt_rand( 0, 0xffff ),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand( 0, 0x0fff ) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand( 0, 0x3fff ) | 0x8000,

      // 48 bits for "node"
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
  );
}
?>