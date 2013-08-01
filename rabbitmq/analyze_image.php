<?php
include 'web.php';
include 'image_analyzer.php';
include 'storage.php';
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
$q->setName('images_to_scan');
$q->declare();
$q->bind('amq.direct', 'analyze_image');
/**
 * Обрабатываем пока в очереди не закончатся сообщения
 */
while (true) {
	$image = $q->get();
	if ($image) {
		$url = $image->getBody();
		echo "Checking: $url\n";
		$analyzer = new ImageAnalyzer($url);
		/**
		 * Если картинка еще не была проанализирована, обрабатываем и добавляем в индекс
		 */
		$analyzer->analyze();
		$q->ack($image->getDeliveryTag());
	} else sleep(1);
}

$rabbit->disconnect();