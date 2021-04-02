<?php

function user_login($email,$password){
    // run select in db
    // if valid generate token & expire time to db
    // then return token to client
    // if invalid , return failed
  $token ="test";
  $res = array("token"=>$token);
  return $res;

}


?>