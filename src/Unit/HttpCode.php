<?php

declare(strict_types=1);

namespace Flytachi\Extra\Unit;

/**
 * Enum HttpStatus
 *
 * HttpStatus is a helper enumeration that provides an easy way
 * to handle HTTP status codes. The aim of this enum is to offer
 * a simple interface to work with HTTP response status codes,
 * making the handling of HTTP responses easier and more standardized.
 *
 * @version 2.0
 * @author Flytachi
 * @example echo HttpStatus::getMessage(HttpStatus::OK); // Outputs "OK"
 */
enum HttpCode: int
{
    // 2xx
    case OK = 200;
    case CREATED = 201;
    case ACCEPTED = 202;
    case NON_AUTHORITATIVE_INFORMATION = 203;
    case NO_CONTENT = 204;
    case RESET_CONTENT = 205;
    case PARTIAL_CONTENT = 206;
    case MULTI_STATUS = 207;
    case ALREADY_REPORTED = 208;
    case IM_USED = 226;

    // 3xx
    case MULTIPLE_CHOICES = 300;
    case MOVED_PERMANENTLY = 301;
    case FOUND = 302;
    case SEE_OTHER = 303;
    case NOT_MODIFIED = 304;
    case USE_PROXY = 305;
    case TEMPORARY_REDIRECT = 307;
    case PERMANENT_REDIRECT = 308;

    // 4xx
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case PAYMENT_REQUIRED = 402;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case NOT_ACCEPTABLE = 406;
    case PROXY_AUTHENTICATION_REQUIRED = 407;
    case REQUEST_TIMEOUT = 408;
    case CONFLICT = 409;
    case GONE = 410;
    case LENGTH_REQUIRED = 411;
    case PRECONDITION_FAILED = 412;
    case REQUEST_ENTITY_TOO_LARGE = 413;
    case REQUEST_URI_TOO_LONG = 414;
    case UNSUPPORTED_MEDIA_TYPE = 415;
    case REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    case EXPECTATION_FAILED = 417;
    case IM_A_TEAPOT = 418;
    case AUTHENTICATION_TIMEOUT_NOT_IN_RFC_2616 = 419;
    case MISDIRECTED_REQUEST = 421;
    case UNPROCESSABLE_ENTITY = 422;
    case LOCKED = 423;
    case FAILED_DEPENDENCY = 424;
    case TOO_EARLY = 425;
    case UPGRADE_REQUIRED = 426;
    case PRECONDITION_REQUIRED = 428;
    case TOO_MANY_REQUESTS = 429;
    case REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    case RETRY_WITH = 449;
    case UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    case CLIENT_CLOSED_REQUEST = 499;


    // 5xx
    case INTERNAL_SERVER_ERROR = 500;
    case NOT_IMPLEMENTED = 501;
    case BAD_GATEWAY = 502;
    case SERVICE_UNAVAILABLE = 503;
    case GATEWAY_TIMEOUT = 504;
    case HTTP_VERSION_NOT_SUPPORTED = 505;
    case VARIANT_ALSO_NEGOTIATES = 506;
    case INSUFFICIENT_STORAGE = 507;
    case LOOP_DETECTED = 508;
    case BANDWIDTH_LIMIT_EXCEEDED = 509;
    case NOT_EXTENDED = 510;
    case NETWORK_AUTHENTICATION_REQUIRED = 511;
    case UNKNOWN_ERROR = 520;
    case WEB_SERVER_IS_DOWN = 521;
    case CONNECTION_TIMED_OUT = 522;
    case ORIGIN_IS_UNREACHABLE = 523;
    case A_TIMEOUT_OCCURRED = 524;
    case SSL_HANDSHAKE_FAILED = 525;
    case INVALID_SSL_CERTIFICATE = 526;

