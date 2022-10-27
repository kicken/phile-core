<?php
/**
 * the filter class for content files
 */

namespace Phile\FilterIterator;

use Iterator;

/**
 * Class ContentFileFilterIterator
 *
 * @package Phile\FilterIterator
 */
class ContentFileFilterIterator extends \FilterIterator {
    private $contentExtension;

    public function __construct(Iterator $iterator, $contentExtension){
        parent::__construct($iterator);
        $this->contentExtension = $contentExtension;
    }

    /**
     * method to decide if file is filtered or not
     *
     * @return bool
     */
    public function accept() : bool{
        /** @var \SplFileInfo $file */
        $file = $this->current();

        return !$this->isHidden($file) && !$this->is404($file) && $this->isCorrectExtension($file);
    }

    private function isHidden(\SplFileInfo $file) : bool{
        return substr($file->getFilename(), 0, 1) === '.';
    }

    private function is404(\SplFileInfo $file) : bool{
        return $file->getFilename() === '404' . $this->contentExtension;
    }

    private function isCorrectExtension(\SplFileInfo $file) : bool{
        return substr($file->getPathname(), -strlen($this->contentExtension)) === $this->contentExtension;
    }
}
