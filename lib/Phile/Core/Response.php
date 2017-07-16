<?php

/**
 * the Response class
 */

namespace Phile\Core;

/**
 * the Response class is responsible for sending a HTTP response to the client
 *
 * Response is chainable and can be used anywhere:
 *
 *     (new Response)->setBody('Hello World')->send();
 *
 * After send() Phile is terminated.
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile
 */
class Response
{

    /**
     * @var string HTTP body
     */
    protected $body = '';

    /**
     * @var array HTTP-headers
     */
    protected $headers = [];

    /**
     * @var int HTTP status code
     */
    protected $statusCode = 200;

    /**
     * redirect to another URL
     *
     * @param string $url        URL
     * @param int    $statusCode
     */
    public function redirect($url, $statusCode = 302)
    {
        $this->setStatusCode($statusCode)
            ->setHeader('Location', $url, true)
            ->setBody('<a href="'.$url.'">'.$url.'</a>')
        ;
    }

    /**
     * set the response body
     *
     * @param  $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * set a response HTTP-header
     *
     * @param  string $key
     * @param  string $value
     * @param  bool   $clear clear out any existing headers
     * @return $this
     */
    public function setHeader($key, $value, $clear = false)
    {
        if ($clear) {
            $this->headers = [];
        }
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * set the response HTTP status code
     *
     * @param  $code
     * @return $this
     */
    public function setStatusCode($code)
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
