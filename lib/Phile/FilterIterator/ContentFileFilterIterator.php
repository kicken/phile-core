<?php
/**
 * the filter class for content files
 */
namespace Phile\FilterIterator;
use Phile\Core\Registry;

/**
 * Class ContentFileFilterIterator
 *
 * @package Phile\FilterIterator
 */
class ContentFileFilterIterator extends \FilterIterator
{
    /**
     * method to decide if file is filtered or not
     * @return bool
     */
    public function accept()
    {
        $settings = Registry::get('Phile_Settings');
        /**
         * @var \SplFileInfo $this
         */
        return (preg_match('/^[^\.]{1}.*'.$settings['content_ext'].'/', $this->getFilename()) > 0);
    }
}
