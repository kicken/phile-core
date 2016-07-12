<?php
/**
 * The Mardown parser class
 */
namespace Phile\Plugin\ParserMarkdown\Parser;

use Michelf\MarkdownExtra;
use Phile\ServiceLocator\ParserInterface;

/**
 * Class Markdown
 *
 * @author  Frank Nägler
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\ParserMarkdown\Parser
 */
class Markdown implements ParserInterface
{
    /** @var array */
    private $config;

    /**
     * the constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * overload parse with the MarkdownExtra parser
     *
     * @param $data
     *
     * @return string
     */
    public function parse($data)
    {
        $parser = new MarkdownExtra;
        foreach ($this->config as $key => $value) {
            $parser->{$key} = $value;
        }

        return $parser->transform($data);
    }
}
