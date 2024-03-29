<?php
/**
 * ServiceLocator MetaParser interface
 */

namespace Phile\Service;

/**
 * Interface MetaInterface
 *
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\ServiceLocator
 */
interface MetaParserInterface {
    /**
     * parse the content
     *
     * @param string $rawData
     *
     * @return array with key/value store
     */
    public function parse(string $rawData) : array;
}
