<?php
/**
 * Plugin class
 */

namespace Phile\Plugin\TemplateTwig;

use Phile\Core\Registry;
use Phile\Core\ServiceLocator;
use Phile\Model\Page;
use Phile\Plugin\AbstractPlugin;
use Phile\Repository\Page as PageRepository;
use Phile\ServiceLocator\TemplateInterface;

/**
 * Class Plugin
 * Default Phile template engine
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\TemplateTwig
 */
class TwigTemplatePlugin extends AbstractPlugin implements TemplateInterface {
    /** @var \Twig_Environment */
    private $twig;

    public function initialize() : void{
        $this->twig = $this->createTwigEngine();
        ServiceLocator::registerService('Phile_Template', $this);
    }

    public function render(Page $page) : string{
        $engine = $this->getTwig();

        $vars = $this->getTemplateVars($page);
        $template = $this->getTemplateFileName($page);

        return $engine->render($template, $vars);
    }

    public function getTwig() : \Twig_Environment{
        return $this->twig;
    }

    protected function createTwigEngine() : \Twig_Environment{
        $options = $this->config['options'] ?? [];
        $loader = new \Twig_Loader_Filesystem($this->getTemplatePath());
        $twig = new \Twig_Environment($loader, $options);

        // load the twig debug extension if required
        if (!empty($options['debug'])){
            $twig->addExtension(new \Twig_Extension_Debug());
        }

        return $twig;
    }

    protected function getTemplateFileName(Page $page){
        $meta = $page->getMeta();
        $template = $meta['template'];
        if (empty($template)){
            $template = 'index';
        }

        if (!empty($this->config['template_extension'])){
            $template .= '.' . $this->config['template_extension'];
        }

        $templatePath = $this->getTemplatePath($template);
        if (!file_exists($templatePath)){
            throw new \RuntimeException("Template file '{$templatePath}' not found.");
        }

        return $template;
    }

    protected function getTemplatePath($sub = '') : string{
        $themePath = $this->config['themes_dir'] . $this->config['theme'];
        if (!empty($sub)){
            $themePath .= DIRECTORY_SEPARATOR . ltrim($sub, DIRECTORY_SEPARATOR);
        }

        return $themePath;
    }

    protected function getTemplateVars(Page $page){
        $repository = new PageRepository($this->phileConfig);
        $defaults = [
            'content' => $page->getParsedContent(),
            'meta' => $page->getMeta(),
            'current_page' => $page,
            'base_dir' => rtrim($this->phileConfig['root_dir'], '/'),
            'base_url' => $this->phileConfig['base_url'],
            'config' => $this->phileConfig,
            'content_dir' => $this->phileConfig['content_dir'],
            'content_url' => $this->phileConfig['base_url'] . '/' . basename($this->phileConfig['content_dir']),
            'pages' => $repository->findAll(),
            'site_title' => $this->phileConfig['site_title'],
            'theme_dir' => $this->config['themes_dir'] . $this->config['theme'],
            'theme_url' => $this->phileConfig['base_url'] . '/' . basename($this->config['themes_dir']) . '/' . $this->config['theme'],
        ];

        $templateVars = [];

        try {
            $templateVars = Registry::get('templateVars');
        } catch (\RuntimeException $e){
        }

        $templateVars += $defaults;

        return $templateVars;
    }
}
