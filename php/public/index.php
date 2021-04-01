<?php
// --- HTTP request -------------------------------------
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
// ------------------------------------------------------

// # include Slim framwork
require '../vendor/autoload.php';
// -----------------------------------------------------

// -- include DB connection file ---
require '../src/config/db.php';
// ---------------------------------

$app = new \Slim\App;

// --- Routes -------------------------------------------------------
$app->get('/', function (Request $req,  Response $res, $args = []) {
    $res = 'wrong method';
    return $res;
});

$app->get('/api/test', function (Request $req,  Response $res, $args = []) {
	$sql = "SELECT * FROM db_bitcoin.user;";
	try {
 
    $db = new db();
    $db = $db->connect();


    $stmt = $db->query( $sql );
    $res = "success" ;
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