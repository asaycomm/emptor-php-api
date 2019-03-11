<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Goutte\Client as Client;
use GuzzleHttp\Client as GuzzleClient;
use Emptor\Corporate\App as Corporate;
use GuzzleHttp\Cookie\FileCookieJar;

$emptor = new Corporate();
$emptor->setBaseUrl("");
$emptor->setUsername("");
$emptor->setPassword("");
$client = $emptor->setClient(new Client());

$guzzleClient = $emptor->setGuzzleClient(new \GuzzleHttp\Client(['timeout' => 90,'verify' => false,'cookies' => true,'allow_redirects' => false]));
$client->setClient($guzzleClient);
$emptor->initialize();