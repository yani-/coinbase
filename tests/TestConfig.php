<?php namespace Yani\Coinbase\Tests;

class TestConfig extends \PHPUnit_Framework_TestCase {
	/**
	 * Coinbase package config
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * Setting up the tests
	 */
	public function setUp()
	{
		$this->config = include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' .
		                        DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
	}

	/**
	 * Test if package config has API KEY
	 */
	public function testConfigHasApiKey()
	{
		$this->assertTrue(isset($this->config['api_key']));
		$this->assertTrue(empty($this->config['api_key']));
	}

	/**
	 * Test if package config has API SECRET
	 */
	public function testConfigHasApiSecret()
	{
		$this->assertTrue(isset($this->config['api_secret']));
		$this->assertTrue(empty($this->config['api_secret']));
	}

	/**
	 * Test if package config has endpoint
	 */
	public function testConfigHasEndpoint()
	{
		$this->assertTrue(isset($this->config['endpoint']));
		$this->assertTrue(empty($this->config['endpoint']));
	}
}
