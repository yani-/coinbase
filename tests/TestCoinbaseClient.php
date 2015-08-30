<?php namespace Yani\Coinbase\Tests;

use GuzzleHttp\Client as Guzzle;
use Yani\Coinbase\CoinbaseClient;
use Yani\Coinbase\Exceptions\CoinbaseOrderException;

class TestCoinbaseClient extends \PHPUnit_Framework_TestCase {
	/**
	 * Coinbase client
	 *
	 * @var mixed
	 */
	protected $client = null;

	/**
	 * Coinbase endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'https://api.sandbox.coinbase.com';

	/**
	 * GuzzleMock object
	 *
	 * @var mixed
	 */
	protected $guzzleMock = null;

	/**
	 * Payload for creating a new order
	 *
	 * @var array
	 */
	protected $payload = array();

	/**
	 * Order Data
	 *
	 * @var array
	 */
	protected $orderData = array();

	/**
	 * Order amount
	 *
	 * @var float
	 */
	protected $amount = 0;

	/**
	 * Order currency
	 *
	 * @var string
	 */
	protected $currency = '';

	/**
	 * Order name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Order description
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Order metadata
	 *
	 * @var array
	 */
	protected $metadata = '';

	/**
	 * Set up the test group by creating a guzzle mock and CoinbaseClient
	 */
	public function setUp()
	{
		$this->guzzleMock = \Mockery::mock('GuzzleHttp\Client');
		$this->client     = new CoinbaseClient($this->guzzleMock, $this->endpoint);

		$this->amount      = 10;
		$this->currency    = 'USD';
		$this->name        = 'ORDER #1';
		$this->description = '';
		$this->metadata    = array();

		$this->payload = array(
			'body' => json_encode(array(
				'amount'      => $this->amount,
				'currency'    => $this->currency,
				'name'        => $this->name,
				'description' => $this->description,
				'metadata'    => $this->metadata,
			))
		);

		$this->orderData = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'order.json');
		$this->orderData = json_decode($this->orderData);
	}

	/**
	 * Test that the class can be initialized
	 */
	public function testCanBeInitialized()
	{
		$this->assertInstanceOf('Yani\Coinbase\CoinbaseClient', $this->client);
	}

	/**
	 * Test for successful orders
	 */
	public function testCreateOrder()
	{
		$successfulOrderResponseMock = \Mockery::mock('successfulOrderResponseMock')
			->shouldReceive('getStatusCode')->andReturn(201)
			->shouldReceive('getBody')->andReturn($this->orderData)->mock();

		$this->guzzleMock
			->shouldReceive('post')
			->with($this->endpoint, $this->payload)
			->andReturn($successfulOrderResponseMock);

		$this->client = new CoinbaseClient($this->guzzleMock, $this->endpoint);
		$order = $this->client->createOrder($this->amount, $this->currency, $this->name);

		$this->assertEquals($order, $this->orderData->data);
	}

	/**
	 * Test for failed orders
	 */
	public function testCreateOrderFailed()
	{
		$failedOrderResponseMock = \Mockery::mock('failedOrderResponseMock')
			->shouldReceive('getStatusCode')->andReturn(500)
			->shouldReceive('getBody')->andReturn('some error from Coinbase')->mock();

		$this->guzzleMock
			->shouldReceive('post')
			->with($this->endpoint, $this->payload)
			->andReturn($failedOrderResponseMock);

		$this->setExpectedException('Yani\Coinbase\Exceptions\CoinbaseOrderException');

		$order = $this->client->createOrder($this->amount, $this->currency, $this->name);
	}

}
