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

    public function offsetExists($offset) : bool{
        return array_key_exists($offset, $this->data);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset){
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value) : void{
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset) : void{
        unset($this->data[$offset]);
    }
}
