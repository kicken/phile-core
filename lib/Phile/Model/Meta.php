<?php
/**
 * Model class
 */

namespace Phile\Model;

/**
 * Meta model
 *
 * @author  Frank Nägler
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Model
 */
class Meta implements \ArrayAccess {

    private $data;

    /**
     * Meta constructor.
     *
     * @param array $data
     */
    public function __construct(array $data){
        $this->data = $data;
    }

    public function offsetExists($offset){
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset){
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value){
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset){
        unset($this->data[$offset]);
    }
}
