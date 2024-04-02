<?php

namespace Extra\Src\Route;

use Extra\Src\HttpCode;
use Extra\Src\Log\Log;


class ResponseFile
{
    final static function json(string|array $jsonData, string $fileName, bool $isAttachment = false): never
    {
        if (is_array($jsonData)) $fileBody = json_encode($jsonData);

        header('Content-Type: application/json');
        header('Content-Disposition: ' . ($isAttachment ? 'attachment' : 'inline') . '; filename=' . basename($fileName, '.json') . '.json');
        header('Expires: 0');
        header('Pragma: public');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . strlen($fileBody));

        Log::trace($_SERVER['REQUEST_URI'] . '[200] => ' . $fileName .  ' => ' . $fileBody);
        file_put_contents('php://output', $fileBody);
        die;
    }

    final static function xml(string|\SimpleXMLElement $xmlData, string $fileName, bool $isAttachment = false): never
    {
        if ($xmlData instanceof \SimpleXMLElement) $xmlData = $xmlData->asXML();

        header('Content-Type: application/xml');
        header('Content-Disposition: ' . ($isAttachment ? 'attachment' : 'inline') . '; filename=' . basename($fileName, '.xml') . '.xml');
        header('Expires: 0');
        header('Pragma: public');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . strlen($xmlData));

        Log::trace($_SERVER['REQUEST_URI'] . '[200] XML => ' . $fileName .  ' => ' . $xmlData);
        file_put_contents('php://output', $xmlData);
        die;
    }

}