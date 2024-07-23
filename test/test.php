<?php

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

echo "Hello, Server!";

(Dotenv::createUnsafeImmutable(__DIR__))->load();

define('TELEGRAM_HTML_DEBUG_BOT_TOKEN', $token = getenv('TELEGRAM_HTML_DEBUG_BOT_TOKEN'));
define('TELEGRAM_HTML_DEBUG_CHAT_ID', $chatId = getenv('TELEGRAM_HTML_DEBUG_CHAT_ID'));

$stdClassExample = new stdClass();
$stdClassExample->string = 'a';
$stdClassExample->array = [1, 2, 3];
$stdClassExample->null = null;
$stdClassExample->true = true;
$stdClassExample->false = false;

trm(); // or telegram_remove_old_debug();

// With token & chat for multi channels / bots
telegram_debug($token, $chatId, $stdClassExample, 'Your Debug Caption');

// Token & chat from global constants for mono chat
td($stdClassExample, 'Your Debug Caption');

echo '<br>';
echo "Goodbye, Server!" . PHP_EOL;
