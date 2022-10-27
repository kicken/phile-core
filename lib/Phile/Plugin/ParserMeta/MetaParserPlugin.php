<?php
/**
 * Plugin class
 */

namespace Phile\Plugin\ParserMeta;

use Phile\Core\ServiceLocator;
use Phile\Plugin\AbstractPlugin;
use Phile\ServiceLocator\MetaParserInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Plugin
 * Default Phile parser plugin for Markdown
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\ParserMeta
 */
class MetaParserPlugin extends AbstractPlugin implements MetaParserInterface {
    public function initialize(){
        ServiceLocator::registerService('Phile_Parser_Meta', $this);
        $defaults = [
            'fences' => [
                'c' => ['open' => '/*', 'close' => '*/'],
                'html' => ['open' => '<!--', 'close' => '-->'],
                'yaml' => ['open' => '---', 'close' => '---']
            ]
        ];

        $this->config = array_replace_recursive($defaults, $this->config);
    }

    /**
     * parse the content and extract meta information
     *
     * @param string $rawData raw page data
     *
     * @return array with key/value store
     */
    public function parse($rawData){
        $rawData = trim($rawData);
        $fences = $this->config['fences'];

        $start = $stop = null;
        foreach ($fences as $fence){
            $start = $fence['open'];
            $length = strlen($start);
            if (substr($rawData, 0, $length) === $start){
                $stop = $fence['close'];
                break;
            }
        }

        if ($stop === null){
            return [];
        }

        $meta = trim(substr($rawData, strlen($start), strpos($rawData, $stop) - (strlen($stop) + 1)));
        $meta = Yaml::parse($meta);
        $meta = ($meta === null) ? [] : $this->convertKeys($meta);

        return $meta;
    }

    /**
     * convert meta data keys
     *
     * Creates "compatible" keys allowing easy access e.g. as template var.
     *
     * Conversions applied:
     *
     * - lowercase all chars
     * - replace special chars and whitespace with underscore
     *
     * @param array $meta meta-data
     *
     * @return array
     */
    protected function convertKeys(array $meta){
        $return = [];
        foreach ($meta as $key => $value){
            if (is_array($value)){
                $value = $this->convertKeys($value);
            }
            $newKey = strtolower($key);
            $newKey = preg_replace('/[^\w+]/', '_', $newKey);
            $return[$newKey] = $value;
        }

        return $return;
    }
}
