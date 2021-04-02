<?php
// -- include libarary--------
require '../vendor/autoload.php'; //slim framwork
require '../src/config/db.php'; //db
require '../function/util.php'; //common util functions
require '../function/user.php'; //user functions
// ---------------------------------

// --- HTTP request -------------------------------------
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// ------------------------------------------------------

$app = new \Slim\App;

// --- Test Routes -------------------------------------------------------
$app->get('/', function (Request $req,  Response $res, $args = []) {
    $res = 'wrong method';

    $token = "fd2cf386-b0cf-41c4-9f28-2963b398a51d";
    $res = finduser($token);
    return $res;
});

$app->get('/bc/test', function (Request $req,  Response $res, $args = []) {
  $res = '';
  $url = 'https://api.blockcypher.com/v1/btc/main';
  $res=geturl($url);
  return json_encode($res);
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
    return json_encode(array("msg"=>$res));
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
$app->run();

?>