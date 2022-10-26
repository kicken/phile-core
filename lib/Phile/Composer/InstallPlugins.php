<?php

namespace Phile\Composer;

use Composer\InstalledVersions;
use Composer\Installer\InstallationManager;
use Composer\Package\Package;
use Composer\Script\Event;

class InstallPlugins {
    public static function installPlugins(Event $event) : void{
        $im = $event->getComposer()->getInstallationManager();
        $siteConfig = self::getSiteConfig();

        foreach (InstalledVersions::getInstalledPackagesByType('phile-plugin') as $name){
            $pluginConfig = self::getPluginConfig($im, $name);
            foreach ($pluginConfig as $class => $classConfig){
                $siteConfig['plugins'][$class] = $siteConfig['plugins'][$class] ?? $classConfig;
            }
        }

        self::saveSiteConfig($siteConfig);
    }

    private static function getPluginConfig(InstallationManager $im, string $packageName) : array{
        $p = new Package($packageName, '*', '*');
        $configFile = sprintf('%s/config.php', $im->getInstallPath($p));
        $configData = [];
        if (file_exists($configFile) && is_readable($configFile)){
            $configData = include $configFile;
        }

        return is_array($configData) ? $configData : [];
    }

    private static function getSiteConfigFileLocation() : string{
        return sprintf('%s/config.php', getcwd());
    }

    private static function getSiteConfig() : array{
        $configFile = self::getSiteConfigFileLocation();
        $configData = [];
        if (file_exists($configFile) && is_readable($configFile)){
            $configData = include $configFile;
        }

        return is_array($configData) ? $configData : [];
    }

    private static function saveSiteConfig(array $siteConfig) : void{
        $configFile = self::getSiteConfigFileLocation();
        if (is_writable($configFile) || is_writable(dirname($configFile))){
            $configFileContent = '<?php return ' . var_export($siteConfig, true) . ';';
            file_put_contents($configFile, $configFileContent);
        }
    }
}
