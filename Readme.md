# What Is IT?
```text
This is library for Easy debug with symfony/var-dumper dd() / dump(), but in html in telegram bot message.
```

# TL;DR;
```shell
composer require --dev makhnanov/php-telegram-html-debug
```

```php
TD::$token = getenv('DEBUG_BOT_TOKEN');
TD::$chat_id = getenv('DEBUG_BOT_CHAT_ID');
TD::$message_thread_id = getenv('DEBUG_BOT_MESSAGE_THREAD_ID');
td($exception, 'Error!');
# Or
telegram_debug($token, $chatId, $varDump, $caption, $messageThreadId);
```
