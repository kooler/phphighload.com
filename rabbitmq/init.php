<?php
if (!isset($argv[1])) {
	die('Page url should be specified');
}

$url = $argv[1];
/**
 * Подключаемся к брокеру и точке обмена сообщениями
 */
$rabbit = new AMQPConnection(array('host' => '127.0.0.1', 'port' => '5672', 'login' => 'guest', 'password' => 'guest'));
$rabbit->connect();
$channel = new AMQPChannel($rabbit);
$queue = new AMQPExchange($channel);
$queue->setName('amq.direct');
/**
 * Добавляем стартовую страницу в очередь для индексирования
 */
$q = new AMQPQueue($channel);
$q->setName('pages_to_scan');
$q->declare();
$q->bind('amq.direct', 'scan');

$queue->publish($url, 'scan');