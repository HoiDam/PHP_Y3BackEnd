<?php
// -- include libarary--------
require '../php-client/autoload.php'; // blockcypher framework 
require '../vendor/autoload.php'; //slim framwork
require '../src/config/db.php'; //db
require '../function/util.php'; //common util functions
require '../function/user.php'; //user functions
require '../function/blockcypher.php'; //bc functions
// ---------------------------------

// --- HTTP request -------------------------------------
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// ------------------------------------------------------

$app = new \Slim\App;

// --- Test Routes -------------------------------------------------------
$app->get('/', function (Request $req,  Response $res, $args = []) {
    $status = "failed";
    $msg = 'wrong method';
    $res = msgPack($status,$msg);
    return json_encode($res);
});

// --- User Routes -------------------------------------------------------
$app->post('/user/login', function (Request $req, Response $res, $arg){

  try {
    $input = $req->getParsedBody();
    $email = $input['email'];
    $password = $input['password'];
  }
  catch (Exception $e){
    return json_encode(msgPack("failed","parameters missing"));
  }  
  return json_encode(userLogin($email,$password));
});

$app->post('/user/register', function (Request $req, Response $res, $arg){

  try {
    $input = $req->getParsedBody();
    $email = $input['email'];
    $nickname = $input['nickname'];
    $password = $input['password'];
    
  }
  catch (Exception $e){
    return json_encode(msgPack("failed","parameters missing"));
  }  
  return json_encode(userRegister($email,$nickname,$password));
});

$app->post('/user/funds/edit', function (Request $req, Response $res, $arg){
  try {
    $input = $req->getParsedBody();
    $token = $input['token'];
    $method = $input['method'];
    $amount = $input["amount"];
  }
  catch (Exception $e){
    return json_encode(msgPack("failed","parameters missing"));
  }
  
  return json_encode(editFunds($token,$method,$amount));

});

$app->post('/user/info', function (Request $req, Response $res, $arg){

  try {
    $input = $req->getParsedBody();
    $token = $input['token'];
  }
  catch (Exception $e){
    return json_encode(msgPack("failed","parameters missing"));
  }  
  return json_encode(userInfo($token));
});

$app->post('/user/pw/edit', function (Request $req, Response $res, $arg){
  try {
    $input = $req->getParsedBody();
    $email = $input['email'];
    $old_password = $input['old_password'];
    $new_password = $input["new_password"];
  }
  catch (Exception $e){
    return json_encode(msgPack("failed","parameters missing"));
  }
  
  return json_encode(changePW($email,$old_password,$new_password));

});

// -----------------------------------------------------------------------

// --- blockcypher Routes -------------------------------------------------------
$app->post('/bc/wallet/add', function (Request $req,  Response $res, $args ) {
  try {
    $input = $req->getParsedBody();
    $token = $input['token'];
  }
  catch (Exception $e){
    return json_encode(msgPack("failed","parameters missing"));
  }
  
  return json_encode(addWallet($token));

});

$app->post('/bc/wallet/list', function (Request $req,  Response $res, $args ) {
  try {
    $input = $req->getParsedBody();
    $token = $input['token'];
  }
  catch (Exception $e){
    return json_encode(msgPack("failed","parameters missing"));
  }
  return json_encode(listWallet($token));
  
});

$app->post('/bc/wallet/delete', function (Request $req,  Response $res, $args ) {
  try {
    $input = $req->getParsedBody();
    $token = $input['token'];
    $count = $input['wallet_user_count'];
  }
  catch (Exception $e){
    return json_encode(msgPack("failed","parameters missing"));
  }

  return json_encode(deleteWallet($token,$count));

});

$app->post('/bc/address/add', function (Request $req,  Response $res, $args ) {
    try {
      $input = $req->getParsedBody();
      $token = $input['token'];
      $count = $input["wallet_user_count"];
    }
    catch (Exception $e){
      return json_encode(msgPack("failed","parameters missing"));
    }
    $apiContexts = genApiContext();
    return json_encode(addAddress($apiContexts,$token,$count));
  
});  

$app->post('/bc/address/list', function (Request $req,  Response $res, $args ) {
    try {
      $input = $req->getParsedBody();
      $token = $input['token'];
      $count = $input["wallet_user_count"];
    }
    catch (Exception $e){
      return json_encode(msgPack("failed","parameters missing"));
    }
    return json_encode(listAddress($token,$count));

});    

$app->post('/bc/address/detail', function (Request $req,  Response $res, $args ) {
  try {
    $input = $req->getParsedBody();
    $address = $input["address"];
  }
  catch (Exception $e){
    return json_encode(msgPack("failed","parameters missing"));
  }
  return json_encode(detailAddress($address));

});  

$app->post('/bc/address/delete', function (Request $req,  Response $res, $args ) {
  try {
    $input = $req->getParsedBody();
    $token = $input['token'];
    $count = $input["wallet_user_count"];
    $address = $input["address"];
  }
  catch (Exception $e){
    return json_encode(msgPack("failed","parameters missing"));
  }
  return json_encode(deleteAddress($token,$count,$address));

});  

$app->post('/bc/transaction/create', function (Request $req,  Response $res, $args ) {
  
  $input = $req->getParsedBody();
  
  return json_encode(createTransaction($input));

});  

$app->post('/bc/transaction/list', function (Request $req,  Response $res, $args ) {
  try {
    $input = $req->getParsedBody();
    $token = $input['token'];
  }
  catch (Exception $e){
    return json_encode(msgPack("failed","parameters missing"));
  }
  return json_encode(listTransaction($token));

}); 

$app->post('/bc/transaction/action', function (Request $req,  Response $res, $args ) {
  try {
    $input = $req->getParsedBody();
    $token = $input['token'];
    $transaction_id = $input['transaction_id'];
    $action = $input['action'];
  }
  catch (Exception $e){
    return json_encode(msgPack("failed","parameters missing"));
  }
  return json_encode(actionTransaction($token,$transaction_id,$action));

}); 

// ---------------------------------------------------------------
$app->run();

?>