<?php

class Phrack_HTTP_Status
{
    static private $STATUS_CODE = array(
        '100' => 'Continue',
        '101' => 'Switching Protocols',
        '102' => 'Processing',                      // RFC 2518 (WebDAV)
        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',
        '207' => 'Multi-Status',                    // RFC 2518 (WebDAV)
        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '307' => 'Temporary Redirect',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '407' => 'Proxy Authentication Required',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request-URI Too Large',
        '415' => 'Unsupported Media Type',
        '416' => 'Request Range Not Satisfiable',
        '417' => 'Expectation Failed',
        '422' => 'Unprocessable Entity',            // RFC 2518 (WebDAV)
        '423' => 'Locked',                          // RFC 2518 (WebDAV)
        '424' => 'Failed Dependency',               // RFC 2518 (WebDAV)
        '425' => 'No code',                         // WebDAV Advanced Collections
        '426' => 'Upgrade Required',                // RFC 2817
        '449' => 'Retry with',                      // unofficial Microsoft
        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported',
        '506' => 'Variant Also Negotiates',         // RFC 2295
        '507' => 'Insufficient Storage',            // RFC 2518 (WebDAV)
        '509' => 'Bandwidth Limit Exceeded',        // unofficial
        '510' => 'Not Extended',                    // RFC 2774
        );

    static public function statusMessage($status)
    {
        return self::$STATUS_CODE[$status];
    }

    static public function isInfo       ($status) { return $status >= 100 && $status < 200; }
    static public function isSuccess    ($status) { return $status >= 200 && $status < 300; }
    static public function isRedirect   ($status) { return $status >= 300 && $status < 400; }
    static public function isError      ($status) { return $status >= 400 && $status < 600; }
    static public function isClientError($status) { return $status >= 400 && $status < 500; }
    static public function isServerError($status) { return $status >= 500 && $status < 600; }

    static public function hasNoEntityBody($status)
    {
        return $status < 200 || $status === 204 || $status === 304;
    }
}
