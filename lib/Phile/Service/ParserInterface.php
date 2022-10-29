<?php
/**
 * The ServiceLocator interface
 */

namespace Phile\Service;

/**
 * Interface ParserInterface
 *
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\ServiceLocator
 */
interface ParserInterface {
    /**
     * parse data
     *
     * @param string $data
     *
     * @return mixed
     */
    public function parse(string $data);
}
