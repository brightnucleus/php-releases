<?php
/**
 * PHPReleasesTest Class
 *
 * @package   brightnucleus/php-releases
 * @author    Alain Schlesser <alain.schlesser@gmail.com>
 * @license   MIT
 * @link      http://www.brightnucleus.com/
 * @copyright 2016 Alain Schlesser, Bright Nucleus
 */

/**
 * Class PHPReleasesTest
 *
 * @since  0.1.0
 *
 * @author Alain Schlesser <alain.schlesser@gmail.com>
 */
class PHPReleasesTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test getting the location of the releases DB.
     *
     * @since  0.1.0
     *
     * @covers PHPReleases::getLocation
     */
    public function testGetLocation()
    {
        $location = PHPReleases::getLocation(false);
        $this->assertInternalType('string', $location);
        $this->assertContains(PHPReleases::DB_FOLDER, $location);
        $locationArray = PHPReleases::getLocation(true);
        $this->assertInternalType('array', $locationArray);
        $this->assertArrayHasKey('folder', $locationArray);
        $this->assertArrayHasKey('file', $locationArray);
        $this->assertContains(PHPReleases::DB_FOLDER, $locationArray['folder']);
        $this->assertEquals(PHPReleases::DB_FILENAME, $locationArray['file']);
    }

    /**
     * Test instantiation.
     *
     * @since  0.1.0
     *
     * @covers PHPReleases::__construct
     */
    public function testInstantiate()
    {
        $releases = new PHPReleases();
        $this->assertInstanceOf('PHPReleases', $releases);
    }

    public function testGetAll()
    {
        $releases = new PHPReleases();
        $array    = $releases->getAll();
        $this->assertInternalType('array', $array);
        $this->assertArrayHasKey('5.0.0', $array);
        $this->assertArrayNotHasKey('5.0.0RC1', $array);
        $this->assertArrayNotHasKey('5.0.0b1', $array);
        $this->assertArrayHasKey('7.0.0', $array);
    }

    /**
     * Test getting release dates.
     *
     * @since  0.1.0
     *
     * @covers PHPReleases::getReleaseDate
     */
    public function testGetReleaseDate()
    {
        $releases = new PHPReleases();
        $this->assertEquals(DateTime::createFromFormat('Y-m-d', '2004-07-13'), $releases->getReleaseDate('5.0.0'));
        $this->assertEquals(DateTime::createFromFormat('Y-m-d', '2015-12-03'), $releases->getReleaseDate('7.0.0'));
        $this->assertFalse($releases->getReleaseDate('6.0.0'));
    }

    /**
     * Test checking for existing versions.
     *
     * @since  0.1.0
     *
     * @covers PHPReleases::exists
     */
    public function testExists()
    {
        $releases = new PHPReleases();
        $this->assertTrue($releases->exists('5.0.0'));
        $this->assertTrue($releases->exists('7.0.0'));
        $this->assertFalse($releases->exists('6.0.0'));
    }
}
