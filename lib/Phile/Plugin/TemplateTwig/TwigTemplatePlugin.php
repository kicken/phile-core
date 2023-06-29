<?php
/**
 * Plugin class
 */

namespace Phile\Plugin\TemplateTwig;

use Phile\Core\Registry;
use Phile\Model\Page;
use Phile\Plugin\AbstractPlugin;
use Phile\Repository\Page as PageRepository;
use Phile\Service\TemplateInterface;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

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
    /** @var Environment */
    private Environment $twig;

    public function initialize() : void{
        $this->twig = $this->createTwigEngine();
        $this->core->registerService(TemplateInterface::class, $this);
    }

    public function render(Page $page) : string{
        $engine = $this->getTwig();

        $vars = $this->getTemplateVars($page);
        $template = $this->getTemplateFileName($page);

        return $engine->render($template, $vars);
    }

    public function getTwig() : Environment{
        return $this->twig;
    }

    protected function createTwigEngine() : Environment{
        $options = $this->config['options'] ?? [];
        $loader = new FilesystemLoader($this->getTemplatePath());
        $twig = new Environment($loader, $options);

        // load the twig debug extension if required
        if (!empty($options['debug'])){
            $twig->addExtension(new DebugExtension());
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
        $repository = new PageRepository($this->core);
        $contentDir = $this->core->getSetting('content_dir');
        $baseUrl = $this->core->getSetting('base_url');

        $defaults = [
            'content' => $page->getParsedContent(),
            'meta' => $page->getMeta(),
            'current_page' => $page,
            'base_dir' => rtrim($this->core->getSetting('root_dir'), '/'),
            'base_url' => $baseUrl,
            'content_dir' => $contentDir,
            'content_url' => $baseUrl . '/' . basename($contentDir),
            'pages' => $repository->findAll(),
            'site_title' => $this->core->getSetting('site_title'),
            'theme_dir' => $this->config['themes_dir'] . $this->config['theme'],
            'theme_url' => $baseUrl . '/' . basename($this->config['themes_dir']) . '/' . $this->config['theme'],
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
