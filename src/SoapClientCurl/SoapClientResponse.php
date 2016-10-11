<?php

namespace SoapClientCurl;

class SoapClientResponse
{
    public $info;
    public $headers;
    public $body;

    /**
     * @param array  $info     response curl_getinfo in Curl request
     * @param string $headers  raw header string from cURL response
     * @param string $raw_body the raw body of the cURL response
     *
     * @internal param array $json_args arguments to pass to json_decode function
     */
    public function __construct($info, $headers, $raw_body)
    {
        $this->info = $info;
        $this->headers = $this->parseHeaders($headers);
        $this->body = $raw_body;
    }

    /**
     * if PECL_HTTP is not available use a fall back function.
     *
     * thanks to ricardovermeltfoort@gmail.com
     * http://php.net/manual/en/function.http-parse-headers.php#112986
     */
    private function parseHeaders($raw_headers)
    {
        if (function_exists('http_parse_headers')) {
            return http_parse_headers($raw_headers);
        } else {
            $key = '';
            $headers = [];
            foreach (explode("\n", $raw_headers) as $i => $h) {
                $h = explode(':', $h, 2);
                if (isset($h[1])) {
                    if (!isset($headers[$h[0]])) {
                        $headers[$h[0]] = trim($h[1]);
                    } elseif (is_array($headers[$h[0]])) {
                        $headers[$h[0]] = array_merge($headers[$h[0]], [trim($h[1])]);
                    } else {
                        $headers[$h[0]] = array_merge([$headers[$h[0]]], [trim($h[1])]);
                    }
                    $key = $h[0];
                } else {
                    if (substr($h[0], 0, 1) == "\t") {
                        $headers[$key] .= "\r\n\t".trim($h[0]);
                    } elseif (!$key) {
                        $headers[0] = trim($h[0]);
                    }
                }
            }

            return $headers;
        }
    }
}
