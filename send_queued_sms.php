<?php

require_once __DIR__.'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

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

echo " [x] Waiting for messages. To exit press CTRL+C\n";

$callback = function($msg) use ($queue_details){
    $message_body = json_decode($msg->body, true);
    
    # TODO: add your code here to send the smses
    // echo ' [x] Received ', $message_body['message'], ' From ', $message_body['number'], "\n";

    if(strlen($message_body['number']) < 10 || strlen($message_body['number']) > 10){
        $status = "failed";
    }else{
        $url = "http://api.nalosolutions.com/bulksms/?username=deksolops&password=deks@l@ps&type=0&dlr=1&destination=";
        $url .= $message_body['number'] . "&source=ANMA&message=" . rawurlencode($message_body['message']);
        $status = file_get_contents($url);
    }

    

    echo ' [x] Message sent to ', $message_body['number'],' status - ',$status,"\n";

    if (!$queue_details['ack_disabled']) {
        $delivery_info = $msg->delivery_info;
        $delivery_info['channel']->basic_ack($delivery_info['delivery_tag']);
    }
};

$channel->basic_qos(null, 1, null);

$channel->basic_consume(
    $queue_details['message_queue'], '', false, $queue_details['ack_disabled'],
    false, false, $callback
);

while($channel->is_consuming()) {
    $channel->wait();
}
