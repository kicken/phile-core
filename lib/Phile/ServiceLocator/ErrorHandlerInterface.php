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
interface ErrorHandlerInterface {
    /**
     * handle the error
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errFile
     * @param int $errLine
     *
     * @return boolean
     */
    public function handleError(int $errno, string $errstr, string $errFile, int $errLine) : bool;

    /**
     * handle all exceptions
     *
     * @param \Throwable|\Exception $exception
     */
    public function handleException($exception);
}
