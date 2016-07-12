<?php
/**
 * Created by PhpStorm.
 * User: Keith
 * Date: 7/12/2016
 * Time: 12:22 AM
 */

namespace Phile\ServiceLocator;


interface RouterInterface
{
    /**
     * Try to resolve a URL to a content path.
     * 
     * @param string $url
     * @return string|null  Path to the content file if found, otherwise null.
     */
    public function match($url);

    /**
     * See if the URL resolves to a redirect.
     * 
     * @param string $url
     * @return string|null New URL if found, otherwise null.
     */
    public function matchRedirect($url);
    
    /**
     * Generate a URL for a given path.
     * 
     * @param string $path
     * @param bool $absolute
     * @return string
     */
    public function urlForPath($path, $absolute=true);
    
}
