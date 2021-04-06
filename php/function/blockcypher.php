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

//--------------------------------
function checkBelongs($address){
    $sql = "SELECT * FROM db_bitcoin.wallet WHERE wallet_id = (SELECT wallet_id FROM db_bitcoin.address WHERE addressData = '$address' ) LIMIT 1;";
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query( $sql );
        $dbFetched = $stmt->fetch()["user_id"];
        
        if (is_null($dbFetched)){
            return -1;
        }
        else{
            return $dbFetched;
        }
        $db = null; // clear db object
                
      } catch( PDOException $e ) {
        return false;
    }
}

function createTransaction($input){
    try {
        $token = $input['token'];
        $from_address = $input["from_address"];
        $to_address = $input["to_address"];
        $method = $input["method"];
        $bitcoin_amount = $input["bitcoin_amount"];
        $fund_amount = $input["fund_amount"];
        $current = getCurrentTime();
    }
    catch (Exception $e){
        return msgPack("failed","parameters missing");
    }
    $user_id = finduser($token);
    if ($user_id ==-1){
        return msgPack("failed","wrong_token");
    }

    if ($user_id!=checkBelongs($from_address)){
        return msgPack("failed","you dont own the address");
    }

    $to_id = checkBelongs($to_address);
    if ($to_id==-1){
        return msgPack("failed","it does not belongs to in this system");
    }

    if ($bitcoin_amount <=0 || gettype($bitcoin_amount)!="int"){
        return msgPack("failed","wrong bitcoin amount");
    }

    if ($fund_amount <=0 || gettype($fund_amount)!="int"){
        return msgPack("failed","wrong fund amount");
    }

    if ($method=="buy"){
        if (detailFund($user_id)<$fund_amount){
            return msgPack("failed","not enough funds");
        }
    }else if ($method=="sell"){
        $fetched = detailAddress($from_address);
        if ($fetched["status"]=="failed"){
            return msgPack("failed","api broken");
        }
        else{
             $amount = $fetched["status"];
        }
        if ($amount<$bitcoin_amount){
            return msgPack("failed","not enough bitcoins");
        }
    }
    else{
        return msgPack("failed","wrong method");
    } 
    $sql = "INSERT INTO db_bitcoin.transaction (from_address , to_address ,method ,bitcoin_amount,fund_amount,t_status,from_id,to_id,start_time) VALUES (?,?,?,?,?,?,?,?,?) ";
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->prepare( $sql );
        $stmt->execute([$from_address,$to_address,$method,$bitcoin_amount,$fund_amount,'requested',$user_id,$to_id,$current]);
        $db = null; // clear db object
                
        } catch( PDOException $e ) {
        return msgPack("failed",$e);
    }
    return msgPack("success","tranaction created");
}

