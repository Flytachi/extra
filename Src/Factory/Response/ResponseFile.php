<?php

namespace Extra\Src\Factory\Response;

use Extra\Src\Log\Log;
use Extra\Src\Sheath\File\XML;


class ResponseFile
{
    final static function json(string|array $jsonData, string $fileName, bool $isAttachment = false): never
    {
        if (is_array($jsonData)) $jsonData = json_encode($jsonData);

        header('Content-Type: application/json');
        header('Content-Disposition: ' . ($isAttachment ? 'attachment' : 'inline') . '; filename=' . basename($fileName, '.json') . '.json');
        header('Expires: 0');
        header('Pragma: public');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . strlen($jsonData));

        Log::trace($_SERVER['REQUEST_URI'] . '[200] => ' . $fileName .  ' => ' . $jsonData);
        file_put_contents('php://output', $jsonData);
        die;
    }

    final static function xml(string|array|\SimpleXMLElement $xmlData, string $fileName, bool $isAttachment = false): never
    {
        if ($xmlData instanceof \SimpleXMLElement) $xmlData = $xmlData->asXML();
        else if (is_array($xmlData)) $xmlData = XML::arrayToXml($xmlData);

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

    final static function csv(array $csvData, string $fileName, bool $isAttachment = false): never
    {
        $fileBody = fopen('php://temp', 'r+b');
        foreach ($csvData as $line) fputcsv($fileBody, (array) $line);
        rewind($fileBody);
        $csvContent = stream_get_contents($fileBody);
        fclose($fileBody);

        header('Content-Type: text/csv');
        header('Content-Disposition: ' . ($isAttachment ? 'attachment' : 'inline') . '; filename=' . basename($fileName, '.csv') . '.csv');
        header('Expires: 0');
        header('Pragma: public');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . strlen($csvContent));

        Log::trace($_SERVER['REQUEST_URI'] . '[200] CSV => ' . $fileName .  ' => ' . $csvContent);
        file_put_contents('php://output', $csvContent);
        die;
    }

    final static function txt(string $txtData, string $fileName, bool $isAttachment = false): never
    {
        header('Content-Type: text/plain');
        header('Content-Disposition: ' . ($isAttachment ? 'attachment' : 'inline') . '; filename=' . basename($fileName, '.txt') . '.txt');
        header('Expires: 0');
        header('Pragma: public');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . strlen($txtData));

        Log::trace($_SERVER['REQUEST_URI'] . '[200] TXT => ' . $fileName .  ' => ' . $txtData);
        file_put_contents('php://output', $txtData);
        die;
    }

    final static function binary($binaryData, string $fileName, string $mimeType = 'application/octet-stream', bool $isAttachment = false): never
    {
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: ' . ($isAttachment ? 'attachment' : 'inline') . '; filename=' . basename($fileName));
        header('Expires: 0');
        header('Pragma: public');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . strlen($binaryData));

        Log::trace($_SERVER['REQUEST_URI'] . '[200] BINARY => ' . $fileName);
        file_put_contents('php://output', $binaryData);
        die;
    }
}