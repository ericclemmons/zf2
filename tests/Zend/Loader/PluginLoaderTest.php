<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Loader
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

namespace ZendTest\Loader;

use \Zend\Loader\Autoloader,
    \Zend\Loader\PluginLoader\PluginLoader;

/**
 * Test class for Zend_Loader_PluginLoader.
 *
 * @category   Zend
 * @package    Zend_Loader
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Loader
 */
class PluginLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $_includeCache;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        if (file_exists($this->_includeCache)) {
            unlink($this->_includeCache);
        }

        // Possible for previous tests to remove autoloader 
        Autoloader::resetInstance();
        $al = Autoloader::getInstance();
        $al->registerPrefix('PHPUnit_');

        PluginLoader::setIncludeFileCache(null);
        $this->_includeCache = __DIR__ . '/_files/includeCache.inc.php';
        $this->libPath = realpath(__DIR__ . '/../../../library');
        $this->key = null;
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->clearStaticPaths();
        PluginLoader::setIncludeFileCache(null);
        if (file_exists($this->_includeCache)) {
            unlink($this->_includeCache);
        }
    }

    public function clearStaticPaths()
    {
        if (null !== $this->key) {
            $loader = new PluginLoader(array(), $this->key);
            $loader->clearPaths();
        }
    }

    public function testAddPrefixPathNonStatically()
    {
        $loader = new PluginLoader();
        $loader->addPrefixPath('Zend_View', $this->libPath . '/Zend/View')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend/Loader')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend');
        $paths = $loader->getPaths();
        $this->assertEquals(2, count($paths));
        $this->assertTrue(array_key_exists('Zend_View_', $paths));
        $this->assertTrue(array_key_exists('Zend_Loader_', $paths));
        $this->assertEquals(1, count($paths['Zend_View_']));
        $this->assertEquals(2, count($paths['Zend_Loader_']));
    }

    public function testAddPrefixPathMultipleTimes()
    {
        $loader = new PluginLoader();
        $loader->addPrefixPath('Zend_Loader', $this->libPath . '/Zend/Loader')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend/Loader');
        $paths = $loader->getPaths();

        $this->assertType('array', $paths);
        $this->assertEquals(1, count($paths['Zend_Loader_']));
    }

    public function testAddPrefixPathStatically()
    {
        $this->key = 'foobar';
        $loader = new PluginLoader(array(), $this->key);
        $loader->addPrefixPath('Zend_View', $this->libPath . '/Zend/View')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend/Loader')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend');
        $paths = $loader->getPaths();
        $this->assertEquals(2, count($paths));
        $this->assertTrue(array_key_exists('Zend_View_', $paths));
        $this->assertTrue(array_key_exists('Zend_Loader_', $paths));
        $this->assertEquals(1, count($paths['Zend_View_']));
        $this->assertEquals(2, count($paths['Zend_Loader_']));
    }

    public function testAddPrefixPathThrowsExceptionWithNonStringPrefix()
    {
        $this->setExpectedException('\\Zend\\Loader\\PluginLoader\\Exception', 'only takes strings');
        $loader = new PluginLoader();
        $loader->addPrefixPath(array(), $this->libPath);
    }

    public function testAddPrefixPathThrowsExceptionWithNonStringPath()
    {
        $this->setExpectedException('\\Zend\\Loader\\PluginLoader\\Exception', 'only takes strings');
        $loader = new PluginLoader();
        $loader->addPrefixPath('Foo_Bar', array());
    }

    public function testRemoveAllPathsForGivenPrefixNonStatically()
    {
        $loader = new PluginLoader();
        $loader->addPrefixPath('Zend_View', $this->libPath . '/Zend/View')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend/Loader')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend');
        $paths = $loader->getPaths('Zend_Loader');
        $this->assertEquals(2, count($paths));
        $loader->removePrefixPath('Zend_Loader');
        $this->assertFalse($loader->getPaths('Zend_Loader'));
    }

    public function testRemoveAllPathsForGivenPrefixStatically()
    {
        $this->key = 'foobar';
        $loader = new PluginLoader(array(), $this->key);
        $loader->addPrefixPath('Zend_View', $this->libPath . '/Zend/View')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend/Loader')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend');
        $paths = $loader->getPaths('Zend_Loader');
        $this->assertEquals(2, count($paths));
        $loader->removePrefixPath('Zend_Loader');
        $this->assertFalse($loader->getPaths('Zend_Loader'));
    }

    public function testRemovePrefixPathThrowsExceptionIfPrefixNotRegistered()
    {
        $this->setExpectedException('\\Zend\\Loader\\PluginLoader\\Exception', 'not found');
        $loader = new PluginLoader();
        $loader->removePrefixPath('Foo_Bar');
    }

    public function testRemovePrefixPathThrowsExceptionIfPrefixPathPairNotRegistered()
    {
        $loader = new PluginLoader();
        $loader->addPrefixPath('Foo_Bar', realpath(__DIR__));
        $paths = $loader->getPaths();
        $this->assertTrue(isset($paths['Foo_Bar_']));
        try {
            $loader->removePrefixPath('Foo_Bar', $this->libPath);
            $this->fail('Removing non-existent prefix/path pair should throw an exception' . "\n" . var_export($loader->getPaths('Foo_Bar'), 1));
        } catch (\Zend\Loader\PluginLoader\Exception $e) {
        }
    }

    public function testClearPathsNonStaticallyClearsPathArray()
    {
        $loader = new PluginLoader();
        $loader->addPrefixPath('Zend_View', $this->libPath . '/Zend/View')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend/Loader')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend');
        $paths = $loader->getPaths();
        $this->assertEquals(2, count($paths));
        $loader->clearPaths();
        $paths = $loader->getPaths();
        $this->assertEquals(0, count($paths));
    }

    public function testClearPathsStaticallyClearsPathArray()
    {
        $this->key = 'foobar';
        $loader = new PluginLoader(array(), $this->key);
        $loader->addPrefixPath('Zend_View', $this->libPath . '/Zend/View')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend/Loader')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend');
        $paths = $loader->getPaths();
        $this->assertEquals(2, count($paths));
        $loader->clearPaths();
        $paths = $loader->getPaths();
        $this->assertEquals(0, count($paths));
    }

    public function testClearPathsWithPrefixNonStaticallyClearsPathArray()
    {
        $loader = new PluginLoader();
        $loader->addPrefixPath('Zend_View', $this->libPath . '/Zend/View')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend/Loader')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend');
        $paths = $loader->getPaths();
        $this->assertEquals(2, count($paths));
        $loader->clearPaths('Zend_Loader');
        $paths = $loader->getPaths();
        $this->assertEquals(1, count($paths));
    }

    public function testClearPathsWithPrefixStaticallyClearsPathArray()
    {
        $this->key = 'foobar';
        $loader = new PluginLoader(array(), $this->key);
        $loader->addPrefixPath('Zend_View', $this->libPath . '/Zend/View')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend/Loader')
               ->addPrefixPath('Zend_Loader', $this->libPath . '/Zend');
        $paths = $loader->getPaths();
        $this->assertEquals(2, count($paths));
        $loader->clearPaths('Zend_Loader');
        $paths = $loader->getPaths();
        $this->assertEquals(1, count($paths));
    }

    public function testGetClassNameNonStaticallyReturnsFalseWhenClassNotLoaded()
    {
        $loader = new PluginLoader();
        $loader->addPrefixPath('Zend_View_Helper', $this->libPath . '/Zend/View/Helper');
        $this->assertFalse($loader->getClassName('FormElement'));
    }

    public function testGetClassNameStaticallyReturnsFalseWhenClassNotLoaded()
    {
        $this->key = 'foobar';
        $loader = new PluginLoader(array(), $this->key);
        $loader->addPrefixPath('Zend_View_Helper', $this->libPath . '/Zend/View/Helper');
        $this->assertFalse($loader->getClassName('FormElement'));
    }

    public function testLoadPluginNonStaticallyLoadsClass()
    {
        $loader = new PluginLoader();
        $loader->addPrefixPath('Zend_View_Helper', $this->libPath . '/Zend/View/Helper');
        try {
            $className = $loader->load('FormButton');
        } catch (Exception $e) {
            $paths = $loader->getPaths();
            $this->fail(sprintf("Failed loading helper; paths: %s", var_export($paths, 1)));
        }
        $this->assertEquals('Zend_View_Helper_FormButton', $className);
        $this->assertTrue(class_exists('Zend_View_Helper_FormButton', false));
        $this->assertTrue($loader->isLoaded('FormButton'));
    }

    public function testLoadPluginStaticallyLoadsClass()
    {
        $this->key = 'foobar';
        $loader = new PluginLoader(array(), $this->key);
        $loader->addPrefixPath('Zend_View_Helper', $this->libPath . '/Zend/View/Helper');
        try {
            $className = $loader->load('FormRadio');
        } catch (Exception $e) {
            $paths = $loader->getPaths();
            $this->fail(sprintf("Failed loading helper; paths: %s", var_export($paths, 1)));
        }
        $this->assertEquals('Zend_View_Helper_FormRadio', $className);
        $this->assertTrue(class_exists('Zend_View_Helper_FormRadio', false));
        $this->assertTrue($loader->isLoaded('FormRadio'));
    }

    public function testLoadThrowsExceptionIfFileFoundInPrefixButClassNotLoaded()
    {
        $this->setExpectedException('\\Zend\\Loader\\PluginLoader\\Exception', 'not found in the registry');
        $loader = new PluginLoader();
        $loader->addPrefixPath('Foo_Helper', $this->libPath . '/Zend/View/Helper');
        $className = $loader->load('Doctype');
    }

    public function testLoadThrowsExceptionIfNoHelperClassLoaded()
    {
        $this->setExpectedException('\\Zend\\Loader\\PluginLoader\\Exception', 'not found in the registry');
        $loader = new PluginLoader();
        $loader->addPrefixPath('Foo_Helper', $this->libPath . '/Zend/View/Helper');
        $className = $loader->load('FooBarBazBat');
    }

    public function testGetClassAfterNonStaticLoadReturnsResolvedClassName()
    {
        $loader = new PluginLoader();
        $loader->addPrefixPath('Zend_View_Helper', $this->libPath . '/Zend/View/Helper');
        try {
            $className = $loader->load('FormSelect');
        } catch (Exception $e) {
            $paths = $loader->getPaths();
            $this->fail(sprintf("Failed loading helper; paths: %s", var_export($paths, 1)));
        }
        $this->assertEquals($className, $loader->getClassName('FormSelect'));
        $this->assertEquals('Zend_View_Helper_FormSelect', $loader->getClassName('FormSelect'));
    }

    public function testGetClassAfterStaticLoadReturnsResolvedClassName()
    {
        $this->key = 'foobar';
        $loader = new PluginLoader(array(), $this->key);
        $loader->addPrefixPath('Zend_View_Helper', $this->libPath . '/Zend/View/Helper');
        try {
            $className = $loader->load('FormCheckbox');
        } catch (Exception $e) {
            $paths = $loader->getPaths();
            $this->fail(sprintf("Failed loading helper; paths: %s", var_export($paths, 1)));
        }
        $this->assertEquals($className, $loader->getClassName('FormCheckbox'));
        $this->assertEquals('Zend_View_Helper_FormCheckbox', $loader->getClassName('FormCheckbox'));
    }

    public function testClassFilesAreSearchedInLifoOrder()
    {
        $loader = new PluginLoader(array());
        $loader->addPrefixPath('Zend_View_Helper', $this->libPath . '/Zend/View/Helper');
        $loader->addPrefixPath('ZfTest', dirname(__FILE__) . '/_files/ZfTest');
        try {
            $className = $loader->load('FormSubmit');
        } catch (Exception $e) {
            $paths = $loader->getPaths();
            $this->fail(sprintf("Failed loading helper; paths: %s", var_export($paths, 1)));
        }
        $this->assertEquals($className, $loader->getClassName('FormSubmit'));
        $this->assertEquals('ZfTest_FormSubmit', $loader->getClassName('FormSubmit'));
    }

    /**
     * @issue ZF-2741
     */
    public function testWin32UnderscoreSpacedShortNamesWillLoad()
    {
        $loader = new PluginLoader(array());
        $loader->addPrefixPath('Zend_Filter', $this->libPath . '/Zend/Filter');
        try {
            // Plugin loader will attempt to load "c:\path\to\library/Zend/Filter/Word\UnderscoreToDash.php"
            $className = $loader->load('Word_UnderscoreToDash');
        } catch (Exception $e) {
            $paths = $loader->getPaths();
            $this->fail(sprintf("Failed loading helper; paths: %s", var_export($paths, 1)));
        }
        $this->assertEquals($className, $loader->getClassName('Word_UnderscoreToDash'));
    }

    /**
     * @group ZF-4670
     */
    public function testIncludeCacheShouldBeNullByDefault()
    {
        $this->assertNull(PluginLoader::getIncludeFileCache());
    }

    /**
     * @group ZF-4670
     */
    public function testPluginLoaderShouldAllowSpecifyingIncludeFileCache()
    {
        $cacheFile = $this->_includeCache;
        $this->testIncludeCacheShouldBeNullByDefault();
        PluginLoader::setIncludeFileCache($cacheFile);
        $this->assertEquals($cacheFile, PluginLoader::getIncludeFileCache());
    }

    /**
     * @group ZF-4670
     */
    public function testPluginLoaderShouldThrowExceptionWhenPathDoesNotExist()
    {
        $this->setExpectedException('\\Zend\\Loader\\PluginLoader\\Exception', 'file does not exist');
        $cacheFile = dirname(__FILE__) . '/_filesDoNotExist/includeCache.inc.php';
        $this->testIncludeCacheShouldBeNullByDefault();
        PluginLoader::setIncludeFileCache($cacheFile);
    }

    /**
     * @group ZF-4670
     */
    public function testPluginLoaderShouldAppendIncludeCacheWhenClassIsFound()
    {
        $cacheFile = $this->_includeCache;
        PluginLoader::setIncludeFileCache($cacheFile);
        $loader = new PluginLoader(array());
        $loader->addPrefixPath('Zend_View_Helper', $this->libPath . '/Zend/View/Helper');
        $loader->addPrefixPath('ZfTest', dirname(__FILE__) . '/_files/ZfTest');
        try {
            $className = $loader->load('CacheTest');
        } catch (Exception $e) {
            $paths = $loader->getPaths();
            $this->fail(sprintf("Failed loading helper; paths: %s", var_export($paths, 1)));
        }
        $this->assertTrue(file_exists($cacheFile));
        $cache = file_get_contents($cacheFile);
        $this->assertContains('CacheTest.php', $cache);
    }

    /**
     * @group ZF-5208
     */
    public function testStaticRegistryNamePersistsInDifferentLoaderObjects()
    {
        $loader1 = new PluginLoader(array(), "PluginLoaderStaticNamespace");
        $loader1->addPrefixPath("Zend_View_Helper", "Zend/View/Helper");

        $loader2 = new PluginLoader(array(), "PluginLoaderStaticNamespace");
        $this->assertEquals(array(
            "Zend_View_Helper_" => array("Zend/View/Helper/"),
        ), $loader2->getPaths());
    }

    /**
     * @group ZF-4697
     */
    public function testClassFilesGrabCorrectPathForLoadedClasses()
    {
        $reflection = new \ReflectionClass('Zend_View_Helper_DeclareVars');
        $expected   = $reflection->getFileName();
        
        $loader = new PluginLoader(array());
        $loader->addPrefixPath('Zend_View_Helper', $this->libPath . '/Zend/View/Helper');
        $loader->addPrefixPath('ZfTest', dirname(__FILE__) . '/_files/ZfTest');
        try {
            // Class in /Zend/View/Helper and not in /_files/ZfTest
            $className = $loader->load('DeclareVars');
        } catch (Exception $e) {
            $paths = $loader->getPaths();
            $this->fail(sprintf("Failed loading helper; paths: %s", var_export($paths, 1)));
        }
        
        $classPath = $loader->getClassPath('DeclareVars');
        $this->assertContains($expected, $classPath);
    }

    /**
     * @group ZF-7350
     */
    public function testPrefixesEndingInBackslashDenoteNamespacedClasses()
    {
        $loader = new PluginLoader(array());
        $loader->addPrefixPath('Zfns\\', dirname(__FILE__) . '/_files/Zfns');
        try {
            $className = $loader->load('Foo');
        } catch (Exception $e) {
            $paths = $loader->getPaths();
            $this->fail(sprintf("Failed loading helper; paths: %s", var_export($paths, 1)));
        }
        $this->assertEquals('Zfns\\Foo', $className);
        $this->assertEquals('Zfns\\Foo', $loader->getClassName('Foo'));
    }
}
