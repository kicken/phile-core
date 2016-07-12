<?php
/**
 * Template engine class
 */
namespace Phile\Plugin\TemplateTwig\Template;

use Phile\Core\Registry;
use Phile\Model\Page;
use Phile\Repository\Page as Repository;
use Phile\ServiceLocator\TemplateInterface;

/**
 * Class Twig
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\TemplateTwig\Template
 */
class Twig implements TemplateInterface
{
    /** @var array Plugin specific config */
    protected $config;

    /** @var array Phile global config */
    private $phileConfig;

    /**
     * the constructor
     *
     * @param array $config
     * @param array $phileConfig
     */
    public function __construct(array $config, array $phileConfig)
    {
        $this->config = $config;
        $this->phileConfig = $phileConfig;
    }

    public function render(Page $page)
    {
        $engine = $this->getEngine();
        $vars = $this->getTemplateVars($page);

        return $this->doRender($page, $engine, $vars);
    }

    /**
     * wrapper to call the render engine
     *
     * @param Page $page
     * @param  \Twig_Environment $engine
     * @param  array $vars
     * @return mixed
     */
    protected function doRender(Page $page, $engine, $vars)
    {
        try {
            $template = $this->getTemplateFileName($page);
        } catch (\RuntimeException $e) {
            return $e->getMessage();
        }
        return $engine->render($template, $vars);
    }

    /**
     * get template engine
     *
     * @return \Twig_Environment
     */
    protected function getEngine()
    {
        $options = isset($this->config['options'])?$this->config['options']:[];
        $loader = new \Twig_Loader_Filesystem($this->getTemplatePath());
        $twig = new \Twig_Environment($loader, $options);

        // load the twig debug extension if required
        if (!empty($options['debug'])) {
            $twig->addExtension(new \Twig_Extension_Debug());
        }
        return $twig;
    }

    /**
     * get template file name
     *
     * @param Page $page
     * @return string
     */
    protected function getTemplateFileName(Page $page)
    {
        $meta = $page->getMeta();
        $template = $meta['template'];
        if (empty($template)) {
            $template = 'index';
        }
        if (!empty($this->config['template_extension'])) {
            $template .= '.' . $this->config['template_extension'];
        }
        $templatePath = $this->getTemplatePath($template);
        if (!file_exists($templatePath)) {
            throw new \RuntimeException(
                "Template file '{$templatePath}' not found.",
                1427990135
            );
        }
        return $template;
    }

    /**
     * get file path to (sub-path) in theme-path
     *
     * @param  string $sub
     * @return string
     */
    protected function getTemplatePath($sub = '')
    {
        $themePath = $this->config['themes_dir'] . $this->config['theme'];
        if (!empty($sub)) {
            $themePath .= DIRECTORY_SEPARATOR . ltrim($sub, DIRECTORY_SEPARATOR);
        }
        return $themePath;
    }

    /**
     * get template vars
     *
     * @param Page $page
     * @return array|mixed
     * @throws \Exception
     */
    protected function getTemplateVars(Page $page)
    {
        $repository = new Repository($this->config);
        $defaults = [
            'content' => $page->getContent(),
            'meta' => $page->getMeta(),
            'current_page' => $page,
            'base_dir' => rtrim($this->config['root_dir'], '/'),
            'base_url' => $this->config['base_url'],
            'config' => $this->config,
            'content_dir' => $this->config['content_dir'],
            'content_url' => $this->config['base_url'] . '/' . basename($this->config['content_dir']),
            'pages' => $repository->findAll(),
            'site_title' => $this->config['site_title'],
            'theme_dir' => $this->config['themes_dir'] . $this->config['theme'],
            'theme_url' => $this->config['base_url'] . '/' . basename($this->config['themes_dir']) . '/' . $this->config['theme'],
        ];

        /**
         * @var array $templateVars
         */
        $templateVars = Registry::get('templateVars');
        $templateVars += $defaults;

        return $templateVars;
    }
}