    /**
     * Returns the corresponding message for a given HTTP status code.
     *
     * @return string The message corresponding to the HTTP status code.
     */
    final public function message(): string
    {
        return match ($this) {
            // 2xx
            self::OK                                      =>  'Ok',
            self::CREATED                                 =>  'Created',
            self::ACCEPTED                                =>  'Accepted',
            self::NON_AUTHORITATIVE_INFORMATION           =>  'Non-Authoritative Information',
            self::NO_CONTENT                              =>  'No Content',
            self::RESET_CONTENT                           =>  'Reset Content',
            self::PARTIAL_CONTENT                         =>  'Partial Content',
            self::MULTI_STATUS                            =>  'Multi-Status',
            self::ALREADY_REPORTED                        =>  'Already Reported',
            self::IM_USED                                 =>  'IM Used',
            // 3xx
            self::MULTIPLE_CHOICES                        =>  'Multiple Choices',
            self::MOVED_PERMANENTLY                       =>  'Moved Permanently',
            self::FOUND                                   =>  'Found',
            self::SEE_OTHER                               =>  'See Other',
            self::NOT_MODIFIED                            =>  'Not Modified',
            self::USE_PROXY                               =>  'Use Proxy',
            self::TEMPORARY_REDIRECT                      =>  'Temporary Redirect',
            self::PERMANENT_REDIRECT                      =>  'Permanent Redirect',
            // 4xx
            self::BAD_REQUEST                             =>  'Bad Request',
            self::UNAUTHORIZED                            =>  'Unauthorized',
            self::PAYMENT_REQUIRED                        =>  'Payment Required',
            self::FORBIDDEN                               =>  'Forbidden',
            self::NOT_FOUND                               =>  'Not Found',
            self::METHOD_NOT_ALLOWED                      =>  'Method Not Allowed',
            self::NOT_ACCEPTABLE                          =>  'Not Acceptable',
            self::PROXY_AUTHENTICATION_REQUIRED           =>  'Proxy Authentication Required',
            self::REQUEST_TIMEOUT                         =>  'Request Timeout',
            self::CONFLICT                                =>  'Conflict',
            self::GONE                                    =>  'Gone',
            self::LENGTH_REQUIRED                         =>  'Length Required',
            self::PRECONDITION_FAILED                     =>  'Precondition Failed',
            self::REQUEST_ENTITY_TOO_LARGE                =>  'Request Entity Too Large',
            self::REQUEST_URI_TOO_LONG                    =>  'Request-URI Too Long',
            self::UNSUPPORTED_MEDIA_TYPE                  =>  'Unsupported Media Type',
            self::REQUESTED_RANGE_NOT_SATISFIABLE         =>  'Requested Range Not Satisfiable',
            self::EXPECTATION_FAILED                      =>  'Expectation Failed',
            self::IM_A_TEAPOT                             =>  'I’m a teapot',
            self::AUTHENTICATION_TIMEOUT_NOT_IN_RFC_2616  =>  'Authentication Timeout (not in RFC 2616)',
            self::MISDIRECTED_REQUEST                     =>  'Misdirected Request',
            self::UNPROCESSABLE_ENTITY                    =>  'Unprocessable Entity',
            self::LOCKED                                  =>  'Locked',
            self::FAILED_DEPENDENCY                       =>  'Failed Dependency',
            self::TOO_EARLY                               =>  'Too Early',
            self::UPGRADE_REQUIRED                        =>  'Upgrade Required',
            self::PRECONDITION_REQUIRED                   =>  'Precondition Required',
            self::TOO_MANY_REQUESTS                       =>  'Too Many Requests',
            self::REQUEST_HEADER_FIELDS_TOO_LARGE         =>  'Request Header Fields Too Large',
            self::RETRY_WITH                              =>  'Retry With',
            self::UNAVAILABLE_FOR_LEGAL_REASONS           =>  'Unavailable For Legal Reasons',
            self::CLIENT_CLOSED_REQUEST                   =>  'Client Closed Request',
            // 5xx
            self::INTERNAL_SERVER_ERROR                   =>  'Internal Server Error',
            self::NOT_IMPLEMENTED                         =>  'Not Implemented',
            self::BAD_GATEWAY                             =>  'Bad Gateway',
            self::SERVICE_UNAVAILABLE                     =>  'Service Unavailable',
            self::GATEWAY_TIMEOUT                         =>  'Gateway Timeout',
            self::HTTP_VERSION_NOT_SUPPORTED              =>  'HTTP Version Not Supported',
            self::VARIANT_ALSO_NEGOTIATES                 =>  'Variant Also Negotiates',
            self::INSUFFICIENT_STORAGE                    =>  'Insufficient Storage',
            self::LOOP_DETECTED                           =>  'Loop Detected',
            self::BANDWIDTH_LIMIT_EXCEEDED                =>  'Bandwidth Limit Exceeded',
            self::NOT_EXTENDED                            =>  'Not Extended',
            self::NETWORK_AUTHENTICATION_REQUIRED         =>  'Network Authentication Required',
            self::UNKNOWN_ERROR                           =>  'Unknown Error',
            self::WEB_SERVER_IS_DOWN                      =>  'Web Server Is Down',
            self::CONNECTION_TIMED_OUT                    =>  'Connection Timed Out',
            self::ORIGIN_IS_UNREACHABLE                   =>  'Origin Is Unreachable',
            self::A_TIMEOUT_OCCURRED                      =>  'A Timeout Occurred',
            self::SSL_HANDSHAKE_FAILED                    =>  'SSL Handshake Failed',
            self::INVALID_SSL_CERTIFICATE                 =>  'Invalid SSL Certificate'
        };
    }

