<?php

namespace clagiordano\weblibs\configmanager\tests;

use clagiordano\weblibs\configmanager\YamlConfigManager;
use PHPUnit\Framework\TestCase;

/**
 * Class YamlConfigManagerTest
 * @package clagiordano\weblibs\configmanager\tests
 */
class YamlConfigManagerTest extends TestCase
{
    /** @var YamlConfigManager $config */
    private $config = null;
    private $configFile = 'testsdata/sample_config_data.yml';

    public function setUp()
    {
        parent::setUp();

        $this->config = new YamlConfigManager("TestConfigData.yml");
        $this->assertInstanceOf('clagiordano\weblibs\configmanager\YamlConfigManager', $this->config);

        $this->assertFileExists($this->configFile);
        $this->config->loadConfig($this->configFile);
    }

    public function testBasicUsage()
    {
        $this->assertNotNull(
            $this->config->getValue('app')
        );
    }

    public function testFastUsage()
    {
        $this->assertNotNull(
            $this->config->getValue('app')
        );
    }

    public function testFastInvalidKey()
    {
        $this->assertNull(
            $this->config->getValue('invalidKey')
        );
    }

    public function testFastInvalidKeyWithDefault()
    {
        $this->assertEquals(
            $this->config->getValue('invalidKey', 'defaultValue'),
            'defaultValue'
        );
    }

    public function testFastNestedConfig()
    {
        $this->assertNotNull(
            $this->config->getValue('other.multi.deep.nested')
        );
    }

    public function testCheckExistConfig()
    {
        $this->assertTrue(
            $this->config->existValue('other.multi.deep.nested')
        );
    }

    public function testCheckNotExistConfig()
    {
        $this->assertFalse(
            $this->config->existValue('invalid.config.path')
        );
    }

    public function testSetValue()
    {
        $this->config->setValue('other.multi.deep.nested', __FUNCTION__);

        $this->assertEquals(
            $this->config->getValue('other.multi.deep.nested'),
            __FUNCTION__
        );
    }

    public function testFailedSaveConfig()
    {
        $this->setExpectedException('Exception');
        $this->config->saveConfigFile('/invalid/path');
    }

    public function testSuccessSaveConfigOnTempAndReload()
    {
        $this->config->setValue('other.multi.deep.nested', "SUPERNESTED");
        $this->config->saveConfigFile("/tmp/testconfig.yml", true);

        $this->assertEquals(
            $this->config->getValue('other.multi.deep.nested'),
            "SUPERNESTED"
        );
    }

    public function testOverwriteSameConfigFile()
    {
        $this->config->saveConfigFile();
    }

    public function testFailWriteConfig()
    {
        $this->setExpectedException('\RuntimeException');
        $this->config->saveConfigFile('/invalid/path/test.yml');
    }
}