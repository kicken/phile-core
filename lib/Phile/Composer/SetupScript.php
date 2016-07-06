<?php
/**
 * Created by PhpStorm.
 * User: Keith
 * Date: 7/6/2016
 * Time: 9:52 AM
 */

namespace Phile\Composer;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use Phile\Core\Utility;

class SetupScript {
    public static function run(Event $event)
    {
        $io = $event->getIO();

        $io->write('Welcome to PhileCMS');
        $io->write('To continue we first need to get a few configuration details out of the way.');

        $config = [
            'site_title' => self::askSiteTitle($io)
            , 'encryptionKey' => self::askEncryptionKey($io)
        ];

        self::writeConfiguration($io, $config);
        self::createVarDirectory($io);
    }

    private static function askSiteTitle(IOInterface $io)
    {
        $title = 'PhileCMS';
        $question = sprintf('<question>Site Title [%s]:</question> ', $title);

        return $io->ask($question, $title);
    }

    private static function askEncryptionKey(IOInterface $io)
    {
        $key = self::generateRandomKey();
        $question = sprintf('<question>Encryption Key [%s]:</question>', '<random>');

        return $io->ask($question, $key);
    }

    private static function generateRandomKey()
    {
        return Utility::generateSecureToken(64);
    }

    private static function getPath($sub)
    {
        $rootDir = __DIR__ . '/../';

        return str_replace('/', DIRECTORY_SEPARATOR, $rootDir . $sub);
    }

    private static function writeConfiguration(IOInterface $io, array $config)
    {
        $contents = '<?php return ' . var_export($config, true) . ';';

        $configFile = self::getPath('config.php');
        $fp = fopen($configFile, 'w');
        if (!$fp) {
            $io->write('<error>Could not open configuration file (' . $configFile . ') for writing.</error>');
            $io->write('Please create this file with the following contents: ');
            $io->write($contents);
        } else {
            fwrite($fp, $contents);
            fclose($fp);
        }
    }

    private static function createVarDirectory(IOInterface $io)
    {
        $cacheDir = self::getPath('var/cache');
        $storageDir = self::getPath('var/datastorage');

        $result = true;
        if (!file_exists($cacheDir)) {
            $result = mkdir($cacheDir, 0775, true);
        }

        if (!file_exists($storageDir)) {
            $result = $result && mkdir($storageDir, 0775, true);
        }

        if (!$result) {
            $io->write('<error>Could not create cache and storage directories.</error>');
            $io->write('Please create the following directories and ensure they are writable by the server.');
            $io->write('  ' . $cacheDir);
            $io->write('  ' . $storageDir);
        }
    }
}
