<?php

use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextualizedDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

if (!function_exists('td')) {
    function td($varDump, $caption = null, $messageThreadId = null)
    {
        if (
            !defined('TELEGRAM_HTML_DEBUG_BOT_TOKEN')
            || !defined('TELEGRAM_HTML_DEBUG_CHAT_ID')
        ) {
            return null;
        }

        if (function_exists('telegram_debug')) {
            telegram_debug(
                constant('TELEGRAM_HTML_DEBUG_BOT_TOKEN'),
                constant('TELEGRAM_HTML_DEBUG_CHAT_ID'),
                $varDump,
                $caption,
                $messageThreadId
            );
        }
    }
}

if (!function_exists('telegram_debug')) {

    function telegram_debug($token, $chatId, $varDump, $caption = null, $messageThreadId = null)
    {
        try {
            $ch = curl_init();

            curl_setopt(
                $ch,
                CURLOPT_URL,
                "https://api.telegram.org/bot"
                . $token
                . "/sendDocument?chat_id="
                . $chatId
                . (!$messageThreadId ?: "&message_thread_id=$messageThreadId")
            );

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $dumper = new ContextualizedDumper(
                new HtmlDumper(),
                [new SourceContextProvider()]
            );

            $cloner = new VarCloner();
            $cloner->setMaxItems(PHP_INT_MAX);
            $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);

            $handler = function ($var) use ($cloner, $dumper) {
                $dumper->dump($cloner->cloneVar($var));
            };

            ob_start();
            if ($caption) {
                echo <<<HTML
<h1 style="color: white;">$caption</h1>
HTML;
            }
            $handler($varDump);
            echo <<<HTML
<style>
body {
    background-color: #18171B;
}
</style>
HTML;
            $htmlData = ob_get_clean();

            $tmpFile = __DIR__ . '/tmp';

            file_put_contents($tmpFile, $htmlData);

            $cFile = new CURLFile($tmpFile, 'text/plain', 'debug.html');

            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                $caption
                    ? [
                    'document' => $cFile,
                    'caption' => $caption,
                ]
                    : [
                    'document' => $cFile,
                ]
            );

            $result = curl_exec($ch);

            try {
                $decoded = json_decode($result, true);
                if (
                    isset($decoded['ok'])
                    && $decoded['ok'] === true
                    && isset($decoded['result']['chat']['id'])
                    && isset($decoded['result']['message_id'])
                ) {
                    $old = @file_get_contents(__DIR__ . '/old');
                    @file_put_contents(
                        __DIR__ . '/old',
                        implode(
                            ';',
                            array_filter([
                                $old ?: '',
                                $decoded['result']['chat']['id'],
                                $decoded['result']['message_id'],
                                $token
                            ])
                        )
                    );
                }

            } catch (Throwable $t) {
                // Silence
            }

            unlink($tmpFile);

            curl_close($ch);

            return $result;

        } catch (Throwable $e) {
            // Silence
            return null;
        }
    }
}

if (!function_exists('trm')) {
    function trm() {
        if (function_exists('telegram_remove_old_debug')) {
            telegram_remove_old_debug();
        }
    }
}

if (!function_exists('telegram_remove_old_debug')) {
    function telegram_remove_old_debug()
    {
        $old = @file_get_contents(__DIR__ . '/old');
        if (!$old) {
            return;
        }

        $explode = explode(';', $old);
        $chunks = array_chunk($explode, 3);

        $bots = [];
        foreach ($chunks as list($chatId, $messageId, $botToken)) {
            $bots[$botToken][$chatId][] = $messageId;
        }

        foreach ($bots as $botToken => $chats) {
            foreach ($chats as $chat => $messages) {
                $chunks = array_chunk($messages, 100);
                foreach ($chunks as $chunk) {
                    try {
                        @file_get_contents(
                            'https://api.telegram.org/bot'
                            . $botToken
                            . '/deleteMessages?chat_id='
                            . $chat
                            . '&message_ids=' . json_encode($chunk)
                        );

                    } catch (Throwable $t) {
                        // Silence
                    }
                }
            }
        }
        @unlink(__DIR__ . '/old');
    }
}

if (!function_exists('get_telegram_bot_updates_link')) {
    function get_telegram_bot_updates_link(): string
    {
        return 'https://api.telegram.org/bot' . constant('TELEGRAM_HTML_DEBUG_BOT_TOKEN') . '/getUpdates';
    }
}

if (!function_exists('dump_telegram_bot_updates_link')) {
    function dump_telegram_bot_updates_link(): void
    {
        dump(get_telegram_bot_updates_link());
    }
}
