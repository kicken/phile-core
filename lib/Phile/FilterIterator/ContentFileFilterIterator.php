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
class ContentFileFilterIterator extends \FilterIterator
{
    private $contentExtension;

    public function __construct(Iterator $iterator, $contentExtension)
    {
        parent::__construct($iterator);
        $this->contentExtension = $contentExtension;
    }

    /**
     * method to decide if file is filtered or not
     * @return bool
     */
    public function accept()
    {
        /** @var \SplFileInfo $file */
        $file = $this->current();

        return !$this->isHidden($file) && $this->isNot404($file) && $this->isCorrectExtension($file);
    }

    private function isHidden(\SplFileInfo $file)
    {
        return substr($file->getFilename(), 0, 1) !== '.';
    }

    private function isNot404(\SplFileInfo $file)
    {
        return $file->getFilename() !== '404' . $this->contentExtension;
    }

    private function isCorrectExtension(\SplFileInfo $file)
    {
        return substr($file->getPathname(), -strlen($this->contentExtension)) === $this->contentExtension;
    }
}
