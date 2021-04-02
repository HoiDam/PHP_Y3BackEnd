<?php
// -- include libarary--------
require '../vendor/autoload.php'; //slim framwork
require '../src/config/db.php'; //db
require '/util.php'; //common util functions

// ---------------------------------

// --- HTTP request -------------------------------------
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// ------------------------------------------------------


$app = new \Slim\App;

// --- Routes -------------------------------------------------------
$app->get('/', function (Request $req,  Response $res, $args = []) {
    $res = 'wrong method';

    $token = "fd2cf386-b0cf-41c4-9f28-2963b398a51d";
    $res = finduser($token);
    return $res;
});

$app->get('/blockcypher', function (Request $req,  Response $res, $args = []) {
  $res = '';

  $token = '7a66a3ce406e4871b7694b4d24abca13';

  return $res;
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
// ------------------------------------------------------------------

$app->run();

?>