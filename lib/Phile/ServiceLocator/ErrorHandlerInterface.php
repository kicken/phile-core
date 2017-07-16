<?php
/**
 * The ErrorHandlerInterface
 */
namespace Phile\ServiceLocator;

/**
 * Interface ErrorHandlerInterface
 *
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\ServiceLocator
 */
interface ErrorHandlerInterface
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
    public function handleError($errno, $errstr, $errFile, $errLine, array $errContext);

    /**
     * handle all exceptions
     *
     * @param \Throwable|\Exception $exception
     */
    public function handleException($exception);
}
