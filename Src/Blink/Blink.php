<?php

namespace Extra\Src\Blink;


use Extra\Src\Enum\HttpCode;
use Extra\Src\Log\Log;

class Blink
{
    const ACCEPT_JSON = 'Accept: application/json';
    const CONTENT_JSON = 'Content-Type: application/json';

    public static function authBearer(string $token): string
    {
        return 'Authorization: Bearer ' . $token;
    }

    private static null|Blink $blink = null;
    private \CurlHandle $curl;
    private int $maxRetry = 1;

    public function __construct()
    {
        $this->curl = curl_init();
    }

    private static function setOption(int $option, mixed $value): void
    {
        if (self::$blink == null) {
            self::$blink = new Blink();
            self::setOption(CURLOPT_RETURNTRANSFER, true);
            self::setOption(CURLOPT_ENCODING, '');
            self::setOption(CURLOPT_MAXREDIRS, 10);
            self::setOption(CURLOPT_TIMEOUT, 0);
            self::setOption(CURLOPT_FOLLOWLOCATION, true);
            self::setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        }
        curl_setopt(self::$blink->curl, $option, $value);
    }

    public static function retry(int $count, int $timeout = 30): static
    {
        self::setOption(CURLOPT_TIMEOUT, $timeout);
        self::$blink->maxRetry = $count;
        return self::$blink;
    }

    public static function headers(string ...$headers): static
    {
        self::setOption(CURLOPT_HTTPHEADER, $headers);
        return self::$blink;
    }

    public static function get(string $url, null|array $params = null): static
    {
        return self::request('GET', $url, $params);
    }

    public static function put(string $url, null|array $params = null): static
    {
        return self::request('PUT', $url, $params);
    }

    public static function post(string $url, null|array $params = null, null|array $body = null): static
    {
        if ($body) self::body($body);
        return self::request('POST', $url, $params);
    }

    public static function delete(string $url, null|array $params = null): static
    {
        return self::request('DELETE', $url, $params);
    }

    public static function patch(string $url, null|array $params = null): static
    {
        return self::request('PATCH', $url, $params);
    }

    public static function request(string $method, string $url, null|array $params = null): static
    {
        self::setOption(CURLOPT_CUSTOMREQUEST, $method);
        if ($params == null) self::setOption(CURLOPT_URL, $url);
        else self::setOption(CURLOPT_URL, $url . '?' . http_build_query($params));
        return self::$blink;
    }

    public static function body(array $body, string $type = 'json'): static
    {
        if ($type == 'json') self::setOption(CURLOPT_POSTFIELDS, json_encode($body));
        else self::setOption(CURLOPT_POSTFIELDS, $body);
        return self::$blink;
    }

    public function send(bool $isThrowable = true): BlinkObject
    {
        $info = curl_getinfo($this->curl);
        $response = null;

        while (true) {
            if ($this->maxRetry == 0) break;

            Log::trace("Blink Send Request: " . $info['url']);
            $response = curl_exec($this->curl);
            if (!$response) {
                if ($this->maxRetry == 1) BlinkError::throw(HttpCode::INTERNAL_SERVER_ERROR, curl_error($this->curl));
                else {
                    --$this->maxRetry;
                    continue;
                }
            };

            $info = curl_getinfo($this->curl);
            if ($isThrowable) {
                if ($info['http_code'] == 0 || 400 <= $info['http_code']) {
                    if ($this->maxRetry > 1 && 500 <= $info['http_code']) {
                        --$this->maxRetry;
                        continue;
                    }
                    BlinkError::throw(HttpCode::INTERNAL_SERVER_ERROR, "Blink Request '{$info['url']}' status => {$info['http_code']}");
                }
            }

            Log::trace("Blink Response: status => " . $info['http_code'] . " response => " . $response);
            break;
        }

        curl_close($this->curl);
        self::$blink = null;
        return new BlinkObject([
            ...$info,
            'response' => $response ?: null
        ]);
    }

}