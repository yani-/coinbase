<?php namespace Yani\Coinbase\Tests;

use GuzzleHttp\Client as Guzzle;
use Yani\Coinbase\CoinbaseClient;
use Yani\Coinbase\CoinbaseServiceProvider;

class TestCoinbaseServiceProvider extends \PHPUnit_Framework_TestCase {

	/**
	 * Test if the class can be instantiated
	 */
	public function testCanbeInstantiated()
	{
		$this->assertInstanceOf('Yani\Coinbase\CoinbaseServiceProvider', new CoinbaseServiceProvider(array()));
	}

	/**
	 * Test boot method
	 */
	public function testBoot()
	{
		$mock = \Mockery::mock('Yani\Coinbase\CoinbaseServiceProvider[package]', array(array()))
			->shouldReceive('package')->with('yani/coinbase')->andReturn('')->mock();
		$mock->boot();
	}

	/**
	 * Test register method
	 */
	public function testRegister()
	{
		$endpoint = 'https://api.sandbox.coinbase.com';
		$appMock = \Mockery::mock('ArrayIterator')->shouldReceive('share')->mock();

		$appMockConfig = \Mockery::mock('config')
			->shouldReceive('get')
			->with('coinbase::endpoint')
			->andReturn($endpoint)
			->mock();

		$this->mockArrayIterator($appMock, array(
			'config'   => $appMockConfig,
			'coinbase' => null,
		));

		$coinbaseServiceProvider = new CoinbaseServiceProvider($appMock);
		$coinbaseServiceProvider->register();
	}

	/**
	 * Test provides method
	 */
	public function testProvides()
	{
		$coinbaseServiceProvider = new CoinbaseServiceProvider(array());
		$this->assertEquals($coinbaseServiceProvider->provides(), array('coinbase'));
	}

	/**
	 * Helper method to extend mock objects with ArrayAccess
	 */
	protected function mockArrayIterator(\Mockery\MockInterface $mock, array $items)
	{
		if ($mock instanceof \ArrayAccess)
		{
			foreach ($items as $key => $val)
			{
				$mock->shouldReceive('offsetGet')->with($key)->andReturn($val);
				$mock->shouldReceive('offsetExists')->with($key)->andReturn(true);
				$mock->shouldReceive('offsetSet')->with($key, $val);
			}
			$mock->shouldReceive('offsetExists')->andReturn(false);
		}
		if ($mock instanceof \Iterator)
		{
			$counter = 0;
			$mock->shouldReceive('rewind')->andReturnUsing(function () use (& $counter) {
				$counter = 0;
			});
			$vals = array_values($items);
			$keys = array_values(array_keys($items));
			$mock->shouldReceive('valid')->andReturnUsing(function () use (& $counter, $vals) {
				return isset($vals[$counter]);
			});
			$mock->shouldReceive('current')->andReturnUsing(function () use (& $counter, $vals) {
				return $vals[$counter];
			});
			$mock->shouldReceive('key')->andReturnUsing(function () use (& $counter, $keys) {
				return $keys[$counter];
			});
			$mock->shouldReceive('next')->andReturnUsing(function () use (& $counter) {
				++$counter;
			});
		}
		if ($mock instanceof \Countable)
		{
			$mock->shouldReceive('count')->andReturn(count($items));
		}
	}
}
