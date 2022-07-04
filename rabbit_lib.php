<?php

require_once __DIR__.'/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->required('RABBITMQ_HOST')->notEmpty();
$dotenv->required('RABBITMQ_PORT')->isInteger();
$dotenv->required('RABBITMQ_USERNAME')->notEmpty();
$dotenv->required('RABBITMQ_PASSWORD')->notEmpty();
$dotenv->required('RABBITMQ_QUEUE')->notEmpty();
$dotenv->required('ACK_DISABLED')->allowedValues(['TRUE', 'FALSE']);
$dotenv->required('QUEUE_IS_DURABLE')->allowedValues(['TRUE', 'FALSE']);


function getQueueSettings() {

    $queue_is_durable = TRUE;
    $ack_disabled = FALSE;

    if (getenv('queue_is_durable') == 'FALSE') {
        $queue_is_durable = FALSE;
    }

    if (getenv('ack_disabled') == 'TRUE') {
        $ack_disabled = TRUE;
    }
    return array(
        'host' => getenv("RABBITMQ_HOST"),
        'message_queue' => getenv('RABBITMQ_QUEUE'),
        'queue_username' => getenv('RABBITMQ_USERNAME'),
        'queue_password' => getenv('RABBITMQ_PASSWORD'),
        'queue_port' => getenv('RABBITMQ_PORT'),
        'ack_disabled' => $ack_disabled,
        'queue_is_durable' => $queue_is_durable
    );
}