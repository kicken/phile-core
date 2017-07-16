<?php
/**
 * The Error Handler
 */

namespace Phile\Plugin\ErrorHandler\Handler;

use Phile\ServiceLocator\ErrorHandlerInterface;

/**
 * Class ErrorLog
 */
class ErrorLog implements ErrorHandlerInterface
{
    /**
     * handle the error
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errFile
     * @param int    $errLine
     * @param array  $errContext
     *
     * @return boolean
     */
    public function handleError($errno, $errstr, $errFile, $errLine, array $errContext)
    {
        error_log("[{$errno}] {$errstr} in {$errFile} on line {$errLine}");

        return true;
    }

    /**
     * handle all exceptions
     *
     * @param \Throwable|\Exception $exception
     */
    public function handleException($exception)
    {
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        error_log("[{$code}] {$message} in {$file} on line {$line}");
    }
}
