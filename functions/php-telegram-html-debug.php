<?php

use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextualizedDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

if (!function_exists('telegram_debug')) {

    function telegram_debug($varDump, $caption = null)
    {
        try {
            $ch = curl_init();

            curl_setopt(
                $ch,
                CURLOPT_URL,
                "https://api.telegram.org/bot"
                . constant('TELEGRAM_HTML_DEBUG_BOT_TOKEN')
                . "/sendDocument?chat_id="
                . constant('TELEGRAM_HTML_DEBUG_CHAT_ID')
            );

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $dumper = new ContextualizedDumper(
                new HtmlDumper(),
                [new SourceContextProvider()]
            );

            $cloner = new VarCloner();
            $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);

            $handler = function ($var) use ($cloner, $dumper) {
                $dumper->dump($cloner->cloneVar($var));
            };

            ob_start();
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

            unlink($tmpFile);

            curl_close($ch);

            return $result;

        } catch (Throwable $e) {
            // Silence
            return null;
        }
    }
}