function listTransaction($token){
    $user_id = finduser($token);
    if ($user_id ==-1){
        return msgPack("failed","wrong_token");
    }
    $sql = "SELECT * FROM db_bitcoin.transaction WHERE from_id = '$user_id' OR to_id = '$user_id' ORDER BY start_time DESC";
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

function actionTransaction($token,$transaction_id,$action){
    $user_id = finduser($token);
    if ($user_id ==-1){
        return msgPack("failed","wrong_token");
    }

    $sql = "SELECT * FROM db_bitcoin.transaction WHERE transaction_id = '$transaction_id' LIMIT 1;";
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query( $sql );
        $transaction = $stmt->fetch();
        
        if (is_null($transaction)){
            return msgPack("failed","No transaction found");
        }
        $db = null; // clear db object
                
      } catch( PDOException $e ) {
        return msgPack("failed",$e);
    }

    if ($transaction["t_status"]!="requested"){
        return msgPack("failed","The transaction is ended ");
    }
    
    
    if ($action == "accept"){
        if ($user_id == $transaction["to_id"]){
            
            $input_address = "";
            $output_address = "";
            $fund_amount = (int)$transaction["fund_amount"];
            $bitcoin_amount = (int)$transaction["bitcoin_amount"];
            $privateData = "";

            if ($transaction["method"] == "buy"){
                
                $input_address = $transaction["to_address"];
                $output_address = $transaction["from_address"];

                if (detailFund($transaction["from_id"])<$fund_amount){
                    
                    return msgPack("failed","from user do not have enough funds");
                }

                $fetched = detailAddress($input_address);
                if ($fetched["status"]=="failed"){
                    return msgPack("failed","api broken");
                }else{
                    $amount = $fetched["msg"]["balance"];
                }
                
                if ($amount<$bitcoin_amount){
                    return msgPack("failed","to user do not have enough bitcoins");
                }
            }else if($transaction["method"] == "sell"){

                $input_address = $transaction["from_address"];
                $output_address = $transaction["to_address"]; 

                if ( detailFund($transaction["to_id"]) < $fund_amount){
                    return msgPack("failed","to user do not have enough funds");
                }

                $fetched = detailAddress($input_address);
                if ($fetched["status"]=="failed"){
                    return msgPack("failed","api broken");
                }else{
                    $amount = $fetched["msg"]["balance"];
                }

                if ($amount<$bitcoin_amount){
                    return msgPack("failed","from user do not have enough bitcoins");
                }
            }
            $fetchedmsg = findPrivate($input_address);
            if ($fetchedmsg["status"]=="failed"){
                return $fetchedmsg;
            }
            $privateData = $fetchedmsg["msg"]["privateData"];

            $reason=commitTransaction($input_address,$output_address,$bitcoin_amount,$privateData);
            if ($reason!="success"){
                return msgPack("failed",$reason);
            }

            
            if ($transaction["method"] == "buy"){
                func_editFunds($transaction["from_id"],"minus",$fund_amount);
                func_editFunds($transaction["to_id"],"add",$fund_amount);
            }else if($transaction["method"] == "sell"){
                func_editFunds($transaction["from_id"],"add",$fund_amount);
                func_editFunds($transaction["to_id"],"minus",$fund_amount);
            }
            $current = getCurrentTime();
            $sql = "UPDATE db_bitcoin.transaction SET t_status = 'transacted' , finish_time = '$current' WHERE transaction_id = '$transaction_id' ;";
            try {
                $db = new db();
                $db = $db->connect();
                $stmt = $db->prepare( $sql );
                $stmt->execute();
                $db = null; // clear db object
                return msgPack("success","transacted");
                        
            } catch( PDOException $e ) {
                return msgPack("failed",$e);
            }
        }
        else
            return msgPack("failed","invalid user");
    }else if ($action == "decline"){
        if ($user_id == $transaction["to_id"]){
            $sql = "UPDATE db_bitcoin.transaction SET t_status = 'declined' WHERE transaction_id = '$transaction_id' ;";
    
            try {
                $db = new db();
                $db = $db->connect();
                $stmt = $db->prepare( $sql );
                $stmt->execute();
                $db = null; // clear db object
                return msgPack("success","declined");
                        
            } catch( PDOException $e ) {
                return msgPack("failed",$e);
            }
        }
        else
            return msgPack("failed","invalid user");
    }else if ($action == "cancel"){
        if ($user_id == $transaction["from_id"]){
            $sql = "UPDATE db_bitcoin.transaction SET t_status = 'canceled' WHERE transaction_id = '$transaction_id' ;";
    
            try {
                $db = new db();
                $db = $db->connect();
                $stmt = $db->prepare( $sql );
                $stmt->execute();
                $db = null; // clear db object
                return msgPack("success","canceled");
                        
            } catch( PDOException $e ) {
                return msgPack("failed",$e);
            }
        }
        else
            return msgPack("failed","invalid user");
    }else
    {
        return msgPack("failed","wrong action");
    }



}

function commitTransaction($input_address,$output_address,$value,$privateData){
    try{
        $apiContext = ApiContext::create(
            'test3', 'btc', 'v1',
            new SimpleTokenCredential("7a66a3ce406e4871b7694b4d24abca13"),
            array('mode' => 'sandbox', 'log.LogEnabled' => true, 'log.FileName' => 'BlockCypher.log', 'log.LogLevel' => 'DEBUG')
            );
        $tx = new TX();

        $input = new \BlockCypher\Api\TXInput();
        $input->addAddress($input_address);
        // $input->addAddress("mhv4shayymusncENLCyatPBevGgA1joZci");
        $tx->addInput($input);

        $output = new \BlockCypher\Api\TXOutput();
        $output->addAddress($output_address);
        // $output->addAddress("mghMPCQ1ZnaC745b18wa91gNiQX4RyqRZC");
        $tx->addOutput($output);

        // For Sample Purposes Only.
        $request = clone $tx;

        $output->setValue($value); // Satoshis

        $txClient = new TXClient($apiContext);
        $txSkeleton = $txClient->create($tx);

        // $privateData = "ac0833632f370eed2aa2d89798c17d58d4734622bc54383e02eeac77ded8da79";
        $privateKeys = array($privateData);
        $txSkeleton = $txClient->sign($txSkeleton, $privateKeys);
        $txSkeleton = $txClient->send($txSkeleton);
        return "success";
    }
    catch (Exception $e){
        return $e;
    }
}

function findPrivate($addressData){
    $sql = "SELECT privateData FROM db_bitcoin.address WHERE addressData = '$addressData' LIMIT 1; ";
    try {
        $db = new db();
        $db = $db->connect();
        $stmt = $db->query( $sql );
        $privateData = $stmt->fetch();
        
        if (is_null($privateData)){
            return msgPack("failed","No records");
        }
        else{
            return msgPack("success",$privateData);
        }
        $db = null; // clear db object
                
      } catch( PDOException $e ) {
        return msgPack("failed",$e);
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
        if (is_null($addressBalance)){
            return msgPack("failed","not found");
        }
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

//--------------------------------




?>
