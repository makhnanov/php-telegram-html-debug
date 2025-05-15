# What Is IT?
```text
This is library for Easy debug with symfony/var-dumper dd() / dump(), but in html in telegram bot message.
```

# TL;DR;
```shell
composer require --dev makhnanov/php-telegram-html-debug
```
```php
define('TD_BOT_TOKEN', 'YOUR_BOT_TOKEN');
define('TD_CHAT_ID', '-CHAT_ID');
define('TD_CHAT_THREAD_ID', '15');
td($exception, 'Error!');
# Or
telegram_debug($token, $chatId, $varDump, $caption, $messageThreadId);
```

# LongRead
## Step 1

WIP
