<?php
/**
 * The Development Error Handler
 */

namespace Phile\Plugin\ErrorHandler\Handler;

/**
 * Class Development
 *
 * this is the development error handler for PhileCMS
 * inspired by the debug exception handler of TYPO3 we create this handler.
 * due to incompatibility of the two licenses (GPL and MIT) we have written
 * the entire code again. we thank the core team of TYPO3 for the great idea.
 */
class Development {

    /**
     * @var array settings
     */
    protected $settings;

    /** @var array Phile global settings */
    protected $phileConfig;


    /**
     * constructor
     *
     * @param array $settings
     * @param array $phileConfig
     */
    public function __construct(array $settings = [], array $phileConfig = []){
        $this->settings = $settings;
        $this->phileConfig = $phileConfig;
    }

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


    /**
     * handle PHP errors which can't be caught by error-handler
     */

    /**
     * handle all exceptions
     *
     * @param \Throwable $exception
     */


    /**
     * show a nice looking and human-readable developer output
     *
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     * @param ?array $backtrace
     * @param ?\Throwable $exception
     */

}
