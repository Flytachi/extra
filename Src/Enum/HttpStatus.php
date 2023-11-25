<?php

namespace Extra\Src\Enum;

abstract class HttpStatus
{
    final public static function status(HttpCode $code): string
    {
        return match ($code) {
            // 2xx
            HttpCode::OK                                      =>  'Ok',
            HttpCode::CREATED                                 =>  'Created',
            HttpCode::ACCEPTED                                =>  'Accepted',
            HttpCode::NON_AUTHORITATIVE_INFORMATION           =>  'Non-Authoritative Information',
            HttpCode::NO_CONTENT                              =>  'No Content',
            HttpCode::RESET_CONTENT                           =>  'Reset Content',
            HttpCode::PARTIAL_CONTENT                         =>  'Partial Content',
            HttpCode::MULTI_STATUS                            =>  'Multi-Status',
            HttpCode::ALREADY_REPORTED                        =>  'Already Reported',
            HttpCode::IM_USED                                 =>  'IM Used',
            // 3xx
            HttpCode::MULTIPLE_CHOICES                        =>  'Multiple Choices',
            HttpCode::MOVED_PERMANENTLY                       =>  'Moved Permanently',
            HttpCode::FOUND                                   =>  'Found',
            HttpCode::SEE_OTHER                               =>  'See Other',
            HttpCode::NOT_MODIFIED                            =>  'Not Modified',
            HttpCode::USE_PROXY                               =>  'Use Proxy',
            HttpCode::TEMPORARY_REDIRECT                      =>  'Temporary Redirect',
            HttpCode::PERMANENT_REDIRECT                      =>  'Permanent Redirect',
            // 4xx
            HttpCode::BAD_REQUEST                             =>  'Bad Request',
            HttpCode::UNAUTHORIZED                            =>  'Unauthorized',
            HttpCode::PAYMENT_REQUIRED                        =>  'Payment Required',
            HttpCode::FORBIDDEN                               =>  'Forbidden',
            HttpCode::NOT_FOUND                               =>  'Not Found',
            HttpCode::METHOD_NOT_ALLOWED                      =>  'Method Not Allowed',
            HttpCode::NOT_ACCEPTABLE                          =>  'Not Acceptable',
            HttpCode::PROXY_AUTHENTICATION_REQUIRED           =>  'Proxy Authentication Required',
            HttpCode::REQUEST_TIMEOUT                         =>  'Request Timeout',
            HttpCode::CONFLICT                                =>  'Conflict',
            HttpCode::GONE                                    =>  'Gone',
            HttpCode::LENGTH_REQUIRED                         =>  'Length Required',
            HttpCode::PRECONDITION_FAILED                     =>  'Precondition Failed',
            HttpCode::REQUEST_ENTITY_TOO_LARGE                =>  'Request Entity Too Large',
            HttpCode::REQUEST_URI_TOO_LONG                    =>  'Request-URI Too Long',
            HttpCode::UNSUPPORTED_MEDIA_TYPE                  =>  'Unsupported Media Type',
            HttpCode::REQUESTED_RANGE_NOT_SATISFIABLE         =>  'Requested Range Not Satisfiable',
            HttpCode::EXPECTATION_FAILED                      =>  'Expectation Failed',
            HttpCode::IM_A_TEAPOT                             =>  'I’m a teapot',
            HttpCode::AUTHENTICATION_TIMEOUT_NOT_IN_RFC_2616  =>  'Authentication Timeout (not in RFC 2616)',
            HttpCode::MISDIRECTED_REQUEST                     =>  'Misdirected Request',
            HttpCode::UNPROCESSABLE_ENTITY                    =>  'Unprocessable Entity',
            HttpCode::LOCKED                                  =>  'Locked',
            HttpCode::FAILED_DEPENDENCY                       =>  'Failed Dependency',
            HttpCode::TOO_EARLY                               =>  'Too Early',
            HttpCode::UPGRADE_REQUIRED                        =>  'Upgrade Required',
            HttpCode::PRECONDITION_REQUIRED                   =>  'Precondition Required',
            HttpCode::TOO_MANY_REQUESTS                       =>  'Too Many Requests',
            HttpCode::REQUEST_HEADER_FIELDS_TOO_LARGE         =>  'Request Header Fields Too Large',
            HttpCode::RETRY_WITH                              =>  'Retry With',
            HttpCode::UNAVAILABLE_FOR_LEGAL_REASONS           =>  'Unavailable For Legal Reasons',
            HttpCode::CLIENT_CLOSED_REQUEST                   =>  'Client Closed Request',
            // 5xx
            HttpCode::INTERNAL_SERVER_ERROR                   =>  'Internal Server Error',
            HttpCode::NOT_IMPLEMENTED                         =>  'Not Implemented',
            HttpCode::BAD_GATEWAY                             =>  'Bad Gateway',
            HttpCode::SERVICE_UNAVAILABLE                     =>  'Service Unavailable',
            HttpCode::GATEWAY_TIMEOUT                         =>  'Gateway Timeout',
            HttpCode::HTTP_VERSION_NOT_SUPPORTED              =>  'HTTP Version Not Supported',
            HttpCode::VARIANT_ALSO_NEGOTIATES                 =>  'Variant Also Negotiates',
            HttpCode::INSUFFICIENT_STORAGE                    =>  'Insufficient Storage',
            HttpCode::LOOP_DETECTED                           =>  'Loop Detected',
            HttpCode::BANDWIDTH_LIMIT_EXCEEDED                =>  'Bandwidth Limit Exceeded',
            HttpCode::NOT_EXTENDED                            =>  'Not Extended',
            HttpCode::NETWORK_AUTHENTICATION_REQUIRED         =>  'Network Authentication Required',
            HttpCode::UNKNOWN_ERROR                           =>  'Unknown Error',
            HttpCode::WEB_SERVER_IS_DOWN                      =>  'Web Server Is Down',
            HttpCode::CONNECTION_TIMED_OUT                    =>  'Connection Timed Out',
            HttpCode::ORIGIN_IS_UNREACHABLE                   =>  'Origin Is Unreachable',
            HttpCode::A_TIMEOUT_OCCURRED                      =>  'A Timeout Occurred',
            HttpCode::SSL_HANDSHAKE_FAILED                    =>  'SSL Handshake Failed',
            HttpCode::INVALID_SSL_CERTIFICATE                 =>  'Invalid SSL Certificate'
        };
    }

