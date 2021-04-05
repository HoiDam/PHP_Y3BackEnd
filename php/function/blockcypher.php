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
        if (is_null($count)) //check find or not
            $count = 1 ; //not found 
        else
            $count = $count + 1;
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
    return msgPack("success");
}

function deleteWallet($apiContexts,$token,$count){
    $user_id = finduser($token);
    if ($user_id ==-1){
        return msgPack("failed","wrong_token");
    }
    $walletName = genWalletID($user_id,$count);

    try{
        $walletClient = new \BlockCypher\Client\WalletClient($apiContexts);
        $result = $walletClient->delete($walletName);
        return msgPack("success",$result);
    }catch(Exception $e){
        return msgPack("failed","cannot reach api");
    }
}

?>
