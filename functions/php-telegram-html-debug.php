<?php /** @noinspection PhpUnused */

declare(strict_types=1);

use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextualizedDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class TD extends GlobalSingleton
{
    public static null|string|int $token = null;
    public static null|string|int $chat_id = null;
    public static null|string|int $message_thread_id = null;
}

function tdPassThrough($varDump, $caption = null)
{
    td($varDump, $caption);
    return $varDump;
}

function td($varDump = null, $caption = null): bool|TD|string|null
{
    if (!func_num_args()) {
        return TD::one();
    }

    if (!TD::$token || !TD::$chat_id) {
        return null;
    }

    if (function_exists('telegram_debug')) {
        return telegram_debug(
            TD::$token,
            TD::$chat_id,
            $varDump,
            $caption,
            TD::$message_thread_id
        );
    }

    return null;
}


/** @noinspection PhpMissingReturnTypeInspection */
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
            . (!$messageThreadId ? '' : "&message_thread_id=$messageThreadId")
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

        } catch (Throwable) {
            // Silence
        }

        unlink($tmpFile);

        curl_close($ch);

        return $result;

    } catch (Throwable) {
        // Silence
        return null;
    }
}

function trm(): void
{
    if (function_exists('telegram_remove_old_debug')) {
        telegram_remove_old_debug();
    }
}

function telegram_remove_old_debug(): void
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

                } catch (Throwable) {
                    // Silence
                }
            }
        }
    }
    @unlink(__DIR__ . '/old');
}

function get_telegram_bot_updates_link(): string
{
    if (!TD::$token) {
        return '';
    }
    return 'https://api.telegram.org/bot' . TD::$token . '/getUpdates';
}

function dump_telegram_bot_updates_link(): void
{
    dump(get_telegram_bot_updates_link());
}
