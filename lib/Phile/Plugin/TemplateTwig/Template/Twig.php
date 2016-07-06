<?php
/**
 * Template engine class
 */
namespace Phile\Plugin\TemplateTwig\Template;

use Phile\Core\Event;
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
    /**
     * @var array the complete phile config
     */
    protected $settings;

    /**
     * @var Page the page model
     */
    protected $page;

    /**
     * the constructor
     *
     * @param array $settings
     */
    public function __construct($settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * method to set the current page
     *
     * @param Page $page the page model
     *
     * @return mixed|void
     */
    public function setCurrentPage(Page $page)
    {
        $this->page = $page;
    }

    /**
     * method to render the page/template
     *
     * @return mixed|string
     */
    public function render()
    {
        $engine = $this->getEngine();
        $vars = $this->getTemplateVars();

        Event::triggerEvent(
            'template_engine_registered',
            ['engine' => &$engine, 'data' => &$vars]
        );

        return $this->doRender($engine, $vars);
    }

    /**
     * wrapper to call the render engine
     *
     * @param  \Twig_Environment $engine
     * @param  array $vars
     * @return mixed
     */
    protected function doRender($engine, $vars)
    {
        try {
            $template = $this->getTemplateFileName();
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
        $options = isset($this->settings['options'])?$this->settings['options']:[];
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
     * @return string
     * @throws \RuntimeException
     */
    protected function getTemplateFileName()
    {
        $template = $this->page->getMeta()->get('template');
        if (empty($template)) {
            $template = 'index';
        }
        if (!empty($this->settings['template_extension'])) {
            $template .= '.' . $this->settings['template_extension'];
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
        $themePath = $this->settings['themes_dir'] . $this->settings['theme'];
        if (!empty($sub)) {
            $themePath .= '/' . ltrim($sub, DIRECTORY_SEPARATOR);
        }
        return $themePath;
    }

    /**
     * get template vars
     *
     * @return array|mixed
     * @throws \Exception
     */
    protected function getTemplateVars()
    {
        $repository = new Repository($this->settings);
        $defaults = [
        'content' => $this->page->getContent(),
        'meta' => $this->page->getMeta(),
        'current_page' => $this->page,
        'base_dir' => rtrim($this->settings['root_dir'], '/'),
        'base_url' => $this->settings['base_url'],
        'config' => $this->settings,
        'content_dir' => $this->settings['content_dir'],
        'content_url' => $this->settings['base_url'] . '/' . basename($this->settings['content_dir']),
        'pages' => $repository->findAll(),
        'site_title' => $this->settings['site_title'],
        'theme_dir' => $this->settings['themes_dir'] . $this->settings['theme'],
        'theme_url' => $this->settings['base_url'] . '/' . basename($this->settings['themes_dir']) . '/' . $this->settings['theme'],
        ];

        /**
         * @var array $templateVars
         */
        $templateVars = Registry::get('templateVars');
        $templateVars += $defaults;

        return $templateVars;
    }
}
