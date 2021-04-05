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

$url = "https://api.blockcypher.com/v1/btc/test3";

$app = new \Slim\App;

// --- Test Routes -------------------------------------------------------
$app->get('/', function (Request $req,  Response $res, $args = []) {
    $status = "failed";
    $res = 'wrong method';
    return json_encode(msgPack($status,$res));
});

$app->get('/api/test', function (Request $req,  Response $res, $args = []) {
	$sql = "SELECT * FROM db_bitcoin.user;";
	try {
    $db = new db();
    $db = $db->connect();
    $stmt = $db->query( $sql );
    // $res = "success" ;
    $res = $stmt->fetch();
    $db = null; // clear db object
  } catch( PDOException $e ) {
    // show error message as Json format
    $res =  $e->getMessage();
  }
    return json_encode(msgPack($res));
});

// --- User Routes -------------------------------------------------------
$app->post('/user/login', function (Request $req, Response $res, $arg){

  $input = $req->getParsedBody();
  $email = $input['email'];
  $password = $input['password'];

  $res = user_login($email,$password);

  return json_encode($res);
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

$app->post('/bc/wallet/delete', function (Request $req,  Response $res, $args ) {
  try {
    $input = $req->getParsedBody();
    $token = $input['token'];
    $count = $input['wallet_user_count'];
  }
  catch (Exception $e){
    return json_encode(msgPack("failed","parameters missing"));
  }
  $apiContexts = genApiContext();
  return json_encode(deleteWallet($apiContexts,$token,$count));

  });
// ---------------------------------------------------------------
$app->run();

?>