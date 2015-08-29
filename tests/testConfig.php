<?php

class TestConfig extends PHPUnit_Framework_TestCase {
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
		$this->config = include_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' .
		                             DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
	}

	/**
	 * Test if package config has all the necessary values
	 */
	public function testConfigHasApiKey()
	{
		$this->assertTrue(isset($this->config['api_key']));
		$this->assertTrue(isset($this->config['endpoint']));
		$this->assertTrue(empty($this->config['api_key']));
		$this->assertTrue(empty($this->config['endpoint']));
	}
}
