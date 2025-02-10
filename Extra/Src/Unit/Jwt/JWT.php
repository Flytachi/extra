<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Unit\Jwt;

final class JWT
{
    private static string $secretKey = '';
    private static string $RSAPublicKeyPath = '';
    private static array $algorithms = [
        'HS256' => 'sha256',
        'HS512' => 'sha512',
        'HS384' => 'sha384',
        'RS256' => OPENSSL_ALGO_SHA256,
        'RS384' => OPENSSL_ALGO_SHA384,
        'RS512' => OPENSSL_ALGO_SHA512
    ];

    /**
     * Encodes a payload into a JWT token.
     *
     * @param array $payload The payload to encode.
     * @param int|null $expireTimeInMinutes The expiration time of the token in minutes. Default is null.
     * @param int|null $notBeforeInMinutes The not-before time of the token in minutes. Default is null.
     * @param string $algorithm The algorithm to be used for signing the token. Default is 'HS256'.
     *
     * @return string The encoded JWT token.
     *
     * @throws JWTException If the provided algorithm is not supported or if there is an error during token generation.
     */
    public static function encode(
        array $payload,
        int $expireTimeInMinutes = null,
        int $notBeforeInMinutes = null,
        string $algorithm = 'HS256'
    ): string {
        if (!isset(self::$algorithms[$algorithm])) {
            throw new JWTException("Algorithm '{$algorithm}' not supported");
        }

        try {
            $header = [
                'typ' => 'JWT',
                'alg' => $algorithm
            ];

            if (!is_null($notBeforeInMinutes)) {
                $payload['nbf'] = time() + ($notBeforeInMinutes * 60);
            }

            if (!is_null($expireTimeInMinutes)) {
                $payload['iat'] = time();
                $payload['exp'] = time() + ($expireTimeInMinutes * 60);
            }

            $base64UrlHeader = self::base64UrlEncode(json_encode($header));
            $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

            if (in_array($algorithm, ['RS256', 'RS384', 'RS512'])) {
                $privateKey = openssl_get_privatekey(file_get_contents(self::$RSAPublicKeyPath));
                openssl_sign(
                    "$base64UrlHeader.$base64UrlPayload",
                    $signature,
                    $privateKey,
                    self::$algorithms[$algorithm]
                );
                openssl_free_key($privateKey);
            } else {
                $secret = self::$secretKey;
                $signature = hash_hmac(
                    self::$algorithms[$algorithm],
                    "$base64UrlHeader.$base64UrlPayload",
                    $secret,
                    true
                );
            }

            $base64UrlSignature = self::base64UrlEncode($signature);
            return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
        } catch (\Throwable $exception) {
            throw new JWTException("Encoding Error '{$exception->getMessage()}");
        }
    }

    /**
     * Decodes a JSON Web Token (JWT) and returns the payload.
     *
     * @param string $token The JWT to decode.
     * @return array The decoded payload as an associative array.
     * @throws JWTException if the token is not yet valid or has expired, if the algorithm is not supported,
     *                   or if the token signature could not be verified.
     */
    public static function decode(string $token): array
    {
        try {
            list($headerEnc, $payloadEnc, $signatureEnc) = explode('.', $token);

            $header = json_decode(self::base64UrlDecode($headerEnc), true);
            $payload = json_decode(self::base64UrlDecode($payloadEnc), true);

            if (isset($payload['nbf']) && time() < $payload['nbf']) {
                throw new JWTException('The token is not yet valid');
            }

            if (isset($payload['exp']) && $payload['exp'] <= time()) {
                throw new JWTException('Token has expired');
            }

            if (!isset(self::$algorithms[$header['alg']])) {
                throw new JWTException("Algorithm '{$header['alg']}' not supported");
            }
            $algorithm = $header['alg'];

            if (in_array($algorithm, ['RS256', 'RS384', 'RS512'])) {
                $publicKey = openssl_get_publickey(file_get_contents(self::$RSAPublicKeyPath));
                $result = openssl_verify(
                    "$headerEnc.$payloadEnc",
                    self::base64UrlDecode($signatureEnc),
                    $publicKey,
                    self::$algorithms[$algorithm]
                );
                openssl_free_key($publicKey);
                if ($result !== 1) {
                    throw new JWTException('Invalid signature. The token signature could not be verified');
                }
            } else {
                $secret = self::$secretKey;
                $signatureCheck = self::base64UrlEncode(hash_hmac(
                    self::$algorithms[$algorithm],
                    "$headerEnc.$payloadEnc",
                    $secret,
                    true
                ));
                if ($signatureCheck !== $signatureEnc) {
                    throw new JWTException('Invalid signature. The token signature could not be verified');
                }
            }

            return $payload;
        } catch (\Throwable $exception) {
            throw new JWTException("Decoding Error '{$exception->getMessage()}");
        }
    }

    /**
     * Encodes the given text using the URL-safe Base64 encoding.
     *
     * @param mixed $text The text to encode. It can be of any type.
     *
     * @return string The encoded text.
     */
    private static function base64UrlEncode(string $text): string
    {
        return rtrim(strtr(base64_encode($text), '+/', '-_'), '=');
    }

    /**
     * Decodes a base64 URL-encoded string into its original form.
     *
     * @param string $text The base64 URL-encoded string to decode.
     *
     * @return false|string Returns the original decoded string. Returns false if the decoding fails.
     */
    private static function base64UrlDecode(string $text): false|string
    {
        return base64_decode(str_pad(strtr($text, '-_', '+/'), strlen($text) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * Sets the secret key used for encryption and decryption.
     *
     * @param string $secretKey The secret key to set. It should be a string.
     *
     * @return void
     */
    public static function setSecretKey(string $secretKey): void
    {
        self::$secretKey = $secretKey;
    }

    /**
     * Sets the path to the RSA public key file.
     *
     * @param string $RSAPublicKeyPath The path to the RSA public key file. It should be a string.
     *
     * @return void
     */
    public static function setRSAPublicKeyPath(string $RSAPublicKeyPath): void
    {
        self::$RSAPublicKeyPath = $RSAPublicKeyPath;
    }
}
