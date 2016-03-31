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

use BrightNucleus_Config as Config;
use BrightNucleus_ConfigInterface as ConfigInterface;

/**
 * Class PHPReleases.
 *
 * @since   0.1.0
 *
 * @package brightnucleus/php-releases
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class PHPReleases
{

    const DB_FILENAME = 'php-releases.php';
    const DB_FOLDER   = 'data';

    /**
     * The configuration data that is queried.
     *
     * @var ConfigInterface
     *
     * @since 0.1.0
     */
    protected $config;

    /**
     * Instantiate a PHPFeature object.
     *
     * @since 0.1.0
     *
     * @param ConfigInterface|null $config Configuration that contains the known features.
     *
     * @throws RuntimeException If the PHP version could not be validated.
     */
    public function __construct(ConfigInterface $config = null)
    {
        if ( ! $config) {
            $config = new Config(include(self::getLocation()));
        }

        $this->config = $config;
    }

    /**
     * Get the location of the database file.
     *
     * @since 0.1.0
     *
     * @param bool $array Optional. Whether to return the location as an array. Defaults to false.
     *
     * @return string|array Either a string, containing the absolute path to the file, or an array with the location
     *                      split up into two keys named 'folder' and 'filename'
     */
    public static function getLocation($array = false)
    {
        $folder   = realpath(dirname(__FILE__) . '/../') . '/' . self::DB_FOLDER;
        $filepath = $folder . '/' . self::DB_FILENAME;
        if ( ! $array) {
            return $filepath;
        }

        return array(
            'folder' => $folder,
            'file'   => self::DB_FILENAME,
        );
    }

    /**
     * Get all the existing PHP release version numbers.
     *
     * @since 0.1.0
     *
     * @return array Array of strings representing PHP release version numbers.
     */
    public function getAll()
    {
        return $this->config->getArrayCopy();
    }

    /**
     * Check whether a specific PHP release version number exists.
     *
     * @since 0.1.0
     *
     * @param string $release String representing a PHP release version number in the format 'X.X.X'.
     *
     * @return bool Whether the requested release exists.
     */
    public function exists($release)
    {
        return $this->config->hasKey($release);
    }

    /**
     * Get the date of a specific release.
     *
     * @since 0.1.0
     *
     * @param string $release String representing a PHP release version number in the format 'X.X.X'.
     *
     * @return DateTime|false Release date of the requested release.
     */
    public function getReleaseDate($release)
    {
        if ( ! $this->config->hasKey($release)) {
            return false;
        }

        return DateTime::createFromFormat('Y-m-d', $this->config->getKey($release));
    }
}