    final public static function isSuccess(HttpCode $code): bool
    {
        return match ($code) {
            HttpCode::OK, HttpCode::CREATED, HttpCode::ACCEPTED,
            HttpCode::NON_AUTHORITATIVE_INFORMATION,
            HttpCode::NO_CONTENT, HttpCode::RESET_CONTENT,
            HttpCode::PARTIAL_CONTENT, HttpCode::MULTI_STATUS,
            HttpCode::ALREADY_REPORTED, HttpCode::IM_USED
            => true,
            default => false
        };
    }

    final public static function isRedirection(HttpCode $code): bool
    {
        return match ($code) {
            HttpCode::MULTIPLE_CHOICES, HttpCode::MOVED_PERMANENTLY,
            HttpCode::FOUND, HttpCode::SEE_OTHER,
            HttpCode::NOT_MODIFIED, HttpCode::USE_PROXY,
            HttpCode::TEMPORARY_REDIRECT, HttpCode::PERMANENT_REDIRECT
            => true,
            default => false
        };
    }

    final public static function isClientError(HttpCode $code): bool
    {
        return match ($code) {
            HttpCode::BAD_REQUEST, HttpCode::UNAUTHORIZED,
            HttpCode::PAYMENT_REQUIRED, HttpCode::FORBIDDEN,
            HttpCode::NOT_FOUND, HttpCode::METHOD_NOT_ALLOWED,
            HttpCode::NOT_ACCEPTABLE, HttpCode::PROXY_AUTHENTICATION_REQUIRED,
            HttpCode::REQUEST_TIMEOUT, HttpCode::CONFLICT, HttpCode::GONE,
            HttpCode::LENGTH_REQUIRED, HttpCode::PRECONDITION_FAILED,
            HttpCode::REQUEST_ENTITY_TOO_LARGE, HttpCode::REQUEST_URI_TOO_LONG,
            HttpCode::UNSUPPORTED_MEDIA_TYPE,
            HttpCode::REQUESTED_RANGE_NOT_SATISFIABLE,
            HttpCode::EXPECTATION_FAILED, HttpCode::IM_A_TEAPOT,
            HttpCode::AUTHENTICATION_TIMEOUT_NOT_IN_RFC_2616,
            HttpCode::MISDIRECTED_REQUEST, HttpCode::UNPROCESSABLE_ENTITY,
            HttpCode::LOCKED, HttpCode::FAILED_DEPENDENCY, HttpCode::TOO_EARLY,
            HttpCode::UPGRADE_REQUIRED, HttpCode::PRECONDITION_REQUIRED,
            HttpCode::TOO_MANY_REQUESTS, HttpCode::REQUEST_HEADER_FIELDS_TOO_LARGE,
            HttpCode::RETRY_WITH, HttpCode::UNAVAILABLE_FOR_LEGAL_REASONS,
            HttpCode::CLIENT_CLOSED_REQUEST,
            => true,
            default => false
        };
    }

    final public static function isServerError(HttpCode $code): bool
    {
        return match ($code) {
            HttpCode::INTERNAL_SERVER_ERROR, HttpCode::NOT_IMPLEMENTED,
            HttpCode::BAD_GATEWAY, HttpCode::SERVICE_UNAVAILABLE,
            HttpCode::GATEWAY_TIMEOUT, HttpCode::HTTP_VERSION_NOT_SUPPORTED,
            HttpCode::VARIANT_ALSO_NEGOTIATES, HttpCode::INSUFFICIENT_STORAGE,
            HttpCode::LOOP_DETECTED, HttpCode::BANDWIDTH_LIMIT_EXCEEDED,
            HttpCode::NOT_EXTENDED, HttpCode::NETWORK_AUTHENTICATION_REQUIRED,
            HttpCode::UNKNOWN_ERROR, HttpCode::WEB_SERVER_IS_DOWN,
            HttpCode::CONNECTION_TIMED_OUT, HttpCode::ORIGIN_IS_UNREACHABLE,
            HttpCode::A_TIMEOUT_OCCURRED, HttpCode::SSL_HANDSHAKE_FAILED,
            HttpCode::INVALID_SSL_CERTIFICATE
            => true,
            default => false
        };
    }
}