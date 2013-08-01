<?php
include 'web.php';
include 'webscanner.php';
include 'storage.php';

error_reporting(E_ERROR);
libxml_use_internal_errors(true);
/**
 * Домен за рамки которого не нужно выходить (мы не же не хотим весь интернет сканировать)
 */
$domain = 'phphighload.com';
$imageDomain = 'dropbox.com';
/**
 * Подключаемся к брокеру и точке обмена сообщениями
 */
$rabbit = new AMQPConnection(array('host' => '127.0.0.1', 'port' => '5672', 'login' => 'guest', 'password' => 'guest'));
$rabbit->connect();
$channel = new AMQPChannel($rabbit);
$queue = new AMQPExchange($channel);
$queue->setName('amq.direct');
/**
 * Добавляем очередь откуда будем брать страницы, которые нужно проиндексировать
 */
$q = new AMQPQueue($channel);
$q->setName('pages_to_scan');
$q->declare();
$q->bind('amq.direct', 'scan');
/**
 * Индексируем, пока в очереди не закончатся сообщения
 */
while ($page = $q->get()) {
	if (!is_object($page)) {
		continue;
	}
	$url = $page->getBody();
	echo "Scanning: $url\n";
	$scanner = new WebScanner($url);
	$links = $scanner->getAllLinks();
	foreach ($links as $link) {
		/**
	 	 * Если страница относится к указанному домену и еще не была проиндексирована -- добавляем ее в очередь на индексацию
	 	 */
		if (strpos($link, $domain) !== FALSE && !Storage::getInstance()->containsPage($link)) {
			$queue->publish($link, 'scan');
		}
	}
	/**
	 * Также если на странице есть картинки добавляем их для анализа
	 */
	$images = $scanner->getAllImages();
	foreach ($images as $image) {
		/**
	 	 * Если картинка относится к указанному домену и еще не была проанализирована -- добавляем ее в очередь
	 	 */
		if (strpos($image, $imageDomain) !== FALSE && !Storage::getInstance()->containsImage($image)) {
			$queue->publish($image, 'analyze_image');
		}
	}
	/**
	 * Текущую страницу тоже в очередь для анализации
	 */
	$queue->publish($url, 'analyze_page');
	$q->ack($page->getDeliveryTag());
}

$rabbit->disconnect();
