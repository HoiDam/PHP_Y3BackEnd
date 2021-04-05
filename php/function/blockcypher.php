<?php

// --------- commitTransactionLibrary ----------
use BlockCypher\Api\TX;
use BlockCypher\Auth\SimpleTokenCredential;
use BlockCypher\Client\TXClient;

// ----------------------------------------------
use BlockCypher\Rest\ApiContext;
// --------------------------------------------



// --- Main -------------------------------------
function genApiContext(){
    $apiContext = ApiContext::create(
        'test3', 'btc', 'v1',
        new SimpleTokenCredential("7a66a3ce406e4871b7694b4d24abca13"),
        array('mode' => 'sandbox', 'log.LogEnabled' => true, 'log.FileName' => 'BlockCypher.log', 'log.LogLevel' => 'DEBUG')
        );
    return $apiContext;
}


function commitTransaction($apiContext,$i_address,$o_address,$value,$privateKeys){
    try{
        $tx = new TX();

        $input = new \BlockCypher\Api\TXInput();
        $input->addAddress($i_address);
        // $input->addAddress("mghMPCQ1ZnaC745b18wa91gNiQX4RyqRZC");

        $tx->addInput($input);
        $output = new \BlockCypher\Api\TXOutput();
        $output->addAddress($o_address);
        // $output->addAddress("mhv4shayymusncENLCyatPBevGgA1joZci");
        $tx->addOutput($output);

        $output->setValue(1000); // Satoshis

        // For Sample Purposes Only.
        $request = clone $tx;

        $txClient = new TXClient($apiContext);
        $txSkeleton = $txClient->create($tx);

        $privateKeys = array("2e447d8db07b4fcbb74064787b60fdd04dd7f30fc2be427457c795da246e3140");
        $txSkeleton = $txClient->sign($txSkeleton, $privateKeys);
        $txSkeleton = $txClient->send($txSkeleton);
        return msgPack("success");
    }
    catch (Exception $e){
        return msgPack("error",$e);
    }
}

//--------------------------------

function addWallet($token){
    $user_id = finduser($token);
    if ($user_id ==-1){
        return msgPack("failed","wrong_token");
    }
    $sql = "SELECT wallet_user_count FROM db_bitcoin.wallet WHERE user_id = '$user_id' ORDER BY wallet_user_count DESC LIMIT 1 ";
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query( $sql );
        $count = $stmt->fetch()["wallet_user_count"];
        if (is_null($count)){ //check find or not
            $count = 1 ; //not found
        } 
        else{
            $count = $count + 1;
        }
        $db = null; // clear db object
                
      } catch( PDOException $e ) {
        return msgPack("failed","unexpected error 1");
    }

    $data = [
        'user_id' => $user_id,
        'wallet_user_count' => $count
    ];
    $sql = "INSERT INTO db_bitcoin.wallet (user_id , wallet_user_count) VALUES (:user_id,:wallet_user_count) ";
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare( $sql );
        $stmt->execute($data);
        $db = null; // clear db object
                
      } catch( PDOException $e ) {
        return msgPack("failed",$e);
    }
    return msgPack("success","wallet {$count} created");
}

function listWallet($token){
    $user_id = finduser($token);
    if ($user_id ==-1){
        return msgPack("failed","wrong_token");
    }
    $sql = "SELECT wallet_user_count , custom_desc FROM db_bitcoin.wallet WHERE user_id = '$user_id' AND active = 1 ORDER BY wallet_user_count DESC ";
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query( $sql );
        $wallets = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        if (is_null($wallets)){
            return msgPack("success","No records");
        }
        else{
            return msgPack("success",$wallets);
        }
        $db = null; // clear db object
                
      } catch( PDOException $e ) {
        return msgPack("failed",$e);
    }
}

