<?php
include 'web.php';
include 'page_analyzer.php';
include 'storage.php';

error_reporting(E_ERROR);
libxml_use_internal_errors(true);
/**
 * Подключаемся к брокеру и точке обмена сообщениями
 */
$rabbit = new AMQPConnection(array('host' => '127.0.0.1', 'port' => '5672', 'login' => 'guest', 'password' => 'guest'));
$rabbit->connect();
$channel = new AMQPChannel($rabbit);
$queue = new AMQPExchange($channel);
$queue->setName('amq.direct');
/**
 * Добавляем очередь откуда будем брать страницы
 */
$q = new AMQPQueue($channel);
$q->setName('pages_to_scan');
$q->declare();
$q->bind('amq.direct', 'analyze_page');
/**
 * Обрабатываем пока в очереди не закончатся сообщения
 */
while (true) {
	$page = $q->get();
	if ($page) {
		$url = $page->getBody();
		echo "Parsing: $url\n";
		$analyzer = new PageAnalyzer($url);
		/**
		 * Если страница еще не была проанализирована, обрабатываем и добавляем в индекс
		 */
		$analyzer->analyze();	
		$q->ack($page->getDeliveryTag());
	} else sleep(1);
}

$rabbit->disconnect();