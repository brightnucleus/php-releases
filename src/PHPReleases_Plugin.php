<?php
/**
 * Bright Nucleus PHP Releases.
 *
 * Automatically fetch a list of PHP releases from the official PHP website.
 *
 * @package   brightnucleus/php-releases
 * @author    Alain Schlesser <alain.schlesser@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.brightnucleus.com/
 * @copyright 2016 Alain Schlesser, Bright Nucleus
 */

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;

/**
 * Class PHPReleases_Plugin.
 *
 * This class integrates with Composer to automate downloading and parsing of new release data.
 *
 * @since   0.1.0
 *
 * @package brightnucleus/php-releases
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class PHPReleases_Plugin implements PluginInterface, EventSubscriberInterface
{

    // Web pages that contain the change log HTML.
    const PHP_5_RELEASES = 'http://php.net/ChangeLog-5.php';
    const PHP_7_RELEASES = 'http://php.net/ChangeLog-7.php';

    // Used to skip beta versions and release candidates.
    const SKIP_PATTERN = '/.*[RC|b].*/';

    /**
     * Get the event subscriber configuration for this plugin.
     *
     * @since 0.1.0
     *
     * @return array<string,string> The events to listen to, and their associated handlers.
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_INSTALL_CMD => 'update',
            ScriptEvents::POST_UPDATE_CMD  => 'update',
        );
    }

    /**
     * Update the stored database.
     *
     * @since 0.1.0
     *
     * @param Event $event The event that has called the update method.
     */
    public static function update(Event $event)
    {
        $dbFilename = PHPReleases::getLocation();

        self::maybeCreateDBFolder(dirname($dbFilename));

        $io = $event->getIO();

        $io->write('Fetching change logs from official PHP website...', false);
        $io->write(' PHP5...', false);
        $php5 = self::downloadFile(self::PHP_5_RELEASES);
        $io->write(' PHP7...', false);
        $php7 = self::downloadFile(self::PHP_7_RELEASES);
        $io->write(' done!', true);

        $releases = array();
        $io->write('Parsing change logs to extract versions...', false);
        $io->write(' PHP5...', false);
        $php5Releases = self::parseReleases($php5);
        $io->write('(' . count($php5Releases) . ' releases)', false);
        $releases = array_merge($releases, $php5Releases);
        $io->write(' PHP7...', false);
        $php7Releases = self::parseReleases($php7);
        $io->write('(' . count($php7Releases) . ' releases)', false);
        $releases = array_merge($releases, $php7Releases);
        $io->write(' done!', true);

        ksort($releases, SORT_NATURAL);

        self::generateConfig($releases, $dbFilename);

        $io->write('The PHP Releases database has been updated.', true);
    }

    /**
     * Create the DB folder if it does not exist yet.
     *
     * @since 0.1.0
     *
     * @param string $folder Name of the DB folder.
     */
    protected static function maybeCreateDBFolder($folder)
    {
        $filesystem = new Filesystem();
        $filesystem->ensureDirectoryExists($folder);
    }

    /**
     * Download a file from an URL.
     *
     * @since 0.1.0
     *
     * @return string Downloaded file contents.
     */
    protected static function downloadFile($url)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 600,
            CURLOPT_URL            => $url,
        );

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    /**
     * Parse a PHP change log web page to extract PHP releases.
     *
     * @since 0.1.0
     *
     * @param string $html HTML of a PHP change log web page.
     *
     * @return array Array of strings that represent existing PHP version numbers.
     */
    protected static function parseReleases($html)
    {
        $releases         = array();
        $xmlErrorHandling = libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $nodes = self::getByClass('version', $xpath);
        foreach ($nodes as $node) {
            $version = $node->getAttribute('id');
            if (1 !== preg_match(self::SKIP_PATTERN, $version)) {
                $date      = '';
                $dateNodes = self::getByClass('releasedate', $xpath, $node);
                foreach ($dateNodes as $dateNode) {
                    $date = $dateNode->getAttribute('datetime');
                }
                $releases[$version] = empty($date) ? '' : $date;
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($xmlErrorHandling);

        return (array)$releases;
    }

    /**
     * Get a node list of elements that have a specific class.
     *
     * @since 0.1.0
     *
     * @param string   $class The class to look for.
     * @param DOMXPath $xpath XPath object to look in.
     * @param DOMNode  $node  A node relative to which the search is made. Defaults to root node.
     *
     * @return DOMNodeList
     */
    protected static function getByClass($class, $xpath, $node = null)
    {
        return $xpath->query(
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]",
            $node
        );
    }

    /**
     * Generate a PHP configuration file from the CSV data file.
     *
     * @since 0.1.0
     *
     * @param array  $releases Array of strings representing release version numbers.
     * @param string $phpFile  Path to the PHP file.
     */
    protected static function generateConfig($releases, $phpFile)
    {
        $data = '<?php' . PHP_EOL;
        $data .= '// DO NOT EDIT! This file has been automatically generated.' . PHP_EOL;
        $data .= '// Run composer update to fetch a new version.' . PHP_EOL;
        $data .= 'return array(' . PHP_EOL;
        foreach ($releases as $version => $date) {
            $data .= '   \'' . $version . '\' => \'' . $date . '\',' . PHP_EOL;
        }
        $data .= ');' . PHP_EOL;

        file_put_contents($phpFile, $data);
    }

    /**
     * Activate the plugin.
     *
     * @since 0.1.0
     *
     * @param Composer    $composer The main Composer object.
     * @param IOInterface $io       The i/o interface to use.
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // no action required
    }
}
