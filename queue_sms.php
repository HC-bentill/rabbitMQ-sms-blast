<?php
require_once __DIR__.'/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
require_once __DIR__.'/rabbit_lib.php';

$queue_details = getQueueSettings();

$connection = new AMQPStreamConnection(
    $queue_details['host'], $queue_details['queue_port'],
    $queue_details['queue_username'], $queue_details['queue_password']
);

$channel = $connection->channel();

$channel->queue_declare(
    $queue_details['message_queue'], false,
    $queue_details['queue_is_durable'], false, false
);




# TODO: Replace json_encode(array('message'=> 'Hello World!")) with the details of the
# smses to send
//open uploaded csv file with read only mode
$csvFile = fopen('./files/ANMA_2022BILL_SMSBLAST_FINAL_COMMERCIAL.csv', 'r');

// skip first line
// if your csv file have no heading, just comment the next line
fgetcsv($csvFile);

$assembly = "Akuapem North Municipal Assembly";
$assembly_abbrv = "ANMA";
$bill_type = "Commercial Property Rate";
$year = "2022";
$phone_no = "0541214224";



while(($line = fgetcsv($csvFile)) !== FALSE){
    if($line[4] == "NULL"){
        
    }else{
        $msg = new AMQPMessage(
            // json_encode(array('message' => "Dear Customer \nYour ".$line[1]." Bill for ". $line[2]." for ".$line[3]." is GHs".number_format((float)$line[4], 0, '.', '')."; Arrears is GHs".number_format((float)$line[5], 0, '.', '')."; Total Bill is GHs".number_format((float)$line[6], 0, '.', '').". Plz pay at any nearest DEKSOL office, Pay point or collector. Insist on your receipt for any payment made. MoMo#: 0547168973. Call after every MoMo transfer to get your receipt.\nThank U", "number" => $line[0])),
            json_encode(array('message' => "Dear Customer \nGreetings from ".$assembly.".\nYour ".$year." ".$bill_type." Bill for ". $line[2]." is Ghs".number_format((float)$line[4], 0, '.', '').", Arrears is Ghs".number_format((float)$line[5], 0, '.', '')."\nTotal Bill is GHs".number_format((float)$line[6], 0, '.', '').".\nPlz pay at any nearest ".$assembly_abbrv." office, Paypoint or collector. Insist on your receipt for any payment. Plz call ".$phone_no." if you have any issue or if your bill has been paid already for the necessary corrections to be made on your account.\nThank U", "number" => $line[0])),
            array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
        );
    
        $channel->basic_publish($msg, '', $queue_details['message_queue']);
    }

}

echo " [x] Sent\n";

$channel->close();
$connection->close();

