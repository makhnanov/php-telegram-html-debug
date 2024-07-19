<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "Hello, Server!";

define('TELEGRAM_HTML_DEBUG_BOT_TOKEN', '**********:**********');
define('TELEGRAM_HTML_DEBUG_CHAT_ID', -000000000000);

$stdClassExample = new stdClass();
$stdClassExample->string = 'a';
$stdClassExample->array = [1, 2, 3];
$stdClassExample->null = null;
$stdClassExample->true = true;
$stdClassExample->false = false;

telegram_debug($stdClassExample, 'Your Debug Caption');

echo '<br>';
echo "Goodbye, Server!";