    /**
     * Determines whether the HTTP status code represents a success.
     *
     * @return bool True if the HTTP status code represents a success, false otherwise.
     */
    final public function isSuccess(): bool
    {
        return match ($this) {
            self::OK, self::CREATED, self::ACCEPTED,
            self::NON_AUTHORITATIVE_INFORMATION,
            self::NO_CONTENT, self::RESET_CONTENT,
            self::PARTIAL_CONTENT, self::MULTI_STATUS,
            self::ALREADY_REPORTED, self::IM_USED
            => true,
            default => false
        };
    }

    /**
     * Checks if the HTTP status code is a redirection.
     *
     * @return bool True if the HTTP status code is a redirection, otherwise false.
     */
    final public function isRedirection(): bool
    {
        return match ($this) {
            self::MULTIPLE_CHOICES, self::MOVED_PERMANENTLY,
            self::FOUND, self::SEE_OTHER,
            self::NOT_MODIFIED, self::USE_PROXY,
            self::TEMPORARY_REDIRECT, self::PERMANENT_REDIRECT
            => true,
            default => false
        };
    }

    /**
     * Returns true if the HTTP status code is a client error, false otherwise.
     *
     * @return bool
     */
    final public function isClientError(): bool
    {
        return match ($this) {
            self::BAD_REQUEST, self::UNAUTHORIZED,
            self::PAYMENT_REQUIRED, self::FORBIDDEN,
            self::NOT_FOUND, self::METHOD_NOT_ALLOWED,
            self::NOT_ACCEPTABLE, self::PROXY_AUTHENTICATION_REQUIRED,
            self::REQUEST_TIMEOUT, self::CONFLICT, self::GONE,
            self::LENGTH_REQUIRED, self::PRECONDITION_FAILED,
            self::REQUEST_ENTITY_TOO_LARGE, self::REQUEST_URI_TOO_LONG,
            self::UNSUPPORTED_MEDIA_TYPE,
            self::REQUESTED_RANGE_NOT_SATISFIABLE,
            self::EXPECTATION_FAILED, self::IM_A_TEAPOT,
            self::AUTHENTICATION_TIMEOUT_NOT_IN_RFC_2616,
            self::MISDIRECTED_REQUEST, self::UNPROCESSABLE_ENTITY,
            self::LOCKED, self::FAILED_DEPENDENCY, self::TOO_EARLY,
            self::UPGRADE_REQUIRED, self::PRECONDITION_REQUIRED,
            self::TOO_MANY_REQUESTS, self::REQUEST_HEADER_FIELDS_TOO_LARGE,
            self::RETRY_WITH, self::UNAVAILABLE_FOR_LEGAL_REASONS,
            self::CLIENT_CLOSED_REQUEST,
            => true,
            default => false
        };
    }

    /**
     * Returns a boolean value indicating whether the current HTTP status code
     * represents a server error.
     *
     * @return bool Returns true if the HTTP status code represents a server error,
     *              and false otherwise.
     */
    final public function isServerError(): bool
    {
        return match ($this) {
            self::INTERNAL_SERVER_ERROR, self::NOT_IMPLEMENTED,
            self::BAD_GATEWAY, self::SERVICE_UNAVAILABLE,
            self::GATEWAY_TIMEOUT, self::HTTP_VERSION_NOT_SUPPORTED,
            self::VARIANT_ALSO_NEGOTIATES, self::INSUFFICIENT_STORAGE,
            self::LOOP_DETECTED, self::BANDWIDTH_LIMIT_EXCEEDED,
            self::NOT_EXTENDED, self::NETWORK_AUTHENTICATION_REQUIRED,
            self::UNKNOWN_ERROR, self::WEB_SERVER_IS_DOWN,
            self::CONNECTION_TIMED_OUT, self::ORIGIN_IS_UNREACHABLE,
            self::A_TIMEOUT_OCCURRED, self::SSL_HANDSHAKE_FAILED,
            self::INVALID_SSL_CERTIFICATE
            => true,
            default => false
        };
    }
}
