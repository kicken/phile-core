<?php
/**
 * The TemplateInterface
 */

namespace Phile\ServiceLocator;

use Phile\Model\Page;

/**
 * Interface TemplateInterface
 *
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\ServiceLocator
 */
interface TemplateInterface {
    /**
     * render the template
     *
     * @param Page $page
     *
     * @return string
     */
    public function render(Page $page) : string;
}