function deleteWallet($token,$count){
    $user_id = finduser($token);
    if ($user_id ==-1){
        return msgPack("failed","wrong_token");
    }
    $wallet_id = findWallet($user_id,$count);

    $sql = "DELETE FROM db_bitcoin.address WHERE wallet_id = ? ; UPDATE db_bitcoin.wallet SET active = 0 WHERE wallet_id = ? ;";
    
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare( $sql );
        $stmt->execute([$wallet_id,$wallet_id]);
        $db = null; // clear db object
        return msgPack("success","deleted");
                
      } catch( PDOException $e ) {
        return msgPack("failed",$e);
    }
}

function findWallet($user_id,$count){
    $sql = "SELECT wallet_id FROM wallet WHERE user_id = '$user_id' AND wallet_user_count = '$count' LIMIT 1 ";
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query( $sql );
        $wallet_id = $stmt->fetch();
        
        if (is_null($wallet_id)){
            return -1;
        }
        else{
            return $wallet_id["wallet_id"];
        }
        $db = null; // clear db object
                
      } catch( PDOException $e ) {
        return msgPack("failed",$e);
    }
}

//--------------------------------



function spawnAddress($apiContexts){
    try {
        $addressKeyChain = posturl("https://api.blockcypher.com/v1/btc/test3/addrs",array());
        return $addressKeyChain; 
    }catch(Exception $e){
        return -1; //failed
    }
}


function addAddress($apiContexts,$token,$count){
    $user_id = finduser($token);
    if ($user_id ==-1){
        return msgPack("failed","wrong_token");
    }
    $wallet_id = findWallet($user_id,$count);
    if ($wallet_id ==-1){
        return msgPack("failed","wrong_wallet");
    }
    $addressDict = spawnAddress($apiContexts);
    if ($addressDict ==-1){
        return msgPack("failed","unable to get");
    }
    // $addressData = "addresssss";
    // $privateData = "privatessss";
    // $publicData = "publicssss";
    // $wifData = "wifssss";
    $addressData = $addressDict["address"];
    $privateData = $addressDict["private"];
    $publicData = $addressDict["public"];
    $wifData = $addressDict["wif"];
    
    $sql = "INSERT INTO db_bitcoin.address (addressData ,privateData,publicData,wifData,wallet_id) VALUES (?,?,?,?,?) ";
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare( $sql );
        $stmt->execute([$addressData,$privateData,$publicData,$wifData, $wallet_id]);
        $db = null; // clear db object
        return msgPack("success",$addressData);
                
      } catch( PDOException $e ) {
        return msgPack("failed",$e);
    }
}

function listAddress($token,$count){
    $user_id = finduser($token);
    if ($user_id ==-1){
        return msgPack("failed","wrong_token");
    }
    $wallet_id = findWallet($user_id,$count);
    if ($wallet_id ==-1){
        return msgPack("failed","wrong_wallet");
    }
    $sql = "SELECT addressData FROM db_bitcoin.address WHERE wallet_id = '$wallet_id' ; ";
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query( $sql );
        $addresses = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        if (is_null($addresses)){
            return msgPack("success","No records");
        }
        else{
            return msgPack("success",$addresses);
        }
        $db = null; // clear db object
                
      } catch( PDOException $e ) {
        return msgPack("failed",$e);
    }

}

function detailAddress($address){
    $token = "7a66a3ce406e4871b7694b4d24abca13";
    try{
        $addressBalance = geturl("https://api.blockcypher.com/v1/btc/test3/addrs/{$address}/balance?token={$token}");
        return msgPack("success",$addressBalance);
    }
    catch(Exception $e){
        return msgPack("failed",$e);
    }
}

function deleteAddress($token,$count,$address){
    $user_id = finduser($token);
    if ($user_id ==-1){
        return msgPack("failed","wrong_token");
    }
    $wallet_id = findWallet($user_id,$count);
    if ($wallet_id ==-1){
        return msgPack("failed","wrong_wallet");
    }
    $sql = "DELETE FROM db_bitcoin.address WHERE wallet_id = ? AND addressData = ? ;";
    
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare( $sql );
        $stmt->execute([$wallet_id,$address]);
        $db = null; // clear db object
        return msgPack("success",$address);
                
      } catch( PDOException $e ) {
        return msgPack("failed",$e);
    }
}

?>
