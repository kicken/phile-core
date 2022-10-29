<?php
/**
 * The Persistence ServiceLocator interface
 */

namespace Phile\Service;

/**
 * Interface PersistenceInterface
 *
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\ServiceLocator
 */
interface PersistenceInterface {
    /**
     * check if an entry exists for given key
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key) : bool;

    /**
     * get the entry by given key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key);

    /**
     * set the value for given key
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set(string $key, $value) : void;

    /**
     * delete the entry by given key
     *
     * @param string $key
     *
     * @return void
     */
    public function delete(string $key) : void;
}
